@php
    use Adeliom\HorizonTools\Services\BlogPostService;
    use App\Admin\Post\PostSummaryAdmin;
    use App\Blocks\Content\PostSummaryBlock;

    $useHtml = PostSummaryAdmin::USE_HTML;
    $hierarchical = PostSummaryAdmin::HIERARCHICAL;
    $retrieveOnly = PostSummaryAdmin::TO_RETRIEVE;
@endphp

@if (!empty($fields[PostSummaryBlock::FIELD_IS_TOP]))
    @if (BlogPostService::hasClosingTag())
        @php($titles = BlogPostService::getPostTitles(retrieveOnly: $retrieveOnly, useHtml: $useHtml, hierarchical: $hierarchical, useCache: false))

        @if (is_admin())
            <p>Ouverture du sommaire</p>
        @endif

        @php($summaryTitle = $fields[PostSummaryBlock::FIELD_TITLE] ?? '')
        @php($summaryId = wp_unique_id('post-summary-'))

        <div class="post-content container grid grid-cols-12">
            <nav aria-label="{{ $summaryTitle ?: __('Sommaire', 'horizon-blocks') }}"
                class="summary bg-color-01-50 col-span-full lg:col-span-10 lg:col-start-2 rounded p-6 trans-default lg:hover:bg-color-01-200">
                <div class="summary-container" js-summary-container @if ($useHtml) html-based @endif>
                    @if (!empty($summaryTitle))
                        <button type="button" js-toggle-summary aria-expanded="true" aria-controls="{{ $summaryId }}"
                            class="group cursor-pointer items-center w-full flex gap-2 justify-between">
                            <span class="font-semibold text-large">{{ $summaryTitle }}</span>
                            <x-ui.icon icon="far-chevron-up"
                                class="icon-4 group-aria-[expanded=false]:rotate-180 transition-all duration-200" />
                        </button>
                    @endif

                    <div id="{{ $summaryId }}"
                        class="grid grid-rows-[1fr] transition-[grid-template-rows] duration-200 ease-in"
                        js-summary-content>
                        <div class="overflow-hidden min-h-0">
                            <ul class="pt-2 summary-list" scroll-offset="{{ PostSummaryBlock::SCROLL_OFFSET }}"
                                @if ($useHtml) html-based @endif active-class="summary-active"
                                before-active-class="summary-before-active">
                                @if (is_array($titles))
                                    @if (!$hierarchical)
                                        @foreach ($titles as $title)
                                            <li class="group font-semibold">
                                                <a href="#{{ sanitize_title($title) }}" class="summary-elt"
                                                    data-title="{{ trim($title) }}" data-level="h2">
                                                    @if (!empty($context['titlesOverride'][$title]))
                                                        {{ trim($context['titlesOverride'][$title]) }}
                                                    @else
                                                        {{ trim($title) }}
                                                    @endif
                                                </a>
                                            </li>
                                        @endforeach
                                    @else
                                        @foreach ($titles as $title)
                                            @if (is_array($title))
                                                <x-navigation.hierarchical-summary-element :element="$title"
                                                    :title-overrides="$context['titlesOverride']" />
                                            @endif
                                        @endforeach
                                    @endif
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
            </nav>

            <div class="blocks col-span-full lg:col-span-10 lg:col-start-2">
            @else
            </div>
        </div>

        @if (is_admin())
            <p>Fermeture du sommaire</p>
        @endif
    @endif
@endif
