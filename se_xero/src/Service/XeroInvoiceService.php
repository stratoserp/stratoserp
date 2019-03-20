<?php

namespace Drupal\se_xero\Service;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\TypedData\TypedDataManagerInterface;
use Drupal\node\Entity\Node;
use Drupal\xero\Plugin\DataType\XeroItemList;
use Drupal\xero\XeroQueryFactory;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class XeroInvoiceService {

  /**
   * A logger instance.
   *
   * @var LoggerInterface
   */
  protected $logger;

  /**
   * A Xero query.
   *
   * @var XeroQueryFactory;
   */
  protected $xeroQueryFactory;

  /**
   * The typed data manager.
   *
   * @var TypedDataManagerInterface
   */
  protected $typedDataManager;

  /**
   * SeXeroInvoiceService constructor.
   *
   * @param LoggerInterface $logger
   * @param TypedDataManagerInterface $typed_data_manager
   * @param XeroQueryFactory $xero_query_factory
   */
  public function __construct(LoggerInterface $logger, TypedDataManagerInterface $typed_data_manager, XeroQueryFactory $xero_query_factory) {
    $this->logger = $logger;
    $this->typedDataManager = $typed_data_manager;
    $this->xeroQueryFactory = $xero_query_factory;
  }

  public function lookupInvoice(Node $node) {
//    $xeroQuery = $this->xeroQueryFactory->get();
//    $xeroQuery->setType('xero_invoice');
//    $xeroQuery->addCondition('InvoiceNumber', $node->invoice_id->value);
    return FALSE;
  }

  /**
   * Create an array of data to sync to xero.
   *
   * @param ImmutableConfig $settings
   * @param \Drupal\xero\Plugin\DataType\XeroItemList $invoices
   * @param Node $node
   *
   * @return \Drupal\xero\Plugin\DataType\XeroItemList|boolean
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  private function setInvoiceValues(ImmutableConfig $settings, XeroItemList $invoices, Node $node) {

    // Setup the invoice data
    $values = [
      'Contact' => [
        'ContactID' => $node->customer->se_xero_uuid->value,
      ],
      'Type' => 'ACCREC',
      'Date' => date('Y-m-d', $node->created->value),
      'DueDate' => date('Y-m-d', $node->created->value + (86400 * 7)), // TODO
      'LineAmountTypes' => 'Inclusive',
      'Status' => 'SUBMITTED',
      'InvoiceNumber' => $node->field_in_id->value,
      'LineItems' => [],
    ];
    $invoices->appendItem($values);

    // Add the line item (no breakdown for xero as we have
    // more items than they recommend).
    $invoices->get(0)->get('LineItems')->appendItem([
      'Description' => 'ERP Sale',
      'Quantity' => 1,
      'UnitAmount' => $node->field_in_total->value,
      'LineAmount' => $node->field_in_total->value,
      'AccountCode' => $settings->get('invoice.account'),
    ]);

    return $invoices;
  }

  /**
   * Create an Invoice in Xero
   *
   * @param \Drupal\node\Entity\Node $node
   *
   * @return bool
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function sync(Node $node) {
    $settings = \Drupal::configFactory()->get('se_xero.settings');
    if (!$settings->get('system.enabled')) {
      return FALSE;
    }

    // If the invoice has zero value, bail.
    if ($node->field_in_total->value == 0) {
      return FALSE;
    }

    // See if the customer already exists, add them if not.
    $node->customer = \Drupal::service('se_customer.service')->lookupCustomer($node);
    if (!isset($node->customer->se_xero_uuid->value)) {
      \Drupal::service('se_xero.contact_service')->sync($node->customer);
    }

    // Setup the data structure
    $list_definition = $this->typedDataManager->createListDataDefinition('xero_invoice');
    /** @var \Drupal\xero\Plugin\DataType\XeroItemList $invoices */
    $invoices = $this->typedDataManager->create($list_definition, []);

    // Setup the query
    $xeroQuery = $this->xeroQueryFactory->get();

    // Setup the values, return if there are no invoice lines.
    if (!$invoices = $this->setInvoiceValues($settings, $invoices, $node)) {
      return FALSE;
    }

    // Check if its an existing invoice that needs updating.
    if ($invoice = $this->lookupInvoice($node)) {
      // Update?
      $xeroQuery->setId($invoice->get('InvoiceID')->getValue());
    }

    $xeroQuery->setType('xero_invoice')
      ->setData($invoices)
      ->setMethod('post');

    $result = $xeroQuery->execute();

    if ($result === FALSE) {
      $this->logger->log(LogLevel::ERROR, (string) new FormattableMarkup('Cannot create invoice @invoice, operation failed.', [
        '@invoice' => $node->title->value,
      ]));
      return FALSE;
    }

    if ($result->count() > 0) {
      /** @var \Drupal\xero\Plugin\DataType\Contact $createdXeroInvoice */
      $createdXeroInvoice = $result->get(0);
      $remote_id = $createdXeroInvoice->get('InvoiceID')->getValue();
      $node->set('se_xero_uuid', $remote_id);
      $node->save();

      $this->logger->log(LogLevel::INFO, (string) new FormattableMarkup('Created invoice @invoice with remote id @remote_id.', [
        '@invoice' => $node->title->value,
        '@remote_id' => $remote_id,
      ]));
      return TRUE;
    }
    return FALSE;

  }

}
