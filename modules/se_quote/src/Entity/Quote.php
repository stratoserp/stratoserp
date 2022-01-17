<?php

declare(strict_types=1);

namespace Drupal\se_quote\Entity;

use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\stratoserp\Entity\StratosEntityBase;

/**
 * Defines the Quote entity.
 *
 * @ingroup se_quote
 *
 * @ContentEntityType(
 *   id = "se_quote",
 *   label = @Translation("Quote"),
 *   handlers = {
 *     "storage" = "Drupal\se_quote\QuoteStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\se_quote\QuoteListBuilder",
 *     "views_data" = "Drupal\se_quote\Entity\QuoteViewsData",
 *     "translation" = "Drupal\se_quote\QuoteTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\se_quote\Form\QuoteForm",
 *       "add" = "Drupal\se_quote\Form\QuoteForm",
 *       "edit" = "Drupal\se_quote\Form\QuoteForm",
 *       "delete" = "Drupal\se_quote\Form\QuoteDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\se_quote\QuoteHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\se_quote\QuoteAccessControlHandler",
 *   },
 *   base_table = "se_quote",
 *   data_table = "se_quote_field_data",
 *   revision_table = "se_quote_revision",
 *   revision_data_table = "se_quote_field_revision",
 *   translatable = TRUE,
 *   admin_permission = "administer quote entities",
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
 *     "canonical" = "/quote/{se_quote}",
 *     "add-form" = "/quote/add",
 *     "edit-form" = "/quote/{se_quote}/edit",
 *     "delete-form" = "/quote/{se_quote}/delete",
 *     "version-history" = "/quote/{se_quote}/revisions",
 *     "revision" = "/quote/{se_quote}/revisions/{se_quote_revision}/view",
 *     "revision_revert" = "/quote/{se_quote}/revisions/{se_quote_revision}/revert",
 *     "revision_delete" = "/quote/{se_quote}/revisions/{se_quote_revision}/delete",
 *     "translation_revert" = "/quote/{se_quote}/revisions/{se_quote_revision}/revert/{langcode}",
 *     "collection" = "/se/quote-list",
 *   },
 *   field_ui_base_route = "se_quote.settings",
 *   revision_metadata_keys = {
 *     "revision_user" = "revision_user",
 *     "revision_created" = "revision_created",
 *     "revision_log_message" = "revision_log_message"
 *   }
 * )
 */
class Quote extends StratosEntityBase implements QuoteInterface {

  /**
   * {@inheritdoc}
   */
  public function getSearchPrefix(): string {
    return 'qu';
  }

  /**
   * {@inheritdoc}
   */
  public function getTotal(): int {
    return (int) $this->se_qu_total->value;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Quote entity.'))
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
      ->setDescription(t('The name of the Quote entity.'))
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
