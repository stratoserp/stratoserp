<?php

declare(strict_types=1);

namespace Drupal\se_goods_receipt\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\se_goods_receipt\Entity\GoodsReceiptInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for reverting a Goods receipt revision.
 *
 * @ingroup se_goods_receipt
 */
class GoodsReceiptRevisionRevertForm extends ConfirmFormBase {

  /**
   * The Goods receipt revision.
   *
   * @var \Drupal\se_goods_receipt\Entity\GoodsReceiptInterface
   */
  protected $revision;

  /**
   * The Goods receipt storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $goodsReceiptStorage;

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->goodsReceiptStorage = $container->get('entity_type.manager')->getStorage('se_goods_receipt');
    $instance->dateFormatter = $container->get('date.formatter');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'se_goods_receipt_revision_revert_confirm';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to revert to the revision from %revision-date?', [
      '%revision-date' => $this->dateFormatter->format($this->revision->getRevisionCreationTime()),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.se_goods_receipt.version_history', ['se_goods_receipt' => $this->revision->id()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Revert');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $se_goods_receipt_revision = NULL) {
    $this->revision = $this->GoodsReceiptStorage->loadRevision($se_goods_receipt_revision);
    $form = parent::buildForm($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // The revision timestamp will be updated when the revision is saved. Keep
    // the original one for the confirmation message.
    $original_revision_timestamp = $this->revision->getRevisionCreationTime();

    $this->revision = $this->prepareRevertedRevision($this->revision, $form_state);
    $this->revision->revision_log = $this->t('Copy of the revision from %date.', [
      '%date' => $this->dateFormatter->format($original_revision_timestamp),
    ]);
    $this->revision->save();

    $this->logger('content')->notice('Goods receipt: reverted %title revision %revision.', [
      '%title' => $this->revision->label(),
      '%revision' => $this->revision->getRevisionId(),
    ]);
    $this->messenger()
      ->addMessage(t('Goods receipt %title has been reverted to the revision from %revision-date.', [
        '%title' => $this->revision->label(),
        '%revision-date' => $this->dateFormatter->format($original_revision_timestamp),
      ]));
    $form_state->setRedirect(
      'entity.se_goods_receipt.version_history',
      ['se_goods_receipt' => $this->revision->id()]
    );
  }

  /**
   * Prepares a revision to be reverted.
   *
   * @param \Drupal\se_goods_receipt\Entity\GoodsReceiptInterface $revision
   *   The revision to be reverted.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return \Drupal\se_goods_receipt\Entity\GoodsReceiptInterface
   *   The prepared revision ready to be stored.
   */
  protected function prepareRevertedRevision(GoodsReceiptInterface $revision, FormStateInterface $form_state) {
    $revision->setNewRevision();
    $revision->isDefaultRevision(TRUE);
    $revision->setRevisionCreationTime(Drupal::time()->getRequestTime());

    return $revision;
  }

}
