<?php

declare(strict_types=1);

namespace Drupal\se_relationship\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\se_relationship\Entity\RelationshipInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class RelationshipController.
 *
 *  Returns responses for Relationship routes.
 */
class RelationshipController extends ControllerBase {

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
   * Displays a Relationship revision.
   *
   * @param int $se_relationship_revision
   *   The Relationship revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($se_relationship_revision) {
    $se_relationship = $this->entityTypeManager()->getStorage('se_relationship')
      ->loadRevision($se_relationship_revision);
    $view_builder = $this->entityTypeManager()->getViewBuilder('se_relationship');

    return $view_builder->view($se_relationship);
  }

  /**
   * Page title callback for a Relationship revision.
   *
   * @param int $se_relationship_revision
   *   The Relationship revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($se_relationship_revision) {
    $se_relationship = $this->entityTypeManager()->getStorage('se_relationship')
      ->loadRevision($se_relationship_revision);
    return $this->t('Revision of %title from %date', [
      '%title' => $se_relationship->label(),
      '%date' => $this->dateFormatter->format($se_relationship->getRevisionCreationTime()),
    ]);
  }

  /**
   * Generates an overview table of older revisions of a Relationship.
   *
   * @param \Drupal\se_relationship\Entity\RelationshipInterface $se_relationship
   *   A Relationship object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(RelationshipInterface $se_relationship) {
    $account = $this->currentUser();
    $se_relationship_storage = $this->entityTypeManager()->getStorage('se_relationship');

    $langcode = $se_relationship->language()->getId();
    $langname = $se_relationship->language()->getName();
    $languages = $se_relationship->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', [
      '@langname' => $langname,
      '%title' => $se_relationship->label(),
    ]) : $this->t('Revisions for %title', ['%title' => $se_relationship->label()]);

    $header = [$this->t('Revision'), $this->t('Operations')];
    $revert_permission = (($account->hasPermission("revert all relationship revisions") || $account->hasPermission('administer relationship entities')));
    $delete_permission = (($account->hasPermission("delete all relationship revisions") || $account->hasPermission('administer relationship entities')));

    $rows = [];

    $vids = $se_relationship_storage->revisionIds($se_relationship);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\se_relationship\Entity\RelationshipInterface $revision */
      $revision = $se_relationship_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link for revisions that are not active.
        $date = $this->dateFormatter->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $se_relationship->getRevisionId()) {
          $link = Link::fromTextAndUrl($date, new Url('entity.se_relationship.revision', [
            'se_relationship' => $se_relationship->id(),
            'se_relationship_revision' => $vid,
          ]))->toString();
        }
        else {
          $link = $se_relationship->toLink($date)->toString();
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
              Url::fromRoute('entity.se_relationship.translation_revert', [
                'se_relationship' => $se_relationship->id(),
                'se_relationship_revision' => $vid,
                'langcode' => $langcode,
              ]) :
              Url::fromRoute('entity.se_relationship.revision_revert', [
                'se_relationship' => $se_relationship->id(),
                'se_relationship_revision' => $vid,
              ]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.se_relationship.revision_delete', [
                'se_relationship' => $se_relationship->id(),
                'se_relationship_revision' => $vid,
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

    $build['se_relationship_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
