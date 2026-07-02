# Listing "Period" Filter (post_date) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add a new Listing filter type "Période" that filters results by publication date (`post_date`) via editor-chosen predefined periods.

**Architecture:** A new `PERIOD` filter type (mirrors the type-less `SEARCH` pattern) whose choices are periods from a code-defined catalogue (`ListingPeriod` enum). At query time the selected period slug resolves to a `[after, before]` `post_date` window applied through a new `DateQuery` class in horizon-querybuilder (mirrors `MetaQuery`/`TaxQuery`). The front reuses the existing `select` appearance, so `filter.blade.php` is untouched.

**Tech Stack:** PHP 8.4, WordPress/ACF (extended-acf `vinkla/extended-acf`), Livewire, Roots Acorn. Three Composer packages + the woah Sage theme.

## Global Constraints

- **Identifiers in English.** User-facing strings via `__('…', 'horizon-blocks')` with **French source strings** (Horizon i18n back-office convention). No hard-coded translated strings.
- **Naming:** PHP class/enum names `PascalCase`; methods/vars `camelCase`; class constant **names** `UPPER_SNAKE_CASE`; constant **values** used as ACF field keys `camelCase`; WP-facing slugs (filter type value, period slugs) lowercase kebab.
- **`declare(strict_types=1);`** at the top of every new PHP file.
- **Indentation:** match each file's existing style — horizon-querybuilder & the `Listing` livewire use **4 spaces**; `ListingBlock.php` uses **tabs**.
- **Branches:** horizon-querybuilder → `main` (`dev-main`); horizon-tools & horizon-blocks → `sage/11` (`dev-sage/11`).
- **Rollout order (dependency-driven):** querybuilder → tools → blocks → theme. `composer update` in the theme only works once each package change is on its tracked branch.
- Package clones live at `/Users/emmadeliom/Projets/{horizon-querybuilder,horizon-tools,horizon-blocks}`. Standalone unit tests run with host `php` (8.4.2).

---

### Task 1: `DateQuery` class (horizon-querybuilder)

**Files:**
- Create: `/Users/emmadeliom/Projets/horizon-querybuilder/src/Database/DateQuery.php`
- Test: `/Users/emmadeliom/Projets/horizon-querybuilder/tests/DateQueryTest.php`

**Interfaces:**
- Produces: `Adeliom\HorizonQueryBuilder\Database\DateQuery` with fluent `column(string='post_date')`, `after(\DateTimeInterface|string|null)`, `before(\DateTimeInterface|string|null)`, `inclusive(bool=true)`, `getQuery(): array` (empty when no bound set), `generateDateQueryArray(): array`.

- [ ] **Step 1: Create feature branch**

```bash
cd /Users/emmadeliom/Projets/horizon-querybuilder
git checkout main && git pull --ff-only origin main
git checkout -b feat/date-query
```

- [ ] **Step 2: Write the failing test**

Create `/Users/emmadeliom/Projets/horizon-querybuilder/tests/DateQueryTest.php`:

```php
<?php

declare(strict_types=1);

require __DIR__ . '/../src/Database/DateQuery.php';

use Adeliom\HorizonQueryBuilder\Database\DateQuery;

function check(string $label, bool $ok): void
{
    echo ($ok ? "PASS" : "FAIL") . " - {$label}\n";
    if (!$ok) {
        $GLOBALS['failed'] = true;
    }
}

// Both bounds → full date_query array with formatted strings.
$dq = (new DateQuery())
    ->after(new DateTimeImmutable('2026-07-01 00:00:00'))
    ->before(new DateTimeImmutable('2026-07-02 23:59:59'));

check('generates column+inclusive+after+before', $dq->generateDateQueryArray() === [
    'column' => 'post_date',
    'inclusive' => true,
    'after' => '2026-07-01 00:00:00',
    'before' => '2026-07-02 23:59:59',
]);

// Open-ended (only after) → no 'before' key.
$dqAfter = (new DateQuery())->after('2026-01-01 00:00:00');
check('omits before when only after set', $dqAfter->generateDateQueryArray() === [
    'column' => 'post_date',
    'inclusive' => true,
    'after' => '2026-01-01 00:00:00',
]);

// Custom column + inclusive(false).
$dqCol = (new DateQuery())->column('post_modified')->inclusive(false)->after('2026-05-01 00:00:00');
check('honours custom column and inclusive(false)', $dqCol->generateDateQueryArray() === [
    'column' => 'post_modified',
    'inclusive' => false,
    'after' => '2026-05-01 00:00:00',
]);

// No bounds → getQuery() empty (so QueryBuilder can skip it).
check('getQuery empty when no bounds', (new DateQuery())->getQuery() === []);
check('getQuery non-empty when a bound is set', $dqAfter->getQuery() !== []);

echo empty($GLOBALS['failed']) ? "\nALL PASS\n" : "\nFAILURES\n";
exit(empty($GLOBALS['failed']) ? 0 : 1);
```

