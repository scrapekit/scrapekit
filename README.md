# Work In Progress

Top secret project.

## Installation

```bash
composer require scrapekit/scrapekit
```

## Browser Automation

```php

    $tab = chrome()->tabs()->new();

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
