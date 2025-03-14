@if (isset($review) && $review)
    <div @class([
        'card-customer-review',
        'relative flex flex-col gap-card rounded-card border-card p-card',
        $attributes['class'],
    ])>
        @php
            $reviewInfo = $review['review'] ?? null;
            $reviewer = $review['reviewer'] ?? null;
        @endphp

        @isset($reviewInfo['rating'])
            <x-ui.rating :score="$reviewInfo['rating']" />
        @endisset

        @isset($reviewInfo['review'])
            <p>{{ $reviewInfo['review'] }}</p>
        @endisset

        <div class="flex items-center gap-small">
            @if (@isset($reviewer['avatar']) && $reviewer['avatar'] !== false)
                <x-media.img :image="$reviewer['avatar']" class="h-10 w-10 flex-none rounded-pill" size="thumbnail" />
            @else
                <div
                    class="flex h-10 w-10 flex-none items-center justify-center rounded-pill bg-gray-400 text-large uppercase text-white">
                    {{ !empty($reviewer['firstname']) ? substr($reviewer['firstname'], 0, 1) : '' }}{{ !empty($reviewer['lastname']) ? substr($reviewer['lastname'], 0, 1) : '' }}
                </div>
            @endif
            <div class="text-small">
                <p class="font-semibold">
                    {{ !empty($reviewer['firstname']) ? $reviewer['firstname'] : '' }}{{ !empty($reviewer['lastname']) ? ', ' . $reviewer['lastname'] : '' }}
                </p>

                @if (!empty($reviewer['job']))
                    <p>{{ $reviewer['job'] }}</p>
                @endif
            </div>
        </div>
    </div>
@endif
