<?php

namespace Drupal\se_items\Controller;

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
    preg_match("/(field_.._items)\[(\d)\].*/", $trigger, $matches);
    if (count($matches) < 2) {
      return $response;
    }

    // To extract the fields we need.
    [$type, $index] = array_slice($matches, 1);

    // Load the chosen item
    if (!$item = Item::load($values[$type][$index]['subform']['field_it_line_item'][0]['target_id'])) {
      return $response;
    }

    // Create a new ajax response to set the price.
    $response->addCommand(new InvokeCommand(
      "form input[data-drupal-selector='edit-field-in-items-{$index}-subform-field-it-price-0-value']",
      'val',
      [$item->get('field_it_sell_price')->value]
    ));

    // TODO - Copy the items description to that field?

    // TODO - Update invoice total here.

    return $response;
  }

}
