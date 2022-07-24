<?php

declare(strict_types=1);

namespace Drupal\se_purchase_order\Entity;

use Drupal\stratoserp\Entity\StratosLinesEntityBase;

/**
 * Defines the Purchase order entity.
 *
 * @ingroup se_purchase_order
 *
 * @ContentEntityType(
 *   id = "se_purchase_order",
 *   label = @Translation("Purchase order"),
 *   label_collection = @Translation("Purchase orders"),
 *   handlers = {
 *     "storage" = "Drupal\se_purchase_order\PurchaseOrderStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\se_purchase_order\PurchaseOrderListBuilder",
 *     "views_data" = "Drupal\se_purchase_order\Entity\PurchaseOrderViewsData",
 *     "translation" = "Drupal\se_purchase_order\PurchaseOrderTranslationHandler",
 *     "form" = {
 *       "default" = "Drupal\se_purchase_order\Form\PurchaseOrderForm",
 *       "add" = "Drupal\se_purchase_order\Form\PurchaseOrderForm",
 *       "edit" = "Drupal\se_purchase_order\Form\PurchaseOrderForm",
 *       "delete" = "Drupal\se_purchase_order\Form\PurchaseOrderDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\se_purchase_order\PurchaseOrderHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\se_purchase_order\PurchaseOrderAccessControlHandler",
 *   },
 *   base_table = "se_purchase_order",
 *   data_table = "se_purchase_order_field_data",
 *   revision_table = "se_purchase_order_revision",
 *   revision_data_table = "se_purchase_order_field_revision",
 *   show_revision_ui = TRUE,
 *   translatable = TRUE,
 *   admin_permission = "administer purchase order entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "vid",
 *     "label" = "name",
 *     "langcode" = "langcode",
 *     "uuid" = "uuid",
 *     "status" = "status",
 *     "published" = "status",
 *     "uid" = "uid",
 *     "owner" = "uid",
 *   },
 *   links = {
 *     "canonical" = "/purchase-order/{se_purchase_order}",
 *     "add-form" = "/purchase-order/add",
 *     "edit-form" = "/purchase-order/{se_purchase_order}/edit",
 *     "delete-form" = "/purchase-order/{se_purchase_order}/delete",
 *     "version-history" = "/purchase-order/{se_purchase_order}/revisions",
 *     "revision" = "/purchase-order/{se_purchase_order}/revisions/{se_purchase_order_revision}/view",
 *     "revision_revert" = "/purchase-order/{se_purchase_order}/revisions/{se_purchase_order_revision}/revert",
 *     "revision_delete" = "/purchase-order/{se_purchase_order}/revisions/{se_purchase_order_revision}/delete",
 *     "translation_revert" = "/purchase-order/{se_purchase_order}/revisions/{se_purchase_order_revision}/revert/{langcode}",
 *     "collection" = "/se/customers/purchase-order-list",
 *   },
 *   field_ui_base_route = "se_purchase_order.settings",
 *   revision_metadata_keys = {
 *     "revision_user" = "revision_user",
 *     "revision_created" = "revision_created",
 *     "revision_log_message" = "revision_log_message"
 *   }
 * )
 */
class PurchaseOrder extends StratosLinesEntityBase implements PurchaseOrderInterface {

  /**
   * {@inheritdoc}
   */
  public function getSearchPrefix(): string {
    return 'po';
  }

}
