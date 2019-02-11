<?php

namespace Drupal\se_item\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the Item type entity.
 *
 * @ConfigEntityType(
 *   id = "item_type",
 *   label = @Translation("Item type"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\se_item\ItemTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\se_item\Form\ItemTypeForm",
 *       "edit" = "Drupal\se_item\Form\ItemTypeForm",
 *       "delete" = "Drupal\se_item\Form\ItemTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\se_item\ItemTypeHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "item_type",
 *   admin_permission = "administer site configuration",
 *   bundle_of = "item",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/se/admin/structure/item_type/{item_type}",
 *     "add-form" = "/se/admin/structure/item_type/add",
 *     "edit-form" = "/se/admin/structure/item_type/{item_type}/edit",
 *     "delete-form" = "/se/admin/structure/item_type/{item_type}/delete",
 *     "collection" = "/se/admin/structure/item_type"
 *   }
 * )
 */
class ItemType extends ConfigEntityBundleBase implements ItemTypeInterface {

  /**
   * The Item type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Item type label.
   *
   * @var string
   */
  protected $label;

}
