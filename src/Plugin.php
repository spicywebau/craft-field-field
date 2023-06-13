<?php

namespace spicyweb\fieldfield;

use craft\base\Plugin as BasePlugin;
use craft\events\RegisterComponentTypesEvent;
use craft\services\Fields;
use spicyweb\fieldfield\fields\FieldField;
use yii\base\Event;

/**
 * Main Field Field plugin class.
 *
 * @package spicyweb\fieldfield
 * @author Spicy Web <plugins@spicyweb.com.au>
 * @since 1.0.0
 */
class Plugin extends BasePlugin
{
    /**
     * @inheritdoc
     */
    public function init(): void
    {
        parent::init();

        Event::on(
            Fields::class,
            Fields::EVENT_REGISTER_FIELD_TYPES,
            function(RegisterComponentTypesEvent $event) {
                // $event->types[] = FieldField::class;
            }
        );
    }
}
