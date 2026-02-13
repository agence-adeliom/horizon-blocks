<div class="flex flex-wrap gap-2">
    @if ($hasCopy)
        <livewire:action.share-button
            :icon-only="$iconOnly"
            :type="\App\Livewire\Action\ShareButton::TYPE_COPY"
            title="Copier dans le presse-papier"
        />
    @endif

    @if ($hasEmail)
        <livewire:action.share-button
            :icon-only="$iconOnly"
            :type="\App\Livewire\Action\ShareButton::TYPE_EMAIL"
            title="Partager par e-mail"
        />
    @endif

    @if ($hasSms)
        <livewire:action.share-button :icon-only="$iconOnly" :type="\App\Livewire\Action\ShareButton::TYPE_SMS" title="Partager par SMS" />
    @endif

    @if ($hasWhatsApp)
        <livewire:action.share-button
            :icon-only="$iconOnly"
            :type="\App\Livewire\Action\ShareButton::TYPE_WHATSAPP"
            title="Partager sur WhatsApp"
        />
    @endif

    @if ($hasMessenger)
        <livewire:action.share-button
            :icon-only="$iconOnly"
            :type="\App\Livewire\Action\ShareButton::TYPE_MESSENGER"
            title="Partager sur Messenger"
        />
    @endif

    @if ($hasChatGPT)
        <livewire:action.share-button
            :icon-only="$iconOnly"
            :type="\App\Livewire\Action\ShareButton::TYPE_CHATGPT"
            :post-id="get_the_ID()"
            title="Partager à ChatGPT"
        />
    @endif

    @if ($hasClaude)
        <livewire:action.share-button
            :icon-only="$iconOnly"
            :type="\App\Livewire\Action\ShareButton::TYPE_CLAUDE"
            :post-id="get_the_ID()"
            title="Partager à Claude"
        />
    @endif

    @if ($hasPerplexity)
        <livewire:action.share-button
            :icon-only="$iconOnly"
            :type="\App\Livewire\Action\ShareButton::TYPE_PERPLEXITY"
            :post-id="get_the_ID()"
            title="Partager à Perplexity"
        />
    @endif
</div>
