<?php

declare(strict_types=1);

namespace Drupal\farm_eggs;

use Drupal\asset\Entity\AssetInterface;

/**
 * Helper methods for managing egg harvests.
 */
interface EggsInterface {

  /**
   * Determines if an asset produces eggs.
   */
  public function producesEggs(AssetInterface $asset): bool;

}
