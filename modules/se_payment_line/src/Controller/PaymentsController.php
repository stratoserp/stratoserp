<?php

declare(strict_types=1);

namespace Drupal\se_payment_line\Controller;

use Drupal\Component\Datetime\DateTimePlus;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\se_accounting\Service\CurrencyFormat;
use Drupal\se_invoice\Entity\Invoice;
use Drupal\se_item\Entity\Item;
use Drupal\se_timekeeping\Entity\Timekeeping;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class for Ajax controller to update the item serial/price.
 */
class PaymentsController extends ControllerBase {

  public static function updateFields(array &$form, FormStateInterface $form_state, Request $request): AjaxResponse {
    $values = $form_state->getValues();
    $response = new AjaxResponse();
    $currencyService = \Drupal::service('se_accounting.currency_format');

    // Use the triggering element to determine the line index.
    $trigger = $request->request->get('_triggering_element_name');

    // Which we can then use with a regular expression;.
    preg_match("/se_payment_lines\[(\d)\]\[(.*?)\].*/", $trigger, $matches);
    if (count($matches) < 3) {
      return $response;
    }

    // To extract the fields we need.
    [$index, $trigger] = array_slice($matches, 1);

    switch ($trigger) {
      case 'target_id':
        self::targetIdChange($response, $currencyService, $values, $index);
        break;

      case 'price':
        // On price updates, nothing needs doing except total calc.
        break;
    }

    // Always Update the total.
    self::reCalculateTotal($response, $currencyService, $values);

    return $response;
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
   * @param string $index
   *   The line index to update.
   */
  private static function targetIdChange(AjaxResponse $response, CurrencyFormat $currencyService, array &$values, string $index): void {
    // If there is no target selected we can return now.
    if ($values['se_payment_lines'][$index]['target_id'] === NULL) {
      return;
    }

    if (!$invoice = Invoice::load($values['se_payment_lines'][$index]['target_id'])) {
      return;
    }

    $invoiceAmount = $invoice->se_total->value;
    $displayPrice = $currencyService->formatDisplay((int) $invoiceAmount);

    // Update the values so that the total gets updated as well.
    $values['se_payment_lines'][$index]['amount'] = $displayPrice;

    // Create a new ajax response to set the price.
    $response->addCommand(new InvokeCommand(
      "form input[data-drupal-selector='edit-se-payment-lines-{$index}-amount']",
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
   */
  private static function reCalculateTotal(AjaxResponse $response, CurrencyFormat $currencyService, array $values): void {
    $total = 0;
    foreach ($values['se_payment_lines'] as $index => $value) {
      if (is_int($index)) {
        $amount = $currencyService->formatStorage($value['amount']);
        if (!empty($amount)) {
          $total += $amount;
        }
      }
    }


    $response->addCommand(new InvokeCommand(
      "form input[data-drupal-selector='edit-se-total-0-value']",
      'val',
      [$currencyService->formatDisplay((int) $total)]
    ));

  }

}
