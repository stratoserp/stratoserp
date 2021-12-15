<?php

namespace Drupal\se_bill\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for deleting a Bill revision.
 *
 * @ingroup se_bill
 */
class BillRevisionDeleteForm extends ConfirmFormBase {

  /**
   * The Bill revision.
   *
   * @var \Drupal\se_bill\Entity\BillInterface
   */
  protected $revision;

  /**
   * The Bill storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $billStorage;

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
    $instance->billStorage = $container->get('entity_type.manager')->getStorage('se_bill');
    $instance->connection = $container->get('database');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'se_bill_revision_delete_confirm';
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
    return new Url('entity.se_bill.version_history', ['se_bill' => $this->revision->id()]);
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
  public function buildForm(array $form, FormStateInterface $form_state, $se_bill_revision = NULL) {
    $this->revision = $this->billStorage->loadRevision($se_bill_revision);
    $form = parent::buildForm($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->billStorage->deleteRevision($this->revision->getRevisionId());

    $this->logger('content')->notice('Bill: deleted %title revision %revision.', [
      '%title' => $this->revision->label(),
      '%revision' => $this->revision->getRevisionId(),
    ]);
    $this->messenger()
      ->addMessage(t('Revision from %revision-date of Bill %title has been deleted.', [
        '%revision-date' => format_date($this->revision->getRevisionCreationTime()),
        '%title' => $this->revision->label(),
      ]));
    $form_state->setRedirect(
      'entity.se_bill.canonical',
      ['se_bill' => $this->revision->id()]
    );
    if ($this->connection->query('SELECT COUNT(DISTINCT vid) FROM {se_bill_field_revision} WHERE id = :id', [':id' => $this->revision->id()])
      ->fetchField() > 1) {
      $form_state->setRedirect(
        'entity.se_bill.version_history',
        ['se_bill' => $this->revision->id()]
      );
    }
  }

}
