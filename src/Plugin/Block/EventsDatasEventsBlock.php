<?php

namespace Drupal\eventsdatas_events\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\eventsdatas_events\Service\EventsDatasApiClient;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

/**
 * Provides an EventsDatas events list block.
 *
 * @Block(
 *   id = "eventsdatas_events_block",
 *   admin_label = @Translation("EventsDatas - Liste d’événements"),
 *   category = @Translation("EventsDatas")
 * )
 */
final class EventsDatasEventsBlock extends BlockBase implements ContainerFactoryPluginInterface {

  public function __construct(array $configuration, $plugin_id, $plugin_definition, private readonly EventsDatasApiClient $apiClient) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): self {
    return new self($configuration, $plugin_id, $plugin_definition, $container->get('eventsdatas_events.api_client'));
  }

  public function defaultConfiguration(): array {
    return [
      'city' => '',
      'category' => '',
      'date_from' => '',
      'date_to' => '',
      'api_limit' => 12,
      'per_page' => 12,
    ] + parent::defaultConfiguration();
  }

  public function blockForm($form, FormStateInterface $form_state): array {
    foreach (['city' => 'Ville', 'category' => 'Catégorie', 'date_from' => 'Date de début', 'date_to' => 'Date de fin'] as $key => $label) {
      $form[$key] = [
        '#type' => 'textfield',
        '#title' => $this->t($label),
        '#default_value' => $this->configuration[$key] ?? '',
      ];
    }

    $form['api_limit'] = [
      '#type' => 'number',
      '#title' => $this->t('Nombre total récupéré API'),
      '#default_value' => $this->configuration['api_limit'] ?? 12,
      '#min' => 1,
      '#max' => 500,
    ];

    $form['per_page'] = [
      '#type' => 'number',
      '#title' => $this->t('Nombre affiché'),
      '#default_value' => $this->configuration['per_page'] ?? 12,
      '#min' => 1,
      '#max' => 100,
    ];

    return $form;
  }

  public function blockSubmit($form, FormStateInterface $form_state): void {
    foreach (['city', 'category', 'date_from', 'date_to', 'api_limit', 'per_page'] as $key) {
      $this->configuration[$key] = $form_state->getValue($key);
    }
  }

  public function build(): array {
    $params = [];
    foreach (['city', 'category', 'date_from', 'date_to', 'api_limit'] as $key) {
      $value = trim((string) ($this->configuration[$key] ?? ''));
      if ($value !== '') {
        $params[$key] = $value;
      }
    }

    $result = $this->apiClient->fetchEvents($params);
    if (!$result['success']) {
      return [
        '#markup' => '<div class="eventsdatas-empty">' . htmlspecialchars((string) $result['message'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</div>',
        '#attached' => ['library' => ['eventsdatas_events/frontend']],
        '#cache' => ['max-age' => 60],
      ];
    }

    $events = is_array($result['events']) ? $result['events'] : [];
    $perPage = $this->apiClient->getPerPage((int) ($this->configuration['per_page'] ?? 12));
    $events = array_slice($events, 0, $perPage);

    return [
      '#theme' => 'eventsdatas_events_list',
      '#events' => $events,
      '#pagination' => [],
      '#attached' => ['library' => ['eventsdatas_events/frontend']],
      '#cache' => ['max-age' => 300],
    ];
  }

}
