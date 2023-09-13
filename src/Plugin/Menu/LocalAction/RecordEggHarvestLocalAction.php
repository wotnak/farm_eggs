<?php

declare(strict_types=1);

namespace Drupal\farm_eggs\Plugin\Menu\LocalAction;

use Drupal\asset\Entity\AssetInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Menu\LocalActionDefault;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Routing\RouteProviderInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Local action for recording egg harvests.
 */
class RecordEggHarvestLocalAction extends LocalActionDefault {

  /**
   * Constructs a new RecordEggHarvest action object.
   */
  public function __construct(
    array $configuration,
    string $plugin_id,
    array $plugin_definition,
    RouteProviderInterface $route_provider,
    protected RouteMatchInterface $routeMatch,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $route_provider);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition,
  ): static {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('router.route_provider'),
      $container->get('current_route_match'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getOptions(RouteMatchInterface $routeMatch): array {
    $options = parent::getOptions($routeMatch);

    // Add the asset id to the route query parameters.
    $asset = $routeMatch->getParameter('asset');
    if ($asset instanceof AssetInterface) {
      $options['query']['assets'] = $asset->id();
    }
    elseif (is_numeric($asset)) {
      $options['query']['assets'] = $asset;
    }

    // Add the destination to the route query parameters.
    $options['query']['destination'] = Url::fromRouteMatch($routeMatch)->toString();

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags(): array {
    $tags = parent::getCacheTags();

    // Add cache tags of the asset.
    $asset = $this->routeMatch->getParameter('asset');
    if ($asset instanceof AssetInterface) {
      $tags = Cache::mergeTags($tags, $asset->getCacheTags());
    }

    return $tags;
  }

}