- [ ] **Step 3: Run test to verify it fails**

Run: `php /Users/emmadeliom/Projets/horizon-querybuilder/tests/DateQueryTest.php`
Expected: FAIL — fatal error, `DateQuery.php` does not exist yet (require fails).

- [ ] **Step 4: Write the implementation**

Create `/Users/emmadeliom/Projets/horizon-querybuilder/src/Database/DateQuery.php`:

```php
<?php

declare(strict_types=1);

namespace Adeliom\HorizonQueryBuilder\Database;

class DateQuery
{
    private string $column = 'post_date';
    private ?\DateTimeInterface $after = null;
    private ?\DateTimeInterface $before = null;
    private bool $inclusive = true;

    public function __construct() {}

    public function column(string $column = 'post_date'): self
    {
        $this->column = $column;

        return $this;
    }

    public function after(\DateTimeInterface|string|null $after): self
    {
        $this->after = $this->normalize($after);

        return $this;
    }

    public function before(\DateTimeInterface|string|null $before): self
    {
        $this->before = $this->normalize($before);

        return $this;
    }

    public function inclusive(bool $inclusive = true): self
    {
        $this->inclusive = $inclusive;

        return $this;
    }

    private function normalize(\DateTimeInterface|string|null $value): ?\DateTimeInterface
    {
        if (null === $value || '' === $value) {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value;
        }

        return new \DateTimeImmutable($value);
    }

    public function getQuery(): array
    {
        return array_filter([
            'after' => $this->after,
            'before' => $this->before,
        ], static fn ($bound) => null !== $bound);
    }

    public function generateDateQueryArray(): array
    {
        $query = [
            'column' => $this->column,
            'inclusive' => $this->inclusive,
        ];

        if (null !== $this->after) {
            $query['after'] = $this->after->format('Y-m-d H:i:s');
        }

        if (null !== $this->before) {
            $query['before'] = $this->before->format('Y-m-d H:i:s');
        }

        return $query;
    }
}
```

- [ ] **Step 5: Run test to verify it passes**

Run: `php /Users/emmadeliom/Projets/horizon-querybuilder/tests/DateQueryTest.php`
Expected: `ALL PASS`, exit 0.

- [ ] **Step 6: Commit**

```bash
cd /Users/emmadeliom/Projets/horizon-querybuilder
git add src/Database/DateQuery.php tests/DateQueryTest.php
git commit -m "feat(query): add DateQuery for post_date range filtering"
```

---

### Task 2: `QueryBuilder::addDateQuery()` + args injection (horizon-querybuilder)

**Files:**
- Modify: `/Users/emmadeliom/Projets/horizon-querybuilder/src/Database/QueryBuilder.php` (property list ~line 35; after `addLatLngQuery()` ~line 301; in `getWpQueryArgs()` after the `latLngQueries` block ~line 598)
- Test: `/Users/emmadeliom/Projets/horizon-querybuilder/tests/QueryBuilderDateQueryTest.php`

**Interfaces:**
- Consumes: `DateQuery` from Task 1.
- Produces: `QueryBuilder::addDateQuery(DateQuery $dateQuery): self`; `getWpQueryArgs()` emits `$args['date_query'][]` per added query (with `'relation' => 'AND'` when more than one).

- [ ] **Step 1: Write the failing test**

Create `/Users/emmadeliom/Projets/horizon-querybuilder/tests/QueryBuilderDateQueryTest.php`:

```php
<?php

declare(strict_types=1);

require __DIR__ . '/../src/Database/DateQuery.php';
require __DIR__ . '/../src/Database/QueryBuilder.php';

use Adeliom\HorizonQueryBuilder\Database\DateQuery;
use Adeliom\HorizonQueryBuilder\Database\QueryBuilder;

$failed = false;
function check(string $label, bool $ok): void
{
    echo ($ok ? "PASS" : "FAIL") . " - {$label}\n";
    if (!$ok) {
        $GLOBALS['failed'] = true;
    }
}

// Invoke the private getWpQueryArgs() via reflection (it needs no WordPress runtime).
function argsOf(QueryBuilder $qb): array
{
    $ref = new ReflectionMethod($qb, 'getWpQueryArgs');
    $ref->setAccessible(true);

    return $ref->invoke($qb);
}

// One date query → single date_query entry, no relation key.
$qb = (new QueryBuilder())->postType('post')->addDateQuery(
    (new DateQuery())->after('2026-07-01 00:00:00')->before('2026-07-02 23:59:59')
);
$args = argsOf($qb);
check('date_query present', isset($args['date_query'][0]));
check('date_query content correct', ($args['date_query'][0] ?? null) === [
    'column' => 'post_date',
    'inclusive' => true,
    'after' => '2026-07-01 00:00:00',
    'before' => '2026-07-02 23:59:59',
]);
check('no relation key for single date query', !isset($args['date_query']['relation']));

// Empty DateQuery (no bounds) is skipped.
$qbEmpty = (new QueryBuilder())->postType('post')->addDateQuery(new DateQuery());
check('empty DateQuery skipped', !isset(argsOf($qbEmpty)['date_query']));

// Two date queries → relation AND.
$qb2 = (new QueryBuilder())->postType('post')
    ->addDateQuery((new DateQuery())->after('2026-01-01 00:00:00'))
    ->addDateQuery((new DateQuery())->before('2026-12-31 23:59:59'));
$args2 = argsOf($qb2);
check('two date queries kept', count(array_filter($args2['date_query'], 'is_array')) === 2);
check('relation AND set for multiple', ($args2['date_query']['relation'] ?? null) === 'AND');

echo empty($GLOBALS['failed']) ? "\nALL PASS\n" : "\nFAILURES\n";
exit(empty($GLOBALS['failed']) ? 0 : 1);
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php /Users/emmadeliom/Projets/horizon-querybuilder/tests/QueryBuilderDateQueryTest.php`
Expected: FAIL — `Call to undefined method …QueryBuilder::addDateQuery()`.

