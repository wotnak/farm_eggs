<?php

declare(strict_types=1);

namespace Drupal\farm_eggs\Plugin\Field\FieldFormatter;

use Drupal\asset\Entity\AssetInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldFormatter\BooleanFormatter;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Drupal\farm_eggs\EggsInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Field formatter for the produces eggs asset field.
 *
 * @FieldFormatter(
 *   id = "asset_produces_eggs",
 *   label = @Translation("Asset produces eggs"),
 *   description = @Translation("Display the record egg harvest button for egg producing assets."),
 *   field_types = {
 *     "boolean"
 *   }
 * )
 */
class AssetProducesEggsFormatter extends BooleanFormatter {

  /**
   * Constructs a AssetProducesEggsFormatter object.
   */
  public function __construct(
    $plugin_id,
    $plugin_definition,
    FieldDefinitionInterface $field_definition,
    array $settings,
    $label,
    $view_mode,
    array $third_party_settings,
    protected EggsInterface $eggs,
    protected RouteMatchInterface $routeMatch,
  ) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
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
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('farm_eggs.eggs'),
      $container->get('current_route_match'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings(): array {
    return [
      'record_egg_harvest_button' => FALSE,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state): array {
    $elements = parent::settingsForm($form, $form_state);
    $elements['record_egg_harvest_button'] = [
      '#title' => $this->t('Record egg harvest button'),
      '#description' => $this->t('Include a button to record egg harvest for the asset.'),
      '#type' => 'checkbox',
      '#default_value' => $this->getSetting('record_egg_harvest_button'),
    ];
    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary(): array {
    $summary = parent::settingsSummary();
    $summary[] = $this->getSetting('record_egg_harvest_button') ? $this->t('Include record egg harvest button') : $this->t('No record egg harvest button');
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode): array {

    // Build labels in parent.
    $elements = parent::viewElements($items, $langcode);

    // Get the asset.
    $asset = $items->getEntity();

    // Add the record egg harvest button if configured.
    if (
      $this->getSetting('record_egg_harvest_button')
      && $asset instanceof AssetInterface
      && $this->eggs->producesEggs($asset)
    ) {
      // Append a "Record egg harvest" link.
      $elements[] = [
        '#type' => 'link',
        '#title' => $this->t('Record egg harvest'),
        '#url' => Url::fromRoute(
          'farm.quick.eggs',
          [],
          [
            'query' => [
              'assets' => $asset->id(),
              'destination' => Url::fromRouteMatch($this->routeMatch)->toString(),
            ],
          ],
        ),
        '#attributes' => [
          'class' => ['button', 'button--small'],
        ],
      ];
    }
    return $elements;
  }

}
