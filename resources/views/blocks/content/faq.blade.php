<x-block :fields="$fields" :block="$block">
    @if(!empty($context['structuredData']))
        <script type="application/ld+json">
            @json($context['structuredData'])
        </script>
    @endif
    
    <div class="grid-12">
        <div class="lg:col-span-5 lg:mr-12">
            @if (!empty($fields['img']))
                <x-media.img :image="$fields['img']" class="w-20 mb-3" size="thumbnail" />
            @endif
            
            @if (!empty($fields['uptitle']))
                <x-typography.uptitle :content="$fields['uptitle']" />
            @endif
            
            @if (!empty($fields['title']))
                <x-typography.heading :fields="$fields['title']" size="3" />
            @endif
            
            @if (!empty($fields['wysiwyg']))
                <x-typography.text :content="$fields['wysiwyg']" class="pt-medium" />
            @endif
            
            @if (!empty($fields['buttons']))
                <x-action.buttons :buttons="$fields['buttons']"
                                  class="mt-button-text-mobile lg:mt-button-text-desktop" />
            @endif
        
        </div>
        <div class="lg:col-span-7 flex flex-col gap-medium">
            @if (@isset($fields['questions']) && $fields['questions'])
                @foreach ($fields['questions'] as $question)
                    <x-cards.card-faq :question="$question" />
                @endforeach
            @endif
        </div>
    </div>
</x-block>