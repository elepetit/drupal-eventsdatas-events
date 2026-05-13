<?php

namespace Drupal\eventsdatas_events\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Pager\PagerManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use GuzzleHttp\ClientInterface;

class EventsDatasController extends ControllerBase
{
    protected ClientInterface $httpClient;
    protected RequestStack $requestStack;
    protected PagerManagerInterface $pagerManager;

    public function __construct(
        ClientInterface $http_client,
        RequestStack $request_stack,
        PagerManagerInterface $pager_manager
    ) {
        $this->httpClient = $http_client;
        $this->requestStack = $request_stack;
        $this->pagerManager = $pager_manager;
    }

    public static function create(ContainerInterface $container)
    {
        return new static(
            $container->get('http_client'),
            $container->get('request_stack'),
            $container->get('pager.manager')
        );
    }

    public function eventsList()
    {
        $config = $this->config('eventsdatas_events.settings');

        $apiKey = $config->get('api_key');
        $apiBaseUrl = rtrim($config->get('api_base_url'), '/');

        $request = $this->requestStack->getCurrentRequest();

        $page = (int) $request->query->get('page', 0);
        $perPage = 10;
        $currentPage = $page + 1;

        try {
            $response = $this->httpClient->request('GET', $apiBaseUrl . '/events', [
                'headers' => [
                    'X-API-Key' => $apiKey,
                    'Accept' => 'application/json',
                ],
                'query' => [
                    'page' => $currentPage,
                    'per_page' => $perPage,
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), TRUE);

            $events = $data['data'] ?? [];
            $total = $data['total'] ?? count($events);

            $this->pagerManager->createPager($total, $perPage);

            return [
                '#theme' => 'eventsdatas_events_list',
                '#events' => $events,
                '#pager' => [
                    '#type' => 'pager',
                ],
                '#cache' => [
                    'max-age' => 0,
                ],
            ];
        }
        catch (\Exception $e) {
            return [
                '#markup' => $this->t('Unable to load events: @message', [
                    '@message' => $e->getMessage(),
                ]),
            ];
        }
    }
}