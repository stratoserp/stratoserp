<?php

declare(strict_types=1);

namespace Drupal\se_relationship\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\se_relationship\Entity\RelationshipInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for reverting a Relationship revision.
 *
 * @ingroup se_relationship
 */
class RelationshipRevisionRevertForm extends ConfirmFormBase {

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
    $instance->relationshipStorage = $container->get('entity_type.manager')->getStorage('se_relationship');
    $instance->dateFormatter = $container->get('date.formatter');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'se_relationship_revision_revert_confirm';
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
    return new Url('entity.se_relationship.version_history', ['se_relationship' => $this->revision->id()]);
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
  public function buildForm(array $form, FormStateInterface $form_state, $se_relationship_revision = NULL) {
    $this->revision = $this->RelationshipStorage->loadRevision($se_relationship_revision);
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

    $this->logger('content')->notice('Relationship: reverted %title revision %revision.', [
      '%title' => $this->revision->label(),
      '%revision' => $this->revision->getRevisionId(),
    ]);
    $this->messenger()
      ->addMessage(t('Relationship %title has been reverted to the revision from %revision-date.', [
        '%title' => $this->revision->label(),
        '%revision-date' => $this->dateFormatter->format($original_revision_timestamp),
      ]));
    $form_state->setRedirect(
      'entity.se_relationship.version_history',
      ['se_relationship' => $this->revision->id()]
    );
  }

  /**
   * Prepares a revision to be reverted.
   *
   * @param \Drupal\se_relationship\Entity\RelationshipInterface $revision
   *   The revision to be reverted.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return \Drupal\se_relationship\Entity\RelationshipInterface
   *   The prepared revision ready to be stored.
   */
  protected function prepareRevertedRevision(RelationshipInterface $revision, FormStateInterface $form_state) {
    $revision->setNewRevision();
    $revision->isDefaultRevision(TRUE);
    $revision->setRevisionCreationTime(Drupal::time()->getRequestTime());

    return $revision;
  }

}
