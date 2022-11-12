<?php

declare(strict_types=1);

namespace Drupal\se_store\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\se_store\Entity\StoreInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class StoreController.
 *
 *  Returns responses for Store routes.
 */
class StoreController extends ControllerBase {

  /**
   * The date formatter.
   *
   * @var \Drupal\Core\Datetime\DateFormatter
   */
  protected $dateFormatter;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\Renderer
   */
  protected $renderer;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->dateFormatter = $container->get('date.formatter');
    $instance->renderer = $container->get('renderer');
    return $instance;
  }

  /**
   * Displays a Store revision.
   *
   * @param int $se_store_revision
   *   The Store revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($se_store_revision) {
    $se_store = $this->entityTypeManager()->getStorage('se_store')
      ->loadRevision($se_store_revision);
    $view_builder = $this->entityTypeManager()->getViewBuilder('se_store');

    return $view_builder->view($se_store);
  }

  /**
   * Page title callback for a Store revision.
   *
   * @param int $se_store_revision
   *   The Store revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($se_store_revision) {
    $se_store = $this->entityTypeManager()->getStorage('se_store')
      ->loadRevision($se_store_revision);
    return $this->t('Revision of %title from %date', [
      '%title' => $se_store->label(),
      '%date' => $this->dateFormatter->format($se_store->getRevisionCreationTime()),
    ]);
  }

  /**
   * Generates an overview table of older revisions of a Store.
   *
   * @param \Drupal\se_store\Entity\StoreInterface $se_store
   *   A Store object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(StoreInterface $se_store) {
    $account = $this->currentUser();
    $langcode = $se_store->language()->getId();
    $langname = $se_store->language()->getName();
    $languages = $se_store->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $se_store_storage = $this->entityTypeManager()->getStorage('se_store');

    if ($has_translations) {
      $build['#title'] = $this->t('@langname revisions for %title', [
        '@langname' => $langname,
        '%title' => $se_store->label(),
      ]);
    }
    else {
      $this->t('Revisions for %title', [
        '%title' => $se_store->label(),
      ]);
    }

    $header = [$this->t('Revision'), $this->t('Operations')];
    $revert_permission = (($account->hasPermission("revert all store revisions") || $account->hasPermission('administer store entities')));
    $delete_permission = (($account->hasPermission("delete all store revisions") || $account->hasPermission('administer store entities')));

    $rows = [];

    $vids = $se_store_storage->revisionIds($se_store);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\se_store\Entity\StoreInterface $revision */
      $revision = $se_store_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link for revisions that are not active.
        $date = $this->dateFormatter->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $se_store->getRevisionId()) {
          $link = Link::fromTextAndUrl($date, new Url('entity.se_store.revision', [
            'se_store' => $se_store->id(),
            'se_store_revision' => $vid,
          ]))->toString();
        }
        else {
          $link = $se_store->toLink($date)->toString();
        }

        $row = [];
        $column = [
          'data' => [
            '#type' => 'inline_template',
            '#template' => '{% trans %}{{ date }} by {{ username }}{% endtrans %}{% if message %}<p class="revision-log">{{ message }}</p>{% endif %}',
            '#context' => [
              'date' => $link,
              'username' => $this->renderer->renderPlain($username),
              'message' => [
                '#markup' => $revision->getRevisionLogMessage(),
                '#allowed_tags' => Xss::getHtmlTagList(),
              ],
            ],
          ],
        ];
        $this->renderer->addCacheableDependency($column['data'], $username);
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
              Url::fromRoute('entity.se_store.translation_revert', [
                'se_store' => $se_store->id(),
                'se_store_revision' => $vid,
                'langcode' => $langcode,
              ]) :
              Url::fromRoute('entity.se_store.revision_revert', [
                'se_store' => $se_store->id(),
                'se_store_revision' => $vid,
              ]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.se_store.revision_delete', [
                'se_store' => $se_store->id(),
                'se_store_revision' => $vid,
              ]),
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

    $build['se_store_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
      '#attributes' => ['class' => 'se-store-revision-table'],
    ];

    $build['pager'] = ['#type' => 'pager'];

    return $build;
  }

}
