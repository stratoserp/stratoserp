<?php

declare(strict_types=1);

namespace Drupal\se_business\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\se_business\Entity\BusinessInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class BusinessController.
 *
 *  Returns responses for Business routes.
 */
class BusinessController extends ControllerBase {

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
   * Displays a Business revision.
   *
   * @param int $se_business_revision
   *   The Business revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($se_business_revision) {
    $se_business = $this->entityTypeManager()->getStorage('se_business')
      ->loadRevision($se_business_revision);
    $view_builder = $this->entityTypeManager()->getViewBuilder('se_business');

    return $view_builder->view($se_business);
  }

  /**
   * Page title callback for a Business revision.
   *
   * @param int $se_business_revision
   *   The Business revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($se_business_revision) {
    $se_business = $this->entityTypeManager()->getStorage('se_business')
      ->loadRevision($se_business_revision);
    return $this->t('Revision of %title from %date', [
      '%title' => $se_business->label(),
      '%date' => $this->dateFormatter->format($se_business->getRevisionCreationTime()),
    ]);
  }

  /**
   * Generates an overview table of older revisions of a Business.
   *
   * @param \Drupal\se_business\Entity\BusinessInterface $se_business
   *   A Business object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(BusinessInterface $se_business) {
    $account = $this->currentUser();
    $se_business_storage = $this->entityTypeManager()->getStorage('se_business');

    $langcode = $se_business->language()->getId();
    $langname = $se_business->language()->getName();
    $languages = $se_business->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', [
      '@langname' => $langname,
      '%title' => $se_business->label(),
    ]) : $this->t('Revisions for %title', ['%title' => $se_business->label()]);

    $header = [$this->t('Revision'), $this->t('Operations')];
    $revert_permission = (($account->hasPermission("revert all business revisions") || $account->hasPermission('administer business entities')));
    $delete_permission = (($account->hasPermission("delete all business revisions") || $account->hasPermission('administer business entities')));

    $rows = [];

    $vids = $se_business_storage->revisionIds($se_business);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\se_business\Entity\BusinessInterface $revision */
      $revision = $se_business_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link for revisions that are not active.
        $date = $this->dateFormatter->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $se_business->getRevisionId()) {
          $link = Link::fromTextAndUrl($date, new Url('entity.se_business.revision', [
            'se_business' => $se_business->id(),
            'se_business_revision' => $vid,
          ]))->toString();
        }
        else {
          $link = $se_business->toLink($date)->toString();
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
              Url::fromRoute('entity.se_business.translation_revert', [
                'se_business' => $se_business->id(),
                'se_business_revision' => $vid,
                'langcode' => $langcode,
              ]) :
              Url::fromRoute('entity.se_business.revision_revert', [
                'se_business' => $se_business->id(),
                'se_business_revision' => $vid,
              ]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.se_business.revision_delete', [
                'se_business' => $se_business->id(),
                'se_business_revision' => $vid,
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

    $build['se_business_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
