<?php

namespace Drupal\se_information\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the Information type entity.
 *
 * @ConfigEntityType(
 *   id = "se_information_type",
 *   label = @Translation("Information type"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\se_information\InformationTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\se_information\Form\InformationTypeForm",
 *       "edit" = "Drupal\se_information\Form\InformationTypeForm",
 *       "delete" = "Drupal\se_information\Form\InformationTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\se_information\InformationTypeHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "se_information_type",
 *   admin_permission = "administer site configuration",
 *   bundle_of = "se_information",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/information_type/{se_information_type}",
 *     "add-form" = "/admin/information_type/add",
 *     "edit-form" = "/admin/information_type/{se_information_type}/edit",
 *     "delete-form" = "/admin/information_type/{se_information_type}/delete",
 *     "collection" = "/admin/information_type"
 *   }
 * )
 */
class InformationType extends ConfigEntityBundleBase implements InformationTypeInterface {

  /**
   * The Information type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Information type label.
   *
   * @var string
   */
  protected $label;

}
