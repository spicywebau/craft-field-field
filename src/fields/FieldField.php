<?php

namespace spicyweb\fieldfield\fields;

use Craft;
use craft\fields\MultiSelect;

/**
 * Field field type class.
 *
 * @package spicyweb\fieldfield\fields
 * @author Spicy Web <plugins@spicyweb.com.au>
 * @since 1.0.0
 */
class FieldField extends MultiSelect
{
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
    public function getSettingsHtml(): ?string
    {
        // No settings at this stage
        return null;
    }

    /**
     * @inheritdoc
     */
    protected function options(): array
    {
        $fieldsService = Craft::$app->getFields();
        $options = [];

        foreach ($fieldsService->getAllGroups() as $group) {
            $options[] = [
                'optgroup' => $group->name,
            ];

            foreach ($fieldsService->getFieldsByGroupId($group->id) as $field) {
                $options[] = [
                    'label' => $field->name,
                    'value' => $field->id,
                ];
            }
        }

        return $options;
    }
}
