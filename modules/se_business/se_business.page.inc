<?php

declare(strict_types=1);

/**
 * @file
 * Contains se_business.page.inc.
 *
 * Page callback for Business entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Business templates.
 *
 * Default template: se_business.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_se_business(array &$variables) {
  // Fetch Business Entity Object.
  $se_business = $variables['elements']['#se_business'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
