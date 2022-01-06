<?php

/**
 * @file
 * Contains se_payment.page.inc.
 *
 * Page callback for Payment entities.
 */

declare(strict_types=1);

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Payment templates.
 *
 * Default template: se_payment.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_se_payment(array &$variables) {
  // Fetch Payment Entity Object.
  $se_payment = $variables['elements']['#se_payment'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
