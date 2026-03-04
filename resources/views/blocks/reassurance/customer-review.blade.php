@if ($fields)
    <x-block :fields="$fields" :block="$block" class="overflow-hidden">
        <div class="flex w-full flex-col gap-8 lg:gap-10">
            <div class="grid-12">
                <div class="col-span-full flex flex-col items-center text-center lg:col-span-8 lg:col-start-3">
                    @isset($fields['uptitle'])
                        <x-typography.uptitle :content="$fields['uptitle']" class="mb-1" />
                    @endisset

                    @isset($fields['title'])
                        <x-typography.heading :fields="$fields['title']" size="3" />
                    @endisset

                    @isset($fields['wysiwyg'])
                        <x-typography.text :content="$fields['wysiwyg']" class="mt-2 lg:mt-4" />
                    @endisset

                    @if (@isset($context['global-rating']) || @isset($context['btn-reviews']))
                        <div class="mt-2 flex flex-col items-center gap-4 lg:mt-4 lg:flex-row lg:gap-6">
                            @if (@isset($context['global-rating']) && $context['global-rating'])
                                <x-ui.rating :score="$context['global-rating']" showScore />
                            @endif

                            @if (@isset($context['btn-reviews']) && $context['btn-reviews']['link'])
                                <x-action.button :fields="$context['btn-reviews']" icon="fas-arrow-right" type="tertiary" />
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <div class="col-span-full" x-data="initReviewsSlider()">
                <div class="swiper w-full overflow-visible" x-ref="swiperContainer">
                    <div class="swiper-wrapper cursor-grab">
                        @if (isset($fields['reviews']) && $fields['reviews'])
                            @foreach ($fields['reviews'] as $review)
                                @if (is_object($review) && property_exists($review, 'ID'))
                                    <x-cards.card-customer-review :review="get_fields($review->ID)" class="swiper-slide" />
                                @endif
                            @endforeach
                        @endif
                    </div>
                    <div x-ref="swiperPagination" class="mt-6 flex justify-center gap-2"></div>
                </div>
            </div>
        </div>
    </x-block>
@endif