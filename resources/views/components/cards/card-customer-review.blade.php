@if (!empty($review))
    <div @class([
        'card-customer-review',
        'relative flex flex-col gap-card rounded-card border-card p-card',
        $attributes['class'],
    ])>
        @php
            $reviewInfo = $review['review'] ?? null;
            $reviewer = $review['reviewer'] ?? null;
        @endphp

        @if (!empty($reviewInfo['rating']))
            <x-ui.rating :score="$reviewInfo['rating']" :label="__('Note : %s sur %d', 'horizon-blocks')" />
        @endif

        @if (!empty($reviewInfo['review']))
            <blockquote>
                <p>{{ $reviewInfo['review'] }}</p>
            </blockquote>
        @endif

        <div class="flex items-center gap-small">
            @if (!empty($reviewer['avatar']))
                <x-media.img :image="$reviewer['avatar']" :decorative="true" class="h-10 w-10 flex-none rounded-pill" size="thumbnail" />
            @else
                <div class="flex h-10 w-10 flex-none items-center justify-center rounded-pill bg-gray-600 text-large uppercase text-white"
                    aria-hidden="true">
                    {{ !empty($reviewer['firstname']) ? mb_substr($reviewer['firstname'], 0, 1) : '' }}{{ !empty($reviewer['lastname']) ? mb_substr($reviewer['lastname'], 0, 1) : '' }}
                </div>
            @endif
            <div class="text-small">
                <p class="font-semibold">
                    {{ trim(($reviewer['firstname'] ?? '') . ' ' . ($reviewer['lastname'] ?? '')) }}
                </p>

                @if (!empty($reviewer['job']))
                    <p>{{ $reviewer['job'] }}</p>
                @endif
            </div>
        </div>
    </div>
@endif
