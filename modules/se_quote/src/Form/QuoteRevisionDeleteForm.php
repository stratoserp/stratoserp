<?php

declare(strict_types=1);

namespace Drupal\se_quote\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for deleting a Quote revision.
 *
 * @ingroup se_quote
 */
class QuoteRevisionDeleteForm extends ConfirmFormBase {

  /**
   * The Quote revision.
   *
   * @var \Drupal\se_quote\Entity\QuoteInterface
   */
  protected $revision;

  /**
   * The Quote storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $quoteStorage;

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
    $instance->quoteStorage = $container->get('entity_type.manager')->getStorage('se_quote');
    $instance->connection = $container->get('database');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'se_quote_revision_delete_confirm';
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
    return new Url('entity.se_quote.version_history', ['se_quote' => $this->revision->id()]);
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
  public function buildForm(array $form, FormStateInterface $form_state, $se_quote_revision = NULL) {
    $this->revision = $this->QuoteStorage->loadRevision($se_quote_revision);
    $form = parent::buildForm($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->QuoteStorage->deleteRevision($this->revision->getRevisionId());

    $this->logger('content')->notice('Quote: deleted %title revision %revision.', [
      '%title' => $this->revision->label(),
      '%revision' => $this->revision->getRevisionId(),
    ]);
    $this->messenger()
      ->addMessage(t('Revision from %revision-date of Quote %title has been deleted.', [
        '%revision-date' => \Drupal::service('date.formatter')->format($this->revision->getRevisionCreationTime()),
        '%title' => $this->revision->label(),
      ]));
    $form_state->setRedirect(
      'entity.se_quote.canonical',
       ['se_quote' => $this->revision->id()]
    );
    if ($this->connection->query('SELECT COUNT(DISTINCT vid) FROM {se_quote_field_revision} WHERE id = :id', [':id' => $this->revision->id()])->fetchField() > 1) {
      $form_state->setRedirect(
        'entity.se_quote.version_history',
         ['se_quote' => $this->revision->id()]
      );
    }
  }

}