- [ ] **Step 3: Add the `$dateQueries` property**

In `/Users/emmadeliom/Projets/horizon-querybuilder/src/Database/QueryBuilder.php`, find:

```php
    private array $latLngQueries = [];
```

Add immediately below:

```php
    private array $dateQueries = [];
```

- [ ] **Step 4: Add the `addDateQuery()` method**

Find the end of `addLatLngQuery()` (the method that throws "At the moment only one LatLngQuery is supported."). Immediately after its closing `}`, add:

```php
    public function addDateQuery(DateQuery $dateQuery): self
    {
        $this->triggerChange();

        if ([] !== $dateQuery->getQuery()) {
            $this->dateQueries[] = $dateQuery;
        }

        return $this;
    }
```

- [ ] **Step 5: Emit `date_query` in `getWpQueryArgs()`**

In `getWpQueryArgs()`, find the `latLngQueries` block:

```php
        if ([] !== $this->latLngQueries) {
            foreach ($this->latLngQueries as $latLngQuery) {
                if ($latLngQuery instanceof LatLngQuery) {
                    $args['lat_lng_query'][] = $latLngQuery->generateLatLngQueryArray();
                }
            }
        }
```

Add immediately after it:

```php
        if ([] !== $this->dateQueries) {
            foreach ($this->dateQueries as $dateQuery) {
                if ($dateQuery instanceof DateQuery) {
                    $args['date_query'][] = $dateQuery->generateDateQueryArray();
                }
            }

            if (count($this->dateQueries) > 1) {
                $args['date_query']['relation'] = 'AND';
            }
        }
```

- [ ] **Step 6: Run test to verify it passes**

Run: `php /Users/emmadeliom/Projets/horizon-querybuilder/tests/QueryBuilderDateQueryTest.php`
Expected: `ALL PASS`, exit 0.

- [ ] **Step 7: Commit, push, open + merge PR**

```bash
cd /Users/emmadeliom/Projets/horizon-querybuilder
git add src/Database/QueryBuilder.php tests/QueryBuilderDateQueryTest.php
git commit -m "feat(query): QueryBuilder::addDateQuery() emits date_query args"
git push -u origin feat/date-query
gh pr create --base main --head feat/date-query \
  --title "feat(query): DateQuery for post_date range filtering" \
  --body "Adds DateQuery (mirrors MetaQuery/TaxQuery) and QueryBuilder::addDateQuery() emitting WP date_query args. Needed by the Listing period filter."
gh pr merge --squash --delete-branch
```

---

### Task 3: `FilterTypesEnum::PERIOD` + `DateQuery` re-export (horizon-tools)

**Files:**
- Modify: `/Users/emmadeliom/Projets/horizon-tools/src/Enum/FilterTypesEnum.php`
- Create: `/Users/emmadeliom/Projets/horizon-tools/src/Database/DateQuery.php`

**Interfaces:**
- Consumes: `Adeliom\HorizonQueryBuilder\Database\DateQuery` (Task 1).
- Produces: `Adeliom\HorizonTools\Enum\FilterTypesEnum::PERIOD` (value `'period'`); `Adeliom\HorizonTools\Database\DateQuery` (thin subclass, matches the `MetaQuery`/`TaxQuery` re-export pattern).

- [ ] **Step 1: Create feature branch**

```bash
cd /Users/emmadeliom/Projets/horizon-tools
git checkout sage/11 && git pull --ff-only origin sage/11
git checkout -b feat/period-filter-support
```

- [ ] **Step 2: Add the `PERIOD` enum case**

In `/Users/emmadeliom/Projets/horizon-tools/src/Enum/FilterTypesEnum.php`, change:

```php
enum FilterTypesEnum: string
{
    case TAXONOMY = 'taxonomy';
    case META = 'meta';
    case SEARCH = 'search';
}
```

