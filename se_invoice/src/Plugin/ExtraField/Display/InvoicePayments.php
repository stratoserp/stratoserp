<?php

namespace Drupal\se_invoice\Plugin\ExtraField\Display;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Link;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\extra_field\Plugin\ExtraFieldDisplayFormattedBase;
use Drupal\node\Entity\Node;
use Drupal\se_payment\Traits\ErpPaymentTrait;
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
  use ErpPaymentTrait;

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
   * @var \Drupal\node\Entity\Node $entity.
   */
  public function viewElements(ContentEntityInterface $entity) {

    $rows = [];

    $payments = $this->getInvoicePayments($entity);

    foreach ($payments as $payment_line) {
      $row = [];

      /** @var \Drupal\node\Entity\Node $payment */
      $payment = Node::load($payment_line->entity_id);
      $uri = $payment->toUrl();

      foreach ($payment->field_pa_lines as $line) {

        /** @var \Drupal\taxonomy\Entity\Term $type */
        $type = Term::load($line->payment_type);
        $row = [
          'payment' => Link::fromTextAndUrl($payment->field_pa_id->value, $uri),
          'date' => Link::fromTextAndUrl($line->payment_date, $uri),
          'type' => $type->name->value,
          'amount' => \Drupal::service('se_accounting.currency_format')
            ->formatDisplay($line->amount),
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
