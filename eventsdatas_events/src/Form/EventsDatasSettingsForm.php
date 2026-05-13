<?php

namespace Drupal\eventsdatas_events\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

final class EventsDatasSettingsForm extends ConfigFormBase {

  public function getFormId(): string {
    return 'eventsdatas_events_settings_form';
  }

  protected function getEditableConfigNames(): array {
    return ['eventsdatas_events.settings'];
  }

  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = $this->config('eventsdatas_events.settings');

    $form['api_url'] = [
      '#type' => 'url',
      '#title' => $this->t('URL API'),
      '#default_value' => $config->get('api_url') ?: 'https://api.eventsdatas.cloud/api/v1/events',
      '#required' => TRUE,
    ];

    $form['api_key'] = [
      '#type' => 'password',
      '#title' => $this->t('Clé API'),
      '#description' => $this->t('Laissez vide pour conserver la clé existante.'),
    ];

    if ($config->get('api_key')) {
      $form['api_key_current'] = [
        '#markup' => '<p><strong>' . $this->t('Clé API actuellement enregistrée.') . '</strong></p>',
      ];
    }

    $form['api_limit'] = [
      '#type' => 'number',
      '#title' => $this->t('Nombre total récupéré API'),
      '#default_value' => $config->get('api_limit') ?: 100,
      '#min' => 1,
      '#max' => 500,
      '#required' => TRUE,
    ];

    $form['per_page'] = [
      '#type' => 'number',
      '#title' => $this->t('Nombre d’événements par page'),
      '#default_value' => $config->get('per_page') ?: 10,
      '#min' => 1,
      '#max' => 100,
      '#required' => TRUE,
    ];

    $form['filters'] = [
      '#type' => 'details',
      '#title' => $this->t('Filtres par défaut'),
      '#open' => TRUE,
    ];

    foreach (['city' => 'Ville', 'category' => 'Catégorie', 'date_from' => 'Date de début', 'date_to' => 'Date de fin'] as $key => $label) {
      $form['filters'][$key] = [
        '#type' => 'textfield',
        '#title' => $this->t($label),
        '#default_value' => $config->get($key) ?: '',
      ];
    }

    $form['show_location'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Afficher le lieu'),
      '#default_value' => $config->get('show_location') ?? TRUE,
    ];

    $form['usage'] = [
      '#type' => 'details',
      '#title' => $this->t('Utilisation'),
      '#open' => TRUE,
      '#markup' => '<p><strong>Page :</strong> <code>/eventsdatas/events</code></p>' .
      '<p><strong>Shortcode Drupal :</strong> <code>[eventsdatas_events city="Paris" category="concert" api_limit="60" per_page="12"]</code></p>' .
      '<p><strong>Bloc :</strong> ajoutez le bloc “EventsDatas - Liste d’événements” depuis la mise en page des blocs.</p>',
    ];

    return parent::buildForm($form, $form_state);
  }

  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $config = $this->config('eventsdatas_events.settings');
    $config
      ->set('api_url', trim((string) $form_state->getValue('api_url')))
      ->set('api_limit', (int) $form_state->getValue('api_limit'))
      ->set('per_page', (int) $form_state->getValue('per_page'))
      ->set('city', trim((string) $form_state->getValue('city')))
      ->set('category', trim((string) $form_state->getValue('category')))
      ->set('date_from', trim((string) $form_state->getValue('date_from')))
      ->set('date_to', trim((string) $form_state->getValue('date_to')))
      ->set('show_location', (bool) $form_state->getValue('show_location'));

    $apiKey = trim((string) $form_state->getValue('api_key'));
    if ($apiKey !== '') {
      $config->set('api_key', $apiKey);
    }

    $config->save();
    parent::submitForm($form, $form_state);
  }

}