to:

```php
enum FilterTypesEnum: string
{
    case TAXONOMY = 'taxonomy';
    case META = 'meta';
    case SEARCH = 'search';
    case PERIOD = 'period';
}
```

- [ ] **Step 3: Create the `DateQuery` re-export**

Create `/Users/emmadeliom/Projets/horizon-tools/src/Database/DateQuery.php` (mirror `MetaQuery.php`):

```php
<?php

declare(strict_types=1);

namespace Adeliom\HorizonTools\Database;

use Adeliom\HorizonQueryBuilder\Database\DateQuery as HorizonDateQuery;

class DateQuery extends HorizonDateQuery
{
}
```

- [ ] **Step 4: Verify the enum + re-export load**

Run:
```bash
php -r 'require "/Users/emmadeliom/Projets/horizon-tools/src/Enum/FilterTypesEnum.php"; echo Adeliom\HorizonTools\Enum\FilterTypesEnum::PERIOD->value, PHP_EOL;'
```
Expected output: `period`

- [ ] **Step 5: Commit, push, open + merge PR**

```bash
cd /Users/emmadeliom/Projets/horizon-tools
git add src/Enum/FilterTypesEnum.php src/Database/DateQuery.php
git commit -m "feat(listing): add PERIOD filter type and DateQuery re-export"
git push -u origin feat/period-filter-support
gh pr create --base sage/11 --head feat/period-filter-support \
  --title "feat(listing): PERIOD filter type + DateQuery re-export" \
  --body "Adds FilterTypesEnum::PERIOD and the Adeliom\\HorizonTools\\Database\\DateQuery re-export for the Listing period filter."
gh pr merge --squash --delete-branch
```

---

### Task 4: `ListingPeriod` catalogue enum (horizon-blocks)

**Files:**
- Create: `/Users/emmadeliom/Projets/horizon-blocks/src/Enum/ListingPeriod.php`
- Test: `/Users/emmadeliom/Projets/horizon-blocks/tests/ListingPeriodTest.php`

**Interfaces:**
- Produces: `Adeliom\HorizonBlocks\Enum\ListingPeriod` (backed string enum) with `label(): string`, `resolve(\DateTimeImmutable $now): array` returning `[?\DateTimeImmutable $after, ?\DateTimeImmutable $before]`, static `slugs(): array`, static `choices(): array` (slug ⇒ label).

- [ ] **Step 1: Create feature branch**

```bash
cd /Users/emmadeliom/Projets/horizon-blocks
git checkout sage/11 && git pull --ff-only origin sage/11
git checkout -b feat/listing-period-filter
```

(Note: the design spec was committed on this branch name earlier; if it already exists locally, `git checkout feat/listing-period-filter` and skip creation.)

- [ ] **Step 2: Write the failing test**

Create `/Users/emmadeliom/Projets/horizon-blocks/tests/ListingPeriodTest.php`:

```php
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
```

- [ ] **Step 3: Run test to verify it fails**

Run: `php /Users/emmadeliom/Projets/horizon-blocks/tests/ListingPeriodTest.php`
Expected: FAIL — fatal error, `ListingPeriod.php` does not exist.

- [ ] **Step 4: Write the implementation**

Create `/Users/emmadeliom/Projets/horizon-blocks/src/Enum/ListingPeriod.php`:

```php
<?php

declare(strict_types=1);

namespace Adeliom\HorizonBlocks\Enum;

enum ListingPeriod: string
{
    case TODAY = 'today';
    case THIS_WEEK = 'this-week';
    case THIS_MONTH = 'this-month';
    case THIS_YEAR = 'this-year';
    case LAST_7_DAYS = 'last-7-days';
    case LAST_30_DAYS = 'last-30-days';
    case LAST_12_MONTHS = 'last-12-months';
    case LAST_YEAR = 'last-year';

    public function label(): string
    {
        return match ($this) {
            self::TODAY => __('Aujourd’hui', 'horizon-blocks'),
            self::THIS_WEEK => __('Cette semaine', 'horizon-blocks'),
            self::THIS_MONTH => __('Ce mois-ci', 'horizon-blocks'),
            self::THIS_YEAR => __('Cette année', 'horizon-blocks'),
            self::LAST_7_DAYS => __('7 derniers jours', 'horizon-blocks'),
            self::LAST_30_DAYS => __('30 derniers jours', 'horizon-blocks'),
            self::LAST_12_MONTHS => __('12 derniers mois', 'horizon-blocks'),
            self::LAST_YEAR => __('L’an dernier', 'horizon-blocks'),
        };
    }

    /**
     * @return array{0: ?\DateTimeImmutable, 1: ?\DateTimeImmutable} [after, before]
     */
    public function resolve(\DateTimeImmutable $now): array
    {
        $year = (int) $now->format('Y');

        return match ($this) {
            self::TODAY => [$now->setTime(0, 0, 0), $now],
            self::THIS_WEEK => [$now->modify('monday this week')->setTime(0, 0, 0), $now],
            self::THIS_MONTH => [$now->modify('first day of this month')->setTime(0, 0, 0), $now],
            self::THIS_YEAR => [$now->setDate($year, 1, 1)->setTime(0, 0, 0), $now],
            self::LAST_7_DAYS => [$now->modify('-7 days'), $now],
            self::LAST_30_DAYS => [$now->modify('-30 days'), $now],
            self::LAST_12_MONTHS => [$now->modify('-12 months'), $now],
            self::LAST_YEAR => [
                $now->setDate($year - 1, 1, 1)->setTime(0, 0, 0),
                $now->setDate($year - 1, 12, 31)->setTime(23, 59, 59),
            ],
        };
    }

    /**
     * @return string[]
     */
    public static function slugs(): array
    {
        return array_map(static fn (self $case) => $case->value, self::cases());
    }

    /**
     * @return array<string, string> slug => label
     */
    public static function choices(): array
    {
        $choices = [];

        foreach (self::cases() as $case) {
            $choices[$case->value] = $case->label();
        }

        return $choices;
    }
}
```

