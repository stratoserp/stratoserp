<?php

declare(strict_types=1);

namespace Drupal\se_customer\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\se_customer\Entity\CustomerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class CustomerController.
 *
 *  Returns responses for Customer routes.
 */
class CustomerController extends ControllerBase {

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
   * Displays a Customer revision.
   *
   * @param int $se_customer_revision
   *   The Customer revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($se_customer_revision) {
    $se_customer = $this->entityTypeManager()->getStorage('se_customer')
      ->loadRevision($se_customer_revision);
    $view_builder = $this->entityTypeManager()->getViewBuilder('se_customer');

    return $view_builder->view($se_customer);
  }

  /**
   * Page title callback for a Customer revision.
   *
   * @param int $se_customer_revision
   *   The Customer revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($se_customer_revision) {
    $se_customer = $this->entityTypeManager()->getStorage('se_customer')
      ->loadRevision($se_customer_revision);
    return $this->t('Revision of %title from %date', [
      '%title' => $se_customer->label(),
      '%date' => $this->dateFormatter->format($se_customer->getRevisionCreationTime()),
    ]);
  }

  /**
   * Generates an overview table of older revisions of a Customer.
   *
   * @param \Drupal\se_customer\Entity\CustomerInterface $se_customer
   *   A Customer object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(CustomerInterface $se_customer) {
    $account = $this->currentUser();
    $se_customer_storage = $this->entityTypeManager()->getStorage('se_customer');

    $langcode = $se_customer->language()->getId();
    $langname = $se_customer->language()->getName();
    $languages = $se_customer->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', [
      '@langname' => $langname,
      '%title' => $se_customer->label(),
    ]) : $this->t('Revisions for %title', ['%title' => $se_customer->label()]);

    $header = [$this->t('Revision'), $this->t('Operations')];
    $revert_permission = (($account->hasPermission("revert all customer revisions") || $account->hasPermission('administer customer entities')));
    $delete_permission = (($account->hasPermission("delete all customer revisions") || $account->hasPermission('administer customer entities')));

    $rows = [];

    $vids = $se_customer_storage->revisionIds($se_customer);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\se_customer\CustomerInterface $revision */
      $revision = $se_customer_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = $this->dateFormatter->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $se_customer->getRevisionId()) {
          $link = $this->l($date, new Url('entity.se_customer.revision', [
            'se_customer' => $se_customer->id(),
            'se_customer_revision' => $vid,
          ]));
        }
        else {
          $link = $se_customer->link($date);
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
              Url::fromRoute('entity.se_customer.translation_revert', [
                'se_customer' => $se_customer->id(),
                'se_customer_revision' => $vid,
                'langcode' => $langcode,
              ]) :
              Url::fromRoute('entity.se_customer.revision_revert', [
                'se_customer' => $se_customer->id(),
                'se_customer_revision' => $vid,
              ]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.se_customer.revision_delete', [
                'se_customer' => $se_customer->id(),
                'se_customer_revision' => $vid,
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

    $build['se_customer_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
