<?php

declare(strict_types=1);

namespace Drupal\se_timekeeping\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for deleting a Timekeeping revision.
 *
 * @ingroup se_timekeeping
 */
class TimekeepingRevisionDeleteForm extends ConfirmFormBase {

  /**
   * The Timekeeping revision.
   *
   * @var \Drupal\se_timekeeping\Entity\TimekeepingInterface
   */
  protected $revision;

  /**
   * The Timekeeping storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $timekeepingStorage;

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
    $instance->timekeepingStorage = $container->get('entity_type.manager')->getStorage('se_timekeeping');
    $instance->connection = $container->get('database');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'se_timekeeping_revision_delete_confirm';
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
    return new Url('entity.se_timekeeping.version_history', ['se_timekeeping' => $this->revision->id()]);
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
  public function buildForm(array $form, FormStateInterface $form_state, $se_timekeeping_revision = NULL) {
    $this->revision = $this->TimekeepingStorage->loadRevision($se_timekeeping_revision);
    $form = parent::buildForm($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->TimekeepingStorage->deleteRevision($this->revision->getRevisionId());

    $this->logger('content')->notice('Timekeeping: deleted %title revision %revision.', [
      '%title' => $this->revision->label(),
      '%revision' => $this->revision->getRevisionId(),
    ]);
    $this->messenger()
      ->addMessage(t('Revision from %revision-date of Timekeeping %title has been deleted.', [
        '%revision-date' => format_date($this->revision->getRevisionCreationTime()),
        '%title' => $this->revision->label(),
      ]));
    $form_state->setRedirect(
      'entity.se_timekeeping.canonical',
       ['se_timekeeping' => $this->revision->id()]
    );
    if ($this->connection->query('SELECT COUNT(DISTINCT vid) FROM {se_timekeeping_field_revision} WHERE id = :id', [':id' => $this->revision->id()])->fetchField() > 1) {
      $form_state->setRedirect(
        'entity.se_timekeeping.version_history',
         ['se_timekeeping' => $this->revision->id()]
      );
    }
  }

}