- [ ] **Step 5: Run test to verify it passes**

Run: `php /Users/emmadeliom/Projets/horizon-blocks/tests/ListingPeriodTest.php`
Expected: `ALL PASS`, exit 0.

- [ ] **Step 6: Commit**

```bash
cd /Users/emmadeliom/Projets/horizon-blocks
git add src/Enum/ListingPeriod.php tests/ListingPeriodTest.php
git commit -m "feat(listing): add ListingPeriod catalogue enum"
```

---

### Task 5: ACF fields for the period filter (horizon-blocks)

**Files:**
- Modify: `/Users/emmadeliom/Projets/horizon-blocks/src/Blocks/Listing/ListingBlock.php` (imports; constants ~line 84-96; `getFilterRepeaterFields()` — type-choices block ~line 230-258; forced-filters exclusion ~line 385; add periods field near the taxonomy/meta field group)

**Interfaces:**
- Consumes: `FilterTypesEnum::PERIOD` (Task 3), `ListingPeriod::choices()` (Task 4).
- Produces: constant `ListingBlock::FIELD_FILTERS_PERIODS` (value `'periods'`); a `period` option in the filter Type button group; a multiselect field storing an array of period slugs, shown only when type == period.

*(Indentation in this file is TABS.)*

- [ ] **Step 1: Add imports**

In `/Users/emmadeliom/Projets/horizon-blocks/src/Blocks/Listing/ListingBlock.php`, after the line:

```php
use Adeliom\HorizonBlocks\Concerns\EnqueuesBlockAssets;
```

add:

```php
use Adeliom\HorizonBlocks\Enum\ListingPeriod;
```

- [ ] **Step 2: Add the field-key constant**

Find:

```php
	public const string FIELD_FILTERS_NAME = 'name';
	public const string FIELD_FILTERS_PLACEHOLDER = 'placeholder';
```

Add immediately below:

```php
	public const string FIELD_FILTERS_PERIODS = 'periods';
```

- [ ] **Step 3: Add the `period` type choice**

Find:

```php
		$hasTaxonomy = !in_array(FilterTypesEnum::TAXONOMY, $excludedTypes);
		$hasMeta = !in_array(FilterTypesEnum::META, $excludedTypes);
		$hasSearch = !in_array(FilterTypesEnum::SEARCH, $excludedTypes);
```

Add below it:

```php
		$hasPeriod = !in_array(FilterTypesEnum::PERIOD, $excludedTypes);
```

Then find:

```php
		if ($hasSearch) {
			$typeChoices[FilterTypesEnum::SEARCH->value] = __('Recherche', 'horizon-blocks');
		}
```

Add below it:

```php
		if ($hasPeriod) {
			$typeChoices[FilterTypesEnum::PERIOD->value] = __('Période (date de publication)', 'horizon-blocks');
		}
```

- [ ] **Step 4: Add the periods multiselect field**

Find the search info Message block:

```php
		if ($hasSearch) {
			$filterFields[] = Message::make(__('Recherche', 'horizon-blocks'), 'searchinfo')
				->body(__('Actuellement, seule la recherche dans le titre et dans le contenu de l’élément sont prises en charge.', 'horizon-blocks'))
				->conditionalLogic([
					ConditionalLogic::where(self::FIELD_FILTERS_TYPE, '==', FilterTypesEnum::SEARCH->value)
				]);
```

Immediately after that block's closing `}`, add:

```php
		if ($hasPeriod) {
			$filterFields[] = Select::make(__('Périodes à afficher', 'horizon-blocks'), self::FIELD_FILTERS_PERIODS)
				->stylized()
				->multiple()
				->helperText(__('Sélectionnez les périodes proposées dans le filtre. Le visiteur en choisira une pour filtrer par date de publication.', 'horizon-blocks'))
				->choices(ListingPeriod::choices())
				->conditionalLogic([
					ConditionalLogic::where(self::FIELD_FILTERS_TYPE, '==', FilterTypesEnum::PERIOD->value),
				]);
		}
```

