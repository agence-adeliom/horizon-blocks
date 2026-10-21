@if (!empty($document['file']))
    @php
        $filesizeInBytes = (int) ($document['file']['filesize'] ?? 0);
        $filesize = size_format($filesizeInBytes, $filesizeInBytes >= MB_IN_BYTES ? 1 : 0);
        $extension = strtoupper(pathinfo($document['file']['url'], PATHINFO_EXTENSION));
        $title = $document['title'] ?? $document['file']['title'] ?? '';
    @endphp

    <div @class([
        'card-document',
        'relative p-4 flex items-center gap-4 rounded-card bg-white border-card shadow-sm hover:border-primary focus-within:border-primary transition-colors',
        $attributes['class'],
    ])>
        <div class="flex items-start md:items-center flex-1 gap-4">
            <x-far-file-lines class="icon-10 text-primary" />
            <div class="flex flex-col gap-1 md:flex-row md:items-center">
                <x-typography.text class="font-semibold text-text-primary" :content="$title" />
                <x-typography.text class="text-sm" content="({{ $extension }}, {{ $filesize }})" />
            </div>
        </div>
        <x-action.button fullLink :url="$document['file']['url']"
            aria-label="{{ sprintf(__('Télécharger %s (%s, %s)', 'horizon-blocks'), $title, $extension, $filesize) }}" type="secondary" iconOnly>
            <x-far-arrow-down-to-line class="icon-4" />
        </x-action.button>
    </div>
@endif