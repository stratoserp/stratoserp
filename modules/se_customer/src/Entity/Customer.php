<?php

declare(strict_types=1);

namespace Drupal\se_customer\Entity;

use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\stratoserp\Entity\StratosEntityBase;

/**
 * Defines the Customer entity.
 *
 * Note that this could be converted to use bundles and separate the customer
 * and suppliers to different types, but in the end, there would still only
 * be one field different, so leaving that for now.
 *
 * @ingroup se_customer
 *
 * @ContentEntityType(
 *   id = "se_customer",
 *   label = @Translation("Customer"),
 *   label_collection = @Translation("Customers"),
 *   handlers = {
 *     "storage" = "Drupal\se_customer\CustomerStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\se_customer\CustomerListBuilder",
 *     "views_data" = "Drupal\se_customer\Entity\CustomerViewsData",
 *     "translation" = "Drupal\se_customer\CustomerTranslationHandler",
 *     "form" = {
 *       "default" = "Drupal\se_customer\Form\CustomerForm",
 *       "add" = "Drupal\se_customer\Form\CustomerForm",
 *       "edit" = "Drupal\se_customer\Form\CustomerForm",
 *       "delete" = "Drupal\se_customer\Form\CustomerDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\se_customer\CustomerHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\se_customer\CustomerAccessControlHandler",
 *   },
 *   base_table = "se_customer",
 *   data_table = "se_customer_field_data",
 *   revision_table = "se_customer_revision",
 *   revision_data_table = "se_customer_field_revision",
 *   show_revision_ui = TRUE,
 *   translatable = TRUE,
 *   admin_permission = "administer customer entities",
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
 *     "canonical" = "/customer/{se_customer}",
 *     "add-form" = "/customer/add",
 *     "edit-form" = "/customer/{se_customer}/edit",
 *     "delete-form" = "/customer/{se_customer}/delete",
 *     "version-history" = "/customer/{se_customer}/revisions",
 *     "revision" = "/customer/{se_customer}/revisions/{se_customer_revision}/view",
 *     "revision_revert" = "/customer/{se_customer}/revisions/{se_customer_revision}/revert",
 *     "revision_delete" = "/customer/{se_customer}/revisions/{se_customer_revision}/delete",
 *     "translation_revert" = "/customer/{se_customer}/revisions/{se_customer_revision}/revert/{langcode}",
 *     "collection" = "/se/customers",
 *   },
 *   field_ui_base_route = "se_customer.settings",
 *   revision_metadata_keys = {
 *     "revision_user" = "revision_user",
 *     "revision_created" = "revision_created",
 *     "revision_log_message" = "revision_log_message"
 *   }
 * )
 */
class Customer extends StratosEntityBase implements CustomerInterface {

  /**
   * {@inheritdoc}
   */
  public function getSearchPrefix(): string {
    return 'bu';
  }

  /**
   * {@inheritdoc}
   */
  public function getBalance(): int {
    return (int) $this->se_balance->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setBalance(int $value): int {
    $this->set('se_balance', $value);
    $this->save();
    return $this->getBalance();
  }

  /**
   * {@inheritdoc}
   */
  public function adjustBalance(int $value): int {
    if ($value === 0) {
      return $this->getBalance();
    }

    $this->se_balance->value = (int) $this->se_balance->value + $value;
    $this->save();
    return $this->getBalance();
  }

  /**
   * {@inheritdoc}
   */
  public function setSkipXeroEvents(): void {
    $this->skipCustomerXeroEvents = TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function isSkipXeroEvents(): bool {
    return $this->skipCustomerXeroEvents ?? FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Customer entity.'))
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
      ->setDescription(t('The name of the Customer entity.'))
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
