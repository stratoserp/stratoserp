<?php

declare(strict_types=1);

namespace Drupal\se_supplier\Entity;

use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\stratoserp\Entity\StratosEntityBase;

/**
 * Defines the Supplier entity.
 *
 * Note that this could be converted to use bundles and separate the customer
 * and suppliers to different types, but in the end, there would still only
 * be one field different, so leaving that for now.
 *
 * @ingroup se_supplier
 *
 * @ContentEntityType(
 *   id = "se_supplier",
 *   label = @Translation("Supplier"),
 *   label_collection = @Translation("Supplieres"),
 *   handlers = {
 *     "storage" = "Drupal\se_supplier\SupplierStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\se_supplier\SupplierListBuilder",
 *     "views_data" = "Drupal\se_supplier\Entity\SupplierViewsData",
 *     "translation" = "Drupal\se_supplier\SupplierTranslationHandler",
 *     "form" = {
 *       "default" = "Drupal\se_supplier\Form\SupplierForm",
 *       "add" = "Drupal\se_supplier\Form\SupplierForm",
 *       "edit" = "Drupal\se_supplier\Form\SupplierForm",
 *       "delete" = "Drupal\se_supplier\Form\SupplierDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\se_supplier\SupplierHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\se_supplier\SupplierAccessControlHandler",
 *   },
 *   base_table = "se_supplier",
 *   data_table = "se_supplier_field_data",
 *   revision_table = "se_supplier_revision",
 *   revision_data_table = "se_supplier_field_revision",
 *   show_revision_ui = TRUE,
 *   translatable = TRUE,
 *   admin_permission = "administer supplier entities",
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
 *     "canonical" = "/supplier/{se_supplier}",
 *     "add-form" = "/supplier/add",
 *     "edit-form" = "/supplier/{se_supplier}/edit",
 *     "delete-form" = "/supplier/{se_supplier}/delete",
 *     "version-history" = "/supplier/{se_supplier}/revisions",
 *     "revision" = "/supplier/{se_supplier}/revisions/{se_supplier_revision}/view",
 *     "revision_revert" = "/supplier/{se_supplier}/revisions/{se_supplier_revision}/revert",
 *     "revision_delete" = "/supplier/{se_supplier}/revisions/{se_supplier_revision}/delete",
 *     "translation_revert" = "/supplier/{se_supplier}/revisions/{se_supplier_revision}/revert/{langcode}",
 *     "collection" = "/se/suppliers",
 *   },
 *   field_ui_base_route = "se_supplier.settings",
 *   revision_metadata_keys = {
 *     "revision_user" = "revision_user",
 *     "revision_created" = "revision_created",
 *     "revision_log_message" = "revision_log_message"
 *   }
 * )
 */
class Supplier extends StratosEntityBase implements SupplierInterface {

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
    $this->skipSupplierXeroEvents = TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function isSkipXeroEvents(): bool {
    return $this->skipSupplierXeroEvents ?? FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Supplier entity.'))
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
      ->setDescription(t('The name of the Supplier entity.'))
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
