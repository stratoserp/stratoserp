<?php

declare(strict_types=1);

namespace Drupal\se_invoice\Plugin\ExtraField\Display;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Link;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\extra_field\Plugin\ExtraFieldDisplayFormattedBase;
use Drupal\se_payment\Entity\Payment;
use Drupal\se_payment\Traits\PaymentTrait;
use Drupal\taxonomy\Entity\Term;

/**
 * Extra field to display User invoice statistics.
 *
 * @ExtraFieldDisplay(
 *   id = "invoice_payments",
 *   label = @Translation("Invoice payments"),
 *   bundles = {
 *     "node.se_invoice",
 *   }
 * )
 */
class InvoicePayments extends ExtraFieldDisplayFormattedBase {

  use StringTranslationTrait;
  use PaymentTrait;

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->t('Invoice payments');
  }

  /**
   * {@inheritdoc}
   */
  public function getLabelDisplay() {
    return 'hidden';
  }

  /**
   * {@inheritdoc}
   *
   * @var \Drupal\se_invoice\Entity\Invoice $invoice
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function viewElements(ContentEntityInterface $invoice) {

    $rows = [];

    $payments = $this->getInvoicePayments($invoice);

    foreach ($payments as $payment_line) {
      $row = [];

      /** @var \Drupal\se_payment\Entity\Payment $payment */
      $payment = Payment::load($payment_line->entity_id);
      $uri = $payment->toUrl();

      foreach ($payment->se_pa_lines as $line) {

        /** @var \Drupal\taxonomy\Entity\Term $type */
        $type = Term::load($line->payment_type);
        $row = [
          'payment' => Link::fromTextAndUrl($payment->se_pa_id->value, $uri),
          'date' => Link::fromTextAndUrl($line->payment_date, $uri),
          'type' => $type->name->value,
          'amount' => \Drupal::service('se_accounting.currency_format')
            ->formatDisplay((int) $line->amount),
        ];
      }

      $rows[] = $row;
    }

    return [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => [],
    ];
  }

}
