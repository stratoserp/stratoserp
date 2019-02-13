<?php

/**
 * @file
 * Contains se_information.page.inc.
 *
 * Page callback for Information entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Information templates.
 *
 * Default template: se_information.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_se_information(array &$variables) {
  // Fetch Information Entity Object.
  $se_information = $variables['elements']['#se_information'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
