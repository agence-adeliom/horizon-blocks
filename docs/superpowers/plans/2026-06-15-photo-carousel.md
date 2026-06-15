# Carrousel photo — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add a new `PhotoCarouselBlock` (slug `photo-carousel`) to the `agence-adeliom/horizon-blocks` package: a full-frame single-image carousel with per-image caption, progress bar, prev/next buttons, and click-to-zoom lightbox.

**Architecture:** Standard Horizon block (PHP class extending `AbstractBlock` + Blade template + TypeScript asset) registered in `HorizonBlockService`. Reuses the existing Swiper + Alpine pattern from `GalleryBlock`. Per-image caption sourced from the WordPress media `caption` field (native `post_excerpt` of the attachment, exposed by ACF in the Gallery sub-array).

**Tech Stack:** PHP 8.2+, Roots Acorn / Sage 11, Extended ACF (`Extended\ACF\Fields\Gallery`), Horizon Tools (`AbstractBlock`, `ContentTab`, `MediaTab`, `LayoutTab`), Blade, Swiper 11, Alpine.js, Tailwind CSS.

**Testing note:** This repo has no automated test suite (per `CLAUDE.md`). Verification is PHP linting (`php -l`) for syntax + final manual visual test in a consumer Sage project via `ddev acorn import:block photo-carousel`. Commits stay small (one per task) so a regression can be isolated.

**Spec:** `docs/superpowers/specs/2026-06-15-photo-carousel-design.md`

---

## File Structure

| Action | Path | Responsibility |
|---|---|---|
| Create | `src/Blocks/Content/PhotoCarouselBlock.php` | Block class — ACF fields, metadata, asset enqueue |
| Modify | `src/Services/HorizonBlockService.php` | Register block in `getAvailableBlocks()` |
| Create | `resources/views/blocks/content/photo-carousel.blade.php` | Blade template — header + slider + lightbox |
| Create | `resources/scripts/blocks/photo-carousel.ts` | Swiper init (Alpine `initPhotoCarousel`) |

---

## Task 1: Create `PhotoCarouselBlock` PHP class

**Files:**
- Create: `src/Blocks/Content/PhotoCarouselBlock.php`

- [ ] **Step 1: Write the block class**

Create `src/Blocks/Content/PhotoCarouselBlock.php` with:

```php
<?php

declare(strict_types=1);

namespace Adeliom\HorizonBlocks\Blocks\Content;

use Adeliom\HorizonBlocks\Concerns\EnqueuesBlockAssets;
use Adeliom\HorizonTools\Blocks\AbstractBlock;
use Adeliom\HorizonTools\Fields\Buttons\ButtonField;
use Adeliom\HorizonTools\Fields\Layout\LayoutField;
use Adeliom\HorizonTools\Fields\Tabs\ContentTab;
use Adeliom\HorizonTools\Fields\Tabs\LayoutTab;
use Adeliom\HorizonTools\Fields\Tabs\MediaTab;
use Adeliom\HorizonTools\Fields\Text\HeadingField;
use Adeliom\HorizonTools\Fields\Text\UptitleField;
use Adeliom\HorizonTools\Fields\Text\WysiwygField;
use Extended\ACF\Fields\Gallery;


class PhotoCarouselBlock extends AbstractBlock
{
    use EnqueuesBlockAssets;

    public const string FIELD_GALLERY = "gallery";
    public static ?string $slug = 'photo-carousel';
    public static ?string $title = 'Carrousel photo';
    public static ?string $mode = 'preview';
    public static ?string $icon = 'format-image';
    public static ?string $description = "Présente une série de photos dans un carrousel plein cadre, avec légende et navigation.";

    public function getFields(): ?iterable
    {
        yield from ContentTab::make()->fields([
            UptitleField::make(),
            HeadingField::make(HeadingField::LABEL, HeadingField::NAME, null, 'h1')->required(),
            WysiwygField::simple(),
            ButtonField::group(),
        ]);

        yield from MediaTab::make()->fields([
            Gallery::make("Photos", self::FIELD_GALLERY)->required(),
        ]);

        yield from LayoutTab::make()->fields([
            LayoutField::margin(),
        ]);
    }

    public function addToContext(): array
    {
        return [];
    }

    public function renderBlockCallback(): void
    {
        $this->enqueueBlockScript('photo-carousel');
    }
}
```

- [ ] **Step 2: Verify PHP syntax**

Run: `ddev exec php -l src/Blocks/Content/PhotoCarouselBlock.php`
Expected: `No syntax errors detected in src/Blocks/Content/PhotoCarouselBlock.php`

- [ ] **Step 3: Commit**

```bash
git add src/Blocks/Content/PhotoCarouselBlock.php
git commit -m "feat(photo-carousel): add PhotoCarouselBlock class"
```

---

## Task 2: Register block in `HorizonBlockService`

