<?php

declare(strict_types=1);

namespace Drupal\se_bill\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\se_bill\Entity\BillInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class BillController.
 *
 *  Returns responses for Bill routes.
 */
class BillController extends ControllerBase {

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
   * Displays a Bill revision.
   *
   * @param int $se_bill_revision
   *   The Bill revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($se_bill_revision) {
    $se_bill = $this->entityTypeManager()->getStorage('se_bill')
      ->loadRevision($se_bill_revision);
    $view_builder = $this->entityTypeManager()->getViewBuilder('se_bill');

    return $view_builder->view($se_bill);
  }

  /**
   * Page title callback for a Bill revision.
   *
   * @param int $se_bill_revision
   *   The Bill revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($se_bill_revision) {
    $se_bill = $this->entityTypeManager()->getStorage('se_bill')
      ->loadRevision($se_bill_revision);
    return $this->t('Revision of %title from %date', [
      '%title' => $se_bill->label(),
      '%date' => $this->dateFormatter->format($se_bill->getRevisionCreationTime()),
    ]);
  }

  /**
   * Generates an overview table of older revisions of a Bill.
   *
   * @param \Drupal\se_bill\Entity\BillInterface $se_bill
   *   A Bill object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(BillInterface $se_bill) {
    $account = $this->currentUser();
    $se_bill_storage = $this->entityTypeManager()->getStorage('se_bill');

    $langcode = $se_bill->language()->getId();
    $langname = $se_bill->language()->getName();
    $languages = $se_bill->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', [
      '@langname' => $langname,
      '%title' => $se_bill->label(),
    ]) : $this->t('Revisions for %title', ['%title' => $se_bill->label()]);

    $header = [$this->t('Revision'), $this->t('Operations')];
    $revert_permission = (($account->hasPermission("revert all bill revisions") || $account->hasPermission('administer bill entities')));
    $delete_permission = (($account->hasPermission("delete all bill revisions") || $account->hasPermission('administer bill entities')));

    $rows = [];

    $vids = $se_bill_storage->revisionIds($se_bill);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\se_bill\Entity\BillInterface $revision */
      $revision = $se_bill_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link for revisions that are not active.
        $date = $this->dateFormatter->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $se_bill->getRevisionId()) {
          $link = Link::fromTextAndUrl($date, new Url('entity.se_bill.revision', [
            'se_bill' => $se_bill->id(),
            'se_bill_revision' => $vid,
          ]))->toString();
        }
        else {
          $link = $se_bill->toLink($date)->toString();
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
              Url::fromRoute('entity.se_bill.translation_revert', [
                'se_bill' => $se_bill->id(),
                'se_bill_revision' => $vid,
                'langcode' => $langcode,
              ]) :
              Url::fromRoute('entity.se_bill.revision_revert', [
                'se_bill' => $se_bill->id(),
                'se_bill_revision' => $vid,
              ]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.se_bill.revision_delete', [
                'se_bill' => $se_bill->id(),
                'se_bill_revision' => $vid,
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

    $build['se_bill_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
