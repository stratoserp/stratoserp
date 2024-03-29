<?php

declare(strict_types=1);

namespace Drupal\se_invoice\Entity;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\se_payment\Traits\PaymentTrait;
use Drupal\stratoserp\Entity\StratosLinesEntityBase;

/**
 * Defines the Invoice entity.
 *
 * @ingroup se_invoice
 *
 * @ContentEntityType(
 *   id = "se_invoice",
 *   label = @Translation("Invoice"),
 *   label_collection = @Translation("Invoices"),
 *   handlers = {
 *     "storage" = "Drupal\se_invoice\InvoiceStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\se_invoice\InvoiceListBuilder",
 *     "views_data" = "Drupal\se_invoice\Entity\InvoiceViewsData",
 *     "translation" = "Drupal\se_invoice\InvoiceTranslationHandler",
 *     "form" = {
 *       "default" = "Drupal\se_invoice\Form\InvoiceForm",
 *       "add" = "Drupal\se_invoice\Form\InvoiceForm",
 *       "edit" = "Drupal\se_invoice\Form\InvoiceForm",
 *       "delete" = "Drupal\se_invoice\Form\InvoiceDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\se_invoice\InvoiceHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\se_invoice\InvoiceAccessControlHandler",
 *   },
 *   base_table = "se_invoice",
 *   data_table = "se_invoice_field_data",
 *   revision_table = "se_invoice_revision",
 *   revision_data_table = "se_invoice_field_revision",
 *   show_revision_ui = TRUE,
 *   translatable = TRUE,
 *   admin_permission = "administer invoice entities",
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
 *     "canonical" = "/invoice/{se_invoice}",
 *     "add-form" = "/invoice/add",
 *     "edit-form" = "/invoice/{se_invoice}/edit",
 *     "delete-form" = "/invoice/{se_invoice}/delete",
 *     "version-history" = "/invoice/{se_invoice}/revisions",
 *     "revision" = "/invoice/{se_invoice}/revisions/{se_invoice_revision}/view",
 *     "revision_revert" = "/invoice/{se_invoice}/revisions/{se_invoice_revision}/revert",
 *     "revision_delete" = "/invoice/{se_invoice}/revisions/{se_invoice_revision}/delete",
 *     "translation_revert" = "/invoice/{se_invoice}/revisions/{se_invoice_revision}/revert/{langcode}",
 *     "collection" = "/se/customers/invoice-list",
 *   },
 *   field_ui_base_route = "se_invoice.settings",
 *   revision_metadata_keys = {
 *     "revision_user" = "revision_user",
 *     "revision_created" = "revision_created",
 *     "revision_log_message" = "revision_log_message"
 *   }
 * )
 */
class Invoice extends StratosLinesEntityBase implements InvoiceInterface {

  use PaymentTrait;

  /**
   * Used to avoid multiple saves in cascading events.
   *
   * @var bool
   */
  private bool $skipSaveEvents = FALSE;

  /**
   * Storage for item lines during save process.
   *
   * @var int
   */
  private int $totalStorage;

  /**
   * Storage for the current database version to compare with during crud.
   *
   * @var \Drupal\se_invoice\Entity\Invoice
   */
  private Invoice $oldInvoice;

  /**
   * {@inheritdoc}
   */
  public function getSearchPrefix(): string {
    return 'in';
  }

  /**
   * {@inheritdoc}
   */
  public function getOutstanding(): int {
    return (int) $this->se_outstanding->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setOutstanding(int $value): int {
    return $this->se_outstanding->value = $value;
  }

  /**
   * {@inheritdoc}
   */
  public function getInvoiceBalance(): int {
    $paidAmount = 0;

    foreach ($this->getInvoicePaymentAmounts($this) as $payment) {
      $paidAmount += $payment->se_payment_lines_amount;
    }

    return $this->getTotal() - $paidAmount;
  }

  /**
   * {@inheritdoc}
   */
  public function setSkipSaveEvents(bool $value = TRUE): void {
    $this->skipSaveEvents = $value;
  }

  /**
   * {@inheritdoc}
   */
  public function isSkipSaveEvents(): bool {
    return $this->skipSaveEvents ?? FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function storeOldInvoice(): ?Invoice {
    if ($this->isNew()) {
      return NULL;
    }

    if ($oldInvoice = self::load($this->id())) {
      $this->oldInvoice = $oldInvoice;
    }

    return $oldInvoice;
  }

  /**
   * {@inheritdoc}
   */
  public function getOldInvoice(): ?Invoice {
    return $this->oldInvoice ?? NULL;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields['name']->setDescription(t('The name of the invoice.'));

    return $fields;
  }

}
