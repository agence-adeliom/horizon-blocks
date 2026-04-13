<div class="flex flex-col rounded border border-card">
    <div class="p-6">
        @if (!empty($title))
            <x-typography.heading :content="$title" size="4" />
        @endif

        @if (!empty($subtitle))
            <x-typography.text :content="$subtitle"
                               class="mt-1 px-2 py-1 font-semibold text-primary rounded bg-neutral-100 w-fit" />
        @endif

        @if (!empty($price))
            <x-typography.text :content="$price" class="mt-6 font-semibold text-primary text-md" />
        @endif

        @if (!empty($subPrice))
            <x-typography.text :content="$subPrice" class="mt-2" />
        @endif

        @if (!empty($button) && !empty($button['link']) && is_array($button['link']))
            <x-action.button class="mt-6" :fields="$button" class="w-full mt-6" />
        @endif
    </div>

    @if (!empty($characteristics))
        @foreach ($characteristics as $group)
            <div>
                @if (!empty($group['title']))
                    <div class="flex items-center gap-3 py-4 px-6 border-y border-y-card bg-neutral-100">
                        @if (!empty($group['icon']))
                            <x-dynamic-component :component="'fa' . $group['icon']->style[0] . '-' . $group['icon']->id" class="icon-20 text-primary" />
                        @endif
                        <x-typography.text :content="$group['title']" class="font-semibold" />
                    </div>
                @endif

                @if (!empty($group['items']))
                    <ul class="px-6 py-4 flex flex-col gap-3">
                        @foreach ($group['items'] as $item)
                            <li class="flex items-center gap-2">
                                <x-fas-check class="icon-16 text-primary" />
                                <x-typography.text :content="$item['title']" />
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endforeach
    @endif
</div>

