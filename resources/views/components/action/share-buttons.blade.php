<ul role="list" {{ $attributes->merge(['class' => 'flex flex-wrap gap-2']) }}>
    @if ($hasCopy)
        <li>
            <livewire:action.share-button :icon-only="$iconOnly" :type="\App\Livewire\Action\ShareButton::TYPE_COPY" title="Copier le lien de la page" />
        </li>
    @endif

    @if ($hasEmail)
        <li>
            <livewire:action.share-button :icon-only="$iconOnly" :type="\App\Livewire\Action\ShareButton::TYPE_EMAIL" title="Partager par e-mail" />
        </li>
    @endif

    @if ($hasSms)
        <li>
            <livewire:action.share-button :icon-only="$iconOnly" :type="\App\Livewire\Action\ShareButton::TYPE_SMS" title="Partager par SMS" />
        </li>
    @endif

    @if ($hasWhatsApp)
        <li>
            <livewire:action.share-button :icon-only="$iconOnly" :type="\App\Livewire\Action\ShareButton::TYPE_WHATSAPP" title="Partager sur WhatsApp" />
        </li>
    @endif

    @if ($hasMessenger)
        <li>
            <livewire:action.share-button :icon-only="$iconOnly" :type="\App\Livewire\Action\ShareButton::TYPE_MESSENGER" title="Partager sur Messenger" />
        </li>
    @endif

    @if ($hasFacebook)
        <li>
            <livewire:action.share-button :icon-only="$iconOnly" :type="\App\Livewire\Action\ShareButton::TYPE_FACEBOOK" title="Partager sur Facebook" />
        </li>
    @endif

    @if ($hasInstagram)
        <li>
            <livewire:action.share-button :icon-only="$iconOnly" :type="\App\Livewire\Action\ShareButton::TYPE_INSTAGRAM"
                title="Copier le lien pour le partager sur Instagram" />
        </li>
    @endif

    @if ($hasLinkedIn)
        <li>
            <livewire:action.share-button :icon-only="$iconOnly" :type="\App\Livewire\Action\ShareButton::TYPE_LINKEDIN" title="Partager sur LinkedIn" />
        </li>
    @endif

    @if ($hasX)
        <li>
            <livewire:action.share-button :icon-only="$iconOnly" :type="\App\Livewire\Action\ShareButton::TYPE_X" title="Partager sur X" />
        </li>
    @endif

    @if ($hasChatGPT)
        <li>
            <livewire:action.share-button :icon-only="$iconOnly" :type="\App\Livewire\Action\ShareButton::TYPE_CHATGPT" :post-id="get_the_ID()"
                title="Partager à ChatGPT" />
        </li>
    @endif

    @if ($hasClaude)
        <li>
            <livewire:action.share-button :icon-only="$iconOnly" :type="\App\Livewire\Action\ShareButton::TYPE_CLAUDE" :post-id="get_the_ID()"
                title="Partager à Claude" />
        </li>
    @endif

    @if ($hasPerplexity)
        <li>
            <livewire:action.share-button :icon-only="$iconOnly" :type="\App\Livewire\Action\ShareButton::TYPE_PERPLEXITY" :post-id="get_the_ID()"
                title="Partager à Perplexity" />
        </li>
    @endif
</ul>
