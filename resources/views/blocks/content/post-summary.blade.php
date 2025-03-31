@php
    use Adeliom\HorizonTools\Services\BlogPostService;
    use App\Blocks\Content\PostSummaryBlock;
@endphp

@if(isset($fields[PostSummaryBlock::FIELD_IS_TOP]))
    @if($fields[PostSummaryBlock::FIELD_IS_TOP] && BlogPostService::hasClosingTag())
        @php($titles = BlogPostService::getPostTitles())

        <div class="post-content grid grid-cols-12">
            <div class="summary col-span-4">
                <div class="summary-container sticky top-20">
                    @if(isset($fields[PostSummaryBlock::FIELD_TITLE])&&$fields[PostSummaryBlock::FIELD_TITLE])
                        <p>{{$fields[PostSummaryBlock::FIELD_TITLE]}}</p>
                    @endif

                    <ul class="summary-list" scroll-offset="{{PostSummaryBlock::SCROLL_OFFSET}}"
                        active-class="summary-active"
                        before-active-class="summary-before-active">
                        @foreach($titles as $title)
                            <li class="summary-elt group" data-title="{{$title}}">
                                <p class="group-[.summary-active]:text-red-500 group-[.summary-before-active]:text-green-500">
                <span>
                  {{$title}}
                </span>
                                </p>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <div class="blocks col-span-8">
                @else
            </div>
        </div>
    @endif
@endif
