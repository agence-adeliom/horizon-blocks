<?php

declare(strict_types=1);

require __DIR__ . '/../src/Enum/ListingPeriod.php';

use Adeliom\HorizonBlocks\Enum\ListingPeriod;

$failed = false;
function check(string $label, bool $ok): void
{
    echo ($ok ? "PASS" : "FAIL") . " - {$label}\n";
    if (!$ok) {
        $GLOBALS['failed'] = true;
    }
}

// Fixed reference point: Thursday 2026-07-02 15:30:00.
$now = new DateTimeImmutable('2026-07-02 15:30:00');

[$after, $before] = ListingPeriod::TODAY->resolve($now);
check('today after = start of day', $after == new DateTimeImmutable('2026-07-02 00:00:00'));
check('today before = now', $before == $now);

[$after] = ListingPeriod::THIS_WEEK->resolve($now);
check('this-week after = monday 00:00', $after == new DateTimeImmutable('2026-06-29 00:00:00'));

[$after] = ListingPeriod::THIS_MONTH->resolve($now);
check('this-month after = 1st 00:00', $after == new DateTimeImmutable('2026-07-01 00:00:00'));

[$after] = ListingPeriod::THIS_YEAR->resolve($now);
check('this-year after = jan 1 00:00', $after == new DateTimeImmutable('2026-01-01 00:00:00'));

[$after, $before] = ListingPeriod::LAST_7_DAYS->resolve($now);
check('last-7-days after = now-7d', $after == new DateTimeImmutable('2026-06-25 15:30:00'));
check('last-7-days before = now', $before == $now);

[$after] = ListingPeriod::LAST_30_DAYS->resolve($now);
check('last-30-days after = now-30d', $after == new DateTimeImmutable('2026-06-02 15:30:00'));

[$after] = ListingPeriod::LAST_12_MONTHS->resolve($now);
check('last-12-months after = now-12mo', $after == new DateTimeImmutable('2025-07-02 15:30:00'));

[$after, $before] = ListingPeriod::LAST_YEAR->resolve($now);
check('last-year after = prev jan 1', $after == new DateTimeImmutable('2025-01-01 00:00:00'));
check('last-year before = prev dec 31 end', $before == new DateTimeImmutable('2025-12-31 23:59:59'));

check('slugs list matches cases', ListingPeriod::slugs() === [
    'today', 'this-week', 'this-month', 'this-year',
    'last-7-days', 'last-30-days', 'last-12-months', 'last-year',
]);

echo empty($GLOBALS['failed']) ? "\nALL PASS\n" : "\nFAILURES\n";
exit(empty($GLOBALS['failed']) ? 0 : 1);
