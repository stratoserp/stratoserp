<?php

/**
 * @file
 * Contains se_ticket.page.inc.
 *
 * Page callback for Ticket entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Ticket templates.
 *
 * Default template: se_ticket.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_se_ticket(array &$variables) {
  // Fetch Ticket Entity Object.
  $se_ticket = $variables['elements']['#se_ticket'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
