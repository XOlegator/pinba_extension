<?php

declare(strict_types=1);

// Refresh `.github/rpm-matrix.json` from real Remi availability, the RPM analog
// of update-packaging-matrix.php for the Debian/PPA track.
//
// A PHP branch can only be packaged for a Copr target where Remi actually ships
// `php<XY>-php-devel`. For each target we resolve the matching Remi repository
// (fedora vs enterprise, by family + releasever parsed from the chroot name),
// read its primary metadata, and keep the versions that are both upstream
// supported (php-versions.json) and present in Remi.
//
// Only `php_versions` (the Remi SCL set) is refreshed here. The base build
// always runs against each chroot's distro-native php-devel, so its
// `base_php_version` is informational only and kept as a documented constant per
// target (it is fixed for a release's lifetime and only changes when a new chroot
// is added, which is already a manual matrix edit) — this avoids pulling Fedora's
// multi-MB Everything metadata on every discovery run. The script preserves it as
// it only rewrites `php_versions`.
//
// Run after update-php-support-matrix.php so all matrices land in one PR.

$repoRoot = dirname(__DIR__, 2);
$supportFile = $repoRoot . '/.github/php-versions.json';
$matrixFile = $repoRoot . '/.github/rpm-matrix.json';

$support = json_decode((string) file_get_contents($supportFile), true);
if (!is_array($support) || !isset($support['targets']['php']) || !is_array($support['targets']['php'])) {
    fwrite(STDERR, "Failed to read supported PHP branches from $supportFile\n");
    exit(1);
}
$supported = $support['targets']['php'];

$matrix = json_decode((string) file_get_contents($matrixFile), true);
if (!is_array($matrix) || !isset($matrix['targets']) || !is_array($matrix['targets'])) {
    fwrite(STDERR, "Failed to read targets from $matrixFile\n");
    exit(1);
}

/**
 * Return the Remi base URL for a Copr chroot, or null if it isn't a known family.
 * e.g. fedora-43-x86_64 -> .../fedora/43/remi/x86_64/, epel-9-x86_64 -> .../enterprise/9/...
 */
function remi_baseurl(string $chroot, string $family): ?string
{
    if (!preg_match('/-(\d+)-/', $chroot, $m)) {
        return null;
    }
    $rel = $m[1];
    if ($family === 'fedora') {
        return "https://rpms.remirepo.net/fedora/$rel/remi/x86_64";
    }
    if ($family === 'enterprise') {
        return "https://rpms.remirepo.net/enterprise/$rel/remi/x86_64";
    }
    return null;
}

/** PHP branches ("8.5", ...) that ship php<XY>-php-devel in the Remi repo at $base. */
function remi_php_dev_branches(string $base): array
{
    $context = stream_context_create(['http' => ['timeout' => 30, 'ignore_errors' => true]]);
    $repomd = @file_get_contents("$base/repodata/repomd.xml", false, $context);
    if ($repomd === false || !preg_match('#href="([^"]*primary\.xml[^"]*)"#', $repomd, $m)) {
        return [];
    }
    $raw = @file_get_contents("$base/" . $m[1], false, $context);
    if ($raw === false || $raw === '') {
        return [];
    }
    if (str_starts_with($raw, "\x42\x5a\x68")) {            // "BZh" -> bzip2
        $raw = function_exists('bzdecompress') ? bzdecompress($raw) : '';
    } elseif (str_starts_with($raw, "\x1f\x8b")) {          // gzip
        $raw = (string) gzdecode($raw);
    }
    if (!is_string($raw) || $raw === '') {
        return [];
    }
    preg_match_all('/name="php(\d)(\d+)-php-devel"/', $raw, $mm, PREG_SET_ORDER);
    $branches = [];
    foreach ($mm as $hit) {
        $branches[$hit[1] . '.' . $hit[2]] = true;
    }
    return array_keys($branches);
}

$changed = false;

foreach ($matrix['targets'] as $chroot => &$config) {
    $family = $config['family'] ?? '';
    $base = remi_baseurl($chroot, $family);
    if ($base === null) {
        fwrite(STDERR, "warn: cannot resolve Remi URL for '$chroot' (family '$family'); skipping\n");
        continue;
    }

    $available = [];
    foreach (remi_php_dev_branches($base) as $branch) {
        $available[$branch] = true;
    }
    if ($available === []) {
        fwrite(STDERR, "warn: no php*-php-devel found for '$chroot'; keeping existing php_versions\n");
        continue;
    }

    $versions = array_values(array_filter($supported, static fn($v) => isset($available[$v])));
    sort($versions, SORT_NATURAL);

    if (($config['php_versions'] ?? null) !== $versions) {
        $changed = true;
    }
    $config['php_versions'] = $versions;
    echo "$chroot: " . implode(', ', $versions) . PHP_EOL;
}
unset($config);

if ($changed) {
    $matrix['generated_at'] = (new DateTimeImmutable('now', new DateTimeZone('UTC')))->format('Y-m-d');
}

$encoded = json_encode($matrix, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
if ($encoded === false) {
    fwrite(STDERR, "Failed to encode updated matrix\n");
    exit(1);
}
// json_encode indents with 4 spaces; the repo's JSON uses 2.
$encoded = preg_replace_callback(
    '/^ +/m',
    static fn($m) => str_repeat(' ', (int) (strlen($m[0]) / 2)),
    $encoded
);

if (file_put_contents($matrixFile, $encoded . PHP_EOL) === false) {
    fwrite(STDERR, "Failed to write $matrixFile\n");
    exit(1);
}

echo $changed
    ? "Updated .github/rpm-matrix.json\n"
    : "rpm-matrix.json already up to date\n";
