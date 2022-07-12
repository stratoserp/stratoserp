<?php

declare(strict_types=1);

namespace Drupal\se_subscription\Entity;

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
 *     "langcode" = "langcode",
 *     "uuid" = "uuid",
 *     "status" = "status",
 *     "published" = "status",
 *     "uid" = "uid",
 *     "owner" = "uid",
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
 *     "collection" = "/se/customers/subscription-list",
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
   * Load a subscription entity by its external id.
   *
   * @param string $externalId
   *   The unique id from an external integration.
   *
   * @return \Drupal\se_subscription\Entity\Subscription|bool
   *   The retrieved subscription, or NULL.
   */
  public static function loadByExternalId(string $externalId): ?Subscription {
    $subscriptionService = \Drupal::entityTypeManager()->getStorage('se_subscription');

    $subscriptions = $subscriptionService->loadByProperties([
      'se_external_id' => $externalId,
    ]);

    if (count($subscriptions) > 1) {
      // What should we do here?
      return NULL;
    }

    return reset($subscriptions) ?: NULL;
  }

}
