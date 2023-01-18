<?php

declare(strict_types=1);

namespace Drupal\farm_eggs;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\taxonomy\TermInterface;

/**
 * {@inheritdoc}
 */
class EggsService implements EggsServiceInterface {

  use StringTranslationTrait;

  /**
   * Log category taxonomy id.
   */
  protected const LOG_CATEGORY_TAXONOMY_ID = 'log_category';

  /**
   * The entity type manager.
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * Constructs an EggsService object.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    TranslationInterface $string_translation,
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->stringTranslation = $string_translation;
  }

  /**
   * {@inheritdoc}
   */
  public function getEggsLogCategory(): TermInterface {
    $termStorage = $this->entityTypeManager->getStorage('taxonomy_term');
    $eggsCategory = $termStorage->loadByProperties([
      'name' => $this->getEggsCategoryName(),
      'vid' => self::LOG_CATEGORY_TAXONOMY_ID,
    ]);
    if (!empty($eggsCategory)) {
      return reset($eggsCategory);
    }
    $eggsCategory = $termStorage->create([
      'name' => $this->getEggsCategoryName(),
      'vid' => self::LOG_CATEGORY_TAXONOMY_ID,
    ]);
    $eggsCategory->save();
    return $eggsCategory;
  }

  /**
   * {@inheritdoc}
   */
  public function getEggHarvestLogName(int $quantity): string {
    return (string) $this->t('Collected @qty egg(s)', ['@qty' => $quantity]);
  }

  /**
   * {@inheritdoc}
   */
  public function getEggTypes(): array {
    return $this->entityTypeManager->getStorage('taxonomy_term')->loadByProperties([
      'vid' => self::EGG_TYPE_TAXONOMY_ID,
      'status' => 1,
    ]);
  }

  /**
   * Get eggs log category name.
   */
  protected function getEggsCategoryName(): string {
    return (string) $this->t('Eggs');
  }

}
