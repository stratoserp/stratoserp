<?php

/**
 * @file
 * Contains se_stock_item.page.inc.
 *
 * Page callback for Stock item entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Stock item templates.
 *
 * Default template: se_stock_item.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_se_stock_item(array &$variables) {
  // Fetch StockItem Entity Object.
  $se_stock_item = $variables['elements']['#se_stock_item'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
