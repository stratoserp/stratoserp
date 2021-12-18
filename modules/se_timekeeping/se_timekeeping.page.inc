<?php

declare(strict_types=1);

/**
 * @file
 * Contains se_timekeeping.page.inc.
 *
 * Page callback for Ticket entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Ticket templates.
 *
 * Default template: se_timekeeping.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_se_timekeeping(array &$variables) {
  // Fetch Ticket Entity Object.
  $se_timekeeping = $variables['elements']['#se_timekeeping'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
