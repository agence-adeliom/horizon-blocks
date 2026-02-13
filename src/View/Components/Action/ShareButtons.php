<?php

declare(strict_types=1);

namespace Adeliom\HorizonBlocks\View\Components\Action;

use Adeliom\HorizonTools\Admin\ShareOptionsAdmin;
use Adeliom\HorizonTools\Services\ShareService;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ShareButtons extends Component
{
    public bool $display = true;
    public bool $hasCopy = false;
    public bool $hasEmail = false;
    public bool $hasSms = false;
    public bool $hasWhatsApp = false;
    public bool $hasMessenger = false;
    public bool $hasChatGPT = false;
    public bool $hasClaude = false;
    public bool $hasPerplexity = false;
    public bool $hasAtLeastOneLlm = false;

    public function __construct(public bool $iconOnly = false, public bool $withLlm = true)
    {
        $this->handleMethods();
    }

    private function handleMethods(): void
    {
        if ($this->display) {
            foreach (ShareService::getShareOptions() as $shareOptionKey => $shareOptionValue) {
                if ($shareOptionValue) {
                    switch ($shareOptionKey) {
                        case ShareOptionsAdmin::FIELD_SHARE_ENABLE_COPY_LINK:
                            $this->hasCopy = true;
                            break;
                        case ShareOptionsAdmin::FIELD_SHARE_ENABLE_EMAIL:
                            $this->hasEmail = true;
                            break;
                        case ShareOptionsAdmin::FIELD_SHARE_ENABLE_SMS:
                            $this->hasSms = true;
                            break;
                        case ShareOptionsAdmin::FIELD_SHARE_ENABLE_WHATSAPP:
                            $this->hasWhatsApp = true;
                            break;
                        case ShareOptionsAdmin::FIELD_SHARE_ENABLE_MESSENGER:
                            $this->hasMessenger = true;
                            break;
                        case ShareOptionsAdmin::FIELD_SHARE_ENABLE_CHATGPT:
                            $this->hasChatGPT = $this->withLlm;
                            break;
                        case ShareOptionsAdmin::FIELD_SHARE_ENABLE_CLAUDE:
                            $this->hasClaude = $this->withLlm;
                            break;
                        case ShareOptionsAdmin::FIELD_SHARE_ENABLE_PERPLEXITY:
                            $this->hasPerplexity = $this->withLlm;
                            break;
                        default:
                            throw new \Exception('Unknown share option: ' . $shareOptionKey);
                    }
                }
            }

            $this->hasAtLeastOneLlm = $this->hasPerplexity || $this->hasChatGPT || $this->hasClaude;
        }
    }

    public function render(): View|Closure|string
    {
        return view('components.action.share-buttons');
    }
}
