<?php

declare(strict_types=1);

namespace Adeliom\HorizonBlocks\Livewire\Action;

use Adeliom\HorizonTools\Services\PostService;
use Illuminate\View\View;
use Livewire\Component;

class ShareButton extends Component
{
    public const string TYPE_COPY = 'copy';
    public const string TYPE_EMAIL = 'email';
    public const string TYPE_SMS = 'sms';
    public const string TYPE_WHATSAPP = 'whatsapp';
    public const string TYPE_MESSENGER = 'messenger';
    public const string TYPE_CHATGPT = 'chatgpt';
    public const string TYPE_CLAUDE = 'claude';
    public const string TYPE_PERPLEXITY = 'perplexity';

    public ?string $label = null;
    public ?string $icon = null;
    public ?string $url = null;
    public ?string $target = null;
    public ?string $title = null;
    public ?string $buttonId = null;
    private ?string $currentUrl = null;
    private ?string $currentTitle = null;
    public ?string $buttonType = 'secondary';
    public ?string $actionOnClick = null;

    public string $type;
    public bool $hideUrl = false;
    public bool $iconOnly = false;
    public ?int $postId = null;

    private const array ALLOWED_TYPES = [
        self::TYPE_COPY,
        self::TYPE_EMAIL,
        self::TYPE_SMS,
        self::TYPE_WHATSAPP,
        self::TYPE_MESSENGER,
        self::TYPE_CHATGPT,
        self::TYPE_CLAUDE,
        self::TYPE_PERPLEXITY,
    ];

    public function mount()
    {
        if (!in_array($this->type, self::ALLOWED_TYPES)) {
            throw new \Exception('Unknown share button type: ' . $this->type);
        }

        $this->currentUrl = request()->fullUrl();
        $this->currentTitle = strip_tags(get_the_title());

        if ($this->iconOnly) {
            $this->buttonType = 'primary';
        }

        $this->handleType();
    }

    private function handleType(): void
    {
        /**
         * @var string|null $prompt
         */
        $prompt = match ($this->type) {
            self::TYPE_CHATGPT, self::TYPE_CLAUDE, self::TYPE_PERPLEXITY => 'Résume le texte suivant de manière claire et concise : ',
            default => null,
        };

        /**
         * @var string|null $pageContent
         */
        $pageContent = null;

        $this->label = match ($this->type) {
            self::TYPE_COPY => 'Copier le lien',
            self::TYPE_EMAIL => 'Email',
            self::TYPE_SMS => 'SMS',
            self::TYPE_WHATSAPP => 'WhatsApp',
            self::TYPE_MESSENGER => 'Messenger',
            self::TYPE_CHATGPT => 'ChatGPT',
            self::TYPE_CLAUDE => 'Claude',
            self::TYPE_PERPLEXITY => 'Perplexity',
            default => null,
        };

        $this->icon = match ($this->type) {
            self::TYPE_COPY => 'fas-link',
            self::TYPE_EMAIL => 'far-envelope',
            self::TYPE_SMS => 'far-message',
            self::TYPE_WHATSAPP => 'fab-whatsapp',
            self::TYPE_MESSENGER => 'fab-facebook-messenger',
            self::TYPE_CHATGPT => 'far-brain',
            self::TYPE_CLAUDE => 'far-robot',
            self::TYPE_PERPLEXITY => 'far-lightbulb',
            default => null,
        };

        switch ($this->type) {
            case self::TYPE_EMAIL:
                $emailSubject = rawurlencode(sprintf('Partage Immoval : %s', $this->currentTitle));
                $emailContent = view('share.email-share-page', [
                    'title' => $this->currentTitle,
                    'url' => $this->currentUrl,
                ])->toHtml();

                $this->url = sprintf('mailto:?subject=%s&body=%s', $emailSubject, rawurlencode($emailContent));
                break;
            case self::TYPE_SMS:
                $smsContent = view('share.sms-share-page', [
                    'title' => $this->currentTitle,
                    'url' => $this->currentUrl,
                ])->toHtml();

                $this->url = sprintf('sms:?body=%s', rawurlencode($smsContent));
                break;
            case self::TYPE_WHATSAPP:
                $whatsappContent = view('share.whatsapp-share-page', [
                    'title' => $this->currentTitle,
                    'url' => $this->currentUrl,
                ])->toHtml();

                $this->url = sprintf('https://wa.me/?text=%s', rawurlencode($whatsappContent));
                $this->target = '_blank';
                break;
            case self::TYPE_MESSENGER:
                $this->url = sprintf('https://m.me/?text=%s', rawurlencode($this->currentUrl));
                $this->target = '_blank';
                break;
            case self::TYPE_CHATGPT:
            case self::TYPE_CLAUDE:
            case self::TYPE_PERPLEXITY:
                $this->actionOnClick = sprintf('handleLlm(\'%s\')', $this->type);
                break;
            default:
                break;
        }
    }

    public function handleLlm(string $type): void
    {
        $content = PostService::getRawTextFromPage(
            post: $this->postId,
            maxLength: 5000,
            trimMarker: '[...]',
            excludedBlocks: ['acf/post-summary'],
        );

        if (empty($content)) {
            return;
        }

        $prompt = sprintf('Résume le texte suivant de manière claire et concise : %s', $content);

        $url = sprintf(
            match ($this->type) {
                self::TYPE_CLAUDE => 'https://claude.ai/new?q=%s',
                self::TYPE_CHATGPT => 'https://chatgpt.com/?q=%s',
                self::TYPE_PERPLEXITY => 'https://www.perplexity.ai/?q=%s',
                default => null,
            },
            rawurlencode($prompt),
        );

        if (!empty($url)) {
            $this->js('window.open("' . $url . '", "_blank");');
        }
    }

    public function render(): View|string
    {
        return view('livewire.action.share-button');
    }
}
