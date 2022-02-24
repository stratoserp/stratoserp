<?php

/**
 * @file
 * Contains se_relationship.page.inc.
 *
 * Page callback for Relationship entities.
 */

declare(strict_types=1);

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Relationship templates.
 *
 * Default template: se_relationship.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_se_relationship(array &$variables) {
  // Fetch Relationship Entity Object.
  $se_relationship = $variables['elements']['#se_relationship'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
