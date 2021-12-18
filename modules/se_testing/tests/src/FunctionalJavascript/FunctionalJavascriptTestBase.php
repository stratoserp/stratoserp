<?php

declare(strict_types=1);

namespace Drupal\Tests\se_testing\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\JSWebAssert;
use Drupal\Tests\se_testing\Functional\FunctionalTestBase;
use weitzman\DrupalTestTraits\Selenium2DriverTrait;

/**
 * A base class for Javascript testing an installed site.
 */
class FunctionalJavascriptTestBase extends FunctionalTestBase {

  use Selenium2DriverTrait;

  /**
   * Screenshot counter.
   *
   * @var int
   */
  protected $screenshotCounter = 0;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->doResizeScreenToDesktop();
  }

  /**
   * Resizes the window to desktop viewing size.
   */
  protected function doResizeScreenToDesktop() {
    $this->getSession()->resizeWindow(1920, 1080);
  }

  /**
   * {@inheritdoc}
   */
  public function assertSession($name = NULL) {
    return new JSWebAssert($this->getSession($name), $this->baseUrl);
  }

  /**
   * {@inheritdoc}
   */
  protected function getHtmlOutputHeaders() {
    // The webdriver API does not support fetching headers.
    return '';
  }

  /**
   * Creates a screenshot.
   *
   * @return string
   *   Filename.
   *
   * @throws \Behat\Mink\Exception\UnsupportedDriverActionException
   *   When operation not supported by the driver.
   * @throws \Behat\Mink\Exception\DriverException
   *   When the operation cannot be done.
   */
  protected function createScreenshot() {
    $session = $this->getSession();
    $filename = $this->htmlOutputDirectory . '/screenshot-' . $this->htmlOutputTestId . '-' . $this->screenshotCounter . '.jpg';
    $this->screenshotCounter++;
    $session->executeScript("document.body.style.backgroundColor = 'white';");
    $image = $session->getScreenshot();
    file_put_contents($filename, $image);
    return $filename;
  }

  /**
   * Embed and create a screenshot.
   */
  protected function screenshotOutput() {
    $filename = $this->createScreenshot();
    $this->htmlOutput('<html><title>Screenshot</title><body><hr />Ending URL: ' . $this->getSession()->getCurrentUrl() . '<hr/><img src="/sites/simpletest/browser_output/' . basename($filename) . '" /></body></html>');
  }

}
