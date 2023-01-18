<?php

declare(strict_types=1);

namespace Drupal\farm_eggs;

use Drupal\taxonomy\TermInterface;

/**
 * Helper functions for managing egg harvest logs.
 */
interface EggsServiceInterface {

  /**
   * Get log category used to tag egg harvest logs.
   */
  public function getEggsLogCategory(): TermInterface;

  /**
   * Get egg harvest log name.
   */
  public function getEggHarvestLogName(int $quantity): string;

}
