<?php

declare(strict_types=1);

namespace Drupal\farm_eggs\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\farm_eggs\EggHarvestWorkflow;

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

    // Available egg harvest workflow types.
    $workflows = array_reduce(
      EggHarvestWorkflow::cases(),
      function ($result, $workflow) {
        $result[$workflow->getId()] = $workflow;
        return $result;
      },
      [],
    );

    // Determine currently selected workflow.
    $selectedWorkflow = $config->get('require_quantities_per_egg_type');
    if (!in_array($selectedWorkflow, array_keys($workflows))) {
      $selectedWorkflow = EggHarvestWorkflow::default()->getId();
    }

    // Workflow selection field.
    $form['workflow'] = [
      '#type' => 'radios',
      '#title' => $this->t('Workflow'),
      '#options' => array_map(fn($workflow) => $workflow->getTitle(), $workflows),
      '#default_value' => $selectedWorkflow,
    ];

    // Add workflow options descriptions.
    foreach ($workflows as $id => $workflow) {
      /** @var array $form['workflow'][$id] */
      $form['workflow'][$id]['#description'] = $workflow->getDescription();
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->config('farm_eggs.settings')
      ->set('workflow', $form_state->getValue('workflow'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
