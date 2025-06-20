<?php

declare(strict_types=1);

namespace Adeliom\HorizonBlocks\View\Components\Content;

use Adeliom\HorizonTools\Fields\Buttons\ButtonField;
use Adeliom\HorizonTools\Fields\Layout\LayoutField;
use Adeliom\HorizonTools\Fields\Medias\MediaField;
use Adeliom\HorizonTools\Fields\Medias\VideoField;
use Adeliom\HorizonTools\Fields\Text\HeadingField;
use Adeliom\HorizonTools\Fields\Text\UptitleField;
use Adeliom\HorizonTools\Fields\Text\WysiwygField;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class TextMedia extends Component
{
	private const array MEDIA_POSITIONS = [LayoutField::VALUE_MEDIA_POSITION_LEFT, LayoutField::VALUE_MEDIA_POSITION_RIGHT, LayoutField::VALUE_MEDIA_POSITION_BOTTOM];
	private const array MEDIA_RATIOS = ['auto', 'paysage', 'portrait'];

	private const array POSITIONS = [
		'portrait' => [
			LayoutField::VALUE_MEDIA_POSITION_RIGHT => [
				'text' => 'lg:row-start-1 lg:col-span-6',
				'media' => 'max-lg:order-1 lg:col-start-7 lg:col-end-13 xl:col-start-8',
			],
			LayoutField::VALUE_MEDIA_POSITION_LEFT => [
				'text' => 'order-2 lg:col-start-7 lg:col-end-13',
				'media' => 'order-1 lg:col-start-1 lg:col-end-6',
			],
			LayoutField::VALUE_MEDIA_POSITION_BOTTOM => [
				'text' => 'text-center flex flex-col items-center lg:col-start-3 lg:col-end-11',
				'media' => 'lg:col-start-4 lg:col-end-10',
			]
		],
		'paysage' => [
			LayoutField::VALUE_MEDIA_POSITION_LEFT => [
				'text' => 'order-2 lg:col-start-7 lg:col-end-13',
				'media' => 'order-1 lg:col-start-1 lg:col-end-6',
			],
			LayoutField::VALUE_MEDIA_POSITION_RIGHT => [
				'text' => 'lg:row-start-1 lg:col-span-6',
				'media' => 'max-lg:order-1 lg:col-start-7 lg:col-end-13 xl:col-start-8'
			],
			LayoutField::VALUE_MEDIA_POSITION_BOTTOM => [
				'text' => 'text-center flex flex-col items-center lg:col-start-3 lg:col-end-11',
				'media' => 'lg:col-start-3 lg:col-end-11',
			]
		],
	];

	public ?string $containerClass = null;
	public ?string $mediaClass = null;
	public ?string $contentClass = null;

	public ?array $title = null;
	public ?string $uptitle = null;
	public ?string $content = null;
	public ?array $buttons = null;

	public bool $isVideo = false;
	public bool $isImage = false;
	public bool $isYouTube = false;

	public string $mediaPosition = 'left';
	public bool $mediaHasRatio = false;
	public ?string $mediaRatio = null;
	public ?string $ratioClass = null;
	public ?array $image = null;
	public ?array $video = null;
	public ?array $thumbnail = null;
	public ?string $idYouTube = null;
	public ?string $videoUrl = null;

	/**
	 * Create a new component instance.
	 */
	public function __construct(
		public ?array  $fields = [],
		public ?string $class = null,
	)
	{
		$this->handleTitles();
		$this->handleContent();
		$this->handleMediaPosition();
		$this->handleMedia();
		$this->handleMediaRatio();

		$this->handleClasses();
	}

	private function handleTitles(): void
	{
		if (isset($this->fields[HeadingField::NAME]) && $this->fields[HeadingField::NAME]) {
			$this->title = $this->fields[HeadingField::NAME];
		}

		if (isset($this->fields[UptitleField::NAME]) && $this->fields[UptitleField::NAME]) {
			$this->uptitle = $this->fields[UptitleField::NAME];
		}
	}

	private function handleContent(): void
	{
		if (isset($this->fields[WysiwygField::WYSIWYG]) && $this->fields[WysiwygField::WYSIWYG]) {
			$this->content = $this->fields[WysiwygField::WYSIWYG];
		}

		if (isset($this->fields[ButtonField::BUTTONS]) && $this->fields[ButtonField::BUTTONS]) {
			$this->buttons = $this->fields[ButtonField::BUTTONS];
		}
	}

	private function handleMediaPosition(): void
	{
		if (isset($this->fields[LayoutField::FIELD_MEDIA_POSITION]) && $this->fields[LayoutField::FIELD_MEDIA_POSITION]) {
			$tempPosition = $this->fields[LayoutField::FIELD_MEDIA_POSITION];

			if (in_array($tempPosition, self::MEDIA_POSITIONS)) {
				$this->mediaPosition = $tempPosition;
			}
		}
	}

	private function handleMediaRatio(): void
	{
		if ($this->isImage || $this->isVideo || $this->isYouTube) {
			if (isset($this->fields[LayoutField::FIELD_MEDIA_RATIO]) && $this->fields[LayoutField::FIELD_MEDIA_RATIO]) {
				$ratioData = $this->fields[LayoutField::FIELD_MEDIA_RATIO];

				if (is_array($ratioData)) {
					if (isset($ratioData[LayoutField::FIELD_HAS_MEDIA_RATIO]) && $ratioData[LayoutField::FIELD_HAS_MEDIA_RATIO]) {
						if (isset($ratioData[LayoutField::FIELD_MEDIA_RATIO_VALUE]) && $ratioData[LayoutField::FIELD_MEDIA_RATIO_VALUE]) {
							if (in_array($ratioData[LayoutField::FIELD_MEDIA_RATIO_VALUE], self::MEDIA_RATIOS)) {
								$this->mediaHasRatio = true;
								$this->mediaRatio = $ratioData[LayoutField::FIELD_MEDIA_RATIO_VALUE];
							}
						}
					}
				}
			}
		}

		if ($this->mediaHasRatio && $this->mediaRatio === 'auto') {
			$this->mediaRatio = 'paysage';

			$baseImage = null;

			if ($this->isImage) {
				$baseImage = $this->image;
			} elseif ($this->isVideo || $this->isYouTube) {
				$baseImage = $this->thumbnail;
			}

			if ($baseImage && isset($baseImage['height'], $baseImage['width'])) {
				if ($baseImage['height'] > $baseImage['width']) {
					$this->mediaRatio = 'portrait';
				}
			}
		}

		switch ($this->mediaRatio) {
			case 'portrait':
				$this->ratioClass = 'aspect-square';
				break;
			case 'paysage':
				$this->ratioClass = 'aspect-[4/3]';
				break;
			default:
				break;
		}
	}

	private function handleMedia(): void
	{
		if (isset($this->fields[MediaField::MEDIA]) && $this->fields[MediaField::MEDIA]) {
			$mediaData = $this->fields[MediaField::MEDIA];

			if (is_array($mediaData) && isset($mediaData[MediaField::TYPE])) {
				$type = $mediaData[MediaField::TYPE];

				switch ($type) {
					case 'image':
						if (isset($mediaData['image'])) {
							if ($mediaData['image']) {
								$this->isImage = true;
								$this->image = $mediaData['image'];
							}
						}
						break;
					case 'video':
						if (isset($mediaData['video'])) {
							if (isset($mediaData['video'][VideoField::IS_YOUTUBE])) {
								if ($mediaData['video'][VideoField::IS_YOUTUBE]) {
									if (isset($mediaData['video'][VideoField::ID_YOUTUBE])) {
										$this->isYouTube = true;
										$this->idYouTube = $mediaData['video'][VideoField::ID_YOUTUBE];
										$this->videoUrl = 'https://www.youtube.com/embed/' . $this->idYouTube;
									}
								} elseif (isset($mediaData['video'][VideoField::VIDEO_FILE]) && $mediaData['video'][VideoField::VIDEO_FILE]) {
									$file = $mediaData['video'][VideoField::VIDEO_FILE];
									if (is_array($file)) {
										$this->isVideo = true;
										$this->video = $file;
										$this->videoUrl = $file['url'];
									}
								}
							}

							if ($this->isVideo || $this->isYouTube) {
								if (isset($mediaData['video'][VideoField::THUMBNAIL])) {
									$this->thumbnail = $mediaData['video'][VideoField::THUMBNAIL];
								}
							}
						}
						break;
					default:
						break;
				}
			}
		}
	}

	private function handleClasses(): void
	{
		$this->containerClass = 'grid items-center gap-6 lg:grid-cols-12';

		$this->mediaClass = implode(' ', [
			'col-span-full',
			self::POSITIONS[$this->mediaRatio ?? 'paysage'][$this->mediaPosition]['media'],
		]);

		$this->contentClass = implode(' ', [
			'col-span-full',
			self::POSITIONS[$this->mediaRatio ?? 'paysage'][$this->mediaPosition]['text'],
		]);
	}

	/**
	 * Get the view / contents that represent the component.
	 */
	public function render(): View|Closure|string
	{
		return view('components.content.text-media');
	}
}
