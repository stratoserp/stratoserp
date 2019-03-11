<?php

namespace Drupal\se_item\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the Item type entity.
 *
 * @ConfigEntityType(
 *   id = "se_item_type",
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
 *   config_prefix = "se_item_type",
 *   admin_permission = "administer site configuration",
 *   bundle_of = "se_item",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/item_type/{se_item_type}",
 *     "add-form" = "/admin/item_type/add",
 *     "edit-form" = "/admin/item_type/{se_item_type}/edit",
 *     "delete-form" = "/admin/item_type/{se_item_type}/delete",
 *     "collection" = "/admin/item_type"
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
