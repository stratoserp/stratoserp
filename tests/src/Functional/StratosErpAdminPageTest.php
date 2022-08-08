<?php

declare(strict_types=1);

namespace Drupal\Tests\stratoserp\Functional;

use Drupal\Tests\se_testing\Functional\FunctionalTestBase;

/**
 * Test that the settings page has attached to the ticket page.
 *
 * @covers \Drupal\stratoserp\Form\SettingsForm
 * @group stratoserp_core
 * @group stratoserp
 */
class StratosErpAdminPageTest extends FunctionalTestBase {

  /**
   * Text link to check.
   */
  public const ADMIN_LINK_TEXT = 'StratosERP UI';

  /**
   * Test that the integration admin page shows and has things we expect.
   *
   * @throws \Behat\Mink\Exception\ResponseTextException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testStratosErpAdminPageTest() {

    $page = $this->getSession()->getPage();
    $assert = $this->assertSession();

    $developer = $this->setupAdministratorUser();
    $this->drupalLogin($developer);

    $this->drupalGet('/admin/structure/stratoserp/ui');
    $assert->pageTextContains(self::ADMIN_LINK_TEXT);

    $page->findLink(self::ADMIN_LINK_TEXT)->click();

    $assert->pageTextContains('Hide the Search box');
    $assert->pageTextContains('Hide the Contextual buttons');
  }

}
