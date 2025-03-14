<x-block :fields="$fields">
    <div class="grid-12" x-data="initArgumentsSlider()" x-intersect:enter="shown = true" x-intersect:leave="shown = false">
        <div class="lg:col-span-5">
            @isset($fields['uptitle'])
                <x-typography.uptitle :content="$fields['uptitle']" />
            @endisset

            @isset($fields['title'])
                <x-typography.heading :fields="$fields['title']" size="3" />
            @endisset

            <div x-ref="pagination" class="mt-6 flex flex-col gap-6 max-lg:hidden" x-show="shown">
            </div>

            <div class="lg:hidden">
                @foreach ($fields['args'] as $arg)
                    <div class="mt-6 flex flex-col gap-4">
                        <div class="flex flex-col gap-2">
                            <h3 class="step-title{{ $loop->index }} text-md font-semibold">
                                {{ $arg['arg_title'] }}</h3>
                            <p class="p step-desc{{ $loop->index }}">{{ $arg['arg_desc'] }}</p>
                            <x-typography.text x-ref="stepTitle" :content="$arg['arg_desc']" />
                        </div>
                        @isset($arg['arg_img'])
                            <x-media.img ratio="aspect-square" container-class="max-w-[500px]" :image="$arg['arg_img']" />
                        @endisset
                    </div>
                @endforeach
            </div>
            <div class="mt-8 flex items-center justify-between">
                @isset($fields['button'])
                    <x-action.button class="max-lg:flex-1" :fields="$fields['button']" type="primary" />
                @endisset
                <x-action.button class="max-lg:hidden" type="tertiary" @click="togglePause">
                    <template x-if="isPlaying">
                        <x-far-circle-pause class="icon-16" />
                    </template>
                    <template x-if="!isPlaying">
                        <x-far-circle-play class="icon-16" />
                    </template>
                    <span x-text="isPlaying ? 'Pause' : 'Lecture'"></span>
                </x-action.button>
            </div>
        </div>

        <div class="swiper-container max-lg:hidden lg:col-span-6 lg:col-start-7" x-ref="swiperContainer">
            <div class="swiper-wrapper">
                @foreach ($fields['args'] as $arg)
                    @isset($arg['arg_img'])
                        <div class="swiper-slide">
                            <x-media.img ratio="aspect-square" container-class="" :image="$arg['arg_img']" />
                        </div>
                    @endisset
                @endforeach
            </div>
        </div>
    </div>
</x-block>
