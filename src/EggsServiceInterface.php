<?php

declare(strict_types=1);

namespace Drupal\farm_eggs;

use Drupal\taxonomy\TermInterface;

/**
 * Helper functions for managing egg harvest logs.
 */
interface EggsServiceInterface {

  /**
   * Egg types taxonomy id.
   */
  public const EGG_TYPE_TAXONOMY_ID = 'egg_type';

  /**
   * Simple workflow id.
   */
  public const WORKFLOW_SIMPLE = 'simple';

  /**
   * Detailed workflow id.
   */
  public const WORKFLOW_DETAILED = 'detailed';

  /**
   * Get log category used to tag egg harvest logs.
   */
  public function getEggsLogCategory(): TermInterface;

  /**
   * Get egg harvest log name.
   */
  public function getEggHarvestLogName(int $quantity): string;

  /**
   * Get available egg types.
   *
   * @return \Drupal\taxonomy\TermInterface[]
   *   List of published taxonomy term from Egg types vocabulary.
   */
  public function getEggTypes(): array;

  /**
   * Checks if detailed workflow is activated.
   */
  public function isDetailedWorkflow(): bool;

  /**
   * Checks if simple workflow is activated.
   */
  public function isSimpleWorkflow(): bool;

  /**
   * Get currently active workflow type.
   */
  public function getActiveWorkflow(): EggHarvestWorkflow;

}
