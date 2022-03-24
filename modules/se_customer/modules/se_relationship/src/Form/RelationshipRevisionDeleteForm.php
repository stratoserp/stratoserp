<?php

declare(strict_types=1);

namespace Drupal\se_relationship\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for deleting a Relationship revision.
 *
 * @ingroup se_relationship
 */
class RelationshipRevisionDeleteForm extends ConfirmFormBase {

  /**
   * The Relationship revision.
   *
   * @var \Drupal\se_relationship\Entity\RelationshipInterface
   */
  protected $revision;

  /**
   * The Relationship storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $relationshipStorage;

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
    $instance->relationshipStorage = $container->get('entity_type.manager')->getStorage('se_relationship');
    $instance->connection = $container->get('database');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'se_relationship_revision_delete_confirm';
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
    return new Url('entity.se_relationship.version_history', ['se_relationship' => $this->revision->id()]);
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
  public function buildForm(array $form, FormStateInterface $form_state, $se_relationship_revision = NULL) {
    $this->revision = $this->RelationshipStorage->loadRevision($se_relationship_revision);
    $form = parent::buildForm($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->RelationshipStorage->deleteRevision($this->revision->getRevisionId());

    $this->logger('content')->notice('Relationship: deleted %title revision %revision.', [
      '%title' => $this->revision->label(),
      '%revision' => $this->revision->getRevisionId(),
    ]);
    $this->messenger()
      ->addMessage(t('Revision from %revision-date of Relationship %title has been deleted.', [
        '%revision-date' => format_date($this->revision->getRevisionCreationTime()),
        '%title' => $this->revision->label(),
      ]));
    $form_state->setRedirect(
      'entity.se_relationship.canonical',
       ['se_relationship' => $this->revision->id()]
    );
    if ($this->connection->query('SELECT COUNT(DISTINCT vid) FROM {se_relationship_field_revision} WHERE id = :id', [':id' => $this->revision->id()])->fetchField() > 1) {
      $form_state->setRedirect(
        'entity.se_relationship.version_history',
         ['se_relationship' => $this->revision->id()]
      );
    }
  }

}
