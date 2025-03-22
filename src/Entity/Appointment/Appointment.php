<?php

namespace Drupal\appointment\Entity\Appointment;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\user\EntityOwnerTrait;
use Drupal\Core\Entity\EntityChangedTrait;

/**
 * Defines the Appointment entity.
 *
 * @ContentEntityType(
 *   id = "appointment",
 *   label = @Translation("Appointment"),
 *   label_collection = @Translation("Appointments"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\appointment\AppointmentListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "form" = {
 *       "add" = "Drupal\appointment\Form\AppointmentForm",
 *       "edit" = "Drupal\appointment\Form\AppointmentForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider"
 *     },
 *     "access" = "Drupal\appointment\AppointmentAccessControlHandler",
 *   },
 *   base_table = "appointment",
 *   data_table = "appointment_field_data",
 *   translatable = TRUE,
 *   admin_permission = "administer appointments",
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid",
 *     "label" = "title",
 *     "owner" = "uid",
 *     "langcode" = "langcode",
 *   },
 *   links = {
 *     "canonical" = "/appointment/{appointment}",
 *     "add-form" = "/appointment/add",
 *     "edit-form" = "/appointment/{appointment}/edit",
 *     "delete-form" = "/appointment/{appointment}/delete",
 *     "collection" = "/admin/content/appointments"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "type",
 *     "description"
 *   }
 * )
 */
class Appointment extends ContentEntityBase implements AppointmentInterface
{
    use EntityOwnerTrait;
    use EntityChangedTrait; // Add this trait

  /**
   * {@inheritdoc}
   */
    public static function baseFieldDefinitions(EntityTypeInterface $entity_type)
    {
        $fields = parent::baseFieldDefinitions($entity_type);
        $fields += static::ownerBaseFieldDefinitions($entity_type);

        $fields['title'] = BaseFieldDefinition::create('string')
        ->setLabel(t('Title'))
        ->setRequired(true)
        ->setTranslatable(true)
        ->setSetting('max_length', 255)
        ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
        ])
        ->setDisplayConfigurable('form', true)
        ->setDisplayConfigurable('view', true);

        $fields['appointment_date'] = BaseFieldDefinition::create('datetime')
        ->setLabel(t('Appointment Date'))
        ->setRequired(true)
        ->setSetting('datetime_type', 'datetime')
        ->setDisplayOptions('form', [
          'type' => 'datetime_default',
          'weight' => -4,
        ])
        ->setDisplayOptions('view', [
          'label' => 'above',
          'type' => 'datetime_default',
          'settings' => [
            'format_type' => 'medium',
          ],
          'weight' => -4,
        ])
        ->setDisplayConfigurable('form', true)
        ->setDisplayConfigurable('view', true);

        $fields['customer_name'] = BaseFieldDefinition::create('string')
        ->setLabel(t('Customer Name'))
        ->setRequired(true)
        ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -3,
        ])
        ->setDisplayConfigurable('form', true)
        ->setDisplayConfigurable('view', true);

        $fields['customer_email'] = BaseFieldDefinition::create('email')
        ->setLabel(t('Customer Email'))
        ->setRequired(true)
        ->setDisplayOptions('form', [
        'type' => 'email_default',
        'weight' => -2,
        ])
        ->setDisplayConfigurable('form', true)
        ->setDisplayConfigurable('view', true);

        $fields['customer_phone'] = BaseFieldDefinition::create('telephone')
        ->setLabel(t('Customer Phone'))
        ->setRequired(true)
        ->setDisplayOptions('form', [
        'type' => 'telephone_default',
        'weight' => -1,
        ])
        ->setDisplayConfigurable('form', true)
        ->setDisplayConfigurable('view', true);

        $fields['status'] = BaseFieldDefinition::create('list_string')
        ->setLabel(t('Status'))
        ->setRequired(true)
        ->setSettings([
        'allowed_values' => [
          'pending' => 'Pending',
          'confirmed' => 'Confirmed',
          'cancelled' => 'Cancelled',
        ],
        ])
        ->setDefaultValue('pending')
        ->setDisplayOptions('form', [
        'type' => 'options_select',
        'weight' => 0,
        ])
        ->setDisplayConfigurable('form', true)
        ->setDisplayConfigurable('view', true);

        $fields['notes'] = BaseFieldDefinition::create('text_long')
        ->setLabel(t('Notes'))
        ->setDisplayOptions('form', [
        'type' => 'text_textarea',
        'weight' => 1,
        ])
        ->setDisplayConfigurable('form', true)
        ->setDisplayConfigurable('view', true);

        $fields['created'] = BaseFieldDefinition::create('created')
        ->setLabel(t('Created'))
        ->setDescription(t('The time that the appointment was created.'));

        $fields['changed'] = BaseFieldDefinition::create('changed')
        ->setLabel(t('Changed'))
        ->setDescription(t('The time that the appointment was last edited.'));

        $fields['agency'] = BaseFieldDefinition::create('entity_reference')
        ->setLabel(t('Agency'))
        ->setDescription(t('The agency where the appointment will take place.'))
        ->setRequired(true)
        ->setSetting('target_type', 'agency')
        ->setDisplayOptions('view', [
          'label' => 'above',
          'type' => 'entity_reference_label',
          'weight' => -3,
        ])
        ->setDisplayOptions('form', [
          'type' => 'entity_reference_autocomplete',
          'weight' => -3,
          'settings' => [
            'match_operator' => 'CONTAINS',
            'size' => '60',
            'placeholder' => '',
          ],
        ])
        ->setDisplayConfigurable('form', true)
        ->setDisplayConfigurable('view', true);

        $fields['adviser'] = BaseFieldDefinition::create('entity_reference')
        ->setLabel(t('Adviser'))
        ->setDescription(t('The adviser for this appointment.'))
        ->setSetting('target_type', 'user')
        ->setSetting('handler', 'default')
        ->setDisplayOptions('view', [
            'label' => 'above',
            'type' => 'author',
            'weight' => 5,
        ])
        ->setDisplayOptions('form', [
            'type' => 'entity_reference_autocomplete',
            'weight' => 5,
            'settings' => [
                'match_operator' => 'CONTAINS',
                'size' => '60',
                'autocomplete_type' => 'tags',
                'placeholder' => '',
            ],
        ])
        ->setDisplayConfigurable('form', true)
        ->setDisplayConfigurable('view', true);
        $fields['time_slot'] = BaseFieldDefinition::create('string')
        ->setLabel(t('Time Slot'))
        ->setDescription(t('The time slot for this appointment (format: HHMM)'))
        ->setSettings([
            'max_length' => 4,
        ])
        ->setDisplayOptions('form', [
            'type' => 'string_textfield',
            'weight' => 0,
        ])
        ->setDisplayConfigurable('form', true)
        ->setDisplayConfigurable('view', true);

        return $fields;
    }
}
