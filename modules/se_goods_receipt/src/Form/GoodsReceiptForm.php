<?php

declare(strict_types=1);

namespace Drupal\se_goods_receipt\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\stratoserp\Traits\RevisionableEntityTrait;

/**
 * Form controller for Goods Receipt edit forms.
 *
 * @ingroup se_goods_receipt
 */
class GoodsReceiptForm extends ContentEntityForm {

  use RevisionableEntityTrait;
  
  /**
   * The current user account.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected AccountProxyInterface $account;

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $service = \Drupal::service('se.form_alter');
    $service->setBusinessField($form, 'se_bu_ref');
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

    if (!$this->entity->isNew()) {
      $form['group_gr_extra']['new_revision'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Create new revision'),
        '#default_value' => FALSE,
        '#weight' => -20,
      ];
    }

    return $form;
  }

}
