@if (!empty($fields))
    <x-block :fields="$fields" :block="$block" class="overflow-hidden">
        <div class="flex w-full flex-col gap-8 lg:gap-10">
            <div class="grid-12">
                <div class="col-span-full flex flex-col items-center text-center lg:col-span-8 lg:col-start-3">
                    @if(!empty($fields['uptitle']))
                        <x-typography.uptitle :content="$fields['uptitle']" class="mb-1" />
                    @endif

                    @if(!empty($fields['title']))
                        <x-typography.heading :fields="$fields['title']" size="3" />
                    @endif

                    @if(!empty($fields['wysiwyg']))
                        <x-typography.text :content="$fields['wysiwyg']" class="mt-2 lg:mt-4" />
                    @endif

                    @if (!empty($context['global-rating']) || !empty($context['btn-reviews']))
                        <div class="mt-2 flex flex-col items-center gap-4 lg:mt-4 lg:flex-row lg:gap-6">
                            @if (!empty($context['global-rating']))
                                <x-ui.rating :score="$context['global-rating']" showScore />
                            @endif

                            @if (!empty($context['btn-reviews']) && !empty($context['btn-reviews']['link']))
                                <x-action.button :fields="$context['btn-reviews']" icon="fas-arrow-right" type="tertiary" />
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            @php
                $reviews = array_filter(
                    $fields['reviews'] ?? [],
                    fn($review) => is_object($review) && property_exists($review, 'ID'),
                );
            @endphp

            @if (!empty($reviews))
                <div class="min-w-0" x-data="initReviewsSlider()">
                    <div class="swiper w-full overflow-visible!" x-ref="swiperContainer">
                        <ul role="list" class="swiper-wrapper cursor-grab">
                            @foreach ($reviews as $review)
                                <li class="swiper-slide h-auto">
                                    <x-cards.card-customer-review :review="get_fields($review->ID)" class="h-full" />
                                </li>
                            @endforeach
                        </ul>
                        <div x-ref="swiperPagination" class="mt-6 flex justify-center gap-2" aria-hidden="true"></div>
                    </div>

                    @if (count($reviews) > 1)
                        <div class="mt-4 flex justify-end gap-2" x-show="!locked">
                            <x-action.button tag="button" type="primary" variant="outline" iconOnly
                                at-click="swiper.slidePrev()" aria-label="{{ __('Avis précédent', 'horizon-blocks') }}">
                                <x-fas-angle-left />
                            </x-action.button>
                            <x-action.button tag="button" type="primary" variant="outline" iconOnly
                                at-click="swiper.slideNext()" aria-label="{{ __('Avis suivant', 'horizon-blocks') }}">
                                <x-fas-angle-right />
                            </x-action.button>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </x-block>
@endif