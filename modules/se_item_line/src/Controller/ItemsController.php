<?php

declare(strict_types=1);

namespace Drupal\se_item_line\Controller;

use Drupal\Component\Datetime\DateTimePlus;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\se_accounting\Service\CurrencyFormat;
use Drupal\se_item\Entity\Item;
use Drupal\se_timekeeping\Entity\Timekeeping;
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
  public static function updateFields(array &$form, FormStateInterface $form_state, Request $request): AjaxResponse {
    $values = $form_state->getValues();
    $response = new AjaxResponse();
    $currencyService = \Drupal::service('se_accounting.currency_format');

    // Use the triggering element to determine the line index;.
    $trigger = $request->request->get('_triggering_element_name');

    // Which we can then use with a regular expression;.
    preg_match("/(se_(..)_lines)\[(\d)\]\[(.*?)\].*/", $trigger, $matches);
    if (count($matches) < 5) {
      return $response;
    }

    // To extract the fields we need.
    [$field, $type, $index, $trigger] = array_slice($matches, 1);

    // Changing target type is a special case, just empty some fields.
    switch ($trigger) {
      case 'target_type':
        self::targetTypeChange($response, $type, $index);
        break;

      case 'target_id':
        self::targetIdChange($response, $currencyService, $values, $field, $type, $index);
        break;

      case 'price':
        // On price updates, nothing needs doing except total calc.
        break;

    }

    // Always Update the total.
    self::reCalculateTotal($response, $currencyService, $values, $field, $type);

    return $response;
  }

  /**
   * Set up the fields if the target type was changed.
   *
   * @param \Drupal\Core\Ajax\AjaxResponse $response
   *   The ajax response we're building.
   * @param string $type
   *   The new target type.
   * @param string $index
   *   The line index to update.
   */
  private static function targetTypeChange(AjaxResponse $response, string $type, string $index): void {
    $response->addCommand(
      new InvokeCommand(
        "form input[data-drupal-selector='edit-se-{$type}-lines-{$index}-quantity']",
        'val',
        [1]
      ),
    );
    $response->addCommand(
      new InvokeCommand(
        "form input[data-drupal-selector='edit-se-{$type}-lines-{$index}-target_id']",
        'val',
        ['']
      )
    );
    $response->addCommand(
      new InvokeCommand(
        "form input[data-drupal-selector='edit-se-{$type}-lines-{$index}-serial']",
        'val',
        ['']
      ),
    );
  }

  /**
   * Set up the fields if the target id was changed.
   *
   * @param \Drupal\Core\Ajax\AjaxResponse $response
   *   The ajax response we're building.
   * @param \Drupal\se_accounting\Service\CurrencyFormat $currencyService
   *   Service for displaying currency.
   * @param array $values
   *   Form values to work with.
   * @param string $field
   *   Field from the triggering element.
   * @param string $type
   *   The new target type.
   * @param string $index
   *   The line index to update.
   */
  private static function targetIdChange(AjaxResponse $response, CurrencyFormat $currencyService, array &$values, string $field, string $type, string $index): void {
    // If there is no item code to load we can return now.
    /** @var \Drupal\se_item\Entity\Item $item */
    if ($values[$field][$index]['target_id'] === NULL) {
      return;
    }

    $targetType = $values[$field][$index]['target_type'];
    switch ($targetType) {
      case 'comment':
        /** @var \Drupal\se_timekeeping\Entity\Timekeeping $timekeeping */
        if (($timekeeping = Timekeeping::load($values[$field][$index]['target_id'])) && $item = $timekeeping->se_tk_item->entity) {
          $date = new DateTimePlus($timekeeping->se_tk_date->value, date_default_timezone_get());
          $response->addCommand(
            new InvokeCommand(
              "form input[data-drupal-selector='edit-se-{$type}-lines-{$index}-completed-date-date']",
              'val',
              [$date->format('Y-m-d')]
            )
          );
          $response->addCommand(
            new InvokeCommand(
              "form input[data-drupal-selector='edit-se-{$type}-lines-{$index}-quantity']",
              'val',
              [$timekeeping->se_tk_amount]
            ),
          );
        }
        break;

      case 'se_item':
        /** @var \Drupal\se_item\Entity\Item $item */
        if ($item = Item::load($values[$field][$index]['target_id'])) {
          $response->addCommand(new InvokeCommand(
            "form input[data-drupal-selector='edit-se-{$type}-lines-{$index}-serial']",
            'val',
            [$item->se_it_serial->value]
          ));
        }

        break;
    }

    if (!isset($item)) {
      return;
    }

    $item_price = $item->se_it_sell_price->value;
    $displayPrice = $currencyService->formatDisplay((int) $item_price);

    // Update the values so that the total gets updated as well.
    $values[$field][$index]['price'] = $displayPrice;

    // Create a new ajax response to set the price.
    $response->addCommand(new InvokeCommand(
      "form input[data-drupal-selector='edit-se-{$type}-lines-{$index}-price']",
      'val',
      [$displayPrice]
    ));
  }

  /**
   * Re calculate the total field.
   *
   * @param \Drupal\Core\Ajax\AjaxResponse $response
   *   The ajax response we're building.
   * @param \Drupal\se_accounting\Service\CurrencyFormat $currencyService
   *   Service for displaying currency.
   * @param array $values
   *   Form values to work with.
   * @param string $field
   *   Field from the triggering element.
   * @param string $type
   *   The new target type.
   */
  private static function reCalculateTotal(AjaxResponse $response, CurrencyFormat $currencyService, array $values, string $field, string $type): void {
    $total = 0;
    foreach ($values[$field] as $index => $value) {
      if (is_int($index) && !empty($value['target_id'])) {
        $price = $currencyService->formatStorage($value['price']);
        if (!empty($price)) {
          $total += $value['quantity'] * $price;
        }
      }
    }

    $response->addCommand(new InvokeCommand(
      "form input[data-drupal-selector='edit-se-{$type}-total-0-value']",
      'val',
      [$currencyService->formatDisplay((int) $total)]
    ));

  }

}
