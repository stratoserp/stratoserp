<?php

declare(strict_types=1);

namespace Drupal\se_bill\Entity;

use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\stratoserp\Entity\StratosEntityBase;

/**
 * Defines the Bill entity.
 *
 * @ingroup se_bill
 *
 * @ContentEntityType(
 *   id = "se_bill",
 *   label = @Translation("Bill"),
 *   handlers = {
 *     "storage" = "Drupal\se_bill\BillStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\se_bill\BillListBuilder",
 *     "views_data" = "Drupal\se_bill\Entity\BillViewsData",
 *     "translation" = "Drupal\se_bill\BillTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\se_bill\Form\BillForm",
 *       "add" = "Drupal\se_bill\Form\BillForm",
 *       "edit" = "Drupal\se_bill\Form\BillForm",
 *       "delete" = "Drupal\se_bill\Form\BillDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\se_bill\BillHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\se_bill\BillAccessControlHandler",
 *   },
 *   base_table = "se_bill",
 *   data_table = "se_bill_field_data",
 *   revision_table = "se_bill_revision",
 *   revision_data_table = "se_bill_field_revision",
 *   translatable = TRUE,
 *   admin_permission = "administer bill entities",
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
 *     "canonical" = "/bill/{se_bill}",
 *     "add-form" = "/bill/add",
 *     "edit-form" = "/bill/{se_bill}/edit",
 *     "delete-form" = "/bill/{se_bill}/delete",
 *     "version-history" = "/bill/{se_bill}/revisions",
 *     "revision" = "/bill/{se_bill}/revisions/{se_bill_revision}/view",
 *     "revision_revert" = "/bill/{se_bill}/revisions/{se_bill_revision}/revert",
 *     "revision_delete" = "/bill/{se_bill}/revisions/{se_bill_revision}/delete",
 *     "translation_revert" = "/bill/{se_bill}/revisions/{se_bill_revision}/revert/{langcode}",
 *     "collection" = "/se/bill-list",
 *   },
 *   field_ui_base_route = "se_bill.settings",
 *   revision_metadata_keys = {
 *     "revision_user" = "revision_user",
 *     "revision_created" = "revision_created",
 *     "revision_log_message" = "revision_log_message"
 *   }
 * )
 */
class Bill extends StratosEntityBase implements BillInterface {

  /**
   * {@inheritdoc}
   */
  public function getSearchPrefix(): string {
    return 'bi';
  }

  /**
   * {@inheritdoc}
   */
  public function getTotal(): int {
    return (int) $this->se_total->value;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Bill entity.'))
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
      ->setDescription(t('The name of the Bill entity.'))
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
