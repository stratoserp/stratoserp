<?php

/**
 * @file
 * Contains se_invoice.page.inc.
 *
 * Page callback for Invoice entities.
 */

declare(strict_types=1);

use Drupal\Core\Render\Element;
use Drupal\se_invoice\Entity\Invoice;

/**
 * Prepares variables for Invoice templates.
 *
 * Default template: se-invoice.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_se_invoice(array &$variables): void {
  // Fetch Invoice Entity Object.
  // $se_invoice = $variables['elements']['#se_invoice'];
  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    // Ditch the created stamp.
    if ($key === 'created') {
      continue;
    }
    $variables['content'][$key] = $variables['elements'][$key];
  }
}

/**
 * Prepares variables for Invoice templates.
 *
 * Default template: entity-print--se-invoice.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_entity_print__se_invoice(array &$variables): void {
  $dateFormatter = \Drupal::service('date.formatter');

  // Retrieve the store and set the logo path.
  $stores = \Drupal::entityTypeManager()->getStorage('se_store')->loadByProperties([
    'se_status' => TRUE,
  ]);
  /** @var \Drupal\se_store\Entity\Store $store */
  $store = reset($stores);

  // @todo better way for this?
  if (!$invoice = $variables['content'][0][0]['#se_invoice'] ?? NULL) {
    $invoice = Invoice::load(\Drupal::routeMatch()->getParameter('entity_id'));
  }
  $customer = $invoice->se_cu_ref->entity;

  /** @var \Drupal\Core\File\FileUrlGeneratorInterface $fileUrlGenerator */
  $fileUrlGenerator = \Drupal::service('file_url_generator');
  $variables['store_logo'] = $fileUrlGenerator->generate($store->se_logo->entity->uri->value);

  // Company address section.
  $variables['store_name'] = $store->getName();
  $variables['store_address'] = $store->se_address->value;
  $variables['store_suburb'] = $store->se_suburb->value;
  $variables['store_state'] = $store->se_state->value;
  $variables['store_postcode'] = $store->se_postcode->value;

  // Details section.
  $invoiceDate = $dateFormatter->format($invoice->getCreatedTime(), 'short');
  $variables['tax_information'] = $store->se_tax_information->value;
  $variables['invoice_date'] = $invoiceDate;
  $variables['invoice_number'] = $invoice->id();

  // Billing.
  $variables['customer_name'] = $customer->getName();
  $variables['customer_address'] = $customer->se_address->value;
  $variables['customer_suburb'] = $customer->se_suburb->value;
  $variables['customer_state'] = $customer->se_state->value;
  $variables['customer_postcode'] = $customer->se_postcode->value;

  // Info pane.
  $variables['purchase_order'] = $invoice->se_customer_order->value;
  $variables['terms'] = $customer->se_terms->value;
  // @todo due date calculation.
  // $variables['due_date'] =
  // \Drupal::service('se_customer')->calculateDueDate($invoice);
  // Sales rep only if not anonymous.
  $creator = $invoice->getOwner();
  if ($creator->id() !== '0') {
    $variables['sales_rep'] = $creator->getAccountName();
  }

  // Footer section.
  $variables['bank_details'] = se_invoice_details_formatter($store->se_bank_details);
  $variables['contact_details'] = se_invoice_details_formatter($store->se_contact_details);
}

/**
 * Helper to format the details fields.
 */
function se_invoice_details_formatter($field): array {
  // Transform the details into something safe to display.
  return [
    '#type' => 'processed_text',
    '#text' => $field->value,
    '#format' => $field->format,
    '#filter_types_to_skip' => [],
  ];
}
