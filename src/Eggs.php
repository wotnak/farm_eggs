<?php

declare(strict_types=1);

namespace Drupal\farm_eggs;

use Drupal\asset\Entity\AssetInterface;

/**
 * {@inheritdoc}
 */
class Eggs implements EggsInterface {

  /**
   * {@inheritdoc}
   */
  public function producesEggs(AssetInterface $entity): bool {
    return $entity->hasField('produces_eggs')
      && $entity->get('produces_eggs')->getValue()[0]['value'] === '1';
  }

}
