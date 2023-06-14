<?php

/*
The `FieldField::inputHtml()` method is based on a large section of the
`\craft\fields\MultiSelect::inputHtml()` method, from Craft CMS 4.4.13, by Pixel & Tonic, Inc.
https://github.com/craftcms/cms/blob/4.4.13/src/fields/MultiSelect.php#L53-L85
Craft CMS is released under the terms of the Craft License, a copy of which is included below.
https://github.com/craftcms/cms/blob/4.4.13/LICENSE.md

Copyright © Pixel & Tonic

Permission is hereby granted to any person obtaining a copy of this software
(the “Software”) to use, copy, modify, merge, publish and/or distribute copies
of the Software, and to permit persons to whom the Software is furnished to do
so, subject to the following conditions:

1. **Don’t plagiarize.** The above copyright notice and this license shall be
   included in all copies or substantial portions of the Software.

2. **Don’t use the same license on more than one project.** Each licensed copy
   of the Software shall be actively installed in no more than one production
   environment at a time.

3. **Don’t mess with the licensing features.** Software features related to
   licensing shall not be altered or circumvented in any way, including (but
   not limited to) license validation, payment prompts, feature restrictions,
   and update eligibility.

4. **Pay up.** Payment shall be made immediately upon receipt of any notice,
   prompt, reminder, or other message indicating that a payment is owed.

5. **Follow the law.** All use of the Software shall not violate any applicable
   law or regulation, nor infringe the rights of any other person or entity.

Failure to comply with the foregoing conditions will automatically and
immediately result in termination of the permission granted hereby. This
license does not include any right to receive updates to the Software or
technical support. Licensees bear all risk related to the quality and
performance of the Software and any modifications made or obtained to it,
including liability for actual and consequential harm, such as loss or
corruption of data, and any necessary service, repair, or correction.

THE SOFTWARE IS PROVIDED “AS IS”, WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES, OR OTHER
LIABILITY, INCLUDING SPECIAL, INCIDENTAL AND CONSEQUENTIAL DAMAGES, WHETHER IN
AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

namespace spicyweb\fieldfield\fields;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\base\PreviewableFieldInterface;
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
     * @var string|string[] Which field groups a selectable field must belong to.
     */
    public string|array $allowedFieldGroups = '*';

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
    public function settingsAttributes(): array
    {
        $attributes = parent::settingsAttributes();
        $attributes[] = 'allowedFieldGroups';

        return $attributes;
    }

    /**
     * @inheritdoc
     */
    protected function inputHtml(mixed $value, ?ElementInterface $element = null): string
    {
        $view = Craft::$app->getView();

        if (empty($value) || $value->isEmpty()) {
            $view->setInitialDeltaValue($this->handle, null);
        }

        $id = $this->getInputId();

        $view->registerJsWithVars(fn($id) => <<<JS
$('#' + $id).selectize({
  plugins: ['remove_button'],
  dropdownParent: 'body',
});
JS, [
            $view->namespaceInputId($id),
        ]);

        return Cp::multiSelectHtml([
            'id' => $id,
            'describedBy' => $this->describedBy,
            'class' => 'selectize',
            'name' => $this->handle,
            'values' => $value?->ids(),
            'options' => $this->_options(),
            'inputAttributes' => [
                'style' => [
                    'display' => 'none', // Hide it before selectize does its thing
                ],
            ],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getSettingsHtml(): ?string
    {
        return Cp::checkboxSelectFieldHtml([
            'label' => Craft::t('field-field', 'Allowed Field Groups'),
            'instructions' => Craft::t('field-field', 'Which field groups a selectable field must belong to.'),
            'id' => 'allowedFieldGroups',
            'name' => 'allowedFieldGroups',
            'options' => array_map(
                fn($group) => [
                    'label' => Craft::t('site', $group->name),
                    'value' => $group->uid,
                ],
                Craft::$app->getFields()->getAllGroups()
            ),
            'values' => $this->allowedFieldGroups,
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

        $fields = array_filter(
            Craft::$app->getFields()->getAllFields(),
            fn($field) => in_array($field->id, $value)
        );
        $fields = array_values($fields);

        return FieldCollection::make($fields);
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
        $fieldsService = Craft::$app->getFields();
        $options = [];

        foreach ($fieldsService->getAllGroups() as $group) {
            // Ensure the group is allowed to be selected from
            if (is_array($this->allowedFieldGroups) && !in_array($group->uid, $this->allowedFieldGroups)) {
                continue;
            }

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
