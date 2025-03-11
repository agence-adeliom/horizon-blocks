<?php

declare(strict_types=1);

namespace Adeliom\HorizonBlocks\Blocks\Listing;

use Adeliom\HorizonTools\Blocks\AbstractBlock;
use Adeliom\HorizonTools\Enum\FilterTypesEnum;
use Adeliom\HorizonTools\Fields\Select\PostTypeSelectField;
use Adeliom\HorizonTools\Fields\Tabs\ContentTab;
use Adeliom\HorizonTools\Fields\Tabs\LayoutTab;
use Adeliom\HorizonTools\Fields\Tabs\SettingsTab;
use Adeliom\HorizonTools\Fields\Text\HeadingField;
use Adeliom\HorizonTools\Fields\Text\UptitleField;
use Adeliom\HorizonTools\Services\BudService;
use Adeliom\HorizonTools\Services\ClassService;
use Adeliom\HorizonTools\Services\FileService;
use Adeliom\HorizonTools\Taxonomies\AbstractTaxonomy;
use Extended\ACF\ConditionalLogic;
use Extended\ACF\Fields\ButtonGroup;
use Extended\ACF\Fields\Field;
use Extended\ACF\Fields\Group;
use Extended\ACF\Fields\Message;
use Extended\ACF\Fields\Number;
use Extended\ACF\Fields\RadioButton;
use Extended\ACF\Fields\Repeater;
use Extended\ACF\Fields\Select;
use Extended\ACF\Fields\Text;
use Extended\ACF\Fields\TrueFalse;
use Extended\ACF\Location;

class ListingBlock extends AbstractBlock
{
	public static ?string $slug = 'listing';
	public static ?string $title = 'Liste d’éléments';
	public static ?string $mode = 'preview';
    public static ?string $icon = 'list-view';

	private array $treatedFields = [];

	public const bool USE_FIELDS_TO_DEFINE_FILTERS = true;
	public const bool ALWAYS_DISPLAY_FILTERS = true;

	public const string FIELD_PER_PAGE = 'perPage';
	public const string FIELD_FILTERS = 'filters';
	public const string FIELD_SECONDARY_FILTERS = 'secondaryFilters';
	public const string FIELD_SECONDARY_FILTERS_BUTTON_LABEL = 'secondaryFiltersButtonLabel';
	public const string FIELD_SECONDARY_FILTERS_TITLE = 'secondaryFiltersTitle';
	public const string FIELD_WITH_SECONDARY_FILTERS = 'withSecondaryFilters';

	public const string FIELD_FILTERS_TYPE = 'type';
	public const string FIELD_FILTERS_FIELD = 'field';
	public const string FIELD_FILTERS_TAXONOMY = 'taxonomy';
	public const string FIELD_FILTERS_APPEARANCE = 'appearance';
	public const string FIELD_FILTERS_META_APPEARANCE = self::FIELD_FILTERS_APPEARANCE . 'Meta';
	public const string FIELD_FILTERS_TAX_APPEARANCE = self::FIELD_FILTERS_APPEARANCE . 'Tax';
	public const string FIELD_FILTERS_NAME = 'name';
	public const string FIELD_FILTERS_PLACEHOLDER = 'placeholder';

	public const string VALUE_FILTER_APPEARANCE_SELECT = 'select';
	public const string VALUE_FILTER_APPEARANCE_CHECKBOX = 'checkbox';
	public const string VALUE_FILTER_APPEARANCE_RADIO = 'radio';
	public const string VALUE_FILTER_APPEARANCE_TEXT = 'text';
	public const string VALUE_FILTER_APPEARANCE_MULTISELECT = 'multiselect';
	public const string VALUE_FILTER_APPEARANCE_SINGLESELECT = 'singleselect';

	public function getFields(): ?iterable
	{
		$postTypeField = PostTypeSelectField::make(callback: function ($postType): bool {
			if (property_exists($postType, 'availableInListingBlock')) {
				return (bool)$postType::$availableInListingBlock;
			}

			return false;
		});

		if (self::USE_FIELDS_TO_DEFINE_FILTERS) {
			$postTypeField->helperText('Enregistrez la page après modification de ce champ pour afficher les bonnes valeurs dans les filtres.');
		}

		yield from ContentTab::make()->fields([
			UptitleField::make(),
			HeadingField::make()->required(),
			$postTypeField
		]);

		yield from LayoutTab::make()->fields([
			Number::make(__('Nombre d’éléments par page'), self::FIELD_PER_PAGE)
				->max(24)
				->min(3)
				->step(3)
		]);

		if (self::USE_FIELDS_TO_DEFINE_FILTERS) {
			yield from self::filterFields();
		}
	}

