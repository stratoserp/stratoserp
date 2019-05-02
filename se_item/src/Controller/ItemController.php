<?php

namespace Drupal\se_item\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\se_item\Entity\ItemInterface;

/**
 * Class ItemController.
 *
 *  Returns responses for Item routes.
 */
class ItemController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Displays a Item  revision.
   *
   * @param int $se_item_revision
   *   The Item  revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($se_item_revision) {
    $se_item = $this->entityTypeManager()->getStorage('se_item')->loadRevision($se_item_revision);
    $view_builder = $this->entityTypeManager()->getViewBuilder('se_item');

    return $view_builder->view($se_item);
  }

  /**
   * Page title callback for a Item  revision.
   *
   * @param int $se_item_revision
   *   The Item  revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($se_item_revision) {
    $se_item = $this->entityTypeManager()->getStorage('se_item')->loadRevision($se_item_revision);
    return $this->t('Revision of %title from %date', ['%title' => $se_item->label(), '%date' => \Drupal::service('date.formatter')->format($se_item->getRevisionCreationTime())]);
  }

  /**
   * Generates an overview table of older revisions of a Item .
   *
   * @param \Drupal\se_item\Entity\ItemInterface $se_item
   *   A Item  object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(ItemInterface $se_item) {
    $account = $this->currentUser();
    $langcode = $se_item->language()->getId();
    $langname = $se_item->language()->getName();
    $languages = $se_item->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $se_item_storage = $this->entityTypeManager()->getStorage('se_item');

    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $se_item->label()]) : $this->t('Revisions for %title', ['%title' => $se_item->label()]);
    $header = [$this->t('Revision'), $this->t('Operations')];

    $revert_permission = (($account->hasPermission("revert all item revisions") || $account->hasPermission('administer item entities')));
    $delete_permission = (($account->hasPermission("delete all item revisions") || $account->hasPermission('administer item entities')));

    $rows = [];

    $vids = $se_item_storage->revisionIds($se_item);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\se_item\ItemInterface $revision */
      $revision = $se_item_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = \Drupal::service('date.formatter')->format($revision->getRevisionCreationTime(), 'short');
        if ($vid !== $se_item->getRevisionId()) {
          $link = Link::fromTextAndUrl($date, new Url('entity.se_item.revision', ['se_item' => $se_item->id(), 'se_item_revision' => $vid]));
        }
        else {
          $link = $se_item->toLink($date);
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
              Url::fromRoute('entity.se_item.translation_revert', ['se_item' => $se_item->id(), 'se_item_revision' => $vid, 'langcode' => $langcode]) :
              Url::fromRoute('entity.se_item.revision_revert', ['se_item' => $se_item->id(), 'se_item_revision' => $vid]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.se_item.revision_delete', ['se_item' => $se_item->id(), 'se_item_revision' => $vid]),
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

    $build['se_item_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
