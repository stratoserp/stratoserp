<?php

declare(strict_types=1);

namespace Drupal\se_bill\Entity;

use Drupal\stratoserp\Entity\StratosEntityBase;

/**
 * Defines the Bill entity.
 *
 * @ingroup se_bill
 *
 * @ContentEntityType(
 *   id = "se_bill",
 *   label = @Translation("Bill"),
 *   label_collection = @Translation("Bills"),
 *   handlers = {
 *     "storage" = "Drupal\se_bill\BillStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\se_bill\BillListBuilder",
 *     "views_data" = "Drupal\se_bill\Entity\BillViewsData",
 *     "translation" = "Drupal\se_bill\BillTranslationHandler",
 *     "form" = {
 *       "default" = "Drupal\se_bill\Form\BillForm",
 *       "add" = "Drupal\se_bill\Form\BillForm",
 *       "edit" = "Drupal\se_bill\Form\BillForm",
 *       "delete" = "Drupal\se_bill\Form\BillDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\se_bill\BillHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\se_bill\BillAccessControlHandler",
 *   },
 *   base_table = "se_bill",
 *   data_table = "se_bill_field_data",
 *   revision_table = "se_bill_revision",
 *   revision_data_table = "se_bill_field_revision",
 *   show_revision_ui = TRUE,
 *   translatable = TRUE,
 *   admin_permission = "administer bill entities",
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
 *     "canonical" = "/bill/{se_bill}",
 *     "add-form" = "/bill/add",
 *     "edit-form" = "/bill/{se_bill}/edit",
 *     "delete-form" = "/bill/{se_bill}/delete",
 *     "version-history" = "/bill/{se_bill}/revisions",
 *     "revision" = "/bill/{se_bill}/revisions/{se_bill_revision}/view",
 *     "revision_revert" = "/bill/{se_bill}/revisions/{se_bill_revision}/revert",
 *     "revision_delete" = "/bill/{se_bill}/revisions/{se_bill_revision}/delete",
 *     "translation_revert" = "/bill/{se_bill}/revisions/{se_bill_revision}/revert/{langcode}",
 *     "collection" = "/se/suppliers/bill-list",
 *   },
 *   field_ui_base_route = "se_bill.settings",
 *   revision_metadata_keys = {
 *     "revision_user" = "revision_user",
 *     "revision_created" = "revision_created",
 *     "revision_log_message" = "revision_log_message"
 *   }
 * )
 */
class Bill extends StratosEntityBase implements BillInterface {

  /**
   * {@inheritdoc}
   */
  public function getSearchPrefix(): string {
    return 'bi';
  }

  /**
   * {@inheritdoc}
   */
  public function getTotal(): int {
    return (int) $this->se_total->value;
  }

}
