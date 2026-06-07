<?php

declare(strict_types=1);

$repoRoot = dirname(__DIR__, 2);
$matrixFile = $repoRoot . '/.github/php-versions.json';
$sourceUrl = 'https://www.php.net/supported-versions.php';

$json = file_get_contents($matrixFile);
if ($json === false) {
    fwrite(STDERR, "Failed to read $matrixFile\n");
    exit(1);
}

$matrix = json_decode($json, true);
if (!is_array($matrix)) {
    fwrite(STDERR, "Failed to decode $matrixFile\n");
    exit(1);
}

$html = file_get_contents($sourceUrl);
if ($html === false) {
    fwrite(STDERR, "Failed to download $sourceUrl\n");
    exit(1);
}

libxml_use_internal_errors(true);
$dom = new DOMDocument();
if (!$dom->loadHTML($html)) {
    fwrite(STDERR, "Failed to parse supported versions HTML\n");
    exit(1);
}

$xpath = new DOMXPath($dom);
$rows = $xpath->query('//h3[contains(normalize-space(.), "Currently Supported Versions")]/following-sibling::table[1]//tr');
if ($rows === false || $rows->length === 0) {
    fwrite(STDERR, "Failed to locate supported versions table\n");
    exit(1);
}

$phpVersions = [];
$notes = [];

foreach ($rows as $row) {
    $cells = $xpath->query('./th|./td', $row);
    if ($cells === false || $cells->length < 4) {
        continue;
    }

    $branch = trim($cells->item(0)->textContent);
    if (!preg_match('/^\d+\.\d+$/', $branch)) {
        continue;
    }

    if ((float) $branch < 8.2) {
        continue;
    }

    $activeUntil = trim(preg_replace('/\s+/', ' ', $cells->item(2)->textContent));
    $securityUntil = trim(preg_replace('/\s+/', ' ', $cells->item(3)->textContent));

    $phpVersions[] = $branch;

    $now = new DateTimeImmutable('now', new DateTimeZone('UTC'));
    $activeDate = DateTimeImmutable::createFromFormat('j M Y', preg_replace('/\s+.*$/', '', $activeUntil));
    $securityDate = DateTimeImmutable::createFromFormat('j M Y', preg_replace('/\s+.*$/', '', $securityUntil));

    if ($activeDate instanceof DateTimeImmutable && $activeDate < $now) {
        $notes[$branch] = sprintf(
            'security support only until %s',
            $securityDate instanceof DateTimeImmutable ? $securityDate->format('Y-m-d') : $securityUntil
        );
    } else {
        $notes[$branch] = sprintf(
            'active support until %s',
            $activeDate instanceof DateTimeImmutable ? $activeDate->format('Y-m-d') : $activeUntil
        );
    }
}

sort($phpVersions, SORT_NATURAL);
$matrix['generated_at'] = (new DateTimeImmutable('now', new DateTimeZone('UTC')))->format('Y-m-d');
$matrix['source'] = $sourceUrl;
$matrix['targets']['php'] = array_values($phpVersions);
$matrix['notes'] = $notes;

$encoded = json_encode($matrix, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
if ($encoded === false) {
    fwrite(STDERR, "Failed to encode updated matrix\n");
    exit(1);
}

if (file_put_contents($matrixFile, $encoded) === false) {
    fwrite(STDERR, "Failed to write $matrixFile\n");
    exit(1);
}

echo "Updated .github/php-versions.json with PHP branches: " . implode(', ', $phpVersions) . PHP_EOL;
