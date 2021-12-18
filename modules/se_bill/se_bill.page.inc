<?php

declare(strict_types=1);

/**
 * @file
 * Contains se_bill.page.inc.
 *
 * Page callback for Bill entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Bill templates.
 *
 * Default template: se_bill.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_se_bill(array &$variables) {
  // Fetch Bill Entity Object.
  $se_bill = $variables['elements']['#se_bill'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