	private function getFilterRepeaterFields(int $level = 1): array
	{
		$filterFields = [];

		$availableFields = $this->getAvailableFilterChoices(level: $level);
		$availableTaxonomies = $this->getAvailableTaxonomies(level: $level);

		$typeChoices = [];
		if (!empty($availableFields) || self::ALWAYS_DISPLAY_FILTERS) {
			$typeChoices[FilterTypesEnum::META->value] = __('Méta');
		}

		if (!empty($availableTaxonomies) || self::ALWAYS_DISPLAY_FILTERS) {
			$typeChoices[FilterTypesEnum::TAXONOMY->value] = __('Taxonomie');
		}

		$typeChoices[FilterTypesEnum::SEARCH->value] = __('Recherche');

		if (!empty($availableFields) || !empty($availableTaxonomies) || self::ALWAYS_DISPLAY_FILTERS) {
			$filterFields[] = ButtonGroup::make(__('Type'), self::FIELD_FILTERS_TYPE)
				->required()
				->choices($typeChoices);
			$filterFields[] = Message::make(__('Recherche'), 'searchinfo')
				->body(__('Actuellement, seule la recherche dans le titre et dans le contenu de l’élément sont prises en charge.'))
				->conditionalLogic([
					ConditionalLogic::where(self::FIELD_FILTERS_TYPE, '==', FilterTypesEnum::SEARCH->value)
				]);
			$filterFields[] = Text::make(__('Nom du filtre'), self::FIELD_FILTERS_NAME)->required();
			$filterFields[] = Text::make(__('Texte par défaut du filtre'), self::FIELD_FILTERS_PLACEHOLDER)->helperText(__('Si non renseigné, le nom sera utilisé'));
			$filterFields[] = RadioButton::make(__('Apparence du filtre'), self::FIELD_FILTERS_META_APPEARANCE)->choices([
				self::VALUE_FILTER_APPEARANCE_SELECT => 'Sélection',
				self::VALUE_FILTER_APPEARANCE_CHECKBOX => 'Cases à cocher',
				self::VALUE_FILTER_APPEARANCE_RADIO => 'Choix unique',
				self::VALUE_FILTER_APPEARANCE_TEXT => 'Champ libre',
				self::VALUE_FILTER_APPEARANCE_MULTISELECT => 'Sélection multiple',
				self::VALUE_FILTER_APPEARANCE_SINGLESELECT => 'Sélection unique',
			])
				->default(self::VALUE_FILTER_APPEARANCE_SELECT)
				->conditionalLogic([ConditionalLogic::where(self::FIELD_FILTERS_TYPE, '==', FilterTypesEnum::META->value)]);
			$filterFields[] = RadioButton::make(__('Apparence du filtre'), self::FIELD_FILTERS_TAX_APPEARANCE)->choices([
				self::VALUE_FILTER_APPEARANCE_SELECT => 'Sélection',
				self::VALUE_FILTER_APPEARANCE_CHECKBOX => 'Cases à cocher',
				self::VALUE_FILTER_APPEARANCE_RADIO => 'Choix unique',
				self::VALUE_FILTER_APPEARANCE_MULTISELECT => 'Sélection multiple',
				self::VALUE_FILTER_APPEARANCE_SINGLESELECT => 'Sélection unique',
			])
				->default(self::VALUE_FILTER_APPEARANCE_SELECT)
				->conditionalLogic([ConditionalLogic::where(self::FIELD_FILTERS_TYPE, '==', FilterTypesEnum::TAXONOMY->value)]);
			$filterFields[] = Select::make(__('Champ'), self::FIELD_FILTERS_FIELD)
				->stylized()
				->helperText(__('Si aucune option n’est sélectionnée, le filtre ne s’affichera pas.'))
				->choices($availableFields)
				->lazyLoad()
				->conditionalLogic([ConditionalLogic::where(self::FIELD_FILTERS_TYPE, '==', FilterTypesEnum::META->value)]);
			$filterFields[] = Select::make(__('Taxonomie'), self::FIELD_FILTERS_TAXONOMY)
				->stylized()
				->helperText(__('Si aucune option n’est sélectionnée, le filtre ne s’affichera pas.'))
				->choices($availableTaxonomies)
				->lazyLoad()
				->conditionalLogic([ConditionalLogic::where(self::FIELD_FILTERS_TYPE, '==', FilterTypesEnum::TAXONOMY->value)]);
		}

		return $filterFields;
	}

