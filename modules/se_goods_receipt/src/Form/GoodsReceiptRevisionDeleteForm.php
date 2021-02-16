<?php

declare(strict_types=1);

namespace Drupal\se_goods_receipt\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for deleting a Goods Receipt revision.
 *
 * @ingroup se_goods_receipt
 */
class GoodsReceiptRevisionDeleteForm extends ConfirmFormBase {

  /**
   * The Goods Receipt revision.
   *
   * @var \Drupal\se_goods_receipt\Entity\GoodsReceiptInterface
   */
  protected $revision;

  /**
   * The Goods Receipt storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $goodsReceiptStorage;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->goodsReceiptStorage = $container->get('entity_type.manager')->getStorage('se_goods_receipt');
    $instance->connection = $container->get('database');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'se_goods_receipt_revision_delete_confirm';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete the revision from %revision-date?', [
      '%revision-date' => format_date($this->revision->getRevisionCreationTime()),
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
    return $this->t('Delete');
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
    $this->GoodsReceiptStorage->deleteRevision($this->revision->getRevisionId());

    $this->logger('content')->notice('Goods Receipt: deleted %title revision %revision.', [
      '%title' => $this->revision->label(),
      '%revision' => $this->revision->getRevisionId(),
    ]);
    $this->messenger()
      ->addMessage(t('Revision from %revision-date of Goods Receipt %title has been deleted.', [
        '%revision-date' => format_date($this->revision->getRevisionCreationTime()),
        '%title' => $this->revision->label(),
      ]));
    $form_state->setRedirect(
      'entity.se_goods_receipt.canonical',
      ['se_goods_receipt' => $this->revision->id()]
    );
    if ($this->connection->query('SELECT COUNT(DISTINCT vid) FROM {se_goods_receipt_field_revision} WHERE id = :id', [':id' => $this->revision->id()])
      ->fetchField() > 1) {
      $form_state->setRedirect(
        'entity.se_goods_receipt.version_history',
        ['se_goods_receipt' => $this->revision->id()]
      );
    }
  }

}
