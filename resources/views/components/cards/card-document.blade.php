@if ($document['file'])
    @php
        $filesizeInBytes = $document['file']['filesize'];

        if ($filesizeInBytes >= 1048576) {
            $filesize = round($filesizeInBytes / 1048576, 0) . 'Mo';
        } else {
            $filesize = round($filesizeInBytes / 1024, 0) . 'Ko';
        }
    @endphp

    <div @class([
        'relative p-4 flex items-center gap-4 rounded-card bg-white border-card shadow-sm hover:border-primary transition-colors',
    ])>
        <div class="flex items-start md:items-center flex-1 gap-4">
            <x-far-file-lines class="icon-24 text-primary" />
            <div class="flex flex-col gap-1 md:flex-row md:items-center">
                <x-typography.text class="font-semibold text-text-primary" :content="$document['title'] ?? $document['file']['name']" />
                <x-typography.text class="text-sm" content="(.{{ $document['file']['subtype'] . ', ' . $filesize }})" />
            </div>
        </div>
        <x-action.button download fullLink :url="$document['file']['url']"
            aria-label="Télécharger le document {{ $item['title'] ?? '' }}" type="secondary" iconOnly>
            <x-far-arrow-down-to-line class="icon-16" />
        </x-action.button>
    </div>
@endif
