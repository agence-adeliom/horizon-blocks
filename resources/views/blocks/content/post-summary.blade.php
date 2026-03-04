@php
    use Adeliom\HorizonTools\Services\BlogPostService;
    use App\Admin\Post\PostSummaryAdmin;
    use App\Blocks\Content\PostSummaryBlock;

    $useHtml = PostSummaryAdmin::USE_HTML;
    $hierarchical = PostSummaryAdmin::HIERARCHICAL;
    $retrieveOnly = PostSummaryAdmin::TO_RETRIEVE;
@endphp

@if(!empty($fields[PostSummaryBlock::FIELD_IS_TOP]))
    @if($fields[PostSummaryBlock::FIELD_IS_TOP] && BlogPostService::hasClosingTag())
        @php($titles = BlogPostService::getPostTitles(retrieveOnly: $retrieveOnly, useHtml: $useHtml, hierarchical: $hierarchical, useCache: false))

        @if(is_admin())
            <p>Ouverture du sommaire</p>
        @endif

        <div class="post-content container grid grid-cols-12">
            <div
                class="summary bg-color-01-50 col-span-full lg:col-span-10 lg:col-start-2 rounded p-6 trans-default lg:hover:bg-color-01-200">
                <div class="summary-container is-open" js-summary-container @if($useHtml) html-based @endif>
                    @if(!empty($fields[PostSummaryBlock::FIELD_TITLE])&&$fields[PostSummaryBlock::FIELD_TITLE])
                        <button js-toggle-summary aria-label="Fermer le sommaire"
                                class="cursor-pointer items-center w-full flex gap-2 justify-between">
                            <p class="font-semibold text-large">{{$fields[PostSummaryBlock::FIELD_TITLE]}}</p>
                            <x-ui.icon icon="far-chevron-up" class="icon-4 is-open:rotate-180 transition-all duration-200"/>
                        </button>
                    @endif

                    <div class="transition-all duration-200 ease-in overflow-hidden" js-summary-content>
                        <ul class="pt-2 summary-list" scroll-offset="{{PostSummaryBlock::SCROLL_OFFSET}}"
                            @if($useHtml) html-based @endif
                            active-class="summary-active"
                            before-active-class="summary-before-active">
                            @if(is_array($titles))
                                @if(!$hierarchical)
                                    @foreach($titles as $title)
                                        <li class="group font-semibold">
                                            <span class="summary-elt" data-title="{{trim($title)}}" data-level="h2">
                                                @if(!empty($context['titlesOverride'][$title]))
                                                    {{trim($context['titlesOverride'][$title])}}
                                                @else
                                                    {{trim($title)}}
                                                @endif
                                            </span>
                                        </li>
                                    @endforeach
                                @else
                                    @foreach($titles as $title)
                                        @if(is_array($title))
                                            <x-navigation.hierarchical-summary-element :element="$title" :title-overrides="$context['titlesOverride']" />
                                        @endif
                                    @endforeach
                                @endif
                            @endif
                        </ul>
                    </div>
                </div>
            </div>

            <div class="blocks col-span-full lg:col-span-10 lg:col-start-2">
                @else
            </div>
        </div>

        @if(is_admin())
            <p>Fermeture du sommaire</p>
        @endif
    @endif
@endif
