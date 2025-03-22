<?php

namespace Drupal\appointment\Entity\Agency;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Defines the Agency entity.
 *
 * @ContentEntityType(
 *   id = "agency",
 *   label = @Translation("Agency"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\appointment\AgencyListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "form" = {
 *       "add" = "Drupal\appointment\Form\AgencyForm",
 *       "edit" = "Drupal\appointment\Form\AgencyForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider"
 *     },
 *     "access" = "Drupal\appointment\AgencyAccessControlHandler",
 *   },
 *   base_table = "agency",
 *   data_table = "agency_field_data",
 *   translatable = TRUE,
 *   admin_permission = "administer agency",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "langcode" = "langcode",
 *   },
 *   links = {
 *     "add-form" = "/admin/structure/agency/add",
 *     "canonical" = "/admin/structure/agency/{agency}",
 *     "edit-form" = "/admin/structure/agency/{agency}/edit",
 *     "delete-form" = "/admin/structure/agency/{agency}/delete",
 *     "collection" = "/admin/structure/agency"
 *   }
 * )
 */
class Agency extends ContentEntityBase
{
  /**
   * {@inheritdoc}
   */
    public static function baseFieldDefinitions(EntityTypeInterface $entity_type)
    {
        $fields = parent::baseFieldDefinitions($entity_type);

        $fields['name'] = BaseFieldDefinition::create('string')
        ->setLabel(t('Name'))
        ->setRequired(true)
        ->setTranslatable(true)
        ->setSettings([
        'max_length' => 255,
        'text_processing' => 0,
        ])
        ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
        'weight' => -5,
        ])
        ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
        ])
        ->setDisplayConfigurable('form', true)
        ->setDisplayConfigurable('view', true);

        $fields['address'] = BaseFieldDefinition::create('string_long')
        ->setLabel(t('Address'))
        ->setRequired(true)
        ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
        ])
        ->setDisplayOptions('form', [
        'type' => 'string_textarea',
        'weight' => -4,
        ])
        ->setDisplayConfigurable('form', true)
        ->setDisplayConfigurable('view', true);

        $fields['phone'] = BaseFieldDefinition::create('telephone')
        ->setLabel(t('Phone'))
        ->setRequired(true)
        ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -3,
        ])
        ->setDisplayOptions('form', [
        'type' => 'telephone_default',
        'weight' => -3,
        ])
        ->setDisplayConfigurable('form', true)
        ->setDisplayConfigurable('view', true);

        $fields['status'] = BaseFieldDefinition::create('boolean')
        ->setLabel(t('Status'))
        ->setDefaultValue(true)
        ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'weight' => -2,
        ]);
        $fields['email'] = BaseFieldDefinition::create('email')
        ->setLabel(t('Email'))
        ->setRequired(true)
        ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'email_mailto',
        'weight' => -2,
        ])
        ->setDisplayOptions('form', [
        'type' => 'email_default',
        'weight' => -2,
        ])
        ->setDisplayConfigurable('form', true)
        ->setDisplayConfigurable('view', true);

    // Operating Hours
        $fields['operating_hours_start'] = BaseFieldDefinition::create('string')
        ->setLabel(t('Opening Time'))
        ->setRequired(true)
        ->setSettings([
        'max_length' => 5,
        ])
        ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -1,
        ])
        ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -1,
        ])
        ->setDisplayConfigurable('form', true)
        ->setDisplayConfigurable('view', true);

        $fields['operating_hours_end'] = BaseFieldDefinition::create('string')
        ->setLabel(t('Closing Time'))
        ->setRequired(true)
        ->setSettings([
        'max_length' => 5,
        ])
        ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => 0,
        ])
        ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 0,
        ])
        ->setDisplayConfigurable('form', true)
        ->setDisplayConfigurable('view', true);

        $fields['status'] = BaseFieldDefinition::create('boolean')
        ->setLabel(t('Status'))
        ->setDefaultValue(true)
        ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'weight' => 1,
        ])
        ->setDisplayConfigurable('form', true)
        ->setDisplayConfigurable('view', true);
        // Add operating days
        $fields['operating_days'] = BaseFieldDefinition::create('list_string')
        ->setLabel(t('Operating Days'))
        ->setRequired(true)
        ->setCardinality(-1) // Multiple values
        ->setSettings([
        'allowed_values' => [
        'monday' => 'Monday',
        'tuesday' => 'Tuesday',
        'wednesday' => 'Wednesday',
        'thursday' => 'Thursday',
        'friday' => 'Friday',
        'saturday' => 'Saturday',
        'sunday' => 'Sunday',
        ],
        ])
        ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'list_default',
        'weight' => -1,
        ])
        ->setDisplayOptions('form', [
        'type' => 'options_buttons',
        'weight' => -1,
        ])
        ->setDisplayConfigurable('form', true)
        ->setDisplayConfigurable('view', true);

        return $fields;
    }
}
