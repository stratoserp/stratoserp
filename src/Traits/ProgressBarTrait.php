<?php

declare(strict_types=1);

namespace Drupal\stratoserp\Traits;

use Symfony\Component\Console\Helper\ProgressBar;

/**
 * Trait to provide basic function for drush commands.
 */
trait ProgressBarTrait {

  protected ProgressBar $progressBar;

  /**
   * Start the progress bar.
   *
   * @param string $message
   *   Message to tell user.
   */
  private function startProgress($message) {
    $this->stderr()->writeln($message);
    $this->progressBar = new ProgressBar($this->stderr());
    $this->progressBar->start();
  }

  /**
   * Update progress step.
   */
  private function progress() {
    $this->progressBar->advance();
  }

  /**
   * End the progress bar.
   *
   * @param string $message
   *   Message to tell user.
   */
  private function endProgress($message = "\nCompleted") {
    $this->progressBar->finish();
    $this->stderr()->writeln($message);
  }

}
