<?php

declare(strict_types=1);

namespace Drupal\se_item_line\Controller;

use Drupal\Component\Datetime\DateTimePlus;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\se_accounting\Service\CurrencyFormatService;
use Drupal\se_accounting\Service\TaxAmountService;
use Drupal\se_item\Entity\Item;
use Drupal\se_timekeeping\Entity\Timekeeping;

/**
 * Class for Ajax controller to update the item serial/price.
 */
class ItemLineController extends ControllerBase {

  /**
   * Lookup the selected item and update the price and serial.
   *
   * @param array $form
   *   The form being processed.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   The ajax response with appropriate details.
   */
  public static function updateFields(array &$form, FormStateInterface $form_state): AjaxResponse {
    $values = $form_state->getValues();
    $response = new AjaxResponse();
    $currencyService = \Drupal::service('se_accounting.currency_format');
    $taxService = \Drupal::service('se_accounting.tax_amount');

    // Get the triggering element.
    $trigger = $form_state->getTriggeringElement();
    if ($trigger['#type'] === 'submit') {
      self::reCalculateTotal($response, $currencyService, $taxService, $values);

      return $response;
    }

    // Check the trigger line with a regular expression;.
    preg_match("/se_item_lines\[(\d)\]\[(.*?)\].*/", $trigger['#name'], $matches);
    if (count($matches) < 3) {
      return $response;
    }

    // To extract the fields we need.
    [$index, $trigger] = array_slice($matches, 1);

    // Changing target type is a special case, just empty some fields.
    switch ($trigger) {
      case 'target_type':
        self::targetTypeChange($response, $index);
        break;

      case 'target_id':
        self::targetIdChange($response, $currencyService, $values, $index);
        break;

      case 'price':
      case 'quantity':
        // On these updates, nothing needs doing except total calc.
        break;

    }

    // Always Update the total.
    self::reCalculateTotal($response, $currencyService, $taxService, $values);

    return $response;
  }

  /**
   * Set up the fields if the target type was changed.
   *
   * @param \Drupal\Core\Ajax\AjaxResponse $response
   *   The ajax response we're building.
   * @param string $index
   *   The line index to update.
   */
  private static function targetTypeChange(AjaxResponse $response, string $index): void {
    $response->addCommand(
      new InvokeCommand(
        "form input[data-drupal-selector='edit-se-item-lines-{$index}-quantity']",
        'val',
        [1]
      ),
    );
    $response->addCommand(
      new InvokeCommand(
        "form input[data-drupal-selector='edit-se-item-lines-{$index}-target_id']",
        'val',
        ['']
      )
    );
    $response->addCommand(
      new InvokeCommand(
        "form input[data-drupal-selector='edit-se-item-lines-{$index}-serial']",
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
   * @param \Drupal\se_accounting\Service\CurrencyFormatService $currencyService
   *   Service for displaying currency.
   * @param array $values
   *   Form values to work with.
   * @param string $index
   *   The line index to update.
   */
  private static function targetIdChange(AjaxResponse $response, CurrencyFormatService $currencyService, array &$values, string $index): void {
    // If there is no target selected we can return now.
    if ($values['se_item_lines'][$index]['target_id'] === NULL) {
      return;
    }

    $targetType = $values['se_item_lines'][$index]['target_type'];
    switch ($targetType) {
      case 'se_timekeeping':
        /** @var \Drupal\se_timekeeping\Entity\Timekeeping $timekeeping */
        if (($timekeeping = Timekeeping::load($values['se_item_lines'][$index]['target_id']))
        && $item = $timekeeping->se_it_ref->entity) {
          $date = new DateTimePlus($timekeeping->se_date->value, date_default_timezone_get());
          $response->addCommand(
            new InvokeCommand(
              "form input[data-drupal-selector='edit-se-item-lines-{$index}-completed-date-date']",
              'val',
              [$date->format('Y-m-d')]
            )
          );
          $response->addCommand(
            new InvokeCommand(
              "form input[data-drupal-selector='edit-se-item-lines-{$index}-quantity']",
              'val',
              [$timekeeping->se_amount]
            ),
          );
        }
        break;

      case 'se_item':
        /** @var \Drupal\se_item\Entity\Item $item */
        if ($item = Item::load($values['se_item_lines'][$index]['target_id'])) {
          $response->addCommand(new InvokeCommand(
            "form input[data-drupal-selector='edit-se-item-lines-{$index}-serial']",
            'val',
            [$item->se_serial->value]
          ));
        }

        break;
    }

    if (!isset($item)) {
      return;
    }

    $item_price = $item->se_sell_price->value;
    $displayPrice = $currencyService->formatDisplay((int) $item_price);
    $item_cost = $item->se_cost_price->value;

    // Update the values so that the total gets updated as well.
    $values['se_item_lines'][$index]['price'] = $displayPrice;
    $values['se_item_lines'][$index]['cost'] = $item_cost;

    // Create a new ajax response to set the price.
    $response->addCommand(new InvokeCommand(
      "form input[data-drupal-selector='edit-se-item-lines-{$index}-price']",
      'val',
      [$displayPrice]
    ));
  }

  /**
   * Re calculate the total field.
   *
   * @param \Drupal\Core\Ajax\AjaxResponse $response
   *   The ajax response we're building.
   * @param \Drupal\se_accounting\Service\CurrencyFormatService $currencyService
   *   Service for displaying currency.
   * @param array $values
   *   Form values to work with.
   */
  private static function reCalculateTotal(AjaxResponse $response, CurrencyFormatService $currencyService, TaxAmountService $taxService, array $values): void {
    $total = 0;
    foreach ($values['se_item_lines'] as $index => $value) {
      if (is_int($index) && !empty($value['target_id'])) {
        $price = $currencyService->formatStorage($value['price']);
        if (!empty($price)) {
          $total += $value['quantity'] * $price;
        }
      }
    }

    $response->addCommand(new InvokeCommand(
      "form input[data-drupal-selector='edit-se-tax-0-value']",
      'val',
      [$currencyService->formatDisplay((int) $taxService->calculateTax((int) $total))]
    ));

    $response->addCommand(new InvokeCommand(
      "form input[data-drupal-selector='edit-se-total-0-value']",
      'val',
      [$currencyService->formatDisplay((int) $total)]
    ));

  }

}
