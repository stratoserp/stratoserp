<?php

declare(strict_types=1);

namespace Drupal\se_goods_receipt\Entity;

use Drupal\stratoserp\Entity\StratosLinesEntityBase;

/**
 * Defines the Goods receipt entity.
 *
 * @ingroup se_goods_receipt
 *
 * @ContentEntityType(
 *   id = "se_goods_receipt",
 *   label = @Translation("Goods receipt"),
 *   label_collection = @Translation("Goods receipts"),
 *   handlers = {
 *     "storage" = "Drupal\se_goods_receipt\GoodsReceiptStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\se_goods_receipt\GoodsReceiptListBuilder",
 *     "views_data" = "Drupal\se_goods_receipt\Entity\GoodsReceiptViewsData",
 *     "translation" = "Drupal\se_goods_receipt\GoodsReceiptTranslationHandler",
 *     "form" = {
 *       "default" = "Drupal\se_goods_receipt\Form\GoodsReceiptForm",
 *       "add" = "Drupal\se_goods_receipt\Form\GoodsReceiptForm",
 *       "edit" = "Drupal\se_goods_receipt\Form\GoodsReceiptForm",
 *       "delete" = "Drupal\se_goods_receipt\Form\GoodsReceiptDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\se_goods_receipt\GoodsReceiptHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\se_goods_receipt\GoodsReceiptAccessControlHandler",
 *   },
 *   base_table = "se_goods_receipt",
 *   data_table = "se_goods_receipt_field_data",
 *   revision_table = "se_goods_receipt_revision",
 *   revision_data_table = "se_goods_receipt_field_revision",
 *   show_revision_ui = TRUE,
 *   translatable = TRUE,
 *   admin_permission = "administer goods receipt entities",
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
 *     "canonical" = "/goods-receipt/{se_goods_receipt}",
 *     "add-form" = "/goods-receipt/add",
 *     "edit-form" = "/goods-receipt/{se_goods_receipt}/edit",
 *     "delete-form" = "/goods-receipt/{se_goods_receipt}/delete",
 *     "version-history" = "/goods-receipt/{se_goods_receipt}/revisions",
 *     "revision" = "/goods-receipt/{se_goods_receipt}/revisions/{se_goods_receipt_revision}/view",
 *     "revision_revert" = "/goods-receipt/{se_goods_receipt}/revisions/{se_goods_receipt_revision}/revert",
 *     "revision_delete" = "/goods-receipt/{se_goods_receipt}/revisions/{se_goods_receipt_revision}/delete",
 *     "translation_revert" = "/goods-receipt/{se_goods_receipt}/revisions/{se_goods_receipt_revision}/revert/{langcode}",
 *     "collection" = "/se/customers/goods-receipt-list",
 *   },
 *   field_ui_base_route = "se_goods_receipt.settings",
 *   revision_metadata_keys = {
 *     "revision_user" = "revision_user",
 *     "revision_created" = "revision_created",
 *     "revision_log_message" = "revision_log_message"
 *   }
 * )
 */
class GoodsReceipt extends StratosLinesEntityBase implements GoodsReceiptInterface {

  /**
   * {@inheritdoc}
   */
  public function getSearchPrefix(): string {
    return 'gr';
  }

}
