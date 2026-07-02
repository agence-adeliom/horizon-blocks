# Listing "Period" filter (post_date) — Design

**Date:** 2026-07-02
**Status:** Approved (design), pending implementation plan
**Packages:** horizon-querybuilder, horizon-tools, horizon-blocks (+ woah theme re-sync)

## 1. Goal

Add a new **filter type** to the Listing block that lets a front-end visitor
narrow results by **publication date** (`post_date`), by picking a
**predefined period** (e.g. "This month", "This year") from a dropdown.

The editor configures, per filter, **which periods** from a standard catalogue
are offered. This is not a calendar/datepicker widget — it is a period selector
whose choices resolve to a `post_date` range.

### Decisions locked during brainstorming

- Filters on **`post_date`** (publication date), not on an ACF/meta date field.
- Selection is via **predefined periods**, not a free calendar range.
- The catalogue of periods is defined in code; the **editor picks which ones to
  display** for a given filter.
- Implementation: a **dedicated new filter type** `PERIOD` (mirrors the existing
  type-less `SEARCH` pattern), plus a **dedicated `DateQuery` class** in
  horizon-querybuilder (mirrors `MetaQuery`/`TaxQuery`/`LatLngQuery`).

## 2. Scope (4 targets)

| Package | Branch | Change |
|---|---|---|
| horizon-querybuilder | `main` (`dev-main`) | `DateQuery` class + `QueryBuilder::addDateQuery()` + `$args['date_query']` in `getWpQueryArgs()` |
| horizon-tools | `sage/11` (`dev-sage/11`) | `FilterTypesEnum::PERIOD = 'period'` + thin re-export `Database\DateQuery` |
| horizon-blocks | `sage/11` (`dev-sage/11`) | ACF fields, period catalogue, Livewire `initPeriodFilter()` + `applyFilters` date branch |
| woah theme | — | `composer update` the 3 packages + re-sync imported copies `App\Blocks\Listing\ListingBlock` and `App\Livewire\Listing\Listing` |

Rollout order follows dependencies: **querybuilder → tools → blocks → theme**.

## 3. Architecture

### 3.1 `DateQuery` (horizon-querybuilder)

New class `Adeliom\HorizonQueryBuilder\Database\DateQuery`, structured like the
existing query classes.

Public API:
- `column(string $column = 'post_date'): self` — target column (default `post_date`).
- `after(\DateTimeInterface|string|null $after): self` — lower bound.
- `before(\DateTimeInterface|string|null $before): self` — upper bound.
- `inclusive(bool $inclusive = true): self` — inclusive bounds (default true).
- `generateDateQueryArray(): array` — returns one WP `date_query` sub-array,
  e.g. `['column' => 'post_date', 'after' => '2026-07-01 00:00:00', 'before' => '2026-07-02 23:59:59', 'inclusive' => true]`.
  Omits `after`/`before` keys when the corresponding bound is null (open-ended range).

`QueryBuilder` additions:
- private `array $dateQueries = [];`
- `addDateQuery(DateQuery $dateQuery): self` — appends to `$dateQueries`.
- in `getWpQueryArgs()`: `foreach ($this->dateQueries as $dq) { $args['date_query'][] = $dq->generateDateQueryArray(); }`.
  If more than one date query is added, also set `$args['date_query']['relation'] = 'AND'`
  (default). (Multiple date queries is not used by the period filter today, but
  the API stays consistent with `MetaQuery`/`TaxQuery`.)

Thin re-export in horizon-tools:
`Adeliom\HorizonTools\Database\DateQuery extends Adeliom\HorizonQueryBuilder\Database\DateQuery {}`.

### 3.2 Period catalogue (horizon-blocks)

New backed enum `Adeliom\HorizonBlocks\Enum\ListingPeriod: string` (or a small
service if an enum proves awkward with closures). Each case exposes:
- the **slug** (enum value),
- a `label(): string` returning the i18n label (`__('…', 'horizon-blocks')`),
- a `resolve(\DateTimeImmutable $now): array` returning `[?\DateTimeImmutable $after, ?\DateTimeImmutable $before]`.

`$now` is injected (not read inside via a disallowed clock call) so the resolver
stays pure/testable.

Catalogue (approved):

