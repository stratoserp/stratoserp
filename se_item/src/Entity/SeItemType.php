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
 *     "list_builder" = "Drupal\se_item\SeItemTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\se_item\Form\SeItemTypeForm",
 *       "edit" = "Drupal\se_item\Form\SeItemTypeForm",
 *       "delete" = "Drupal\se_item\Form\SeItemTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\se_item\SeItemTypeHtmlRouteProvider",
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
 *     "canonical" = "/se/admin/structure/se_item_type/{se_item_type}",
 *     "add-form" = "/se/admin/structure/se_item_type/add",
 *     "edit-form" = "/se/admin/structure/se_item_type/{se_item_type}/edit",
 *     "delete-form" = "/se/admin/structure/se_item_type/{se_item_type}/delete",
 *     "collection" = "/se/admin/structure/se_item_type"
 *   }
 * )
 */
class SeItemType extends ConfigEntityBundleBase implements SeItemTypeInterface {

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
