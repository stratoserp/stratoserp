<?php

namespace Drupal\se_purchase_order\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for deleting a PurchaseOrder revision.
 *
 * @ingroup se_purchase_order
 */
class PurchaseOrderRevisionDeleteForm extends ConfirmFormBase {

  /**
   * The PurchaseOrder revision.
   *
   * @var \Drupal\se_purchase_order\Entity\PurchaseOrderInterface
   */
  protected $revision;

  /**
   * The PurchaseOrder storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $purchaseOrderStorage;

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
    $instance->purchaseOrderStorage = $container->get('entity_type.manager')->getStorage('se_purchase_order');
    $instance->connection = $container->get('database');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'se_purchase_order_revision_delete_confirm';
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
    return new Url('entity.se_purchase_order.version_history', ['se_purchase_order' => $this->revision->id()]);
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
  public function buildForm(array $form, FormStateInterface $form_state, $se_purchase_order_revision = NULL) {
    $this->revision = $this->PurchaseOrderStorage->loadRevision($se_purchase_order_revision);
    $form = parent::buildForm($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->PurchaseOrderStorage->deleteRevision($this->revision->getRevisionId());

    $this->logger('content')->notice('PurchaseOrder: deleted %title revision %revision.', [
      '%title' => $this->revision->label(),
      '%revision' => $this->revision->getRevisionId(),
    ]);
    $this->messenger()
      ->addMessage(t('Revision from %revision-date of PurchaseOrder %title has been deleted.', [
        '%revision-date' => format_date($this->revision->getRevisionCreationTime()),
        '%title' => $this->revision->label(),
      ]));
    $form_state->setRedirect(
      'entity.se_purchase_order.canonical',
      ['se_purchase_order' => $this->revision->id()]
    );
    if ($this->connection->query('SELECT COUNT(DISTINCT vid) FROM {se_purchase_order_field_revision} WHERE id = :id', [':id' => $this->revision->id()])
      ->fetchField() > 1) {
      $form_state->setRedirect(
        'entity.se_purchase_order.version_history',
        ['se_purchase_order' => $this->revision->id()]
      );
    }
  }

}