| slug | label (FR source) | range |
|---|---|---|
| `today` | Aujourd'hui | start of today → now |
| `this-week` | Cette semaine | Monday 00:00 → now |
| `this-month` | Ce mois-ci | 1st of month 00:00 → now |
| `this-year` | Cette année | Jan 1 00:00 → now |
| `last-7-days` | 7 derniers jours | now − 7 days → now |
| `last-30-days` | 30 derniers jours | now − 30 days → now |
| `last-12-months` | 12 derniers mois | now − 12 months → now |
| `last-year` | L'an dernier | Jan 1 → Dec 31 of previous year |

Bounds are inclusive. All periods except `last-year` end at "now"; `last-year`
is a fully closed previous-calendar-year range.

### 3.3 ACF fields (`ListingBlock`)

- Add `period` to the filter **type** choices (the field currently offering
  meta / taxonomy / search): label `__('Période (date de publication)', 'horizon-blocks')`.
- New field `FIELD_FILTERS_PERIODS` (constant value `'periods'`): a **multi-value
  Select** listing the catalogue (`ListingPeriod` cases → slug ⇒ label). The
  editor ticks which periods to display. `conditionalLogic` = shown when the
  filter type == `period`.
- The meta-field selector and taxonomy selector are hidden for the `period` type
  (their existing conditional logic already targets meta/taxonomy types, so no
  change needed there).

Naming: constant names `UPPER_SNAKE_CASE`; ACF field-key **values** in
`camelCase` (project convention). Filter type value `period` (WP-facing slug, lowercase).

### 3.4 Livewire (`Listing`)

- `initFiltersByLevel()`: add a `case FilterTypesEnum::PERIOD` that calls a new
  `initPeriodFilter()`.
- `initPeriodFilter()`: builds `$workingFilters[$name]` with
  `type => 'period'`, `appearance => VALUE_FILTER_APPEARANCE_SELECT`, `name`,
  `label`, `placeholder`, and `choices` = the editor-selected presets as
  `[{slug, name}]` (name = `ListingPeriod::from($slug)->label()`). No DB query.
- `applyFilters()`: add a branch handling `type == period`. When a non-empty
  period slug is selected, resolve it via `ListingPeriod::from($slug)->resolve($now)`
  and call `$qb->addDateQuery((new DateQuery())->after($after)->before($before))`.
  A neutral/empty selection applies no date query (all dates).
- Reset: handled by the existing `resetFilters()` / `resetByModelName()`
  (period filters live in `filterFields` like the others).

### 3.5 Rendering (`filter.blade.php`)

**No change.** Period filters reuse the existing `select` appearance branch
(their `choices` are `{slug, name}` like any other select). The default empty
`<option>` acts as the neutral "all dates" choice.

(Radio/other appearances for periods are intentionally out of scope; can be
added later by exposing an appearance sub-field for the period type.)

## 4. i18n

- All BO labels and period labels go through `__('…', 'horizon-blocks')` with
  **French source strings** (consistent with the Horizon i18n back-office
  chantier). Period labels reach the front translated via horizon-blocks `.mo`.
- No hard-coded translated strings.

## 5. Testing / verification

These packages have no automated test suite. Strategy:
- The **pure** units — `DateQuery::generateDateQueryArray()` and each
  `ListingPeriod::resolve()` — are written to be side-effect-free (inject `$now`),
  so they can be exercised by a lightweight standalone assertion script if
  desired.
- **Manual verification** on the woah site: add a `period` filter to the
  `/newsroom/all-news/` Listing block, select each period, confirm the result set
  matches the expected `post_date` window (and that the neutral option shows
  everything).
- Verify no regression on existing meta/taxonomy/search filters and on the
  recently added "choice all" for select.

## 6. Risks / notes

- **Imported copies:** the woah theme runs its own imported `ListingBlock` and
  `Listing` livewire. After the packages ship, both copies must be re-synced
  (they will not update via `composer update`, only the vendor package does).
  The `filter.blade.php` (vendor, not imported) needs no change here.
- **Cache:** period ranges are computed from "now" at request time. If a filter
  uses `useCache`, the query hash already serializes the args (which include the
  resolved dates), so different windows produce different cache keys — acceptable.
- **Timezone:** ranges are computed in WordPress' configured timezone (use
  `wp_timezone()` when building `$now`) so `post_date` comparisons line up with
  how WordPress stores/displays dates.
- **Multiple date queries relation** defaults to AND; only one is used by the
  period filter today.

## 7. Out of scope

- Free calendar/datepicker range selection.
- Filtering on ACF/meta date fields (only `post_date`).
- Radio/checkbox/multiselect appearances for the period type.
- Editor-defined custom periods (label + arbitrary range).
