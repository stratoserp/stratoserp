<?php

declare(strict_types=1);

namespace Drupal\se_payment\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for deleting a Payment revision.
 *
 * @ingroup se_payment
 */
class PaymentRevisionDeleteForm extends ConfirmFormBase {

  /**
   * The Payment revision.
   *
   * @var \Drupal\se_payment\Entity\PaymentInterface
   */
  protected $revision;

  /**
   * The Payment storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $paymentStorage;

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
    $instance->paymentStorage = $container->get('entity_type.manager')->getStorage('se_payment');
    $instance->connection = $container->get('database');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'se_payment_revision_delete_confirm';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete the revision from %revision-date?', [
      '%revision-date' => \Drupal::service('date.formatter')->format($this->revision->getRevisionCreationTime()),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.se_payment.version_history', ['se_payment' => $this->revision->id()]);
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
  public function buildForm(array $form, FormStateInterface $form_state, $se_payment_revision = NULL) {
    $this->revision = $this->PaymentStorage->loadRevision($se_payment_revision);
    $form = parent::buildForm($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->PaymentStorage->deleteRevision($this->revision->getRevisionId());

    $this->logger('content')->notice('Payment: deleted %title revision %revision.', [
      '%title' => $this->revision->label(),
      '%revision' => $this->revision->getRevisionId(),
    ]);
    $this->messenger()
      ->addMessage(t('Revision from %revision-date of Payment %title has been deleted.', [
        '%revision-date' => \Drupal::service('date.formatter')->format($this->revision->getRevisionCreationTime()),
        '%title' => $this->revision->label(),
      ]));
    $form_state->setRedirect(
      'entity.se_payment.canonical',
      ['se_payment' => $this->revision->id()]
    );
    if ($this->connection->query('SELECT COUNT(DISTINCT vid) FROM {se_payment_field_revision} WHERE id = :id', [':id' => $this->revision->id()])
      ->fetchField() > 1) {
      $form_state->setRedirect(
        'entity.se_payment.version_history',
        ['se_payment' => $this->revision->id()]
      );
    }
  }

}