**Files:**
- Modify: `src/Services/HorizonBlockService.php`

- [ ] **Step 1: Add the `use` statement**

In `src/Services/HorizonBlockService.php`, add this `use` after the `GalleryBlock` use (keep alphabetical order within `Content`):

```php
use Adeliom\HorizonBlocks\Blocks\Content\PhotoCarouselBlock;
```

The block goes between `GalleryBlock` and `HighlightBlock` alphabetically:
```php
use Adeliom\HorizonBlocks\Blocks\Content\GalleryBlock;
use Adeliom\HorizonBlocks\Blocks\Content\HighlightBlock;
use Adeliom\HorizonBlocks\Blocks\Content\PhotoCarouselBlock;
use Adeliom\HorizonBlocks\Blocks\Content\PostSummaryBlock;
```

- [ ] **Step 2: Add the block entry to `$blocks`**

In `getAvailableBlocks()`, insert this entry **right after** the `GalleryBlock::class` entry:

```php
PhotoCarouselBlock::class => [
    self::REQUIRES_LIVEWIRE => false,
    self::ASSET_FILES => ['resources/scripts/blocks/photo-carousel.ts'],
    self::LIVEWIRE_COMPONENTS => [],
],
```

After insertion, that section of `$blocks` looks like:

```php
GalleryBlock::class => [
    self::REQUIRES_LIVEWIRE => false,
    self::ASSET_FILES => ['resources/scripts/blocks/gallery.ts'],
    self::LIVEWIRE_COMPONENTS => [],
],
PhotoCarouselBlock::class => [
    self::REQUIRES_LIVEWIRE => false,
    self::ASSET_FILES => ['resources/scripts/blocks/photo-carousel.ts'],
    self::LIVEWIRE_COMPONENTS => [],
],
QuoteBlock::class => [
    ...
```

- [ ] **Step 3: Verify PHP syntax**

Run: `ddev exec php -l src/Services/HorizonBlockService.php`
Expected: `No syntax errors detected in src/Services/HorizonBlockService.php`

- [ ] **Step 4: Commit**

```bash
git add src/Services/HorizonBlockService.php
git commit -m "feat(photo-carousel): register block in HorizonBlockService"
```

---

## Task 3: Create the Blade template

**Files:**
- Create: `resources/views/blocks/content/photo-carousel.blade.php`

- [ ] **Step 1: Write the template**

Create `resources/views/blocks/content/photo-carousel.blade.php`:

```blade
@php
	use App\Blocks\Content\PhotoCarouselBlock;
	$btnClass = "awc-theme-dark cursor-pointer -translate-y-1/2 fixed top-1/2 btn--contained flex btn--icon-only btn--primary rounded-full flex-center w-10 h-10 z-20";
	$arrowClass = "awc-theme-dark cursor-pointer btn--contained flex btn--icon-only btn--primary rounded-full flex-center w-12 h-12";
	$gallery = $fields[PhotoCarouselBlock::FIELD_GALLERY] ?? [];
@endphp
<x-block :fields="$fields" :block="$block">
	<div class="grid-12" x-data="initPhotoCarousel()">
		<div class="lg:col-start-3 lg:col-span-8 text-center">
			@if(!empty($fields['uptitle']))
				<x-typography.uptitle :content="$fields['uptitle']" />
			@endif

			@if(!empty($fields['title']))
				<x-typography.heading :fields="$fields['title']" size="3" />
			@endif

			@if(!empty($fields['wysiwyg']))
				<x-typography.text :content="$fields['wysiwyg']" />
			@endif

			@if(!empty($fields['buttons']['one']['link']) || !empty($fields['buttons']['two']['link']))
				<x-action.buttons :buttons="$fields['buttons']"
				                  class="w-fit mx-auto mt-button-text-mobile lg:mt-button-text-desktop" />
			@endif
		</div>

		@if(!empty($gallery))
			<div class="lg:col-start-3 lg:col-span-8 col-span-full mt-10">
				<div x-ref="horizontalContainer" class="swiper">
					<div class="swiper-wrapper">
						@foreach ($gallery as $item)
							<div class="swiper-slide rounded-card overflow-hidden cursor-pointer"
							     @click="open = true; document.body.classList.add('overflow-hidden'); swiper.slideTo( {{ $loop->index }} )">
								<x-media.img :image="$item" class="object-cover w-full h-full absolute"
								             size="full"
								             container-class="relative aspect-[16/9] w-full h-auto" />
							</div>
						@endforeach
					</div>
				</div>

				@if (collect($gallery)->contains(fn($item) => !empty($item['caption'])))
					<div class="mt-2 text-sm italic swiper-caption-container min-h-[1.5em]" x-ref="captionContainer">
						@foreach ($gallery as $item)
							<p class="swiper-caption {{ $loop->first ? '' : 'hidden' }}"
							   data-caption-index="{{ $loop->index }}">
								{{ $item['caption'] ?? '' }}
							</p>
						@endforeach
					</div>
				@endif

				<div class="flex items-center justify-between gap-4 mt-4">
					<div x-ref="progressBar"
					     class="h-1 bg-neutral-300 rounded overflow-hidden flex-1 relative"></div>
					<div class="flex gap-2 shrink-0">
						<div class="{{ $arrowClass }}" x-ref="horizontalPrev" role="button"
						     aria-label="{{ __('Précédent') }}">
							<x-far-chevron-left class="icon-5" />
						</div>
						<div class="{{ $arrowClass }}" x-ref="horizontalNext" role="button"
						     aria-label="{{ __('Suivant') }}">
							<x-far-chevron-right class="icon-5" />
						</div>
					</div>
				</div>
			</div>

			{{-- Lightbox plein écran --}}
			<div x-transition.opacity class="fixed inset-0 z-[999] flex items-center justify-center" x-cloak
			     x-show="open">
				<div class="bg-black cursor-pointer bg-opacity-50 absolute inset-0 z-0"
				     @click="open = false; document.body.classList.remove('overflow-hidden')"></div>
				<div x-ref="swiperContainer"
				     class="swiper aspect-[16/9] w-[90vw] lg:w-[1000px] h-auto flex items-center justify-center">
					<x-action.button
							aria-label="{{__('Fermer le carrousel')}}"
							type="primary"
							class="fixed top-10 right-10 awc-theme-dark z-[100]"
							iconOnly
							@click="open = false; document.body.classList.remove('overflow-hidden')"
					>
						<x-far-xmark />
					</x-action.button>
					<div class="swiper-wrapper">
						@foreach ($gallery as $item)
							<div class="swiper-slide w-full h-full">
								<x-media.img :image="$item" class="max-w-full max-h-full object-contain"
								             size="full"
								             container-class="relative w-full h-full" />
							</div>
						@endforeach
					</div>
					<div class="left-10 {{ $btnClass }}" x-ref="buttonPrev">
						<x-far-chevron-left class="icon-5" />
					</div>
					<div class="right-10 {{ $btnClass }}" x-ref="buttonNext">
						<x-far-chevron-right class="icon-5" />
					</div>
				</div>
			</div>
		@endif
	</div>
</x-block>
```

