<?php

declare(strict_types=1);

namespace Drupal\se_subscription\Entity;

use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\stratoserp\Entity\StratosLinesEntityBase;

/**
 * Defines the Subscription entity.
 *
 * @ingroup se_subscription
 *
 * @ContentEntityType(
 *   id = "se_subscription",
 *   label = @Translation("Subscription"),
 *   label_collection = @Translation("Subscriptions"),
 *   bundle_label = @Translation("Subscription type"),
 *   handlers = {
 *     "storage" = "Drupal\se_subscription\SubscriptionStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\se_subscription\SubscriptionListBuilder",
 *     "views_data" = "Drupal\se_subscription\Entity\SubscriptionViewsData",
 *     "translation" = "Drupal\se_subscription\SubscriptionTranslationHandler",
 *     "form" = {
 *       "default" = "Drupal\se_subscription\Form\SubscriptionForm",
 *       "add" = "Drupal\se_subscription\Form\SubscriptionForm",
 *       "edit" = "Drupal\se_subscription\Form\SubscriptionForm",
 *       "delete" = "Drupal\se_subscription\Form\SubscriptionDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\se_subscription\SubscriptionHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\se_subscription\SubscriptionAccessControlHandler",
 *   },
 *   base_table = "se_subscription",
 *   data_table = "se_subscription_field_data",
 *   revision_table = "se_subscription_revision",
 *   revision_data_table = "se_subscription_field_revision",
 *   show_revision_ui = TRUE,
 *   translatable = TRUE,
 *   admin_permission = "administer subscription entities",
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
 *     "canonical" = "/subscription/{se_subscription}",
 *     "add-page" = "/subscription/add",
 *     "add-form" = "/subscription/add/{se_subscription_type}",
 *     "edit-form" = "/subscription/{se_subscription}/edit",
 *     "delete-form" = "/subscription/{se_subscription}/delete",
 *     "version-history" = "/subscription/{se_subscription}/revisions",
 *     "revision" = "/subscription/{se_subscription}/revisions/{se_subscription_revision}/view",
 *     "revision_revert" = "/subscription/{se_subscription}/revisions/{se_subscription_revision}/revert",
 *     "revision_delete" = "/subscription/{se_subscription}/revisions/{se_subscription_revision}/delete",
 *     "translation_revert" = "/subscription/{se_subscription}/revisions/{se_subscription_revision}/revert/{langcode}",
 *     "collection" = "/se/subscription-list",
 *   },
 *   options = {
 *     "_admin_route" = "0",
 *   },
 *   bundle_entity_type = "se_subscription_type",
 *   field_ui_base_route = "entity.se_subscription_type.edit_form",
 *   revision_metadata_keys = {
 *     "revision_user" = "revision_user",
 *     "revision_created" = "revision_created",
 *     "revision_log_message" = "revision_log_message"
 *   }
 * )
 */
class Subscription extends StratosLinesEntityBase implements SubscriptionInterface {

  /**
   * {@inheritdoc}
   */
  public function getSearchPrefix(): string {
    return 'su';
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Subscription entity.'))
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
      ->setDescription(t('The name of the Subscription entity.'))
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
