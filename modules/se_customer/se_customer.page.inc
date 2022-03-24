<?php

/**
 * @file
 * Contains se_customer.page.inc.
 *
 * Page callback for Customer entities.
 */

declare(strict_types=1);

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Customer templates.
 *
 * Default template: se_customer.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_se_customer(array &$variables) {
  // Fetch Customer Entity Object.
  $se_customer = $variables['elements']['#se_customer'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
