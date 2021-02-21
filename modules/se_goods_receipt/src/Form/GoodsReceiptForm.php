<?php

declare(strict_types=1);

namespace Drupal\se_goods_receipt\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form controller for Goods Receipt edit forms.
 *
 * @ingroup se_goods_receipt
 */
class GoodsReceiptForm extends ContentEntityForm {

  /**
   * The current user account.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $account;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    $instance = parent::create($container);
    $instance->account = $container->get('current_user');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\se_goods_receipt\Entity\GoodsReceipt $entity */
    $form = parent::buildForm($form, $form_state);

    \Drupal::service('stratoserp.set_field')->setBusinessField($form, 'se_bu_ref');
    \Drupal::service('se_purchase_order.set_field')->setPurchaseOrderField($form, 'se_po_ref');

    // Perform some goods receipt specific tweaks.
    foreach ($form['se_gr_lines']['widget'] as $index => $value) {
      // @todo I'm sure there is a better way to filter these out.
      if (is_numeric($index)) {
        // Remove all other options, we can only goods receipt stock.
        $form['se_gr_lines']['target_type']['#options'] = ['se_item:se_stock'];
        $form['se_gr_lines']['target_type']['#type'] = 'value';
      }
    }

    if (!$this->entity->isNew()) {
      $form['new_revision'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Create new revision'),
        '#default_value' => FALSE,
        '#weight' => 10,
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;

    // Save as a new revision if requested to do so.
    if (!$form_state->isValueEmpty('new_revision') && $form_state->getValue('new_revision') != FALSE) {
      $entity->setNewRevision();

      // If a new revision is created, save the current user as revision author.
      $entity->setRevisionCreationTime($this->time->getRequestTime());
      $entity->setRevisionUserId($this->account->id());
    }
    else {
      $entity->setNewRevision(FALSE);
    }

    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created the %label Goods Receipt.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved the %label Goods Receipt.', [
          '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirect('entity.se_goods_receipt.canonical', ['se_goods_receipt' => $entity->id()]);
  }

}
