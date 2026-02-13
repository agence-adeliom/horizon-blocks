# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Horizon Blocks is a PHP library (Composer package) that provides reusable WordPress/ACF blocks for projects using the Roots Sage theme framework (Sage 11 branch). It integrates WordPress with Laravel via Roots Acorn. Blocks are scaffolded into consumer projects via the `import:block` CLI command.

## Commands

**Toutes les commandes doivent être exécutées via DDEV** pour garantir un environnement cohérent :

```bash
# Format code (PHP, Blade, CSS)
ddev exec npx prettier --write .

# Import a block into a consumer project
ddev exec wp acorn import:block
ddev exec wp acorn import:block {slug}   # Quick import by slug

# Composer
ddev composer install
ddev composer require <package>
```

There is no build step, test suite, or linter configured in this repository. The package is consumed by Sage themes that handle their own build (Bud/Vite).

## Architecture

### Block System

Every block extends `Adeliom\HorizonTools\Blocks\AbstractBlock` (from the `horizon-tools` dependency) and must implement:

- `getFields(): ?iterable` — Yields ACF field definitions using Extended ACF builders
- `addToContext(): array` — Returns data for the Blade template
- `renderBlockCallback(): void` — Enqueues scripts/styles
- `getPostTypes(): ?array` — Restricts to specific post types (optional)

Block classes live in `src/Blocks/{Category}/` with categories: Action, Content, Hero, Listing, Reassurance. Each has a corresponding Blade template at `resources/views/blocks/{category}/{slug}.blade.php`.

### Block Registry

`HorizonBlockService::getAvailableBlocks()` is the central registry mapping every block class to its metadata: required assets, Livewire components, view components, post types, and admin classes. **Any new block must be registered here.**

### Import Command

`ImportBlock` (`src/Console/Commands/`) scaffolds a block into the consumer's project by:
1. Copying the block class and template (rewriting namespace `Adeliom\HorizonBlocks` → `App`)
2. Copying associated view components, Livewire components, admin classes
3. Copying TypeScript/CSS assets and registering them in the project's build config (Bud or Vite)
4. Importing required post types via `horizon-posttypes`

### View Components

Blade components in `src/View/Components/` extend `Illuminate\View\Component`. They are registered under the `horizon` prefix via the service provider:
```
<x-horizon:component-name />
```

### Livewire Components

Located in `src/Livewire/`. The `Listing` component is the most complex — it handles filterable, paginated post listings with URL-synced state (`#[Url]` attributes), taxonomy/meta query building, and inner card injection via `ListingInnerCardViewModel`.

### Admin Classes

Extend `AbstractAdmin` from horizon-tools. Define ACF field groups for post edit screens (e.g., `PostSummaryAdmin` adds summary configuration to the post sidebar).

## Conventions

- **Strict types**: All PHP files use `declare(strict_types=1)`
- **Field names as constants**: Block field names are class constants (`FIELD_GALLERY`, `FIELD_IS_TOP`), never raw strings in templates
- **Naming**: Block classes end with `Block` suffix, admin classes with `Admin`, slugs are kebab-case
- **ACF fields use tab organization** for complex blocks: `ContentTab`, `MediaTab`, `LayoutTab`
- **Blade templates** reference fields via `$fields[BlockClass::CONSTANT]` and wrap content in `<x-block :fields="$fields" :block="$block">`
- **Alpine.js** for client-side interactivity, **Tailwind CSS** for styling
- **Prettier** for formatting (PHP, Blade, Tailwind) — config in `.prettierrc.json`

## Dependencies

- `horizon-tools` — Base classes (`AbstractBlock`, `AbstractAdmin`, services)
- `horizon-posttypes` — Custom post type definitions
- `horizon-querybuilder` — Query builder for listings
- All three are internal Adeliom packages on `dev-sage/11` or `dev-main` branches

## Key Files

- `src/Services/HorizonBlockService.php` — Block registry (edit when adding/removing blocks)
- `src/Providers/HorizonBlocksServiceProvider.php` — Package bootstrap
- `src/Console/Commands/ImportBlock.php` — Block scaffolding command
