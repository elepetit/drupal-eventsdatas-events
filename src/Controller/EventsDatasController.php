<?php

namespace Drupal\eventsdatas_events\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Pager\PagerManagerInterface;
use Drupal\eventsdatas_events\Service\EventsDatasApiClient;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

final class EventsDatasController extends ControllerBase {

    public function __construct(
        private readonly EventsDatasApiClient $apiClient,
        private readonly PagerManagerInterface $pagerManager,
        private readonly RequestStack $requestStack,
    ) {}

    public static function create(ContainerInterface $container): self {
        return new self(
            $container->get('eventsdatas_events.api_client'),
            $container->get('pager.manager'),
            $container->get('request_stack'),
        );
    }

    public function list(): array {
        $request = $this->requestStack->getCurrentRequest();

        $params = [];

        foreach (['city', 'category', 'date_from', 'date_to'] as $filter) {
            $value = trim((string) $request->query->get($filter, ''));
            if ($value !== '') {
                $params[$filter] = $value;
            }
        }

        $result = $this->apiClient->fetchEvents($params);

        if (!$result['success']) {
            return $this->emptyBuild((string) $result['message']);
        }

        $events = is_array($result['events'] ?? NULL) ? $result['events'] : [];

        if ($events === []) {
            return $this->emptyBuild('Aucun événement publié pour le moment.');
        }

        $perPage = $this->apiClient->getPerPage();
        $total = count($events);

        $pager = $this->pagerManager->createPager($total, $perPage);
        $currentPage = $pager->getCurrentPage();

        $events = array_slice($events, $currentPage * $perPage, $perPage);

        return [
            '#theme' => 'eventsdatas_events_list',
            '#events' => $events,
            '#pager' => [
                '#type' => 'pager',
            ],
            '#pagination' => [
                'total' => $total,
                'per_page' => $perPage,
            ],
            '#attached' => [
                'library' => [
                    'eventsdatas_events/frontend',
                ],
            ],
            '#cache' => [
                'max-age' => 300,
            ],
        ];
    }

    public function detail(string $event_id): array {
        $result = $this->apiClient->fetchEvent($event_id);

        if (!$result['success'] || empty($result['event'])) {
            return $this->emptyBuild((string) $result['message']);
        }

        $event = $result['event'];
        $links = $this->apiClient->normalizeEventLinks($event);

        return [
            '#theme' => 'eventsdatas_event_detail',
            '#event' => $event,
            '#image_url' => $links['image_url'],
            '#website_url' => $links['website_url'],
            '#booking_url' => $links['booking_url'],
            '#show_location' => $this->apiClient->showLocation(),
            '#attached' => [
                'library' => [
                    'eventsdatas_events/frontend',
                ],
            ],
            '#cache' => [
                'max-age' => 300,
            ],
        ];
    }

    public function detailTitle(string $event_id): string {
        $result = $this->apiClient->fetchEvent($event_id);

        if ($result['success'] && !empty($result['event']['title'])) {
            return (string) $result['event']['title'];
        }

        return 'Événement';
    }

    private function emptyBuild(string $message): array {
        return [
            '#markup' => '<div class="eventsdatas-empty">' . htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</div>',
            '#attached' => [
                'library' => [
                    'eventsdatas_events/frontend',
                ],
            ],
            '#cache' => [
                'max-age' => 60,
            ],
        ];
    }

}