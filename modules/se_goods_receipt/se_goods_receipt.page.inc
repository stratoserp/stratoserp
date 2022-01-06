<?php

/**
 * @file
 * Contains se_goods_receipt.page.inc.
 *
 * Page callback for Goods Receipt entities.
 */

declare(strict_types=1);

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Goods Receipt templates.
 *
 * Default template: se_goods_receipt.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_se_goods_receipt(array &$variables) {
  // Fetch GoodsReceipt Entity Object.
  $se_goods_receipt = $variables['elements']['#se_goods_receipt'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
