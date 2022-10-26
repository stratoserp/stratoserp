<?php

/**
 * @file
 * Contains se_store.page.inc.
 *
 * Page callback for Store entities.
 */

declare(strict_types=1);

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Store templates.
 *
 * Default template: se-store.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_se_store(array &$variables) {
  // Fetch Store Entity Object.
  //$se_store = $variables['elements']['#se_store'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