- [ ] **Step 5: Exclude PERIOD from forced filters**

Forced filters are taxonomy-only. Find (around line 385):

```php
					->fields($this->getFilterRepeaterFields(excludedTypes: [FilterTypesEnum::META, FilterTypesEnum::SEARCH], withFilterName: false, withDefaultText: false, withAppearance: false, withTaxonomyValue: true))
```

Change the `excludedTypes` array to also exclude PERIOD:

```php
					->fields($this->getFilterRepeaterFields(excludedTypes: [FilterTypesEnum::META, FilterTypesEnum::SEARCH, FilterTypesEnum::PERIOD], withFilterName: false, withDefaultText: false, withAppearance: false, withTaxonomyValue: true))
```

- [ ] **Step 6: Lint the file**

Run: `php -l /Users/emmadeliom/Projets/horizon-blocks/src/Blocks/Listing/ListingBlock.php`
Expected: `No syntax errors detected`.

- [ ] **Step 7: Commit**

```bash
cd /Users/emmadeliom/Projets/horizon-blocks
git add src/Blocks/Listing/ListingBlock.php
git commit -m "feat(listing): add period filter type and periods field in BO"
```

---

### Task 6: Livewire init — `initPeriodFilter()` (horizon-blocks)

**Files:**
- Modify: `/Users/emmadeliom/Projets/horizon-blocks/src/Livewire/Listing/Listing.php` (imports; new `initPeriodFilter()` method near `initSearchFilter` ~line 136; the string→enum switch ~line 427; the dispatch switch ~line 459)

**Interfaces:**
- Consumes: `FilterTypesEnum::PERIOD`, `ListingPeriod`, `ListingBlock::FIELD_FILTERS_PERIODS`.
- Produces: a `filters`/`secondaryFilters` entry with `type => 'period'`, `appearance => 'select'`, and `choices` = `[['slug' => …, 'name' => …], …]` for the editor-selected periods. Consumed by Task 7's `applyFilters`.

*(Indentation in this file is 4 SPACES.)*

- [ ] **Step 1: Add imports**

In `/Users/emmadeliom/Projets/horizon-blocks/src/Livewire/Listing/Listing.php`, alongside the other `use` statements add:

```php
use Adeliom\HorizonBlocks\Enum\ListingPeriod;
use Adeliom\HorizonTools\Database\DateQuery;
```

