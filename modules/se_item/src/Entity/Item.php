<?php

declare(strict_types=1);

namespace Drupal\se_item\Entity;

use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\stratoserp\Entity\StratosEntityBase;

/**
 * Defines the Item entity.
 *
 * @ingroup se_item
 *
 * @ContentEntityType(
 *   id = "se_item",
 *   label = @Translation("Item"),
 *   label_collection = @Translation("Items"),
 *   bundle_label = @Translation("Item type"),
 *   handlers = {
 *     "storage" = "Drupal\se_item\ItemStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\se_item\ItemListBuilder",
 *     "views_data" = "Drupal\se_item\Entity\ItemViewsData",
 *     "translation" = "Drupal\se_item\ItemTranslationHandler",
 *     "form" = {
 *       "default" = "Drupal\se_item\Form\ItemForm",
 *       "add" = "Drupal\se_item\Form\ItemForm",
 *       "edit" = "Drupal\se_item\Form\ItemForm",
 *       "delete" = "Drupal\se_item\Form\ItemDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\se_item\ItemHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\se_item\ItemAccessControlHandler",
 *   },
 *   base_table = "se_item",
 *   data_table = "se_item_field_data",
 *   revision_table = "se_item_revision",
 *   revision_data_table = "se_item_field_revision",
 *   show_revision_ui = TRUE,
 *   translatable = TRUE,
 *   admin_permission = "administer item entities",
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
 *     "canonical" = "/item/{se_item}",
 *     "add-page" = "/item/add",
 *     "add-form" = "/item/add/{se_item_type}",
 *     "edit-form" = "/item/{se_item}/edit",
 *     "delete-form" = "/item/{se_item}/delete",
 *     "version-history" = "/item/{se_item}/revisions",
 *     "revision" = "/item/{se_item}/revisions/{se_item_revision}/view",
 *     "revision_revert" = "/item/{se_item}/revisions/{se_item_revision}/revert",
 *     "revision_delete" = "/item/{se_item}/revisions/{se_item_revision}/delete",
 *     "translation_revert" = "/item/{se_item}/revisions/{se_item_revision}/revert/{langcode}",
 *     "collection" = "/se/items",
 *   },
 *   bundle_entity_type = "se_item_type",
 *   field_ui_base_route = "entity.se_item_type.edit_form",
 *   revision_metadata_keys = {
 *     "revision_user" = "revision_user",
 *     "revision_created" = "revision_created",
 *     "revision_log_message" = "revision_log_message"
 *   }
 * )
 */
class Item extends StratosEntityBase implements ItemInterface {

  /**
   * {@inheritdoc}
   */
  public function getSearchPrefix(): string {
    return 'it';
  }

  /**
   * {@inheritdoc}
   */
  public function isStock(): bool {
    return in_array($this->bundle(), ['se_stock', 'se_assembly']);
  }

  /**
   * {@inheritdoc}
   */
  public function hasParent(): bool {
    return !empty($this->se_it_ref->target_id);
  }

  /**
   * {@inheritdoc}
   */
  public static function loadBySupplierCode($supplierCode) {
    $itemService = \Drupal::entityTypeManager()->getStorage('se_item');

    $subscriptions = $itemService->loadByProperties([
      'se_supplier_code' => $supplierCode,
    ]);

    if (count($subscriptions) > 1) {
      // What should we do here?
      return NULL;
    }

    return reset($subscriptions) ?: NULL;

  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Item entity.'))
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
      ->setDescription(t('The name of the Item entity.'))
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
