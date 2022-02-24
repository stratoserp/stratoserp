<?php

declare(strict_types=1);

namespace Drupal\se_contact\Entity;

use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\stratoserp\Entity\StratosEntityBase;

/**
 * Defines the Contact entity.
 *
 * @ingroup se_contact
 *
 * @ContentEntityType(
 *   id = "se_contact",
 *   label = @Translation("Contact"),
 *   label_collection = @Translation("Contacts"),
 *   handlers = {
 *     "storage" = "Drupal\se_contact\ContactStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\se_contact\ContactListBuilder",
 *     "views_data" = "Drupal\se_contact\Entity\ContactViewsData",
 *     "translation" = "Drupal\se_contact\ContactTranslationHandler",
 *     "form" = {
 *       "default" = "Drupal\se_contact\Form\ContactForm",
 *       "add" = "Drupal\se_contact\Form\ContactForm",
 *       "edit" = "Drupal\se_contact\Form\ContactForm",
 *       "delete" = "Drupal\se_contact\Form\ContactDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\se_contact\ContactHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\se_contact\ContactAccessControlHandler",
 *   },
 *   base_table = "se_contact",
 *   data_table = "se_contact_field_data",
 *   revision_table = "se_contact_revision",
 *   revision_data_table = "se_contact_field_revision",
 *   translatable = TRUE,
 *   admin_permission = "administer contact entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "vid",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *   },
 *   links = {
 *     "canonical" = "/contact/{se_contact}",
 *     "add-form" = "/contact/add",
 *     "edit-form" = "/contact/{se_contact}/edit",
 *     "delete-form" = "/contact/{se_contact}/delete",
 *     "version-history" = "/contact/{se_contact}/revisions",
 *     "revision" = "/contact/{se_contact}/revisions/{se_contact_revision}/view",
 *     "revision_revert" = "/contact/{se_contact}/revisions/{se_contact_revision}/revert",
 *     "revision_delete" = "/contact/{se_contact}/revisions/{se_contact_revision}/delete",
 *     "translation_revert" = "/contact/{se_contact}/revisions/{se_contact_revision}/revert/{langcode}",
 *     "collection" = "/se/contact-list",
 *   },
 *   field_ui_base_route = "se_contact.settings",
 *   revision_metadata_keys = {
 *     "revision_user" = "revision_user",
 *     "revision_created" = "revision_created",
 *     "revision_log_message" = "revision_log_message"
 *   }
 * )
 */
class Contact extends StratosEntityBase implements ContactInterface {

  /**
   * {@inheritdoc}
   */
  public function getSearchPrefix(): string {
    return 'co';
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Contact entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
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
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the Contact entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('max_length', 128)
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    $fields['revision_translation_affected'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Revision translation affected'))
      ->setDescription(t('Indicates if the last edit of a translation belongs to current revision.'))
      ->setReadOnly(TRUE)
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE);

    // Make the revision field configurable.
    // https://www.drupal.org/project/drupal/issues/2696555 will solve.
    $fields[$entity_type->getRevisionMetadataKey('revision_log_message')]->setDisplayConfigurable('form', TRUE);

    return $fields;
  }

}
