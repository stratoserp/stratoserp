<?php

declare(strict_types=1);

namespace Drupal\se_timekeeping\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\se_timekeeping\Entity\Timekeeping;
use Drupal\se_timekeeping\Entity\TimekeepingInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class TimekeepingController.
 *
 *  Returns responses for Timekeeping routes.
 */
class TimekeepingController extends ControllerBase {

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
   * Displays a Timekeeping revision.
   *
   * @param int $se_timekeeping_revision
   *   The Timekeeping revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($se_timekeeping_revision) {
    $se_timekeeping = $this->entityTypeManager()->getStorage('se_timekeeping')
      ->loadRevision($se_timekeeping_revision);
    $view_builder = $this->entityTypeManager()->getViewBuilder('se_timekeeping');

    return $view_builder->view($se_timekeeping);
  }

  /**
   * Page title callback for a Timekeeping revision.
   *
   * @param int $se_timekeeping_revision
   *   The Timekeeping revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($se_timekeeping_revision) {
    $se_timekeeping = $this->entityTypeManager()->getStorage('se_timekeeping')
      ->loadRevision($se_timekeeping_revision);
    return $this->t('Revision of %title from %date', [
      '%title' => $se_timekeeping->label(),
      '%date' => $this->dateFormatter->format($se_timekeeping->getRevisionCreationTime()),
    ]);
  }

  /**
   * Generates an overview table of older revisions of a Timekeeping.
   *
   * @param \Drupal\se_timekeeping\Entity\TimekeepingInterface $se_timekeeping
   *   A Timekeeping object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(TimekeepingInterface $se_timekeeping) {
    $account = $this->currentUser();
    $langcode = $se_timekeeping->language()->getId();
    $langname = $se_timekeeping->language()->getName();
    $languages = $se_timekeeping->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $se_timekeeping_storage = $this->entityTypeManager()->getStorage('se_timekeeping');

    if ($has_translations) {
      $build['#title'] = $this->t('@langname revisions for %title', [
        '@langname' => $langname,
        '%title' => $se_timekeeping->label(),
      ]);
    }
    else {
      $build['#title'] = $this->t('Revisions for %title', [
        '%title' => $se_timekeeping->label(),
      ]);
    }

    $header = [$this->t('Revision'), $this->t('Operations')];
    $revert_permission = (($account->hasPermission("revert all timekeeping revisions") || $account->hasPermission('administer timekeeping entities')));
    $delete_permission = (($account->hasPermission("delete all timekeeping revisions") || $account->hasPermission('administer timekeeping entities')));

    $rows = [];

    $vids = $se_timekeeping_storage->revisionIds($se_timekeeping);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\se_timekeeping\Entity\TimekeepingInterface $revision */
      $revision = $se_timekeeping_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link for revisions that are not active.
        $date = $this->dateFormatter->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $se_timekeeping->getRevisionId()) {
          $link = Link::fromTextAndUrl($date, new Url('entity.se_timekeeping.revision', [
            'se_timekeeping' => $se_timekeeping->id(),
            'se_timekeeping_revision' => $vid,
          ]))->toString();
        }
        else {
          $link = $se_timekeeping->toLink($date)->toString();
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
              Url::fromRoute('entity.se_timekeeping.translation_revert', [
                'se_timekeeping' => $se_timekeeping->id(),
                'se_timekeeping_revision' => $vid,
                'langcode' => $langcode,
              ]) :
              Url::fromRoute('entity.se_timekeeping.revision_revert', [
                'se_timekeeping' => $se_timekeeping->id(),
                'se_timekeeping_revision' => $vid,
              ]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.se_timekeeping.revision_delete', [
                'se_timekeeping' => $se_timekeeping->id(),
                'se_timekeeping_revision' => $vid,
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

    $build['se_timekeeping_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
      '#attributes' => ['class' => 'se-timekeeping-revision-table'],
    ];

    $build['pager'] = ['#type' => 'pager'];

    return $build;
  }

  /**
   * The entity submission form for timekeeping creation from a customer.
   *
   * @param \Drupal\Core\Entity\EntityInterface $source
   *   Source entity to copy data from.
   *
   * @return array
   *   An entity submission form.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function fromCustomer(EntityInterface $source): array {
    $entity = Timekeeping::create([
      'bundle' => 'se_timekeeping',
    ]);

    $entity->se_cu_ref = \Drupal::service('se_customer.service')->lookupCustomer($source);

    return $this->entityFormBuilder()->getForm($entity);
  }

}
