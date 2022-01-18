<?php

declare(strict_types=1);

namespace Drupal\se_payment\Entity;

use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\stratoserp\Entity\StratosEntityBase;

/**
 * Defines the Payment entity.
 *
 * @ingroup se_payment
 *
 * @ContentEntityType(
 *   id = "se_payment",
 *   label = @Translation("Payment"),
 *   handlers = {
 *     "storage" = "Drupal\se_payment\PaymentStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\se_payment\PaymentListBuilder",
 *     "views_data" = "Drupal\se_payment\Entity\PaymentViewsData",
 *     "translation" = "Drupal\se_payment\PaymentTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\se_payment\Form\PaymentForm",
 *       "add" = "Drupal\se_payment\Form\PaymentForm",
 *       "edit" = "Drupal\se_payment\Form\PaymentForm",
 *       "delete" = "Drupal\se_payment\Form\PaymentDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\se_payment\PaymentHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\se_payment\PaymentAccessControlHandler",
 *   },
 *   base_table = "se_payment",
 *   data_table = "se_payment_field_data",
 *   revision_table = "se_payment_revision",
 *   revision_data_table = "se_payment_field_revision",
 *   translatable = TRUE,
 *   admin_permission = "administer payment entities",
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
 *     "canonical" = "/payment/{se_payment}",
 *     "add-form" = "/payment/add",
 *     "edit-form" = "/payment/{se_payment}/edit",
 *     "delete-form" = "/payment/{se_payment}/delete",
 *     "version-history" = "/payment/{se_payment}/revisions",
 *     "revision" = "/payment/{se_payment}/revisions/{se_payment_revision}/view",
 *     "revision_revert" = "/payment/{se_payment}/revisions/{se_payment_revision}/revert",
 *     "revision_delete" = "/payment/{se_payment}/revisions/{se_payment_revision}/delete",
 *     "translation_revert" = "/payment/{se_payment}/revisions/{se_payment_revision}/revert/{langcode}",
 *     "collection" = "/se/payment-list",
 *   },
 *   field_ui_base_route = "se_payment.settings",
 *   revision_metadata_keys = {
 *     "revision_user" = "revision_user",
 *     "revision_created" = "revision_created",
 *     "revision_log_message" = "revision_log_message"
 *   }
 * )
 */
class Payment extends StratosEntityBase implements PaymentInterface {

  /**
   * {@inheritdoc}
   */
  public function getSearchPrefix(): string {
    return 'pa';
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
      ->setDescription(t('The user ID of author of the Payment entity.'))
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
      ->setDescription(t('The name of the Payment entity.'))
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
