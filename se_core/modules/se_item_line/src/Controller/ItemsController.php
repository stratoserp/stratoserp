<?php

namespace Drupal\se_item_line\Controller;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\se_item\Entity\Item;
use Symfony\Component\HttpFoundation\Request;

class ItemsController extends ControllerBase {

  public static function updatePrice(array &$form, FormStateInterface $form_state, Request $request): AjaxResponse {
    $values = $form_state->getValues();
    $response = new AjaxResponse();

    // Use the triggering element to determine the line index;
    $trigger = $request->request->get('_triggering_element_name');

    // Which we can then use with a regular expression;
    preg_match("/(field_(..)_lines)\[(\d)\]\[(.*?)\].*/", $trigger, $matches);
    if (count($matches) < 5) {
      return $response;
    }

    // To extract the fields we need.
    [$field, $type, $index, $trigger] = array_slice($matches, 1);

    // Load the chosen item
    /** @var Item $item */
    if (!$item = Item::load($values[$field][$index]['target_id'])) {
      return $response;
    }

    // If the price field was the change, dont update the price.
    if ($trigger !== 'price') {
      $item_price = $item->field_it_sell_price->value;

      // Create a new ajax response to set the price.
      $response->addCommand(new InvokeCommand(
        "form input[data-drupal-selector='edit-field-{$type}-lines-{$index}-price']",
        'val',
        [\Drupal::service('se_accounting.currency_format')->formatDisplay($item_price)]
      ));

      if (!empty($item->field_it_serial->value)) {
        $response->addCommand(new InvokeCommand(
          "form input[data-drupal-selector='edit-field-{$type}-lines-{$index}-serial']",
          'val',
          [$item->field_it_serial->value]
        ));
      }
    }

    // Update the total
    $total = 0;
    foreach ($values[$field] as $index => $value) {
      if (is_int($index) && !empty($value['target_id'])) {
        $price = \Drupal::service('se_accounting.currency_format')->formatStorage($value['price']);
        if (!empty($price)) {
          $total += $value['quantity'] * $price;
        }
        else {
          $total += $value['quantity'] * ($item_price ?? 0);
        }
      }
    }

    $response->addCommand(new InvokeCommand(
      "form input[data-drupal-selector='edit-field-{$type}-total-0-value']",
      'val',
      [\Drupal::service('se_accounting.currency_format')->formatDisplay($total)]
    ));

    return $response;
  }

}
