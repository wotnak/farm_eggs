<?php

declare(strict_types=1);

namespace Drupal\farm_eggs\Plugin\QuickForm;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\farm_eggs\EggsServiceInterface;
use Drupal\farm_quick\Plugin\QuickForm\QuickFormBase;
use Drupal\farm_quick\Traits\QuickLogTrait;
use Psr\Container\ContainerInterface;

/**
 * Eggs harvest quick form.
 *
 * @QuickForm(
 *   id = "eggs",
 *   label = @Translation("Eggs"),
 *   description = @Translation("Record an egg harvest."),
 *   helpText = @Translation("Use this form to record an egg harvest. A harvest log will be created with standard details filled in."),
 *   permissions = {
 *     "create farm_harvest log entities",
 *   }
 * )
 */
class Eggs extends QuickFormBase {

  use QuickLogTrait;

  /**
   * The entity type manager service.
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * The eggs service.
   */
  protected EggsServiceInterface $eggsService;

  /**
   * The request timestamp.
   */
  protected int $requestTimestamp;

  /**
   * Determines if quantities per egg type should be required.
   */
  protected bool $requireQuantitiesPerEggType;

  /**
   * Constructs a Eggs object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\farm_eggs\EggsServiceInterface $eggs_service
   *   The eggs service.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   */
  public function __construct(
    array $configuration,
    string $plugin_id,
    array $plugin_definition,
    MessengerInterface $messenger,
    TranslationInterface $string_translation,
    EntityTypeManagerInterface $entity_type_manager,
    EggsServiceInterface $eggs_service,
    TimeInterface $time,
    ConfigFactoryInterface $config_factory,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $messenger);
    $this->stringTranslation = $string_translation;
    $this->entityTypeManager = $entity_type_manager;
    $this->eggsService = $eggs_service;
    $this->requestTimestamp = $time->getRequestTime();
    $this->requireQuantitiesPerEggType = (bool) $config_factory->get('farm_eggs.settings')->get('require_quantities_per_egg_type');
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
      $container->get('entity_type.manager'),
      $container->get('farm_eggs.eggs_service'),
      $container->get('datetime.time'),
      $container->get('config.factory'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {

    // Harvest timestamp.
    $form['timestamp'] = [
      '#type' => 'datetime',
      '#title' => $this->t('Timestamp'),
      '#default_value' => DrupalDateTime::createFromTimestamp($this->requestTimestamp),
      '#required' => TRUE,
    ];

    // Quantity.
    if (!$this->requireQuantitiesPerEggType) {
      $form['quantity'] = [
        '#type' => 'number',
        '#title' => $this->t('Quantity'),
        '#required' => TRUE,
        '#min' => 0,
        '#step' => 1,
      ];
    }

    // Load active assets with the "produces_eggs" field checked.
    $eggProducers = $this->entityTypeManager->getStorage('asset')->loadByProperties([
      'status' => 'active',
      'produces_eggs' => TRUE,
    ]);
    $assetsOptions = array_map(fn($asset) => $asset->toLink()->toString(), $eggProducers);

    // If there are asset options, add checkboxes.
    if (!empty($assetsOptions)) {
      $form['assets'] = [
        '#type' => 'checkboxes',
        '#title' => $this->t('Group/animal'),
        '#description' => $this->t('Select the group/animal that these eggs came from. To add groups/animals to this list, edit their record and check the "Produces eggs" checkbox.'),
        '#options' => $assetsOptions,
      ];

      // If there is only one option, select it by default.
      if (count($assetsOptions) === 1) {
        $form['assets']['#default_value'] = array_keys($assetsOptions);
      }
    }
    // Otherwise, show some text about adding groups/animals.
    else {
      $form['assets'] = [
        '#type' => 'html_tag',
        '#tag' => 'p',
        '#value' => $this->t('If you would like to associate this egg harvest log with a group/animal asset, edit their record and check the "Produces eggs" checkbox. Then you will be able to select them here.'),
      ];
    }

    // Quantity per egg type.
    $eggTypes = $this->eggsService->getEggTypes();
    if ($this->requireQuantitiesPerEggType && empty($eggTypes)) {
      $form['egg_types'] = [
        '#type' => 'html_tag',
        '#tag' => 'p',
        '#value' => $this->t("If you would like to set quantity for this egg harvest log add some 'Egg types' taxonomy terms or disable 'Require quantities per egg type' settings option."),
      ];
    }
    if (!empty($eggTypes)) {
      $form['egg_types'] = [
        '#type' => 'fieldset',
        '#title' => $this->t('Egg types'),
        '#description' => $this->t('Optionally provide quantities per egg type.'),
        '#description_display' => 'before',
      ];
      foreach ($eggTypes as $type) {
        $form['egg_types'][$type->id() . '_quantity'] = [
          '#type' => 'number',
          '#title' => $type->label(),
          '#description' => $type->getDescription(),
          '#min' => 0,
          '#step' => 1,
        ];
      }
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if ($this->requireQuantitiesPerEggType) {
      $hasQuantity = FALSE;
      foreach($form_state->getValues() as $key => $value) {
        if (
          strpos($key, '_quantity') === FALSE
          || empty($value)
        ) {
          continue;
        }
        $hasQuantity = TRUE;
      }
      if (!$hasQuantity) {
        $form_state->setErrorByName('egg_types', $this->t('At least one quantity value is required.'));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {

    // Get selected assets ids.
    $assets = array_keys(array_filter($form_state->getValue('assets')));

    // Prepare quantities.
    $quantities = [];
    $totalQuantity = 0;
    if (!$this->requireQuantitiesPerEggType) {
      $quantities = [
        [
          'measure' => 'count',
          'value' => $form_state->getValue('quantity'),
          'units' => (string) $this->t('egg(s)'),
        ],
      ];
      $totalQuantity = intval($form_state->getValue('quantity'));
    }
    foreach($form_state->getValues() as $key => $value) {
      if (
        strpos($key, '_quantity') === FALSE
        || empty($value)
      ) {
        continue;
      }
      $quantities[] = [
        'measure' => 'count',
        'value' => $value,
        'units' => (string) $this->t('egg(s)'),
        'label' => $form['egg_types'][$key]['#title'],
      ];
      $totalQuantity += (int) $value;
    }
    if (count($quantities) > 1 && !$this->requireQuantitiesPerEggType) {
      $quantities[0]['label'] = $this->t('Total');
    }

    // Create a new egg harvest log.
    $this->createLog([
      'type' => 'harvest',
      'timestamp' => $form_state->getValue('timestamp')->getTimestamp(),
      'name' => $this->eggsService->getEggHarvestLogName($totalQuantity),
      'asset' => $assets,
      'quantity' => $quantities,
      'category' => $this->eggsService->getEggsLogCategory(),
    ]);

  }

}
