<?php

declare(strict_types=1);

namespace Drupal\farm_eggs\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * The config form for the farm_eggs module.
 */
class FarmEggsSettingsForm extends ConfigFormBase {

  /**
   * {@inheritDoc}
   */
  protected function getEditableConfigNames(): array {
    return [
      'farm_eggs.settings',
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function getFormId(): string {
    return 'farm_eggs_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = $this->config('farm_eggs.settings');
    $form['require_quantities_per_egg_type'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Require quantities per egg type'),
      '#description' => $this->t('By default when creating egg harvest log total egg quantity is required and quantities per egg type are optional. After enabling this option quantities per egg type will be required and total egg count will be automatically calculated based on per egg type values.'),
      '#default_value' => $config->get('require_quantities_per_egg_type'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->config('farm_eggs.settings')
      ->set('require_quantities_per_egg_type', $form_state->getValue('require_quantities_per_egg_type'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
