<?php

/**
 * @file
 * Contains se_item.page.inc.
 *
 * Page callback for Item entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Item templates.
 *
 * Default template: se_item.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_se_item(array &$variables) {
  // Fetch SeItem Entity Object.
  $se_item = $variables['elements']['#se_item'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
