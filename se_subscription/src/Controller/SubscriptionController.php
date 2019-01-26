<?php

namespace Drupal\se_subscription\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Url;
use Drupal\se_subscription\Entity\SubscriptionInterface;

/**
 * Class SubscriptionController.
 *
 *  Returns responses for Subscription routes.
 */
class SubscriptionController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Displays a Subscription  revision.
   *
   * @param int $se_subscription_revision
   *   The Subscription  revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($se_subscription_revision) {
    $se_subscription = $this->entityManager()->getStorage('se_subscription')->loadRevision($se_subscription_revision);
    $view_builder = $this->entityManager()->getViewBuilder('se_subscription');

    return $view_builder->view($se_subscription);
  }

  /**
   * Page title callback for a Subscription  revision.
   *
   * @param int $se_subscription_revision
   *   The Subscription  revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($se_subscription_revision) {
    $se_subscription = $this->entityManager()->getStorage('se_subscription')->loadRevision($se_subscription_revision);
    return $this->t('Revision of %title from %date', ['%title' => $se_subscription->label(), '%date' => format_date($se_subscription->getRevisionCreationTime())]);
  }

  /**
   * Generates an overview table of older revisions of a Subscription .
   *
   * @param \Drupal\se_subscription\Entity\SubscriptionInterface $se_subscription
   *   A Subscription  object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(SubscriptionInterface $se_subscription) {
    $account = $this->currentUser();
    $langcode = $se_subscription->language()->getId();
    $langname = $se_subscription->language()->getName();
    $languages = $se_subscription->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $se_subscription_storage = $this->entityManager()->getStorage('se_subscription');

    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $se_subscription->label()]) : $this->t('Revisions for %title', ['%title' => $se_subscription->label()]);
    $header = [$this->t('Revision'), $this->t('Operations')];

    $revert_permission = (($account->hasPermission("revert all subscription revisions") || $account->hasPermission('administer subscription entities')));
    $delete_permission = (($account->hasPermission("delete all subscription revisions") || $account->hasPermission('administer subscription entities')));

    $rows = [];

    $vids = $se_subscription_storage->revisionIds($se_subscription);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\se_subscription\SubscriptionInterface $revision */
      $revision = $se_subscription_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = \Drupal::service('date.formatter')->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $se_subscription->getRevisionId()) {
          $link = $this->l($date, new Url('entity.se_subscription.revision', ['se_subscription' => $se_subscription->id(), 'se_subscription_revision' => $vid]));
        }
        else {
          $link = $se_subscription->link($date);
        }

        $row = [];
        $column = [
          'data' => [
            '#type' => 'inline_template',
            '#template' => '{% trans %}{{ date }} by {{ username }}{% endtrans %}{% if message %}<p class="revision-log">{{ message }}</p>{% endif %}',
            '#context' => [
              'date' => $link,
              'username' => \Drupal::service('renderer')->renderPlain($username),
              'message' => ['#markup' => $revision->getRevisionLogMessage(), '#allowed_tags' => Xss::getHtmlTagList()],
            ],
          ],
        ];
        $row[] = $column;

        if ($latest_revision) {
          $row[] = [
            'data' => [
              '#prefix' => '<em>',
              '#markup' => $this->t('Current revision'),
              '#suffix' => '</em>',
            ],
          ];
          foreach ($row as &$current) {
            $current['class'] = ['revision-current'];
          }
          $latest_revision = FALSE;
        }
        else {
          $links = [];
          if ($revert_permission) {
            $links['revert'] = [
              'title' => $this->t('Revert'),
              'url' => $has_translations ?
              Url::fromRoute('entity.se_subscription.translation_revert', ['se_subscription' => $se_subscription->id(), 'se_subscription_revision' => $vid, 'langcode' => $langcode]) :
              Url::fromRoute('entity.se_subscription.revision_revert', ['se_subscription' => $se_subscription->id(), 'se_subscription_revision' => $vid]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.se_subscription.revision_delete', ['se_subscription' => $se_subscription->id(), 'se_subscription_revision' => $vid]),
            ];
          }

          $row[] = [
            'data' => [
              '#type' => 'operations',
              '#links' => $links,
            ],
          ];
        }

        $rows[] = $row;
      }
    }

    $build['se_subscription_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
