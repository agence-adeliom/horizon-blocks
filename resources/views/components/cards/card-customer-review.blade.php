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
            <x-ui.rating :score="$reviewInfo['rating']" />
        @endif

        @if (!empty($reviewInfo['review']))
            <p>{{ $reviewInfo['review'] }}</p>
        @endif

        <div class="flex items-center gap-small">
            @if (!empty($reviewer['avatar']))
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
