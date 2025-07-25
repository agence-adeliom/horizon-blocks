@if ($document['file'])
    @php
        $filesizeInBytes = $document['file']['filesize'];

        if ($filesizeInBytes >= 1048576) {
            $filesize = round($filesizeInBytes / 1048576, 0) . 'Mo';
        } else {
            $filesize = round($filesizeInBytes / 1024, 0) . 'Ko';
        }
         $extension = strtolower(pathinfo($document['file']['url'], PATHINFO_EXTENSION));
    @endphp

    <div @class([
        'card-document',
        'relative p-4 flex items-center gap-4 rounded-card bg-white border-card shadow-sm hover:border-primary transition-colors',
        $attributes['class'],
    ])>
        <div class="flex items-start md:items-center flex-1 gap-4">
            <x-far-file-lines class="icon-10 text-primary" />
            <div class="flex flex-col gap-1 md:flex-row md:items-center">
                <x-typography.text class="font-semibold text-text-primary" :content="$document['title'] ?? $document['file']['name']" />
                <x-typography.text class="text-sm" content="(.{{ $extension . ', ' . $filesize }})" />            </div>
        </div>
        <x-action.button fullLink :url="$document['file']['url']"
            aria-label="Télécharger le document {{ $item['title'] ?? '' }}" type="secondary" iconOnly>
            <x-far-arrow-down-to-line class="icon-16" />
        </x-action.button>
    </div>
@endif