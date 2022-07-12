<?php

declare(strict_types=1);

namespace Drupal\se_quote\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\se_quote\Entity\QuoteInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class QuoteController.
 *
 *  Returns responses for Quote routes.
 */
class QuoteController extends ControllerBase {

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
   * Displays a Quote revision.
   *
   * @param int $se_quote_revision
   *   The Quote revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($se_quote_revision) {
    $se_quote = $this->entityTypeManager()->getStorage('se_quote')
      ->loadRevision($se_quote_revision);
    $view_builder = $this->entityTypeManager()->getViewBuilder('se_quote');

    return $view_builder->view($se_quote);
  }

  /**
   * Page title callback for a Quote revision.
   *
   * @param int $se_quote_revision
   *   The Quote revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($se_quote_revision) {
    $se_quote = $this->entityTypeManager()->getStorage('se_quote')
      ->loadRevision($se_quote_revision);
    return $this->t('Revision of %title from %date', [
      '%title' => $se_quote->label(),
      '%date' => $this->dateFormatter->format($se_quote->getRevisionCreationTime()),
    ]);
  }

  /**
   * Generates an overview table of older revisions of a Quote.
   *
   * @param \Drupal\se_quote\Entity\QuoteInterface $se_quote
   *   A Quote object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(QuoteInterface $se_quote) {
    $account = $this->currentUser();
    $langcode = $se_quote->language()->getId();
    $langname = $se_quote->language()->getName();
    $languages = $se_quote->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $se_quote_storage = $this->entityTypeManager()->getStorage('se_quote');

    if ($has_translations) {
      $build['#title'] = $this->t('@langname revisions for %title', [
        '@langname' => $langname,
        '%title' => $se_quote->label(),
      ]);
    }
    else {
      $this->t('Revisions for %title', [
        '%title' => $se_quote->label(),
      ]);
    }

    $header = [$this->t('Revision'), $this->t('Operations')];
    $revert_permission = (($account->hasPermission("revert all quote revisions") || $account->hasPermission('administer quote entities')));
    $delete_permission = (($account->hasPermission("delete all quote revisions") || $account->hasPermission('administer quote entities')));

    $rows = [];

    $vids = $se_quote_storage->revisionIds($se_quote);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\se_quote\Entity\QuoteInterface $revision */
      $revision = $se_quote_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link for revisions that are not active.
        $date = $this->dateFormatter->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $se_quote->getRevisionId()) {
          $link = Link::fromTextAndUrl($date, new Url('entity.se_quote.revision', [
            'se_quote' => $se_quote->id(),
            'se_quote_revision' => $vid,
          ]))->toString();
        }
        else {
          $link = $se_quote->toLink($date)->toString();
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
              Url::fromRoute('entity.se_quote.translation_revert', [
                'se_quote' => $se_quote->id(),
                'se_quote_revision' => $vid,
                'langcode' => $langcode,
              ]) :
              Url::fromRoute('entity.se_quote.revision_revert', [
                'se_quote' => $se_quote->id(),
                'se_quote_revision' => $vid,
              ]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.se_quote.revision_delete', [
                'se_quote' => $se_quote->id(),
                'se_quote_revision' => $vid,
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

    $build['se_quote_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
      '#attributes' => ['class' => 'se-quote-revision-table'],
    ];

    $build['pager'] = ['#type' => 'pager'];

    return $build;
  }

}
