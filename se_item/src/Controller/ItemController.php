<?php

namespace Drupal\se_item\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
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
   * @param int $item_revision
   *   The Item  revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($item_revision) {
    $item = $this->entityManager()->getStorage('se_item')->loadRevision($item_revision);
    $view_builder = $this->entityManager()->getViewBuilder('se_item');

    return $view_builder->view($item);
  }

  /**
   * Page title callback for a Item  revision.
   *
   * @param int $item_revision
   *   The Item  revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($item_revision) {
    $item = $this->entityManager()->getStorage('se_item')->loadRevision($item_revision);
    return $this->t('Revision of %title from %date', ['%title' => $item->label(), '%date' => format_date($item->getRevisionCreationTime())]);
  }

  /**
   * Generates an overview table of older revisions of a Item .
   *
   * @param \Drupal\se_item\Entity\ItemInterface $item
   *   A Item  object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(ItemInterface $item) {
    $account = $this->currentUser();
    $langcode = $item->language()->getId();
    $langname = $item->language()->getName();
    $languages = $item->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $item_storage = $this->entityManager()->getStorage('se_item');

    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $item->label()]) : $this->t('Revisions for %title', ['%title' => $item->label()]);
    $header = [$this->t('Revision'), $this->t('Operations')];

    $revert_permission = (($account->hasPermission("revert all item revisions") || $account->hasPermission('administer item entities')));
    $delete_permission = (($account->hasPermission("delete all item revisions") || $account->hasPermission('administer item entities')));

    $rows = [];

    $vids = $item_storage->revisionIds($item);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\se_item\ItemInterface $revision */
      $revision = $item_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = \Drupal::service('date.formatter')->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $item->getRevisionId()) {
          $link = $this->l($date, new Url('entity.item.revision', ['se_item' => $item->id(), 'item_revision' => $vid]));
        }
        else {
          $link = $item->link($date);
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
              Url::fromRoute('entity.item.translation_revert', ['se_item' => $item->id(), 'item_revision' => $vid, 'langcode' => $langcode]) :
              Url::fromRoute('entity.item.revision_revert', ['se_item' => $item->id(), 'item_revision' => $vid]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.item.revision_delete', ['se_item' => $item->id(), 'item_revision' => $vid]),
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
