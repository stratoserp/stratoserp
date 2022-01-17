<?php

declare(strict_types=1);

namespace Drupal\se_goods_receipt\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\stratoserp\Form\StratosContentEntityForm;

/**
 * Form controller for Goods Receipt edit forms.
 *
 * @ingroup se_goods_receipt
 */
class GoodsReceiptForm extends StratosContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $service = \Drupal::service('se.form_alter');
    $service->setPurchaseOrderField($form, 'se_po_ref');

    // Perform some goods receipt specific tweaks.
    foreach ($form['se_gr_lines']['widget'] as $index => $value) {
      // @todo I'm sure there is a better way to filter these out.
      if (is_numeric($index)) {
        // Remove all other options, goods receipt is for stock only.
        $form['se_gr_lines']['target_type']['#options'] = ['se_item:se_stock'];
        $form['se_gr_lines']['target_type']['#type'] = 'value';
      }
    }

    return $form;
  }

}
