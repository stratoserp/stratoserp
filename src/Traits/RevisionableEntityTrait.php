<?php

declare(strict_types=1);

namespace Drupal\stratoserp\Traits;

use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provide common functions used by the various entities, rather than duplicate.
 *
 * This should be able to be removed when this issue hits.
 * https://www.drupal.org/project/drupal/issues/2350939
 */
trait RevisionableEntityTrait {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    $instance = parent::create($container);
    $instance->account = $container->get('current_user');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;

    // Save as a new revision if requested to do so.
    if (!$form_state->isValueEmpty('new_revision') && $form_state->getValue('new_revision') != FALSE) {
      $entity->setNewRevision();

      // If a new revision is created, save the current user as revision author.
      $entity->setRevisionCreationTime($this->time->getRequestTime());
      $entity->setRevisionUserId($this->account->id());
    }
    else {
      $entity->setNewRevision(FALSE);
    }

    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created %label @type.', [
          '%label' => $entity->label(),
          '@type' => $entity->getEntityType()->getLabel(),
        ]));
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved %label @type.', [
          '%label' => $entity->label(),
          '@type' => $entity->getEntityType()->getLabel(),
        ]));
    }
    $entityType = $entity->getEntityTypeId();
    $canonical = "entity.$entityType.canonical";
    if ($entity->hasLinkTemplate('canonical')) {
      $form_state->setRedirect($canonical, [$entityType => $entity->id()]);
    }
  }

}
