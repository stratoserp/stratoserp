<?php

declare(strict_types=1);

namespace Drupal\se_payment\Entity;

use Drupal\stratoserp\Entity\StratosEntityBase;

/**
 * Defines the Payment entity.
 *
 * @ingroup se_payment
 *
 * @ContentEntityType(
 *   id = "se_payment",
 *   label = @Translation("Payment"),
 *   label_collection = @Translation("Payments"),
 *   handlers = {
 *     "storage" = "Drupal\se_payment\PaymentStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\se_payment\PaymentListBuilder",
 *     "views_data" = "Drupal\se_payment\Entity\PaymentViewsData",
 *     "translation" = "Drupal\se_payment\PaymentTranslationHandler",
 *     "form" = {
 *       "default" = "Drupal\se_payment\Form\PaymentForm",
 *       "add" = "Drupal\se_payment\Form\PaymentForm",
 *       "edit" = "Drupal\se_payment\Form\PaymentForm",
 *       "delete" = "Drupal\se_payment\Form\PaymentDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\se_payment\PaymentHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\se_payment\PaymentAccessControlHandler",
 *   },
 *   base_table = "se_payment",
 *   data_table = "se_payment_field_data",
 *   revision_table = "se_payment_revision",
 *   revision_data_table = "se_payment_field_revision",
 *   show_revision_ui = TRUE,
 *   translatable = TRUE,
 *   admin_permission = "administer payment entities",
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
 *     "canonical" = "/payment/{se_payment}",
 *     "add-form" = "/payment/add",
 *     "edit-form" = "/payment/{se_payment}/edit",
 *     "delete-form" = "/payment/{se_payment}/delete",
 *     "version-history" = "/payment/{se_payment}/revisions",
 *     "revision" = "/payment/{se_payment}/revisions/{se_payment_revision}/view",
 *     "revision_revert" = "/payment/{se_payment}/revisions/{se_payment_revision}/revert",
 *     "revision_delete" = "/payment/{se_payment}/revisions/{se_payment_revision}/delete",
 *     "translation_revert" = "/payment/{se_payment}/revisions/{se_payment_revision}/revert/{langcode}",
 *     "collection" = "/se/customers/payment-list",
 *   },
 *   field_ui_base_route = "se_payment.settings",
 *   revision_metadata_keys = {
 *     "revision_user" = "revision_user",
 *     "revision_created" = "revision_created",
 *     "revision_log_message" = "revision_log_message"
 *   }
 * )
 */
class Payment extends StratosEntityBase implements PaymentInterface {

  /**
   * Storage for payment lines during save process.
   *
   * @var \Drupal\se_payment\Entity\Payment
   */
  private Payment $oldPayment;

  /**
   * {@inheritdoc}
   */
  public function getSearchPrefix(): string {
    return 'pa';
  }

  /**
   * {@inheritdoc}
   */
  public function getTotal(): int {
    return (int) $this->se_total->value;
  }

  /**
   * {@inheritdoc}
   */
  public function storeOldPayment(): void {
    if (!isset($this->oldPayment) && !$this->isNew()) {
      if ($oldPayment = self::load($this->id())) {
        $this->oldPayment = $oldPayment;
      }
      else {
        unset($this->oldPayment);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getOldPayment(): ?Payment {
    return $this->oldPayment ?? NULL;
  }

}
