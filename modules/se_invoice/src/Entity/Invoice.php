<?php

declare(strict_types=1);

namespace Drupal\se_invoice\Entity;

use Drupal\Core\Field\BaseFieldDefinition;
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
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "status" = "status",
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
   */
  private int $totalStorage;

  /**
   * Storage for the current database version to compare with during crud.
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
  public function getTotal(): int {
    return (int) $this->se_total->value;
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
  public function setOutstanding(int $value): void {
    $this->se_outstanding->value = (string) $value;
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
  public function storeOldTotal($total): void {
    $this->totalStorage = $total;
  }

  /**
   * {@inheritdoc}
   */
  public function getOldTotal(): int {
    return $this->totalStorage ?? 0;
  }

  /**
   * {@inheritdoc}
   */
  public function storeOldInvoice(): void {
    if ($this->isNew()) {
      return;
    }

    if ($oldInvoice = self::load($this->id())) {
      $this->oldInvoice = $oldInvoice;
    }
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

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Invoice entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the Invoice entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('max_length', 128)
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    $fields['revision_translation_affected'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Revision translation affected'))
      ->setDescription(t('Indicates if the last edit of a translation belongs to current revision.'))
      ->setReadOnly(TRUE)
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE);

    // Make the revision field configurable.
    // https://www.drupal.org/project/drupal/issues/2696555 will solve.
    $fields[$entity_type->getRevisionMetadataKey('revision_log_message')]->setDisplayConfigurable('form', TRUE);

    return $fields;
  }

}