	private function filterFields(): iterable
	{
		if (!empty($availableFields) || !empty($availableTaxonomies) || self::ALWAYS_DISPLAY_FILTERS) {
			yield from SettingsTab::make()->fields([
				Repeater::make(__('Filtres primaires'), self::FIELD_FILTERS)
					->button(__('Ajouter un filtre'))
					->layout('block')
					->minRows(0)
					->maxRows(3)
					->fields($this->getFilterRepeaterFields()),
				TrueFalse::make(__('Activer les filtres secondaires'), self::FIELD_WITH_SECONDARY_FILTERS)
					->stylized(),
				Text::make(__('Label du bouton'), self::FIELD_SECONDARY_FILTERS_BUTTON_LABEL)
					->helperText(__('Texte affiché sur le bouton pour afficher les filtres secondaires'))
					->placeholder('Filtres avancés')
					->conditionalLogic([ConditionalLogic::where(self::FIELD_WITH_SECONDARY_FILTERS, '==', 1)]),
				Text::make(__('Titre des filtres secondaires'), self::FIELD_SECONDARY_FILTERS_TITLE)
					->helperText(__('Titre affiché au-dessus des filtres secondaires'))->placeholder('Filtres avancés')
					->conditionalLogic([ConditionalLogic::where(self::FIELD_WITH_SECONDARY_FILTERS, '==', 1)]),
				Repeater::make(__('Filtres secondaires'), self::FIELD_SECONDARY_FILTERS)
					->button(__('Ajouter un filtre secondaire'))
					->layout('block')
					->fields($this->getFilterRepeaterFields(level: 2))
					->conditionalLogic([ConditionalLogic::where(self::FIELD_WITH_SECONDARY_FILTERS, '==', 1)]),
			]);
		}
	}

	public function addToContext(): array
	{
		return [];
	}

	public function renderBlockCallback(): void
	{
		if ($listingJs = BudService::getUrl('listing.js')) {
			wp_enqueue_script('listing-block', $listingJs);
		}
	}

	private function getAvailableFilterChoices(int $level = 1): array
	{
		$postType = $this->getFilteredPostType();
		$fieldChoices = [];

		if ($postType) {
			$fields = $this->getPostTypeFields(postTypeSlug: $postType, level: $level);

			foreach ($fields as $field) {
				$fieldChoices[sprintf('%s_%s', $field['type'], $field['name'])] = $field['label'];
			}
		}

		return $fieldChoices;
	}

	private function getAvailableTaxonomies(int $level = 1): array
	{
		$postType = $this->getFilteredPostType();
		$taxonomyChoices = [];

		switch ($postType) {
			case 'post':
				$taxonomyChoices['category'] = __('Catégories');
				break;
			case 'page':
			default:
				break;
		}

		if ($postType) {
			if ($taxonomies = FileService::getCustomTaxonomyFiles()) {
				foreach ($taxonomies as $taxonomy) {
					require_once $taxonomy;
				}

				if ($availableTaxonomies = ClassService::getAllCustomTaxonomyClasses()) {
					foreach ($availableTaxonomies as $availableTaxonomy) {
						$availableTaxonomy = new $availableTaxonomy();

						if ($availableTaxonomy instanceof AbstractTaxonomy) {
							if (in_array($postType, $availableTaxonomy->getPostTypes())) {
								$name = get_class($availableTaxonomy);

								if (isset($availableTaxonomy->getConfig()['args']['label'])) {
									$name = $availableTaxonomy->getConfig()['args']['label'];
								}

								if (property_exists($availableTaxonomy, 'slug')) {
									$taxonomyChoices[$availableTaxonomy::$slug] = $name;
								}
							}
						}
					}
				}
			}
		}

		return $taxonomyChoices;
	}

