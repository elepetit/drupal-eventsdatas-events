<?php

namespace Drupal\eventsdatas_events\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use GuzzleHttp\ClientInterface;
use Psr\Log\LoggerInterface;

/**
 * Client API EventsDatas.
 */
final class EventsDatasApiClient {

  public function __construct(
    private readonly ClientInterface $httpClient,
    private readonly ConfigFactoryInterface $configFactory,
    private readonly LoggerInterface $logger,
  ) {}

  public function fetchEvents(array $params = []): array {
    $config = $this->configFactory->get('eventsdatas_events.settings');
    $apiUrl = trim((string) $config->get('api_url')) ?: 'https://api.eventsdatas.cloud/api/v1/events';
    $apiKey = trim((string) $config->get('api_key'));

    if ($apiUrl === '' || $apiKey === '') {
      return $this->error('Configuration EventsDatas incomplète.', 'events');
    }

    $apiLimit = $this->positiveInt($params['api_limit'] ?? $config->get('api_limit'), 100, 500);

    $query = [
      'per_page' => $apiLimit,
      'page' => 1,
    ];

    foreach (['city', 'category', 'date_from', 'date_to'] as $filter) {
      $value = trim((string) ($params[$filter] ?? $config->get($filter) ?? ''));
      if ($value !== '') {
        $query[$filter] = $value;
      }
    }

    try {
      $response = $this->httpClient->request('GET', $apiUrl, [
        'timeout' => 15,
        'allow_redirects' => ['max' => 3],
        'headers' => [
          'X-API-Key' => $apiKey,
          'Accept' => 'application/json',
        ],
        'query' => $query,
      ]);

      $statusCode = $response->getStatusCode();
      $json = json_decode((string) $response->getBody(), TRUE);

      if ($statusCode !== 200 || !is_array($json)) {
        return $this->error('Réponse API invalide.', 'events');
      }

      return [
        'success' => TRUE,
        'message' => NULL,
        'events' => is_array($json['data'] ?? NULL) ? $json['data'] : [],
      ];
    }
    catch (\Throwable $exception) {
      $this->logger->error('Erreur API EventsDatas : @message', ['@message' => $exception->getMessage()]);
      return $this->error($exception->getMessage(), 'events');
    }
  }

  public function fetchEvent(string $eventId): array {
    $config = $this->configFactory->get('eventsdatas_events.settings');
    $apiUrl = rtrim(trim((string) $config->get('api_url')), '/') ?: 'https://api.eventsdatas.cloud/api/v1/events';
    $apiKey = trim((string) $config->get('api_key'));
    $eventId = trim($eventId);

    if ($apiUrl === '' || $apiKey === '' || $eventId === '') {
      return $this->error('Configuration EventsDatas incomplète.', 'event');
    }

    try {
      $response = $this->httpClient->request('GET', $apiUrl . '/' . rawurlencode($eventId), [
        'timeout' => 15,
        'allow_redirects' => ['max' => 3],
        'headers' => [
          'X-API-Key' => $apiKey,
          'Accept' => 'application/json',
        ],
      ]);

      $statusCode = $response->getStatusCode();
      $json = json_decode((string) $response->getBody(), TRUE);

      if ($statusCode !== 200 || !is_array($json)) {
        return $this->error('Événement introuvable ou réponse API invalide.', 'event');
      }

      return [
        'success' => TRUE,
        'message' => NULL,
        'event' => is_array($json['data'] ?? NULL) ? $json['data'] : NULL,
      ];
    }
    catch (\Throwable $exception) {
      $this->logger->error('Erreur API EventsDatas détail : @message', ['@message' => $exception->getMessage()]);
      return $this->error($exception->getMessage(), 'event');
    }
  }

  public function normalizeEventLinks(array $event): array {
    $rawItem = is_array($event['extra_data']['raw_item'] ?? NULL) ? $event['extra_data']['raw_item'] : [];

    return [
      'image_url' => (string) ($event['image_url'] ?? $rawItem['image_url'] ?? ''),
      'website_url' => (string) ($event['website_url'] ?? $rawItem['website_url'] ?? ''),
      'booking_url' => (string) ($event['booking_url'] ?? $rawItem['booking_url'] ?? ''),
    ];
  }

  public function getPerPage(?int $override = NULL): int {
    $config = $this->configFactory->get('eventsdatas_events.settings');
    return $this->positiveInt($override ?? $config->get('per_page'), 10, 100);
  }

  public function showLocation(): bool {
    return (bool) $this->configFactory->get('eventsdatas_events.settings')->get('show_location');
  }

  private function positiveInt(mixed $value, int $default, int $max): int {
    $value = (int) $value;
    if ($value < 1) {
      return $default;
    }
    return min($value, $max);
  }

  private function error(string $message, string $type): array {
    return [
      'success' => FALSE,
      'message' => $message,
      $type => $type === 'events' ? [] : NULL,
    ];
  }

}
