# EventsDatas Events for Drupal 10

EventsDatas Events is a Drupal 10 module that allows you to display events from the EventsDatas API on your Drupal website.

The module provides:
- events listing pages
- event detail pages
- Drupal blocks
- shortcode/filter support
- Twig templates
- admin configuration pages

---

# Features

## Events Listing

Displays a list of events retrieved from the EventsDatas API.

Default route:

/eventsdatas/events

---

## Event Detail Page

Displays detailed information for a single event.

Default route:

/eventsdatas/event/{id}

---

## Drupal Block

The module includes a configurable Drupal block that can be added from:

Administration → Structure → Block layout

Block name:

EventsDatas Events Block

---

## Shortcode / Filter Support

The module supports shortcode-like integration for embedding event lists inside content.

Example:

[eventsdatas_events]

Optional parameters:

[eventsdatas_events city="Paris"]

[eventsdatas_events category="music"]

[eventsdatas_events per_page="5"]

---

# Requirements

- Drupal 10
- PHP 8.1 or higher
- An EventsDatas API key

---

# Installation

## Manual Installation

Copy the module into:

/modules/custom/eventsdatas_events

Then enable it:

```bash
drush en eventsdatas_events