# EventsDatas Events pour Drupal 10

Module Drupal 10 permettant d’afficher les événements EventsDatas via l’API.

## Fonctionnalités

- Page automatique : `/eventsdatas/events`
- Page détail : `/eventsdatas/event/{id}`
- Bloc Drupal : `EventsDatas - Liste d’événements`
- Shortcode via filtre de texte : `[eventsdatas_events city="Paris" category="concert" api_limit="60" per_page="12"]`
- Page de configuration admin : `/admin/config/services/eventsdatas-events`
- Templates Twig surchargeables
- CSS dédié

## Installation

1. Copier le dossier `eventsdatas_events` dans `web/modules/custom/`.
2. Activer le module depuis l’administration Drupal ou avec Drush.
3. Aller dans `Configuration > Services > EventsDatas Events`.
4. Renseigner :
   - URL API : `https://api.eventsdatas.cloud/api/v1/events`
   - Clé API EventsDatas
   - filtres par défaut si besoin

## Shortcode

Pour utiliser le shortcode, activez le filtre `Shortcode EventsDatas Events` dans le format de texte concerné.

Exemples :

```text
[eventsdatas_events]
[eventsdatas_events city="Paris"]
[eventsdatas_events category="concert" api_limit="60" per_page="12"]
[eventsdatas_events city="Beauvais" per_page="6"]
```

## Bloc

Ajouter le bloc depuis `Structure > Mise en page des blocs`.
Chaque bloc peut avoir ses propres filtres : ville, catégorie, dates, limite API et nombre affiché.

## Templates

Les templates sont dans `templates/` :

- `eventsdatas-events-list.html.twig`
- `eventsdatas-event-card.html.twig`
- `eventsdatas-event-detail.html.twig`

Ils peuvent être copiés dans le thème Drupal pour surcharge.
