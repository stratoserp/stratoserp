<?php

/**
 * @file
 * Contains se_purchase_order.page.inc.
 *
 * Page callback for PurchaseOrder entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for PurchaseOrder templates.
 *
 * Default template: se_purchase_order.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_se_purchase_order(array &$variables) {
  // Fetch PurchaseOrder Entity Object.
  $se_purchase_order = $variables['elements']['#se_purchase_order'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
