<?php

declare(strict_types=1);

namespace Drupal\se_information\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\se_information\Entity\InformationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class InformationController.
 *
 *  Returns responses for Information routes.
 */
class InformationController extends ControllerBase {

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
   * Displays a Information  revision.
   *
   * @param int $se_information_revision
   *   The Information  revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($se_information_revision) {
    $se_information = $this->entityTypeManager()->getStorage('se_information')->loadRevision($se_information_revision);
    $view_builder = $this->entityTypeManager()->getViewBuilder('se_information');

    return $view_builder->view($se_information);
  }

  /**
   * Page title callback for a Information  revision.
   *
   * @param int $se_information_revision
   *   The Information  revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($se_information_revision) {
    $se_information = $this->entityTypeManager()->getStorage('se_information')->loadRevision($se_information_revision);
    return $this->t('Revision of %title from %date', [
      '%title' => $se_information->label(),
      '%date' => $this->dateFormatter->format($se_information->getRevisionCreationTime()),
    ]);
  }

  /**
   * Generates an overview table of older revisions of a Information .
   *
   * @param \Drupal\se_information\Entity\InformationInterface $se_information
   *   A Information  object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(InformationInterface $se_information) {
    $account = $this->currentUser();
    $langcode = $se_information->language()->getId();
    $langname = $se_information->language()->getName();
    $languages = $se_information->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $se_information_storage = $this->entityTypeManager()->getStorage('se_information');

    if ($has_translations) {
      $build['#title'] = $this->t('@langname revisions for %title', [
        '@langname' => $langname,
        '%title' => $se_information->label(),
      ]);
    }
    else {
      $this->t('Revisions for %title', [
        '%title' => $se_information->label(),
      ]);
    }

    $header = [$this->t('Revision'), $this->t('Operations')];
    $revert_permission = (($account->hasPermission("revert all information revisions") || $account->hasPermission('administer information entities')));
    $delete_permission = (($account->hasPermission("delete all information revisions") || $account->hasPermission('administer information entities')));

    $rows = [];

    $vids = $se_information_storage->revisionIds($se_information);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\se_information\Entity\InformationInterface $revision */
      $revision = $se_information_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link for revisions that are not active.
        $date = $this->dateFormatter->format($revision->getRevisionCreationTime(), 'short');
        if ($vid !== $se_information->getRevisionId()) {
          $link = Link::fromTextAndUrl($date, new Url('entity.se_information.revision', [
            'se_information' => $se_information->id(),
            'se_information_revision' => $vid,
          ]))->toString();
        }
        else {
          $link = $se_information->toLink($date)->toString();
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
              Url::fromRoute('entity.se_information.translation_revert', [
                'se_information' => $se_information->id(),
                'se_information_revision' => $vid,
                'langcode' => $langcode,
              ]) :
              Url::fromRoute('entity.se_information.revision_revert', [
                'se_information' => $se_information->id(),
                'se_information_revision' => $vid,
              ]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.se_information.revision_delete', [
                'se_information' => $se_information->id(),
                'se_information_revision' => $vid,
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

    $build['se_information_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
      '#attributes' => ['class' => 'se-information-revision-table'],
    ];

    $build['pager'] = ['#type' => 'pager'];

    return $build;
  }

}
