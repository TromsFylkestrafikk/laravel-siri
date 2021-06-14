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
artisan vendor:publish --provider=TromsFylkestrafikk\\Siri\\SiriServiceProvider
```

## Usage

The following artisan commands manages SIRI subscriptions:

- `siri:subscribe` – Create new SIRI subscription
- `siri:list` – Show current SIRI subscriptions and status.
- `siri:terminate` – Remove SIRI subscription
