# Laravel SIRI

Add SIRI VM and ET handling to your Laravel project.

VM data is sent to a pub/sub server, whereas ET data is written
directly to activated DB tables (see
[tromsfylkestrafikk/laravel-netex](https://github.com/TromsFylkestrafikk/laravel-netex).)


## Install

This package is not registered on packagist.org (and may never be), so
the repository will have to be manually added to your laravel
project. Add something like this in composer.json:
```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/TromsFylkestrafikk/christmas-tree-parser"
        },
        {
            "type": "vcs",
            "url": "https://github.com/TromsFylkestrafikk/laravel-siri"
        }
    ]
}
```
then run
```shell
composer require tromsfylkestrafikk/laravel-siri
```

Publish the required configuration, then customize it in `config/siri.php`:
```shell
artisan vendor:publish --tag=siri-config
```

This may include using the following environment entries in .env:
```
SIRI_DISK=local
SIRI_SUB_HEARTBEAT_INTERVAL=PT1M
SIRI_SUB_REQUESTOR_REF="Unicorn and rainbows"
```

### Emulate published siri updates.

This tool uses a simple upload form to emulate post request from siri
services.  This uses Vue and Axios to perform the actual request, but
this has to be mix'ed using Laravel Mix.

```shell
npx mix --mix-config ./vendor/tromsfylkestrafikk/laravel-siri/webpack.mix.js
```

## Usage

The following artisan commands manages SIRI subscriptions:

- `siri:subscribe` – Create new SIRI subscription
- `siri:list` – Show current SIRI subscriptions and status.
- `siri:terminate` – Remove SIRI subscription

## Development

Add the following lines in your laravel installation's webpack.mix.js
during development of this package:

```javascript
/**
 * TromsFylkestrafikk/laravel-siri stuff
 */
mix.js('vendor/tromsfylkestrafikk/laravel-siri/resources/js/app.js', 'public/siri/js')
    .vue()
    .extract([
        'axios',
        'vue',
    ]);
```