> **Note:** the `App\Blocks\Content\PhotoCarouselBlock` import uses the `App` namespace because `ImportBlock` rewrites `Adeliom\HorizonBlocks` → `App` when scaffolding into the consumer project (see `gallery.blade.php` for the same pattern).

- [ ] **Step 2: Run prettier on the template**

Run: `ddev exec npx prettier --write resources/views/blocks/content/photo-carousel.blade.php`
Expected: file is reformatted (no errors).

- [ ] **Step 3: Commit**

```bash
git add resources/views/blocks/content/photo-carousel.blade.php
git commit -m "feat(photo-carousel): add blade template"
```

---

## Task 4: Create the TypeScript asset

**Files:**
- Create: `resources/scripts/blocks/photo-carousel.ts`

- [ ] **Step 1: Write the script**

Create `resources/scripts/blocks/photo-carousel.ts`:

```ts
import Swiper from 'swiper';
import { Navigation, Pagination } from 'swiper/modules';
// Uncomment when problem from Swiper lib fixed
// import { SwiperOptions } from "swiper/types";
import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';

document.addEventListener('alpine:init', () => {
    window.Alpine.data('initPhotoCarousel', () => {
        return {
            open: false,
            init() {
                // Lightbox swiper
                // Uncomment when problem from Swiper lib fixed
                // const swiperParams: SwiperOptions = {
                const swiperParams = {
                    modules: [Navigation],
                    slidesPerView: 1,
                    loop: false,
                    navigation: {
                        nextEl: this.$refs.buttonNext,
                        prevEl: this.$refs.buttonPrev,
                        disabledClass: 'opacity-50 pointer-events-none',
                    },
                };
                this.swiper = new Swiper(this.$refs.swiperContainer, swiperParams);

                // Inline slider
                if (this.$refs.horizontalContainer) {
                    const horizontalParams = {
                        modules: [Navigation, Pagination],
                        slidesPerView: 1,
                        spaceBetween: 16,
                        loop: false,
                        navigation: {
                            nextEl: this.$refs.horizontalNext,
                            prevEl: this.$refs.horizontalPrev,
                            disabledClass: 'opacity-50 pointer-events-none',
                        },
                        pagination: {
                            el: this.$refs.progressBar,
                            type: 'progressbar',
                            progressbarFillClass: 'swiper-pagination-progressbar-fill !bg-primary',
                        },
                        on: {
                            slideChange: (sw: any) => {
                                if (!this.$refs.captionContainer) return;
                                const captions = this.$refs.captionContainer.querySelectorAll('.swiper-caption');
                                captions.forEach((el: HTMLElement) => {
                                    const idx = parseInt(el.dataset.captionIndex || '0', 10);
                                    el.classList.toggle('hidden', idx !== sw.realIndex);
                                });
                            },
                        },
                    };
                    this.horizontalSwiper = new Swiper(this.$refs.horizontalContainer, horizontalParams);
                }

                this.$nextTick(() => {
                    this.swiper.init();
                    if (this.horizontalSwiper) {
                        this.horizontalSwiper.init();
                    }
                });
            },
        };
    });
});
```

