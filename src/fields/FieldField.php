<?php

namespace spicyweb\fieldfield\fields;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\base\PreviewableFieldInterface;
use craft\helpers\ArrayHelper;
use craft\helpers\Cp;
use craft\helpers\Json;
use spicyweb\fieldfield\collections\FieldCollection;

/**
 * Field field type class.
 *
 * @package spicyweb\fieldfield\fields
 * @author Spicy Web <plugins@spicyweb.com.au>
 * @since 1.0.0
 */
class FieldField extends Field implements PreviewableFieldInterface
{
    /**
     * @var string|string[] Which fields can be selected with this field.
     * @since 2.0.0
     */
    public string|array $allowedFields = '*';

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('field-field', 'Fields');
    }

    /**
     * @inheritdoc
     */
    public static function icon(): string
    {
        return 'pen-to-square';
    }

    /**
     * @inheritdoc
     */
    public function settingsAttributes(): array
    {
        $attributes = parent::settingsAttributes();
        $attributes[] = 'allowedFields';

        return $attributes;
    }

    /**
     * @inheritdoc
     */
    protected function inputHtml(mixed $value, ?ElementInterface $element = null, bool $inline = false): string
    {
        return Craft::$app->getView()->renderTemplate(
            '_includes/forms/componentSelect.twig',
            [
                'name' => $this->handle . '[]',
                'values' => $value?->all() ?? [],
                'options' => $this->_options(),
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function getSettingsHtml(): ?string
    {
        return Cp::checkboxSelectFieldHtml([
            'label' => Craft::t('field-field', 'Allowed Fields'),
            'instructions' => Craft::t('field-field', 'Which fields can be selected with this field.'),
            'id' => 'allowedFields',
            'name' => 'allowedFields',
            'options' => array_map(
                fn($field) => [
                    'label' => Craft::t('site', $field->name),
                    'value' => $field->uid,
                ],
                Craft::$app->getFields()->getAllFields()
            ),
            'values' => $this->allowedFields,
            'showAllOption' => true,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function normalizeValue(mixed $value, ?ElementInterface $element = null): mixed
    {
        if ($value instanceof FieldCollection) {
            return $value;
        }

        if (!is_array($value)) {
            $value = Json::decodeIfJson($value);
        }

        if (empty($value)) {
            return FieldCollection::make([]);
        }

        $allFields = ArrayHelper::index(Craft::$app->getFields()->getAllFields(), 'id');
        $fieldFields = array_values(array_filter(array_map(
            fn($id) => $allFields[$id] ?? null,
            $value
        )));

        return FieldCollection::make($fieldFields);
    }

    /**
     * @inheritdoc
     */
    public function serializeValue(mixed $value, ?ElementInterface $element = null): mixed
    {
        return $value?->ids()->all();
    }

    /**
     * @inheritdoc
     */
    private function _options(): array
    {
        $allFields = Craft::$app->getFields()->getAllFields();

        if (is_array($this->allowedFields)) {
            return array_filter($allFields, fn($field) => in_array($field->uid, $this->allowedFields));
        }

        return $allFields;
    }
}
