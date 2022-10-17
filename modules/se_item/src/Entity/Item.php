<?php

declare(strict_types=1);

namespace Drupal\se_item\Entity;

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
 *     "langcode" = "langcode",
 *     "uuid" = "uuid",
 *     "status" = "status",
 *     "published" = "status",
 *     "uid" = "uid",
 *     "owner" = "uid",
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
  public function isBulkStock(): bool {
    return $this->bundle() === 'se_bulk_stock';
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
    $fields['name']->setDescription(t('The name of the item.'));

    return $fields;
  }

}
