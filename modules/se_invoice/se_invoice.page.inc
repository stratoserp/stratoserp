<?php

/**
 * @file
 * Contains se_invoice.page.inc.
 *
 * Page callback for Invoice entities.
 */

declare(strict_types=1);

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Invoice templates.
 *
 * Default template: se_invoice.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_se_invoice(array &$variables) {
  // Fetch Invoice Entity Object.
  $se_invoice = $variables['elements']['#se_invoice'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
