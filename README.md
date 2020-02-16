# Work In Progress

Top secret project.

## Installation

```bash
composer require scrapekit/scrapekit
```

## Browser Automation

ScrapeKit Browser uses *Chrome DevTools Protocol*.

```php

    $tab = chrome('http://localhost:9222')->tabs()->new();

    $tab->geo( 45, 21 )
        ->navigate( 'https://mylocation.org/' )
        ->find( '[aria-controls="geo-div"]' )->click()
        ->tab()
        ->find( '#geo-test' )->click()
        ->tab()
        ->pause( 100 )
        ->geo()
        ->navigate( 'https://mylocation.org/' )
        ->find( '[aria-controls="geo-div"]' )->click()
        ->tab()
        ->find( '#geo-test' )->click();

    $tab->pause( 100 );

    $lat = $tab->find( '#geo-latitude' )->text();
    $lng = $tab->find( '#geo-longitude' )->text();

    $tab->close();

```

## HTTP Automation

TODO

## DomQL

TODO

## Contributing

TODO
