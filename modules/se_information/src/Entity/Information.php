<?php

declare(strict_types=1);

namespace Drupal\se_information\Entity;

use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\stratoserp\Entity\StratosEntityBase;

/**
 * Defines the Information entity.
 *
 * @ingroup se_information
 *
 * @ContentEntityType(
 *   id = "se_information",
 *   label = @Translation("Information"),
 *   label_collection = @Translation("Information"),
 *   bundle_label = @Translation("Information type"),
 *   handlers = {
 *     "storage" = "Drupal\se_information\InformationStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\se_information\InformationListBuilder",
 *     "views_data" = "Drupal\se_information\Entity\InformationViewsData",
 *     "translation" = "Drupal\se_information\InformationTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\se_information\Form\InformationForm",
 *       "add" = "Drupal\se_information\Form\InformationForm",
 *       "edit" = "Drupal\se_information\Form\InformationForm",
 *       "delete" = "Drupal\se_information\Form\InformationDeleteForm",
 *     },
 *     "access" = "Drupal\se_information\InformationAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\se_information\InformationHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "se_information",
 *   data_table = "se_information_field_data",
 *   revision_table = "se_information_revision",
 *   revision_data_table = "se_information_field_revision",
 *   show_revision_ui = TRUE,
 *   translatable = TRUE,
 *   admin_permission = "administer information entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "vid",
 *     "bundle" = "type",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *   },
 *   links = {
 *     "canonical" = "/information/{se_information}",
 *     "add-page" = "/information/add",
 *     "add-form" = "/information/add/{se_information_type}",
 *     "edit-form" = "/information/{se_information}/edit",
 *     "delete-form" = "/information/{se_information}/delete",
 *     "version-history" = "/information/{se_information}/revisions",
 *     "revision" = "/information/{se_information}/revisions/{se_information_revision}/view",
 *     "revision_revert" = "/information/{se_information}/revisions/{se_information_revision}/revert",
 *     "revision_delete" = "/information/{se_information}/revisions/{se_information_revision}/delete",
 *     "translation_revert" = "/information/{se_information}/revisions/{se_information_revision}/revert/{langcode}",
 *     "collection" = "/se/information-list",
 *   },
 *   bundle_entity_type = "se_information_type",
 *   field_ui_base_route = "entity.se_information_type.edit_form",
 *   revision_metadata_keys = {
 *     "revision_user" = "revision_user",
 *     "revision_created" = "revision_created",
 *     "revision_log_message" = "revision_log_message"
 *   }
 * )
 */
class Information extends StratosEntityBase implements InformationInterface {

  /**
   * {@inheritdoc}
   */
  public function getSearchPrefix(): string {
    return 'if';
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Information entity.'))
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
      ->setDescription(t('The name of the Information entity.'))
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
