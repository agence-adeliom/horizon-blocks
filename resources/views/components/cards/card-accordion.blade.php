@if (!empty($title) && !empty($content))
    @php($uid = wp_unique_id('accordion-'))

    <div x-data="{ open: false }" @class([
        'accordion-item',
        'rounded-card bg-color-03-50 p-medium',
        $attributes['class'],
    ])>
        <h3>
            <button type="button" id="{{ $uid }}-btn" aria-controls="{{ $uid }}-panel"
                :aria-expanded="open" @click="open = !open"
                class="flex justify-between items-center gap-medium w-full text-left cursor-pointer">
                <x-typography.text :content="$title" class="font-semibold transition-all"
                    x-bind:class="{ 'text-primary': open }" />
                <svg aria-hidden="true" :class="{ 'rotate-180': open }"
                    class="shrink-0 transform transition-transform duration-300 w-5 h-5" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
        </h3>
        <div id="{{ $uid }}-panel" role="region" aria-labelledby="{{ $uid }}-btn" x-show="open" x-collapse
            class="pt-medium">
            <x-typography.text :content="$content" />
        </div>
    </div>
@endif
