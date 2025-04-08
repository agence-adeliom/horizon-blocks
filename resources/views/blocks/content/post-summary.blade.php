@php
    use Adeliom\HorizonTools\Services\BlogPostService;
    use App\Blocks\Content\PostSummaryBlock;
@endphp

@if(isset($fields[PostSummaryBlock::FIELD_IS_TOP]))
    @if($fields[PostSummaryBlock::FIELD_IS_TOP] && BlogPostService::hasClosingTag())
        @php($titles = BlogPostService::getPostTitles())

        @if(is_admin())
            <p>Ouverture du sommaire</p>
        @endif

        <div class="post-content grid grid-cols-12">
            <div class="summary col-span-4">
                <div class="summary-container sticky top-20">
                    @if(isset($fields[PostSummaryBlock::FIELD_TITLE])&&$fields[PostSummaryBlock::FIELD_TITLE])
                        <p>{{$fields[PostSummaryBlock::FIELD_TITLE]}}</p>
                    @endif

                    <ul class="summary-list" scroll-offset="{{PostSummaryBlock::SCROLL_OFFSET}}"
                        active-class="summary-active"
                        before-active-class="summary-before-active">
                        @if(is_array($titles))
                            @foreach($titles as $title)
                                <li class="summary-elt group" data-title="{{$title}}">
                                    <p class="group-[.summary-active]:text-red-500 group-[.summary-before-active]:text-green-500">
                <span>
                  @if(!empty($context['titlesOverride'][$title]))
                        {{$context['titlesOverride'][$title]}}
                    @else
                        {{$title}}
                    @endif
                </span>
                                    </p>
                                </li>
                            @endforeach
                        @endif
                    </ul>
                </div>
            </div>

            <div class="blocks col-span-8">
                @else
            </div>
        </div>

        @if(is_admin())
            <p>Fermeture du sommaire</p>
        @endif
    @endif
@endif
