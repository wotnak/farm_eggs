<?php

namespace Drupal\farm_eggs\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldFormatter\BooleanFormatter;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

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
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'record_egg_harvest_button' => FALSE,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
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
  public function settingsSummary() {
    $summary = parent::settingsSummary();
    $summary[] = $this->getSetting('record_egg_harvest_button') ? $this->t('Include record egg harvest button') : $this->t('No record egg harvest button');
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {

    // Build labels in parent.
    $elements = parent::viewElements($items, $langcode);

    // Get the asset.
    $asset = $items->getEntity();

    $producesEggs = FALSE;
    if (
      !$items->isEmpty()
      && boolval($items->getValue()[0]['value'])
    ) {
      $producesEggs = TRUE;
    }

    // Add the record egg harvest button if configured.
    if (
      $this->getSetting('record_egg_harvest_button')
      && $producesEggs
    ) {

      // Append a "Record egg harvest" link.
      $options = [
        'query' => [
          'asset' => $asset->id(),
          'destination' => $asset->toUrl()->toString(),
        ],
      ];
      $elements[] = [
        '#type' => 'link',
        '#title' => $this->t('Record egg harvest'),
        '#url' => Url::fromRoute('farm.quick.eggs', [], $options),
        '#attributes' => [
          'class' => ['button', 'button--small'],
        ],
      ];
    }
    return $elements;
  }

}