- [ ] **Step 2: Run prettier on the script**

Run: `ddev exec npx prettier --write resources/scripts/blocks/photo-carousel.ts`
Expected: file reformatted, no errors.

- [ ] **Step 3: Commit**

```bash
git add resources/scripts/blocks/photo-carousel.ts
git commit -m "feat(photo-carousel): add swiper init script"
```

---

## Task 5: Manual verification in a consumer Sage project

> No automated tests exist in this repo (per `CLAUDE.md`). The acceptance gate is a manual smoke test in a consumer Sage 11 project that depends on `agence-adeliom/horizon-blocks`.

**Files:** none (verification only)

- [ ] **Step 1: Point a consumer project at this branch**

In a consumer Sage project, temporarily set the dependency to the current branch:

```bash
ddev composer require agence-adeliom/horizon-blocks:dev-sage/11
```

(Or use a path repository in `composer.json` pointing to this local repo.)

- [ ] **Step 2: Import the block into the consumer project**

```bash
ddev exec wp acorn import:block photo-carousel
```

Expected output: the command lists files copied — `PhotoCarouselBlock.php`, `photo-carousel.blade.php`, `photo-carousel.ts` — and updates the consumer's bud/vite config to include the new TS asset.

- [ ] **Step 3: Build assets in the consumer**

Run the consumer's build (`ddev yarn build` or `ddev yarn dev`). Expected: no TS errors, no Tailwind warnings, build succeeds.

- [ ] **Step 4: Smoke test in WP admin**

1. Edit a page → add the **Carrousel photo** block.
2. Fill: uptitle, title, wysiwyg, 2 buttons (optional), upload at least 3 photos in the Gallery field.
3. In the WP **Media Library**, edit two of those photos and set their **Légende** (caption) field.
4. Save the page.

Verify on the front-end:

- [ ] Block renders header (uptitle, title, paragraph, buttons) centered.
- [ ] Slider shows **one image at a time** at `aspect-[16/9]`.
- [ ] Prev/next buttons navigate; first slide disables Prev, last slide disables Next (no `loop`).
- [ ] Progress bar fills proportionally with current slide index.
- [ ] Caption appears below the image **only for slides where caption is set**, and updates when navigating.
- [ ] Clicking an image opens the lightbox at the right slide; nav buttons + close + overlay-click all work; `body` recovers `overflow` on close.
- [ ] Mobile (< 1024px) layout: slider takes full width, header stacks, captions still visible.
- [ ] No JS console errors.

- [ ] **Step 5: Revert consumer project dependency**

Reset the consumer's `composer.json` to whatever version it had before. This step is just hygiene — no commit on the horizon-blocks side.

---

## Task 6: Add the block to the Notion library

**Files:** none (external)

- [ ] **Step 1: Create the page**

In the Notion **Bibliothèque Horizon Blocks** (https://www.notion.so/adeliom/214da39b354280d098b9f36e9986bf1e?v=214da39b354280f1aa94000ce4efd3ae), create a new entry **Carrousel photo** with:

- Slug: `photo-carousel`
- Category: `Content`
- Description: "Présente une série de photos dans un carrousel plein cadre, avec légende et navigation."
- Screenshot(s) of the block on the front-end (desktop + mobile)
- Link to the Figma source: https://www.figma.com/design/0a283WztDTdVbGtEGAAuao/WOAH-UI?node-id=13133-24303&m=dev
- Import command: `ddev acorn import:block photo-carousel`

- [ ] **Step 2: Done**

No code change. Mark the spec task as resolved in the project tracker if applicable.

---

## Self-review checklist (executed by author — already done)

- ✅ Spec coverage: every spec section maps to a task (PHP class → T1, registry → T2, template → T3, JS → T4, manual test → T5, Notion → T6).
- ✅ No placeholders, no "TBD", every code block is complete and runnable.
- ✅ Type consistency: `FIELD_GALLERY`, `initPhotoCarousel`, `$refs.horizontalContainer / progressBar / swiperContainer / buttonPrev / buttonNext / horizontalPrev / horizontalNext / captionContainer` are used consistently between Blade and TS.
- ✅ Commits are small and independent (4 code commits + 0 commits for manual test/Notion).
