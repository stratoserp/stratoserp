<?php

/**
 * @file
 * Contains se_supplier.page.inc.
 *
 * Page callback for Supplier entities.
 */

declare(strict_types=1);

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Supplier templates.
 *
 * Default template: se_supplier.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_se_supplier(array &$variables) {
  // Fetch Supplier Entity Object.
  $se_supplier = $variables['elements']['#se_supplier'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
