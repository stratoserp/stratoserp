<?php

namespace Drupal\se_subscription\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the Subscription type entity.
 *
 * @ConfigEntityType(
 *   id = "se_subscription_type",
 *   label = @Translation("Subscription type"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\se_subscription\SubscriptionTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\se_subscription\Form\SubscriptionTypeForm",
 *       "edit" = "Drupal\se_subscription\Form\SubscriptionTypeForm",
 *       "delete" = "Drupal\se_subscription\Form\SubscriptionTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\se_subscription\SubscriptionTypeHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "se_subscription_type",
 *   admin_permission = "administer site configuration",
 *   bundle_of = "se_subscription",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/se_subscription_type/{se_subscription_type}",
 *     "add-form" = "/admin/structure/se_subscription_type/add",
 *     "edit-form" = "/admin/structure/se_subscription_type/{se_subscription_type}/edit",
 *     "delete-form" = "/admin/structure/se_subscription_type/{se_subscription_type}/delete",
 *     "collection" = "/admin/structure/se_subscription_type"
 *   }
 * )
 */
class SubscriptionType extends ConfigEntityBundleBase implements SubscriptionTypeInterface {

  /**
   * The Subscription type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Subscription type label.
   *
   * @var string
   */
  protected $label;

}
