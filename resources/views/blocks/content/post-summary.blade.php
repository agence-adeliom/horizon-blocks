@php use Adeliom\HorizonTools\Services\BlogPostService;use App\Blocks\Content\PostSummaryBlock; @endphp

@if(isset($fields[PostSummaryBlock::FIELD_IS_TOP]))
    @if($fields[PostSummaryBlock::FIELD_IS_TOP] && BlogPostService::hasClosingTag())
        @php($titles = BlogPostService::getPostTitles())

        <div class="post-content grid grid-cols-12">
            <div class="summary col-span-4">
                @if(isset($fields[PostSummaryBlock::FIELD_TITLE])&&$fields[PostSummaryBlock::FIELD_TITLE])
                    <p>{{$fields[PostSummaryBlock::FIELD_TITLE]}}</p>
                @endif

                <ul class="summary-list">
                    @foreach($titles as $title)
                        <li class="summary-elt" data-title="{{$title}}">
                            <span>{{$title}}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="blocks col-span-8">
                @else
            </div>
        </div>
    @endif
@endif
