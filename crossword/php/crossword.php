<?php
declare(strict_types = 1);

function readDataFromStdin(): array {
    $table = []; $words = [];
    $fp = fopen('php://stdin', 'rb');
    while (($line = fgets($fp, 4096)) !== false) {
        if (strpos($line, ';') !== false) {
            $words = explode(';', trim($line));
        } else {
            $table[] = str_split(trim($line));
        }
    }
    fclose($fp);
    return [$table, $words];
}

function clearScreen(): void {
    echo "\033[H\033[J";
}

function displayTable(array $table, array $segments, array $words, array $segmentWordPairs, bool $inProgress = false): void {
    static $c = 0;
    echo tableToString(fillTableWithWords($table, $segments, $words, $segmentWordPairs));
    echo PHP_EOL . ($inProgress ? 'Iteration: ' . number_format(++$c) : '') . PHP_EOL;
    if ($inProgress) { echo "\033[" . (count($table) + 2) . 'A'; }
}

function tableToString(array $table): string {
    return array_reduce($table, function (string $c, array $row): string {
        return $c . implode('', $row) . PHP_EOL;
    }, '');
}

function wordsToString(array $words): string {
    return implode(', ', $words);
}

function fillTableWithWords(array $table, array $segments, array $words, array $segmentWordPairs): array {
    foreach ($segmentWordPairs as $sKey => $wKey) {
        [$o, $sr, $sc, $er, $ec] = explode('|', $segments[$sKey]);
        $lc = -1;
        if ($o === 'v') {
            for ($i = $sr; $i <= $er; $i++) { $table[$i][$sc] = $words[$wKey][++$lc]; }
        } else {
            for ($i = $sc; $i <= $ec; $i++) { $table[$sr][$i] = $words[$wKey][++$lc]; }
        }
    }
    return $table;
}

////////////////////////////////////////////////////////////////

function extractSegmentsFromTable(array $table): array {
    $segments = [];
    foreach ($table as $rKey => $row) {
        foreach ($row as $cKey => $elem) {
            if ($elem === '-' && ($vs = getVSegmentOfPoint($table, $rKey, $cKey)) !== null) { $segments[] = $vs; }
            if ($elem === '-' && ($hs = getHSegmentOfPoint($table, $rKey, $cKey)) !== null) { $segments[] = $hs; }
        }
    }
    return array_values(array_unique($segments));
}

function getVSegmentOfPoint(array $table, int $r, int $c): ?string {
    $rTop = $rBottom = $r;
    while (isset($table[$rTop][$c]) && $table[$rTop][$c] === '-') { $rTop--; }
    while (isset($table[$rBottom][$c]) && $table[$rBottom][$c] === '-') { $rBottom++; }

    return $rTop + 1 !== $rBottom - 1
        ? 'v|'.($rTop + 1).'|'.$c.'|'.($rBottom - 1).'|'.$c.'|'.($rBottom - $rTop - 1)
        : null;
}

function getHSegmentOfPoint(array $table, int $r, int $c): ?string {
    $cLeft = $cRight = $c;
    while (isset($table[$r][$cLeft]) && $table[$r][$cLeft] === '-') { $cLeft--; }
    while (isset($table[$r][$cRight]) && $table[$r][$cRight] === '-') { $cRight++; }

    return $cLeft + 1 !== $cRight - 1
        ? 'h|'.$r.'|'.($cLeft + 1).'|'.$r.'|'.($cRight - 1).'|'.($cRight - $cLeft - 1)
        : null;
}

function mapWordLengthsToWords(array $words): array {
    $map = [];
    foreach ($words as $k => $word) {
        $map[strlen($word)][$k] = $word;
    }
    return $map;
}

function mapSegmentsToCrosses(array $segments): array {
    $map = [];
    foreach ($segments as $k1 => $s1) {
        foreach ($segments as $k2 => $s2) {
            if ($k1 !== $k2 && ($cross = getCross($s1, $s2)) !== null) {
                $map[$k1][$k2] = $cross;
            }
        }
    }
    return $map;
}

function getCross(string $segment1, string $segment2): ?array {
    $cross = null;
    [$o1, $sr1, $sc1, $er1, $ec1] = explode('|', $segment1);
    [$o2, $sr2, $sc2, $er2, $ec2] = explode('|', $segment2);
    if ($o1 === 'v' && $o2 === 'h' && $sr1 <= $sr2 && $sr2 <= $er1 && $sc2 <= $sc1 && $sc1 <= $ec2) {
        $cross = [$sr2, $sc1];
    } elseif ($o1 === 'h' && $o2 === 'v' && $sr2 <= $sr1 && $sr1 <= $er2 && $sc1 <= $sc2 && $sc2 <= $ec1) {
        $cross = [$sr1, $sc2];
    }
    return $cross;
}

function solve(array $segments, array $words, array $wordLengthsWordsMap, array $segmentsCrossesMap, array $segmentWordPairs, array $table = null): ?array {
    if (count($segmentWordPairs) > 1 && rejectPosition($segments, $words, $segmentsCrossesMap, $segmentWordPairs)) { return null; }
    if (count($segmentWordPairs) === count($segments)) { return $segmentWordPairs; }

    if ($table !== null) { displayTable($table, $segments, $words, $segmentWordPairs, true); }

    $nextSegmentLength = (int)explode('|', $segments[count($segmentWordPairs)])[5];
    $eligibleWordKeys = array_flip(array_diff_key($wordLengthsWordsMap[$nextSegmentLength], array_flip($segmentWordPairs)));

    while (count($eligibleWordKeys) > 0) {
        $segmentWordPairs[] = array_shift($eligibleWordKeys);
        $solution = solve($segments, $words, $wordLengthsWordsMap, $segmentsCrossesMap, $segmentWordPairs, $table);
        if ($solution !== null) { return $solution; }
        array_pop($segmentWordPairs);
    }
    return null;
}

function rejectPosition(array $segments, array $words, array $segmentsCrossesMap, array $segmentWordPairs): bool {
    $currSegmentKey = count($segmentWordPairs) - 1;
    $currSegmentCrosses = $segmentsCrossesMap[$currSegmentKey];
    foreach ($currSegmentCrosses as $crossedSegmentKey => $cross) {
        if (isset($segmentWordPairs[$currSegmentKey], $segmentWordPairs[$crossedSegmentKey])) {
            [$o1, $sr1, $sc1] = explode('|', $segments[$currSegmentKey]);
            [$o2, $sr2, $sc2] = explode('|', $segments[$crossedSegmentKey]);
            $word1 = $words[$segmentWordPairs[$currSegmentKey]];
            $word2 = $words[$segmentWordPairs[$crossedSegmentKey]];
            $letter1 = $o1 === 'v' ? $word1[$cross[0] - $sr1] : $word1[$cross[1] - $sc1];
            $letter2 = $o2 === 'v' ? $word2[$cross[0] - $sr2] : $word2[$cross[1] - $sc2];

            if ($letter1 !== $letter2) { return true; }
        }
    }
    return false;
}

////////////////////////////////////////////////////////////////

[$table, $words] = readDataFromStdin();

clearScreen();
echo tableToString($table);
echo wordsToString($words) . PHP_EOL . PHP_EOL;

$segments = extractSegmentsFromTable($table);
$solution = solve(
    $segments,
        $words,
        mapWordLengthsToWords($words),
        mapSegmentsToCrosses($segments),
        [],
        (isset($argv[1]) && $argv[1] === 'display') ? $table : null
);

displayTable($table, $segments, $words, $solution);
