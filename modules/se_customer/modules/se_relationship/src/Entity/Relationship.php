<?php

declare(strict_types=1);

namespace Drupal\se_relationship\Entity;

use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\stratoserp\Entity\StratosEntityBase;

/**
 * Defines the Relationship entity.
 *
 * @ingroup se_relationship
 *
 * @ContentEntityType(
 *   id = "se_relationship",
 *   label = @Translation("Relationship"),
 *   label_collection = @Translation("Relationships"),
 *   handlers = {
 *     "storage" = "Drupal\se_relationship\RelationshipStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\se_relationship\RelationshipListBuilder",
 *     "views_data" = "Drupal\se_relationship\Entity\RelationshipViewsData",
 *     "translation" = "Drupal\se_relationship\RelationshipTranslationHandler",
 *     "form" = {
 *       "default" = "Drupal\se_relationship\Form\RelationshipForm",
 *       "add" = "Drupal\se_relationship\Form\RelationshipForm",
 *       "edit" = "Drupal\se_relationship\Form\RelationshipForm",
 *       "delete" = "Drupal\se_relationship\Form\RelationshipDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\se_relationship\RelationshipHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\se_relationship\RelationshipAccessControlHandler",
 *   },
 *   base_table = "se_relationship",
 *   data_table = "se_relationship_field_data",
 *   revision_table = "se_relationship_revision",
 *   revision_data_table = "se_relationship_field_revision",
 *   translatable = TRUE,
 *   admin_permission = "administer relationship entities",
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
 *     "canonical" = "/relationship/{se_relationship}",
 *     "add-form" = "/relationship/add",
 *     "edit-form" = "/relationship/{se_relationship}/edit",
 *     "delete-form" = "/relationship/{se_relationship}/delete",
 *     "version-history" = "/relationship/{se_relationship}/revisions",
 *     "revision" = "/relationship/{se_relationship}/revisions/{se_relationship_revision}/view",
 *     "revision_revert" = "/relationship/{se_relationship}/revisions/{se_relationship_revision}/revert",
 *     "revision_delete" = "/relationship/{se_relationship}/revisions/{se_relationship_revision}/delete",
 *     "translation_revert" = "/relationship/{se_relationship}/revisions/{se_relationship_revision}/revert/{langcode}",
 *     "collection" = "/se/customers/relationships",
 *   },
 *   field_ui_base_route = "se_relationship.settings",
 *   revision_metadata_keys = {
 *     "revision_user" = "revision_user",
 *     "revision_created" = "revision_created",
 *     "revision_log_message" = "revision_log_message"
 *   }
 * )
 */
class Relationship extends StratosEntityBase implements RelationshipInterface {

  /**
   * {@inheritdoc}
   */
  public function getSearchPrefix(): string {
    return 're';
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the entity.'))
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
      ->setDescription(t('The name of the entity.'))
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
