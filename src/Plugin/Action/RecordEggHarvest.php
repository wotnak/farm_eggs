<?php

declare(strict_types=1);

namespace Drupal\farm_eggs\Plugin\Action;

use Drupal\asset\Entity\AssetInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Action\ActionBase;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\Url;
use Drupal\farm_eggs\EggsInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Action for recording egg harvests.
 *
 * @Action(
 *   id = "record_egg_harvest",
 *   label = @Translation("Record egg harvest"),
 *   type = "asset",
 * )
 */
class RecordEggHarvest extends ActionBase implements ContainerFactoryPluginInterface {

  /**
   * Constructs a new RecordEggHarvest action object.
   */
  public function __construct(
    array $configuration,
    string $plugin_id,
    array $plugin_definition,
    MessengerInterface $messenger,
    TranslationInterface $stringTranslation,
    protected EggsInterface $eggs,
    protected RouteMatchInterface $routeMatch,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->messenger = $messenger;
    $this->stringTranslation = $stringTranslation;
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
      $container->get('messenger'),
      $container->get('string_translation'),
      $container->get('farm_eggs.eggs'),
      $container->get('current_route_match'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function executeMultiple(array $entities): void {
    // Find all selected assets that produce eggs.
    $assets = [];
    foreach ($entities as $entity) {
      if (
        $entity instanceof AssetInterface
        && $this->eggs->producesEggs($entity)
      ) {
        $assets[] = $entity->id();
      }
    }

    // If no egg-producing assets were selected, show an error message.
    if (empty($assets)) {
      $this->messenger->addError($this->t('No egg-producing assets were selected.'));
      return;
    }

    // If not all selected assets produce eggs, show a warning message.
    if (count($assets) !== count($entities)) {
      $this->messenger->addWarning(
        $this->stringTranslation->formatPlural(
          count($entities) - count($assets),
          'One asset was skipped because it does not produce eggs.',
          '@count assets were skipped because they do not produce eggs.',
        )
      );
    }

    // Redirect to the egg harvest quick form.
    $assetsParamValue = implode(',', $assets);
    $response = new TrustedRedirectResponse(
      Url::fromRoute(
        'farm.quick.eggs',
        [
          'assets' => $assetsParamValue,
          'destination' => Url::fromRouteMatch($this->routeMatch)->toString(),
        ]
      )->toString()
    );
    $response->send();
  }

  /**
   * {@inheritdoc}
   */
  public function execute($object = NULL) {
    $this->executeMultiple([$object]);
  }

  /**
   * {@inheritdoc}
   */
  public function access($entity, ?AccountInterface $account = NULL, $return_as_object = FALSE) {
    $result = AccessResult::allowedIfHasPermission($account, 'create harvest log');
    return $return_as_object ? $result : $result->isAllowed();
  }

}
