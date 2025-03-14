<x-block :fields="$fields" class="relative">
    <div class="grid-12 lg:gap-10">
        <div class="lg:col-span-6">
            <x-typography.heading :fields="$fields['title']" size="3" />
            <x-typography.text :content="$fields['wysiwyg']" class="mt-4 list-check" />

            @if ($fields['offer'])
                <x-offer :fields="$fields['offer']" class="mt-4" />
            @endif
        </div>
        <div class="bg-background p-5xlarge rounded-card lg:col-span-6">
            <x-typography.heading :fields="$fields['form-title']" size="5" />

            @if ($fields['desc'])
                <x-typography.text :content="$fields['desc']" class="mt-4" />
            @endif

            @if ($fields['form_id'])
                <div class="mt-button-text-mobile lg:mt-button-text-desktop">
                    @php
                        echo do_shortcode(
                            '[gravityform id="' .
                                $fields['form_id'] .
                                '" title="false" description="false" ajax="true"]',
                        );
                    @endphp
                </div>
            @endif
        </div>
    </div>
</x-block>
