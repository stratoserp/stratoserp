<?php

/**
 * @file
 * Contains se_quote.page.inc.
 *
 * Page callback for Quote entities.
 */

declare(strict_types=1);

use Drupal\Core\Render\Element;
use Drupal\se_quote\Entity\Quote;

/**
 * Prepares variables for Quote templates.
 *
 * Default template: se_quote.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_se_quote(array &$variables) {
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
 * Prepares variables for Quote templates.
 *
 * Default template: entity-print--se-quote.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_entity_print__se_quote(array &$variables): void {
  $dateFormatter = \Drupal::service('date.formatter');

  // Retrieve the store and set the logo path.
  $stores = \Drupal::entityTypeManager()->getStorage('se_store')->loadByProperties([
    'se_status' => TRUE,
  ]);
  /** @var \Drupal\se_store\Entity\Store $store */
  $store = reset($stores);

  // @todo better way for this?
  if (!$quote = $variables['content'][0][0]['#se_quote'] ?? NULL) {
    $quote = Quote::load(\Drupal::routeMatch()->getParameter('entity_id'));
  }
  $customer = $quote->se_cu_ref->entity;

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
  $quoteDate = $dateFormatter->format($quote->getCreatedTime(), 'short');
  $variables['tax_information'] = $store->se_tax_information->value;
  $variables['quote_date'] = $quoteDate;
  $variables['quote_number'] = $quote->id();

  // Billing.
  $variables['customer_name'] = $customer->getName();
  $variables['customer_address'] = $customer->se_address->value;
  $variables['customer_suburb'] = $customer->se_suburb->value;
  $variables['customer_state'] = $customer->se_state->value;
  $variables['customer_postcode'] = $customer->se_postcode->value;

  // Info pane.
  $variables['terms'] = $customer->se_terms->value;
  // @todo due date calculation.
  // $variables['due_date'] =
  // \Drupal::service('se_customer')->calculateDueDate($quote);
  // Sales rep only if not anonymous.
  $creator = $quote->getOwner();
  if ($creator->id() !== '0') {
    $variables['sales_rep'] = $creator->getAccountName();
  }
}

/**
 * Helper to format the details fields.
 */
function se_quote_details_formatter($field): array {
  // Transform the details into something safe to display.
  return [
    '#type' => 'processed_text',
    '#text' => $field->value,
    '#format' => $field->format,
    '#filter_types_to_skip' => [],
  ];
}
