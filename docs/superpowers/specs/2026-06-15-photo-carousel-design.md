# Carrousel photo — Design

**Date** : 2026-06-15
**Auteur** : Emma Louviot
**Source design** : [Figma — WOAH UI, Slider-03](https://www.figma.com/design/0a283WztDTdVbGtEGAAuao/WOAH-UI?node-id=13133-24303&m=dev)

## Objectif

Ajouter au package `agence-adeliom/horizon-blocks` un nouveau bloc ACF **Carrousel photo** : un slider plein cadre qui affiche **une image à la fois**, avec **légende par image**, **barre de progression** et **navigation prev/next**. Le clic sur une image ouvre une **lightbox plein écran** (Swiper) — pattern déjà utilisé par `GalleryBlock`.

Ce bloc complète `GalleryBlock` (galerie en grille ou strip multi-images sans légende individuelle) en couvrant le cas d'usage *story photo / portfolio guidé*, où chaque visuel mérite sa propre légende et un affichage large.

## Positionnement par rapport aux blocs existants

| Bloc | Affichage | Légende | Lightbox |
|---|---|---|---|
| `GalleryBlock` (mosaic) | Grille (2/3/4 col selon nb d'items) | ❌ | ✅ |
| `GalleryBlock` (slider) | Strip horizontal (1.2 → 3.2 slides visibles) | ❌ | ✅ |
| **`PhotoCarouselBlock` (nouveau)** | **1 slide à la fois, plein cadre** | **✅ via `caption` média** | **✅** |

## Architecture

### Fichiers créés

| Chemin | Rôle |
|---|---|
| `src/Blocks/Content/PhotoCarouselBlock.php` | Classe du bloc — fields ACF + context |
| `resources/views/blocks/content/photo-carousel.blade.php` | Template Blade (header + slider + lightbox) |
| `resources/scripts/blocks/photo-carousel.ts` | Init Swiper Alpine (slider + lightbox) |

### Fichier modifié

| Chemin | Modification |
|---|---|
| `src/Services/HorizonBlockService.php` | Ajout d'`PhotoCarouselBlock::class` dans le registre |

### Métadonnées du bloc

```php
public static ?string $slug = 'photo-carousel';
public static ?string $title = 'Carrousel photo';
public static ?string $mode = 'preview';
public static ?string $icon = 'format-image';
public static ?string $description = "Présente une série de photos dans un carrousel plein cadre, avec légende et navigation.";
```

## Champs ACF

Organisation en 3 tabs (cohérent avec `GalleryBlock`, `TextMediaBlock`, etc.).

### `ContentTab`
- `UptitleField::make()` — surtitre optionnel
- `HeadingField::make(HeadingField::LABEL, HeadingField::NAME, null, 'h1')->required()` — titre (default `h1`, modifiable par le rédacteur)
- `WysiwygField::simple()` — paragraphe d'introduction
- `ButtonField::group()` — 2 boutons CTA optionnels

### `MediaTab`
- `Gallery::make("Photos", FIELD_GALLERY)->required()` — champ Gallery ACF natif, identique au `GalleryBlock`

> **Note rédacteur** : la légende affichée sous chaque slide vient du champ **"Légende"** (`caption`) renseigné par image dans la médiathèque WordPress. Pas de champ ACF dédié — on s'appuie sur la métadonnée native du média (`post_excerpt` de l'attachment).

### `LayoutTab`
- `LayoutField::margin()`

### Constante

```php
public const string FIELD_GALLERY = "gallery";
```

## Template Blade — structure

```
<x-block> avec wrapper x-data="initPhotoCarousel()"
└─ div.grid-12
   ├─ Header (col-start-3 col-span-8, text-center)
   │   ├─ <x-typography.uptitle>      si fields.uptitle
   │   ├─ <x-typography.heading size="3"> si fields.title
   │   ├─ <x-typography.text>          si fields.wysiwyg
   │   └─ <x-action.buttons class="mx-auto"> si fields.buttons.one|two.link
   │
   ├─ Slider inline (col-start-3 col-span-8 mt-10)
   │   si !empty(fields.gallery)
   │   ├─ swiper container — x-ref="horizontalContainer", classe "swiper"
   │   │   └─ swiper-wrapper
   │   │       └─ foreach gallery as item :
   │   │           swiper-slide rounded-card overflow-hidden cursor-pointer
   │   │           @click="open = true; document.body.classList.add('overflow-hidden');
   │   │                   swiper.slideTo({{ $loop->index }})"
   │   │           ├─ <x-media.img aspect-[16/9]>
   │   │           └─ <p class="caption italic text-sm mt-2"> si item.caption
   │   │
   │   └─ Nav row — flex items-center justify-between gap-4 mt-2
   │       ├─ progress bar — x-ref="progressBar"
   │       │   div.h-1.bg-neutral-300.rounded.flex-1.overflow-hidden
   │       │   (Swiper applique son span de fill .swiper-pagination-progressbar-fill)
   │       └─ nav buttons — flex gap-2
   │           ├─ button x-ref="horizontalPrev" — rounded-full bg-primary w-12 h-12 flex-center
   │           │   └─ <x-far-chevron-left class="icon-5" />
   │           └─ button x-ref="horizontalNext" — idem
   │               └─ <x-far-chevron-right class="icon-5" />
   │
   └─ Lightbox popin (identique à gallery.blade.php)
       fixed inset-0 z-[999], x-show="open", x-cloak
       ├─ overlay bg-black/50 + click→close
       └─ swiper container — x-ref="swiperContainer"
           ├─ bouton close (x-far-xmark, fixed top-10 right-10)
           ├─ swiper-wrapper avec foreach gallery items (full size, object-contain)
           └─ buttons prev/next : x-ref="buttonPrev" / "buttonNext"
```

### Détails clés

- **Aspect ratio** : `aspect-[16/9]` sur le container de chaque image inline.
- **Lightbox** : `<x-media.img size="full" object-contain>` (pas object-cover, on veut voir l'image entière).
- **Caption** : `$item['caption']` (clé standard de l'array retourné par le sous-champ image d'une Gallery ACF). Masqué si vide.
- **Boutons** : classes `awc-theme-dark btn--contained btn--primary btn--icon-only rounded-full flex-center w-12 h-12` cohérentes avec le pattern utilisé dans `gallery.blade.php`.
- **Hover effect** : on n'embarque PAS l'effet "rond qui scale" du `GalleryBlock` mosaic — le Figma ne le montre pas pour ce bloc.

## JavaScript — `photo-carousel.ts`

Reprend la structure de `gallery.ts` (Alpine + deux instances Swiper). Différences :
- Le slider inline a `slidesPerView: 1` (vs `1.2 → 3.2` pour Gallery).
- Le slider inline utilise `pagination: { type: 'progressbar' }`.
- La lightbox est identique à celle de Gallery (slidesPerView: 1, navigation).

```ts
import Swiper from 'swiper';
import { Navigation, Pagination } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';

document.addEventListener('alpine:init', () => {
    window.Alpine.data('initPhotoCarousel', () => ({
        open: false,
        init() {
            // Lightbox plein écran
            this.swiper = new Swiper(this.$refs.swiperContainer, {
                modules: [Navigation],
                slidesPerView: 1,
                loop: false,
                navigation: {
                    nextEl: this.$refs.buttonNext,
                    prevEl: this.$refs.buttonPrev,
                    disabledClass: 'opacity-50 pointer-events-none',
                },
            });

            // Slider inline
            if (this.$refs.horizontalContainer) {
                this.horizontalSwiper = new Swiper(this.$refs.horizontalContainer, {
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
                        progressbarFillClass: 'bg-primary swiper-pagination-progressbar-fill',
                    },
                });
            }

            this.$nextTick(() => {
                this.swiper.init();
                if (this.horizontalSwiper) this.horizontalSwiper.init();
            });
        },
    }));
});
```

## Enregistrement dans `HorizonBlockService`

Ajouter dans le tableau `$blocks` de `getAvailableBlocks()`, à côté de `GalleryBlock::class` :

```php
PhotoCarouselBlock::class => [
    self::REQUIRES_LIVEWIRE => false,
    self::ASSET_FILES => ['resources/scripts/blocks/photo-carousel.ts'],
    self::LIVEWIRE_COMPONENTS => [],
],
```

+ le `use Adeliom\HorizonBlocks\Blocks\Content\PhotoCarouselBlock;` en tête de fichier.

## Conventions respectées

- `declare(strict_types=1)` sur le PHP
- Noms de champs en constantes de classe (`FIELD_GALLERY`)
- Slug kebab-case (`photo-carousel`)
- Classe en `Block` suffix (`PhotoCarouselBlock`)
- Template via `<x-block :fields="$fields" :block="$block">`
- Champs référencés via `$fields[PhotoCarouselBlock::FIELD_GALLERY]`
- Alpine + Tailwind, pas de JS hors-Swiper

## Hors scope

- Pas de View Component dédié pour la slide (trop simple pour justifier l'extraction)
- Pas de Livewire
- Pas d'autoplay (non demandé dans le Figma, peut être ajouté plus tard via un champ `LayoutTab` si besoin)
- Pas de variante d'affichage multiple — un seul mode (slider 1 image à la fois)

## Suivi post-implémentation

Une fois mergé, ajouter le bloc à la **bibliothèque Notion Horizon Blocks** :
https://www.notion.so/adeliom/214da39b354280d098b9f36e9986bf1e?v=214da39b354280f1aa94000ce4efd3ae
