@use('Adeliom\HorizonTools\Fields\Medias\ImageField')
<x-block :fields="$fields" :block="$block">
    @php
        $args = array_values(array_filter($fields['args'] ?? [], fn($arg) => !empty($arg['argTitle'])));
        $argHeadingTag = preg_match('/^h([1-5])$/', $fields['title']['tag'] ?? '', $level)
            ? 'h' . ($level[1] + 1)
            : 'h2';
    @endphp

    <div class="grid-12" x-data="initArgumentsSlider()" :class="{ 'is-paused': !running }" x-intersect:enter="shown = true"
        x-intersect:leave="shown = false" @mouseenter="hovered = true" @mouseleave="hovered = false"
        @focusin="focused = true" @focusout="focused = $el.contains($event.relatedTarget)">
        <div class="lg:col-span-5">
            @if (!empty($fields['uptitle']))
                <x-typography.uptitle :content="$fields['uptitle']" />
            @endif

            @if (!empty($fields['title']))
                <x-typography.heading :fields="$fields['title']" size="3" />
            @endif
        </div>

        {{-- Desktop --}}
        @if (!empty($args))
            <ol class="max-lg:hidden lg:col-span-5 lg:col-start-1 flex flex-col gap-6">
                @foreach ($args as $arg)
                    <li class="argument-step relative flex gap-3 rounded-sm has-focus-visible:outline-2 has-focus-visible:outline-offset-4"
                        :class="{ 'is-active': index === {{ $loop->index }} }">
                        <span class="step-progress" aria-hidden="true"><span class="step-progress-inner"></span></span>
                        <div class="flex flex-col gap-2">
                            <{{ $argHeadingTag }} class="step-title font-semibold transition-colors">
                                <button type="button"
                                    class="text-left cursor-pointer outline-none after:absolute after:inset-0"
                                    @click="goTo({{ $loop->index }})" :aria-current="index === {{ $loop->index }}">
                                    {{ $arg['argTitle'] }}
                                </button>
                                </{{ $argHeadingTag }}>
                                @if (!empty($arg['argDesc']))
                                    <p class="p">{{ $arg['argDesc'] }}</p>
                                @endif
                        </div>
                    </li>
                @endforeach
            </ol>
        @endif

        {{-- Slider : image seule en desktop, image + texte en mobile --}}
        <div class="swiper-container w-full overflow-hidden lg:col-span-6 lg:col-start-7 lg:row-start-1 lg:row-span-3"
            x-ref="swiperContainer">
            <div class="swiper-wrapper">
                @foreach ($args as $arg)
                    <div class="swiper-slide"
                        data-announcement="{{ sprintf(__('Argument %d sur %d : %s', 'horizon-blocks'), $loop->iteration, $loop->count, $arg['argTitle']) }}">
                        @if (!empty($arg['argImg']))
                            <x-media.img ratio="aspect-square" class="size-full object-cover" :image="$arg['argImg']"
                                :decorative="ImageField::isDecorative($arg, 'argImg')" :alt="ImageField::alt($arg, 'argImg')" />
                        @endif
                        <div class="lg:hidden mt-6 flex flex-col gap-2">
                            <{{ $argHeadingTag }} class="text-md font-semibold">{{ $arg['argTitle'] }}
                                </{{ $argHeadingTag }}>
                                @if (!empty($arg['argDesc']))
                                    <p class="p">{{ $arg['argDesc'] }}</p>
                                @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Mobile : contrôles du slider --}}
        @if (count($args) > 1)
            <div class="lg:hidden flex items-center justify-center gap-4">
                <x-action.button tag="button" type="secondary" iconOnly at-click="prev()"
                    aria-label="{{ __('Argument précédent', 'horizon-blocks') }}">
                    <x-fas-angle-left class="icon-4" />
                </x-action.button>
                <div class="flex gap-2" aria-hidden="true">
                    @foreach ($args as $arg)
                        <span class="argument-dot" :class="{ 'is-active': index === {{ $loop->index }} }"></span>
                    @endforeach
                </div>
                <x-action.button tag="button" type="secondary" iconOnly at-click="next()"
                    aria-label="{{ __('Argument suivant', 'horizon-blocks') }}">
                    <x-fas-angle-right class="icon-4" />
                </x-action.button>
            </div>
            <p class="sr-only" aria-live="polite" x-text="announcement"></p>
        @endif

        <div class="lg:col-span-5 lg:col-start-1 flex items-center justify-between gap-6 max-lg:flex-col">
            @if (!empty($fields['button']))
                <x-action.button class="max-lg:w-full" :fields="$fields['button']" type="primary" />
            @endif
            @if (count($args) > 1)
                <x-action.button tag="button" type="tertiary" at-click="togglePause()" x-show="!reducedMotion">
                    <template x-if="!userPaused">
                        <x-far-circle-pause class="icon-4" />
                    </template>
                    <template x-if="userPaused">
                        <x-far-circle-play class="icon-4" />
                    </template>
                    <span
                        x-text="userPaused ? '{{ __('Lecture', 'horizon-blocks') }}' : '{{ __('Pause', 'horizon-blocks') }}'">{{ __('Pause', 'horizon-blocks') }}</span>
                </x-action.button>
            @endif
        </div>
    </div>
</x-block>
