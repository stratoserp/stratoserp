<?php

declare(strict_types=1);

namespace Drupal\se_invoice\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for deleting a Invoice revision.
 *
 * @ingroup se_invoice
 */
class InvoiceRevisionDeleteForm extends ConfirmFormBase {

  /**
   * The Invoice revision.
   *
   * @var \Drupal\se_invoice\Entity\InvoiceInterface
   */
  protected $revision;

  /**
   * The Invoice storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $invoiceStorage;

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
    $instance->invoiceStorage = $container->get('entity_type.manager')->getStorage('se_invoice');
    $instance->connection = $container->get('database');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'se_invoice_revision_delete_confirm';
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
    return new Url('entity.se_invoice.version_history', ['se_invoice' => $this->revision->id()]);
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
  public function buildForm(array $form, FormStateInterface $form_state, $se_invoice_revision = NULL) {
    $this->revision = $this->InvoiceStorage->loadRevision($se_invoice_revision);
    $form = parent::buildForm($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->InvoiceStorage->deleteRevision($this->revision->getRevisionId());

    $this->logger('content')->notice('Invoice: deleted %title revision %revision.', [
      '%title' => $this->revision->label(),
      '%revision' => $this->revision->getRevisionId(),
    ]);
    $this->messenger()
      ->addMessage(t('Revision from %revision-date of Invoice %title has been deleted.', [
        '%revision-date' => \Drupal::service('date.formatter')->format($this->revision->getRevisionCreationTime()),
        '%title' => $this->revision->label(),
      ]));
    $form_state->setRedirect(
      'entity.se_invoice.canonical',
      ['se_invoice' => $this->revision->id()]
    );
    if ($this->connection->query('SELECT COUNT(DISTINCT vid) FROM {se_invoice_field_revision} WHERE id = :id', [':id' => $this->revision->id()])
      ->fetchField() > 1) {
      $form_state->setRedirect(
        'entity.se_invoice.version_history',
        ['se_invoice' => $this->revision->id()]
      );
    }
  }

}
