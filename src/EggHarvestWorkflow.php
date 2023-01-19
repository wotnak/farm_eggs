<?php
// Ignore some rules currently having problems with enums. https://www.drupal.org/project/coder/issues/3283741
// phpcs:ignoreFile Drupal.Commenting.InlineComment.DocBlock
// phpcs:ignoreFile Drupal.Commenting.FileComment.Missing

declare(strict_types=1);

namespace Drupal\farm_eggs;

/**
 * Helper functions for managing egg harvest logs.
 */
enum EggHarvestWorkflow: string {

  /**
   * Simple workflow id.
   */
  case SIMPLE = 'simple';

  /**
   * Detailed workflow id.
   */
  case DETAILED = 'detailed';

  /**
   * Get workflow id.
   */
  public function getId(): string {
    return $this->value;
  }

  /**
   * Get translated workflow title.
   */
  public function getTitle(): string {
    return match($this) {
      self::SIMPLE => (string) \t('Simple'),
      self::DETAILED => (string) \t('Detailed'),
    };
  }

  /**
   * Get translated workflow description.
   */
  public function getDescription(): string {
    return match($this) {
      self::SIMPLE => (string) \t(
        'Required total quantity field. Optional additional subtotal quantities per egg type.'
      ),
      self::DETAILED => (string) \t(
        'Required subtotal quantities per egg type. Total quantity is automatically calculated as a sum of all provided quantities.'
      ),
    };
  }

  /**
   * Get default workflow.
   */
  public static function default(): static {
    return self::SIMPLE;
  }

}
