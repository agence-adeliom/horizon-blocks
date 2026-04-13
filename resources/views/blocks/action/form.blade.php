@php
    $positionClass = match (true){
      !empty($fields['position']) && $fields['position'] !== 'left' => 'lg:col-start-3',
      default => '',
    };

    $bgType = $fields['bg-type'] ?? 'bg-color-type';
    $bgColor = $bgType === 'bg-color-type' ? 'bg-color-02-50' : '';
    $bgImage = $bgType === 'bg-image-type' && !empty($fields['bg-image']) ? $fields['bg-image'] : '';
@endphp

<x-block :fields="$fields" :block="$block" class="{{ $bgColor }} relative" background="none">
    @if (!empty($bgImage))
        <x-media.img :image="$bgImage" class="cover-full" size="full" container-class="absolute-full"/>
    @endif

    <div class="z-10 relative">
        <div class="grid-12">
            <div class="lg:col-span-8 {{ $positionClass }}">
                <div class="bg-white rounded-card p-6 lg:p-10">
                    @if (!empty($fields['title']))
                        <x-typography.heading :fields="$fields['title']" size="5"/>
                    @endif

                    @if (!empty($fields['desc']))
                        <x-typography.text :content="$fields['desc']"
                                           class="mt-title-text-mobile lg:mt-title-text-desktop"/>
                    @endif

                    @if (!empty($fields['offer']) && !empty($fields['offer']['enable']))
                        <x-offer :fields="$fields['offer']" class="mt-3xlarge"/>
                    @endif

                    @if (!empty($fields['form_id']))
                        <div class="mt-button-text-mobile lg:mt-button-text-desktop">
                            @php
                                echo do_shortcode(
                                  '[gravityform id="' .$fields['form_id'] . '" title="false" description="false" ajax="true"]',
                                );
                            @endphp
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-block>