	private function getPostTypeFields(string $postTypeSlug, int $level = 1): array
	{
		$fields = [];

		if (in_array($postTypeSlug, ['post', 'page'])) {
			foreach (FileService::getCustomAdminFiles() as $customAdminFile) {
				require_once $customAdminFile;
			}
			foreach (ClassService::getAllCustomAdminClasses() as $adminClass) {
				if (method_exists($adminClass, 'getLocation') && method_exists($adminClass, 'getFields')) {
					$class = new $adminClass();
					foreach (iterator_to_array($class->getLocation(), false) as $item) {
						if ($item instanceof Location) {
							// Use reflection to get protected "rules" property
							$reflection = new \ReflectionClass($item);

							$property = $reflection->getProperty('rules');
							$rules = $property->getValue($item);

							foreach ($rules as $rule) {
								if (isset($rule['param'], $rule['operator'], $rule['value'])) {
									if ($rule['param'] === 'post_type') {
										if (($rule['operator'] === '==' && $rule['value'] === $postTypeSlug) || $rule['operator'] === '!=' && $rule['value'] !== $postTypeSlug) {
											$this->handleFieldClass(classInstance: $class, fields: $fields, level: $level);
										}
									}
								}
							}
						}
					}
				}
			}
		} else {
			$class = ClassService::getPostTypeClassBySlug(slug: $postTypeSlug);
			$classInstance = new $class();

			$this->handleFieldClass(classInstance: $classInstance, fields: $fields, level: $level);
		}

		return $fields;
	}

	private function handleFieldClass($classInstance, array &$fields, int $level = 1): void
	{
		if (method_exists($classInstance, 'getFields')) {
			if ($classFields = iterator_to_array($classInstance->getFields(), preserve_keys: false)) {
				$this->handleFields(fields: $classFields, array: $fields, level: $level);
			}
		}
	}

	/**
	 * @param Field[] $fields
	 */
	private function handleFields(array $fields, array &$array = [], int $level = 1): void
	{
		if ($level === 1) {
			foreach ($fields as $field) {
				$key = null;

				$fieldData = $field->get();

				if ($field instanceof Group) {
					if (isset($fieldData['sub_fields']) && is_array($fieldData['sub_fields'])) {
						if (isset($fieldData['name'])) {
							$key = sprintf('%s_', $fieldData['name']);
						}


						foreach ($fieldData['sub_fields'] as $subField) {
							$toAdd = [];

							if (isset($subField['type'])) {
								$toAdd['type'] = $subField['type'];
							}

							if (isset($subField['name'])) {
								$toAdd['name'] = sprintf('%s%s', $key, $subField['name']);
							}

							if (isset($subField['key'])) {
								$toAdd['key'] = $subField['key'];
							}

							if (isset($subField['label'])) {
								$toAdd['label'] = $subField['label'];
							}

							$this->treatedFields[$toAdd['name']] = $toAdd;

							$array[] = $toAdd;
						}
					}
				} else {
					if ($level === 1) {
						$fieldData = $field->get();
					}

					$toAdd = [];
					if (isset($fieldData['type'])) {
						$toAdd['type'] = $fieldData['type'];
					}

					if (isset($fieldData['name'])) {
						$toAdd['name'] = $fieldData['name'];
					}

					if (isset($fieldData['key'])) {
						$toAdd['key'] = $fieldData['key'];
					}

					if (isset($fieldData['label'])) {
						$toAdd['label'] = $fieldData['label'];
					}

					$this->treatedFields[$toAdd['name']] = $toAdd;

					$array[] = $toAdd;
				}
			}
		} else {
			$array = array_values($this->treatedFields);
		}
	}

	private function getFilteredPostType(): ?string
	{
		$selectedPostType = null;

		$postId = $_GET['post'] ?? $_POST['post_id'] ?? null;

		if ($postId) {
			$post = get_post($postId);

			if ($post && $post->post_content) {
				preg_match('/<!-- wp:acf\/listing.*?"postType":"([^"]+)".*?-->/', $post->post_content, $matches);

				if (isset($matches[1])) {
					$selectedPostType = $matches[1];
				}
			}
		}

		return $selectedPostType;
	}
}