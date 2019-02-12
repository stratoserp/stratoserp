<?php

/**
 * @file
 * Contains se_document.page.inc.
 *
 * Page callback for Document entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Document templates.
 *
 * Default template: se_document.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_se_document(array &$variables) {
  // Fetch SeDocument Entity Object.
  $se_document = $variables['elements']['#se_document'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
