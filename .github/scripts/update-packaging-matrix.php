<?php

declare(strict_types=1);

// Refresh `.github/packaging-matrix.json` from real apt availability instead of
// maintaining the per-suite PHP list by hand.
//
// A PHP branch can only be packaged for an Ubuntu suite when `php<ver>-dev` is
// actually installable on that suite's Launchpad build farm. The installable set
// is the union of the Ubuntu archive (main + universe) and the ondrej/php PPA
// (which has no series for every suite — a 404 there simply contributes nothing).
// Intersecting that with the upstream-supported branches in php-versions.json
// (already refreshed from php.net by update-php-support-matrix.php) yields the
// php_versions each suite should build.
//
// Run after update-php-support-matrix.php so both JSON files land in one PR.

$repoRoot = dirname(__DIR__, 2);
$supportFile = $repoRoot . '/.github/php-versions.json';
$matrixFile = $repoRoot . '/.github/packaging-matrix.json';

$support = json_decode((string) file_get_contents($supportFile), true);
if (!is_array($support) || !isset($support['targets']['php']) || !is_array($support['targets']['php'])) {
    fwrite(STDERR, "Failed to read supported PHP branches from $supportFile\n");
    exit(1);
}
$supported = $support['targets']['php'];

$matrix = json_decode((string) file_get_contents($matrixFile), true);
if (!is_array($matrix) || !isset($matrix['suites']) || !is_array($matrix['suites'])) {
    fwrite(STDERR, "Failed to read suites from $matrixFile\n");
    exit(1);
}

/**
 * Return the PHP branches ("8.5", ...) that ship a php<ver>-dev binary in the
 * apt Packages index at $url. A missing index (e.g. ondrej has no series for the
 * suite) returns an empty list rather than failing.
 */
function php_dev_branches(string $url): array
{
    // ignore_errors keeps a 404 (e.g. ondrej has no series for this suite) from
    // emitting a warning: the error body just yields no Package: lines below.
    $context = stream_context_create(['http' => ['timeout' => 30, 'ignore_errors' => true]]);
    $raw = @file_get_contents($url, false, $context);
    if ($raw === false || $raw === '') {
        return [];
    }
    // Only a real gzip stream (magic 1f 8b) gets decoded; a 404 HTML error body
    // is left as-is and simply yields no Package: lines.
    if (str_starts_with($raw, "\x1f\x8b")) {
        $raw = gzdecode($raw);
        if ($raw === false) {
            return [];
        }
    }
    $text = $raw;
    if (preg_match_all('/^Package: php(\d+\.\d+)-dev$/m', $text, $matches) === false) {
        return [];
    }
    return $matches[1];
}

$changed = false;

foreach ($matrix['suites'] as $suite => &$config) {
    $sources = [
        "http://archive.ubuntu.com/ubuntu/dists/$suite/main/binary-amd64/Packages.gz",
        "http://archive.ubuntu.com/ubuntu/dists/$suite/universe/binary-amd64/Packages.gz",
        "https://ppa.launchpadcontent.net/ondrej/php/ubuntu/dists/$suite/main/binary-amd64/Packages.gz",
    ];

    $available = [];
    foreach ($sources as $url) {
        foreach (php_dev_branches($url) as $branch) {
            $available[$branch] = true;
        }
    }

    if ($available === []) {
        // Treat "found nothing anywhere" as a transient fetch failure or an
        // unpublished suite, not as "drop all PHP versions" — keep the existing
        // list so a network blip never proposes an empty packaging matrix.
        fwrite(STDERR, "warn: no php*-dev found for suite '$suite'; keeping existing php_versions\n");
        continue;
    }

    $versions = array_values(array_filter($supported, static fn($v) => isset($available[$v])));
    sort($versions, SORT_NATURAL);

    if (($config['php_versions'] ?? null) !== $versions) {
        $changed = true;
    }
    $config['php_versions'] = $versions;
    echo "suite $suite: " . implode(', ', $versions) . PHP_EOL;
}
unset($config);

$today = (new DateTimeImmutable('now', new DateTimeZone('UTC')))->format('Y-m-d');
if ($changed) {
    $matrix['generated_at'] = $today;
}

$encoded = json_encode($matrix, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
if ($encoded === false) {
    fwrite(STDERR, "Failed to encode updated matrix\n");
    exit(1);
}

// json_encode indents with 4 spaces; the repo's JSON files use 2. Halve the
// leading run on each line so the only diff is real content, not whitespace.
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
    ? "Updated .github/packaging-matrix.json\n"
    : "packaging-matrix.json already up to date\n";
