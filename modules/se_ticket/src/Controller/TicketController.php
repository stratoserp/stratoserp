<?php

declare(strict_types=1);

namespace Drupal\se_ticket\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\se_ticket\Entity\TicketInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class TicketController.
 *
 *  Returns responses for Ticket routes.
 */
class TicketController extends ControllerBase {

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
   * Displays a Ticket revision.
   *
   * @param int $se_ticket_revision
   *   The Ticket revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($se_ticket_revision) {
    $se_ticket = $this->entityTypeManager()->getStorage('se_ticket')
      ->loadRevision($se_ticket_revision);
    $view_builder = $this->entityTypeManager()->getViewBuilder('se_ticket');

    return $view_builder->view($se_ticket);
  }

  /**
   * Page title callback for a Ticket revision.
   *
   * @param int $se_ticket_revision
   *   The Ticket revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($se_ticket_revision) {
    $se_ticket = $this->entityTypeManager()->getStorage('se_ticket')
      ->loadRevision($se_ticket_revision);
    return $this->t('Revision of %title from %date', [
      '%title' => $se_ticket->label(),
      '%date' => $this->dateFormatter->format($se_ticket->getRevisionCreationTime()),
    ]);
  }

  /**
   * Generates an overview table of older revisions of a Ticket.
   *
   * @param \Drupal\se_ticket\Entity\TicketInterface $se_ticket
   *   A Ticket object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(TicketInterface $se_ticket) {
    $account = $this->currentUser();
    $langcode = $se_ticket->language()->getId();
    $langname = $se_ticket->language()->getName();
    $languages = $se_ticket->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $se_ticket_storage = $this->entityTypeManager()->getStorage('se_ticket');

    if ($has_translations) {
      $build['#title'] = $this->t('@langname revisions for %title', [
        '@langname' => $langname,
        '%title' => $se_ticket->label(),
      ]);
    }
    else {
      $this->t('Revisions for %title', [
        '%title' => $se_ticket->label(),
      ]);
    }

    $header = [$this->t('Revision'), $this->t('Operations')];
    $revert_permission = (($account->hasPermission("revert all ticket revisions") || $account->hasPermission('administer ticket entities')));
    $delete_permission = (($account->hasPermission("delete all ticket revisions") || $account->hasPermission('administer ticket entities')));

    $rows = [];

    $vids = $se_ticket_storage->revisionIds($se_ticket);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\se_ticket\Entity\TicketInterface $revision */
      $revision = $se_ticket_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link for revisions that are not active.
        $date = $this->dateFormatter->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $se_ticket->getRevisionId()) {
          $link = Link::fromTextAndUrl($date, new Url('entity.se_ticket.revision', [
            'se_ticket' => $se_ticket->id(),
            'se_ticket_revision' => $vid,
          ]))->toString();
        }
        else {
          $link = $se_ticket->toLink($date)->toString();
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
              Url::fromRoute('entity.se_ticket.translation_revert', [
                'se_ticket' => $se_ticket->id(),
                'se_ticket_revision' => $vid,
                'langcode' => $langcode,
              ]) :
              Url::fromRoute('entity.se_ticket.revision_revert', [
                'se_ticket' => $se_ticket->id(),
                'se_ticket_revision' => $vid,
              ]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.se_ticket.revision_delete', [
                'se_ticket' => $se_ticket->id(),
                'se_ticket_revision' => $vid,
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

    $build['se_ticket_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
      '#attributes' => ['class' => 'se-ticket-revision-table'],
    ];

    $build['pager'] = ['#type' => 'pager'];

    return $build;
  }

}
