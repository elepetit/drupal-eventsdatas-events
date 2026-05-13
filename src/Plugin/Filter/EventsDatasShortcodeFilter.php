<?php

namespace Drupal\eventsdatas_events\Plugin\Filter;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\eventsdatas_events\Service\EventsDatasApiClient;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Converts [eventsdatas_events] shortcodes into event lists.
 *
 * @Filter(
 *   id = "eventsdatas_events_shortcode",
 *   title = @Translation("Shortcode EventsDatas Events"),
 *   description = @Translation("Permet d’utiliser [eventsdatas_events city=\"Paris\" category=\"concert\"]."),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_MARKUP_LANGUAGE
 * )
 */
final class EventsDatasShortcodeFilter extends FilterBase implements ContainerFactoryPluginInterface {

  public function __construct(array $configuration, $plugin_id, $plugin_definition, private readonly EventsDatasApiClient $apiClient) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): self {
    return new self($configuration, $plugin_id, $plugin_definition, $container->get('eventsdatas_events.api_client'));
  }

  public function process($text, $langcode): FilterProcessResult {
    $processed = preg_replace_callback('/\[eventsdatas_events([^\]]*)\]/', function (array $matches): string {
      $attrs = $this->parseAttributes($matches[1] ?? '');
      $result = $this->apiClient->fetchEvents($attrs);

      if (!$result['success']) {
        return '<div class="eventsdatas-empty">' . htmlspecialchars((string) $result['message'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</div>';
      }

      $events = is_array($result['events']) ? $result['events'] : [];
      if ($events === []) {
        return '<div class="eventsdatas-empty">Aucun événement publié pour le moment.</div>';
      }

      $perPage = $this->apiClient->getPerPage(isset($attrs['per_page']) ? (int) $attrs['per_page'] : NULL);
      $events = array_slice($events, 0, $perPage);

      return $this->renderEvents($events);
    }, $text);

    $result = new FilterProcessResult($processed ?? $text);
    $result->setAttachments(['library' => ['eventsdatas_events/frontend']]);
    $result->setCacheMaxAge(300);
    return $result;
  }

  private function parseAttributes(string $raw): array {
    $attrs = [];
    preg_match_all('/(city|category|date_from|date_to|api_limit|per_page)\s*=\s*("([^"]*)"|\'([^\']*)\'|([^\s]+))/', $raw, $matches, PREG_SET_ORDER);

    foreach ($matches as $match) {
      $attrs[$match[1]] = trim((string) ($match[3] ?: ($match[4] ?: $match[5])));
    }

    return $attrs;
  }

  private function renderEvents(array $events): string {
    $html = '<div class="eventsdatas-wrapper"><div class="eventsdatas-grid">';

    foreach ($events as $event) {
      if (!is_array($event)) {
        continue;
      }
      $links = $this->apiClient->normalizeEventLinks($event);
      $title = htmlspecialchars((string) ($event['title'] ?? 'Événement sans titre'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
      $date = !empty($event['start_datetime']) ? date('d/m/Y H:i', strtotime((string) $event['start_datetime'])) : 'Date non renseignée';
      $venue = htmlspecialchars((string) ($event['venue_name'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
      $description = htmlspecialchars((string) ($event['short_description'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
      $detailUrl = '/eventsdatas/event/' . rawurlencode((string) ($event['id'] ?? ''));

      $html .= '<article class="eventsdatas-card">';
      $html .= '<div class="eventsdatas-image-wrap">';
      if ($links['image_url'] !== '') {
        $html .= '<img class="eventsdatas-image" src="' . htmlspecialchars($links['image_url'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '" alt="' . $title . '">';
      }
      else {
        $html .= '<div class="eventsdatas-placeholder">Aucun visuel</div>';
      }
      $html .= '</div><div class="eventsdatas-body"><h3 class="eventsdatas-title">' . $title . '</h3>';
      $html .= '<div class="eventsdatas-meta-list"><div class="eventsdatas-meta-item"><span class="eventsdatas-meta-icon">📅</span><span>' . htmlspecialchars($date, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</span></div>';
      if ($venue !== '') {
        $html .= '<div class="eventsdatas-meta-item"><span class="eventsdatas-meta-icon">📍</span><span>' . $venue . '</span></div>';
      }
      $html .= '</div>';
      if ($description !== '') {
        $html .= '<div class="eventsdatas-text">' . $description . '</div>';
      }
      $html .= '<div class="eventsdatas-footer"><a class="eventsdatas-link" href="' . htmlspecialchars($detailUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">Détails</a>';
      if ($links['booking_url'] !== '') {
        $html .= '<a class="eventsdatas-link eventsdatas-link-secondary" href="' . htmlspecialchars($links['booking_url'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '" target="_blank" rel="noopener noreferrer">Réserver</a>';
      }
      $html .= '</div></div></article>';
    }

    $html .= '</div></div>';
    return $html;
  }

}
