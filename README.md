# EventsDatas Events for Drupal 10

EventsDatas Events is a Drupal 10 module that allows Drupal websites to display events from the EventsDatas API.

The module is designed for publishers, local media, tourism websites, cultural platforms, city websites, community portals, and any Drupal project that needs to consume event data from EventsDatas.

## Features

- Events listing page
- Event detail page
- Drupal block integration
- Shortcode/filter support
- Admin configuration page
- Twig templates
- Dedicated CSS
- API key authentication
- Basic filtering support
- Drupal 10 compatible structure

## Requirements

- Drupal 10
- PHP 8.1 or higher
- An EventsDatas API key
- Access to the EventsDatas API

## Installation

### Manual installation

Copy the module folder into your Drupal project:

```text
/modules/custom/eventsdatas_events
```

The final structure should look like this:

```text
/modules/custom/eventsdatas_events/eventsdatas_events.info.yml
/modules/custom/eventsdatas_events/eventsdatas_events.module
/modules/custom/eventsdatas_events/eventsdatas_events.routing.yml
/modules/custom/eventsdatas_events/src
/modules/custom/eventsdatas_events/templates
/modules/custom/eventsdatas_events/css
```

Then enable the module from the Drupal administration interface:

```text
Administration → Extend
```

Search for:

```text
EventsDatas Events
```

Enable the module.

If you use Drush, you can also enable it with:

```bash
drush en eventsdatas_events
```

## Configuration

After enabling the module, go to:

```text
/admin/config/services/eventsdatas-events
```

Configure the module with:

- API key
- API base URL
- default display options
- default number of events to display

The default EventsDatas API endpoint is:

```text
https://api.eventsdatas.cloud/api/v1/events
```

Authentication is sent using the following HTTP header:

```text
X-API-Key
```

## Events listing page

The module provides a default events listing page:

```text
/eventsdatas/events
```

This page displays events retrieved from the EventsDatas API.

Example with filters:

```text
/eventsdatas/events?city=Paris
/eventsdatas/events?category=music
/eventsdatas/events?city=Paris&category=music
```

## Event detail page

The module provides an event detail page:

```text
/eventsdatas/event/{id}
```

Example:

```text
/eventsdatas/event/123
```

This page displays details for a single event retrieved from the EventsDatas API.

## Drupal block

The module provides a Drupal block that can be added from:

```text
Administration → Structure → Block layout
```

Block name:

```text
EventsDatas Events Block
```

The block can be placed in any Drupal region depending on your theme.

Typical use cases:

- homepage events section
- sidebar upcoming events
- tourism page events block
- city portal events list
- media article sidebar

## Shortcode / filter support

The module includes shortcode-like support to embed event lists inside content.

Basic usage:

```text
[eventsdatas_events]
```

Examples with parameters:

```text
[eventsdatas_events city="Paris"]
[eventsdatas_events category="music"]
[eventsdatas_events per_page="5"]
[eventsdatas_events city="Paris" category="music" per_page="6"]
```

Depending on your Drupal configuration, make sure the appropriate text format/filter is enabled for the content type where you want to use shortcodes.

## Supported filters

The module supports common EventsDatas API filters such as:

- `city`
- `category`
- `date_from`
- `date_to`
- `per_page`

Examples:

```text
/eventsdatas/events?city=Paris
/eventsdatas/events?category=exhibition
/eventsdatas/events?date_from=2026-06-01
/eventsdatas/events?date_from=2026-06-01&date_to=2026-06-30
```

## API usage

The module communicates with the EventsDatas API.

Default API endpoint:

```text
https://api.eventsdatas.cloud/api/v1/events
```

Authentication header:

```text
X-API-Key: YOUR_API_KEY
```

The API key is configured from the Drupal administration page and should not be hardcoded in templates or theme files.

## Theming

The module uses Drupal render arrays, Twig templates, and a dedicated CSS file.

Templates are located in:

```text
/templates
```

Stylesheets are located in:

```text
/css
```

You can override the provided Twig templates from your Drupal theme if needed.

Typical overrides may include:

- event card design
- event listing layout
- event detail layout
- date formatting
- venue display
- category badges
- call-to-action buttons

## Cache

The module is designed to work with Drupal rendering conventions.

Depending on the project, you may want to adjust cache behavior for:

- frequently updated events
- personalized displays
- filtered listings
- high traffic pages

Future versions may include more advanced cache configuration.

## Security

The module follows Drupal-oriented implementation principles:

- API key stored in Drupal configuration
- output rendered through Drupal/Twig mechanisms
- no direct SQL queries
- routes declared through Drupal routing
- templates separated from business logic
- no hidden tracking
- no third-party script injection by default

Administrators should only give module configuration access to trusted users.

## Recommended usage

This module is useful for:

- city websites
- cultural websites
- tourism offices
- local media
- event portals
- association websites
- festival websites
- destination marketing websites
- intranet or extranet event displays

## Roadmap

Possible future improvements:

- Drupal Views integration
- advanced block configuration
- better cache controls
- multilingual support
- map display
- calendar display
- ICS export links
- advanced pagination
- event search form
- category filter UI
- city filter UI
- improved template suggestions
- Composer package support
- automated tests

## Development

Clone the repository into your Drupal custom modules directory:

```bash
cd web/modules/custom
git clone https://github.com/elepetit/drupal-eventsdatas-events.git eventsdatas_events
```

Enable the module:

```bash
drush en eventsdatas_events
```

Clear Drupal cache:

```bash
drush cr
```

## Repository

GitHub repository:

```text
https://github.com/elepetit/drupal-eventsdatas-events
```

## License

GPL-2.0-or-later

This module is distributed under the GNU General Public License version 2 or later.

## About EventsDatas

EventsDatas is an event data platform that centralizes, enriches, and distributes event data through APIs.

It can be used for:

- cultural events
- festivals
- concerts
- exhibitions
- tourism events
- city event feeds
- local event calendars
- media and publishing platforms
- event data integrations

Website:

```text
https://eventsdatas.cloud
```

API domain:

```text
https://api.eventsdatas.cloud
```

## Support

For questions about the EventsDatas platform, API access, or integration support, please refer to the EventsDatas website.