(`DateQuery` is used in Task 7 but importing it here keeps the two Livewire tasks' imports together.)

- [ ] **Step 2: Add `initPeriodFilter()`**

Immediately after the `initSearchFilter()` method (its closing `}` before `private function initTaxonomyFilter(`), add:

```php
    private function initPeriodFilter(string $filterName, array $periods, string $label, ?string $placeholder = null, int $level = 1): void
    {
        $workingFilters = match ($level) {
            1 => $this->filters,
            2 => $this->secondaryFilters,
        };

        $choices = [];

        foreach ($periods as $slug) {
            if (!is_string($slug)) {
                continue;
            }

            $period = ListingPeriod::tryFrom($slug);

            if (null !== $period) {
                $choices[] = [
                    self::KEY_SLUG => $period->value,
                    self::KEY_NAME => $period->label(),
                ];
            }
        }

        $workingFilters[$filterName] = [
            'type' => FilterTypesEnum::PERIOD->value,
            'name' => $filterName,
            'appearance' => ListingBlock::VALUE_FILTER_APPEARANCE_SELECT,
            'value' => null,
            'label' => $label,
            'placeholder' => $placeholder,
            'choices' => $choices,
        ];

        switch ($level) {
            case 1:
                $this->filters = $workingFilters;
                break;
            case 2:
                $this->secondaryFilters = $workingFilters;
                break;
            default:
                break;
        }
    }
```

- [ ] **Step 3: Map the `period` type string to the enum**

In `initFiltersByLevel()`, find the string→enum switch:

```php
                    case FilterTypesEnum::SEARCH->value:
                        $type = FilterTypesEnum::SEARCH;
                        break;
                }
```

Change it to:

```php
                    case FilterTypesEnum::SEARCH->value:
                        $type = FilterTypesEnum::SEARCH;
                        break;
                    case FilterTypesEnum::PERIOD->value:
                        $type = FilterTypesEnum::PERIOD;
                        break;
                }
```

- [ ] **Step 4: Dispatch to `initPeriodFilter()`**

In the following dispatch switch, find:

```php
                    case FilterTypesEnum::SEARCH:
                        $this->initSearchFilter(searchName: $name, label: $label, placeholder: $placeholder, level: $level);
                        break;
                    default:
                        break;
                }
```

Change it to:

```php
                    case FilterTypesEnum::SEARCH:
                        $this->initSearchFilter(searchName: $name, label: $label, placeholder: $placeholder, level: $level);
                        break;
                    case FilterTypesEnum::PERIOD:
                        $this->initPeriodFilter(
                            filterName: $name,
                            periods: is_array($filter[ListingBlock::FIELD_FILTERS_PERIODS] ?? null) ? $filter[ListingBlock::FIELD_FILTERS_PERIODS] : [],
                            label: $label,
                            placeholder: $placeholder,
                            level: $level,
                        );
                        break;
                    default:
                        break;
                }
```

- [ ] **Step 5: Lint the file**

Run: `php -l /Users/emmadeliom/Projets/horizon-blocks/src/Livewire/Listing/Listing.php`
Expected: `No syntax errors detected`.

- [ ] **Step 6: Commit**

```bash
cd /Users/emmadeliom/Projets/horizon-blocks
git add src/Livewire/Listing/Listing.php
git commit -m "feat(listing): build period filter choices in Livewire init"
```

---

### Task 7: Livewire apply — `applyPeriodFilter()` (horizon-blocks)

**Files:**
- Modify: `/Users/emmadeliom/Projets/horizon-blocks/src/Livewire/Listing/Listing.php` (the `applyFilters()` foreach ~line 666-679; new `applyPeriodFilter()` helper after `applyFilters()`)

**Interfaces:**
- Consumes: the period filter entry from Task 6 (`type => 'period'`), `DateQuery` (Task 3 re-export), `ListingPeriod::resolve()` (Task 4), `QueryBuilder::addDateQuery()` (Task 2).
- Produces: a `post_date` `date_query` on the QueryBuilder when a non-empty valid period slug is selected.

*(Indentation in this file is 4 SPACES.)*

- [ ] **Step 1: Add the period branch in `applyFilters()`**

Find, inside the `foreach ($filtersToApply as $name => $value)` loop:

```php
            if (!empty($value) && isset($workingFilters[$name])) {
                if ($workingFilters[$name] && isset($workingFilters[$name]['isSearch']) && $workingFilters[$name]['isSearch']) {
                    $qb->search($value);
                } else {
```

Change it to:

```php
            if (!empty($value) && isset($workingFilters[$name])) {
                if ($workingFilters[$name] && isset($workingFilters[$name]['isSearch']) && $workingFilters[$name]['isSearch']) {
                    $qb->search($value);
                } elseif (($workingFilters[$name]['type'] ?? null) === FilterTypesEnum::PERIOD->value) {
                    $this->applyPeriodFilter(qb: $qb, value: $value);
                } else {
```

- [ ] **Step 2: Add the `applyPeriodFilter()` helper**

Immediately after the `applyFilters()` method's closing `}`, add:

```php
    private function applyPeriodFilter(QueryBuilder $qb, mixed $value): void
    {
        // A Choices.js enhanced <select> can report its value through Livewire as
        // ['value' => 'slug'] instead of a bare scalar (same shape as taxonomy
        // single-select). Normalise both forms to a scalar slug.
        if (is_array($value)) {
            $value = $value['value'] ?? null;
        }

        if (!is_string($value) || '' === $value) {
            return;
        }

        $period = ListingPeriod::tryFrom($value);

        if (null === $period) {
            return;
        }

        [$after, $before] = $period->resolve(new \DateTimeImmutable('now', wp_timezone()));

        $qb->addDateQuery(
            (new DateQuery())
                ->after($after)
                ->before($before)
        );
    }
```

- [ ] **Step 3: Lint the file**

Run: `php -l /Users/emmadeliom/Projets/horizon-blocks/src/Livewire/Listing/Listing.php`
Expected: `No syntax errors detected`.

- [ ] **Step 4: Re-run the pure enum test (guard against enum drift)**

Run: `php /Users/emmadeliom/Projets/horizon-blocks/tests/ListingPeriodTest.php`
Expected: `ALL PASS`.

- [ ] **Step 5: Commit, push, open + merge PR**

```bash
cd /Users/emmadeliom/Projets/horizon-blocks
git add src/Livewire/Listing/Listing.php
git commit -m "feat(listing): apply period filter as post_date date_query"
git push -u origin feat/listing-period-filter
gh pr create --base sage/11 --head feat/listing-period-filter \
  --title "feat(listing): period (post_date) filter" \
  --body "New Listing filter type filtering by publication date via editor-chosen predefined periods. Requires horizon-querybuilder DateQuery and horizon-tools FilterTypesEnum::PERIOD. Spec: docs/superpowers/specs/2026-07-02-listing-period-filter-design.md"
gh pr merge --squash --delete-branch
```

---

### Task 8: Consume in the woah theme + verify (woah-regionals-site)

**Files:**
- Modify: `/Users/emmadeliom/Projets/woah-regionals-site/composer.lock` (via `composer update`)
- Modify: `/Users/emmadeliom/Projets/woah-regionals-site/web/app/themes/adeliom/app/Blocks/Listing/ListingBlock.php` (imported copy — apply Task 5 changes)
- Modify: `/Users/emmadeliom/Projets/woah-regionals-site/web/app/themes/adeliom/app/Livewire/Listing/Listing.php` (imported copy — apply Task 6 + Task 7 changes)

**Interfaces:**
- Consumes: everything above via the updated Composer packages. The imported theme copies use `Adeliom\HorizonBlocks\Enum\ListingPeriod` and `Adeliom\HorizonTools\Database\DateQuery` from the packages (do NOT copy the enum into the theme).

- [ ] **Step 1: Update the three packages**

```bash
cd /Users/emmadeliom/Projets/woah-regionals-site
ddev composer update agence-adeliom/horizon-querybuilder agence-adeliom/horizon-tools agence-adeliom/horizon-blocks -W
```
Expected: all three upgraded to their new branch references; "Generating optimized autoload files"; no errors.

- [ ] **Step 2: Confirm `DateQuery` + enum are installed in vendor**

Run:
```bash
cd /Users/emmadeliom/Projets/woah-regionals-site
ls vendor/agence-adeliom/horizon-querybuilder/src/Database/DateQuery.php \
   vendor/agence-adeliom/horizon-blocks/src/Enum/ListingPeriod.php
grep -c "case PERIOD" vendor/agence-adeliom/horizon-tools/src/Enum/FilterTypesEnum.php
```
Expected: both paths listed (no "No such file"); grep prints `1`.

- [ ] **Step 3: Apply the ACF changes to the imported `ListingBlock` copy**

Repeat Task 5 Steps 1-5 on `/Users/emmadeliom/Projets/woah-regionals-site/web/app/themes/adeliom/app/Blocks/Listing/ListingBlock.php` (same edits, same tab indentation). The import references (`Adeliom\HorizonBlocks\Enum\ListingPeriod`, `FilterTypesEnum`) resolve from the packages — no path changes needed.

Then lint:
`ddev exec php -l web/app/themes/adeliom/app/Blocks/Listing/ListingBlock.php`
Expected: `No syntax errors detected`.

- [ ] **Step 4: Apply the Livewire changes to the imported `Listing` copy**

Repeat Task 6 Steps 1-4 and Task 7 Steps 1-2 on `/Users/emmadeliom/Projets/woah-regionals-site/web/app/themes/adeliom/app/Livewire/Listing/Listing.php` (same edits, 4-space indentation).

Then lint:
`ddev exec php -l web/app/themes/adeliom/app/Livewire/Listing/Listing.php`
Expected: `No syntax errors detected`.

- [ ] **Step 5: Register the new front strings for Polylang**

The period labels come from horizon-blocks `.mo` (BO/FR→EN). No theme `pll__` change is required for them. Confirm no new hard-coded Blade strings were introduced (there are none — `filter.blade.php` is unchanged). No action beyond this check.

- [ ] **Step 6: Clear cache**

Run: `ddev wp acorn cache:clear`
Expected: `Application cache cleared successfully.`

- [ ] **Step 7: Manual verification (BO + front)**

1. In the BO, edit the Listing block on `/newsroom/all-news/` (or any Listing). Add a primary filter, set **Type = Période (date de publication)**, and tick a few periods (e.g. *Ce mois-ci*, *Cette année*, *12 derniers mois*). Save.
2. Load `/newsroom/all-news/` and confirm the period dropdown renders with an empty neutral option + the ticked periods.
3. Select *Cette année* → confirm only posts published since Jan 1 of the current year remain; the URL carries the `filtres` param; pagination still works.
4. Select the neutral empty option → confirm all results return.
5. Confirm existing taxonomy/meta/search filters on the page still work (no regression), and the "choice all" select option still behaves.

- [ ] **Step 8: Commit the theme changes**

```bash
cd /Users/emmadeliom/Projets/woah-regionals-site
git add composer.lock \
  web/app/themes/adeliom/app/Blocks/Listing/ListingBlock.php \
  web/app/themes/adeliom/app/Livewire/Listing/Listing.php
git commit -m "feat(listing): period (post_date) filter — sync imported copies + deps"
```

---

## Notes for the implementer

- **Order matters.** Tasks 1-2 (querybuilder) must be merged before Task 3 can be verified end-to-end, and all three packages must be merged to their tracked branches before Task 8's `composer update` pulls them.
- **No WordPress in unit tests.** The pure tests (Tasks 1, 2, 4) run with host `php` and never boot WordPress. Anything WP-coupled (ACF rendering, Livewire wiring) is verified manually in Task 8 Step 7.
- **Timezone:** period ranges are built with `wp_timezone()` so `post_date` comparisons match WordPress' stored/displayed dates.
- **Do not edit `vendor/`** in the theme by hand — Task 8 pulls the packages via Composer.
