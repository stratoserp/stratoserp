<?php

declare(strict_types=1);

namespace Drupal\se_store\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for deleting a Store revision.
 *
 * @ingroup se_store
 */
class StoreRevisionDeleteForm extends ConfirmFormBase {

  /**
   * The Store revision.
   *
   * @var \Drupal\se_store\Entity\StoreInterface
   */
  protected $revision;

  /**
   * The Store storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $storeStorage;

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
    $instance->storeStorage = $container->get('entity_type.manager')->getStorage('se_store');
    $instance->connection = $container->get('database');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'se_store_revision_delete_confirm';
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
    return new Url('entity.se_store.version_history', ['se_store' => $this->revision->id()]);
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
  public function buildForm(array $form, FormStateInterface $form_state, $se_store_revision = NULL) {
    $this->revision = $this->StoreStorage->loadRevision($se_store_revision);
    $form = parent::buildForm($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->StoreStorage->deleteRevision($this->revision->getRevisionId());

    $this->logger('content')->notice('Store: deleted %title revision %revision.', [
      '%title' => $this->revision->label(),
      '%revision' => $this->revision->getRevisionId(),
    ]);
    $this->messenger()
      ->addMessage(t('Revision from %revision-date of Store %title has been deleted.', [
        '%revision-date' => \Drupal::service('date.formatter')->format($this->revision->getRevisionCreationTime()),
        '%title' => $this->revision->label(),
      ]));
    $form_state->setRedirect(
      'entity.se_store.canonical',
       ['se_store' => $this->revision->id()]
    );
    if ($this->connection->query('SELECT COUNT(DISTINCT vid) FROM {se_store_field_revision} WHERE id = :id', [':id' => $this->revision->id()])->fetchField() > 1) {
      $form_state->setRedirect(
        'entity.se_store.version_history',
         ['se_store' => $this->revision->id()]
      );
    }
  }

}
