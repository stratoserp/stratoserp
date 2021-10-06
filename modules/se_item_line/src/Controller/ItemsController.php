<?php

declare(strict_types=1);

namespace Drupal\se_item_line\Controller;

use Drupal\comment\Entity\Comment;
use Drupal\Component\Datetime\DateTimePlus;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\se_item\Entity\Item;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class for Ajax controller to upate the item serial/price.
 */
class ItemsController extends ControllerBase {

  /**
   * Lookup the selected item and update the price and serial.
   *
   * @param array $form
   *   The form being processed.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   The ajax response with appropriate details.
   */
  public static function updatePrice(array &$form, FormStateInterface $form_state, Request $request): AjaxResponse {
    $values = $form_state->getValues();
    $response = new AjaxResponse();

    // Use the triggering element to determine the line index;.
    $trigger = $request->request->get('_triggering_element_name');

    // Which we can then use with a regular expression;.
    preg_match("/(se_(..)_lines)\[(\d)\]\[(.*?)\].*/", $trigger, $matches);
    if (count($matches) < 5) {
      return $response;
    }

    // To extract the fields we need.
    [$field, $type, $index, $trigger] = array_slice($matches, 1);

    // Load the chosen item.
    /** @var \Drupal\se_item\Entity\Item $item */
    if ($values[$field][$index]['target_id'] === NULL) {
      return $response;
    }

    $targetType = $values[$field][$index]['target_type'];

    switch ($targetType) {
      case 'comment':
        /** @var \Drupal\comment\Entity\Comment $comment */
        if ($comment = Comment::load($values[$field][$index]['target_id'])) {
          if ($item = $comment->se_tk_item->entity) {
            $date = new DateTimePlus($comment->se_tk_date->value, date_default_timezone_get());
            $response->addCommand(new InvokeCommand(
              "form input[data-drupal-selector='edit-se-{$type}-lines-{$index}-completed-date-date']",
              'val',
              [$date->format('Y-m-d')]
            ));
          }
        }
        break;

      case 'se_item':
        /** @var \Drupal\se_item\Entity\Item $item */
        if ($item = Item::load($values[$field][$index]['target_id'])) {
          if (!empty($item->se_it_serial->value)) {
            $response->addCommand(new InvokeCommand(
              "form input[data-drupal-selector='edit-se-{$type}-lines-{$index}-serial']",
              'val',
              [$item->se_it_serial->value]
            ));
          }

        }

        break;
    }

    if (!isset($item)) {
      return $response;
    }

    // If the price field was the change, don't update the price.
    if ($trigger !== 'price' && empty($values[$field][$index]['price'])) {
      $item_price = $item->se_it_sell_price->value;

      // Create a new ajax response to set the price.
      $response->addCommand(new InvokeCommand(
        "form input[data-drupal-selector='edit-se-{$type}-lines-{$index}-price']",
        'val',
        [\Drupal::service('se_accounting.currency_format')->formatDisplay((int) $item_price)]
      ));
    }

    // Update the total.
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
      "form input[data-drupal-selector='edit-se-{$type}-total-0-value']",
      'val',
      [\Drupal::service('se_accounting.currency_format')->formatDisplay((int) $total)]
    ));

    return $response;
  }

}
