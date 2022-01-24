# Laravel SIRI

Add SIRI consumer service for SX, ET and VM channels.

This package sets up SIRI subscriptions for the Estimated Timetable
(ET), Vehicle Monitoring (VM) and Situation Exchange (SX) service
channels. Incoming channel data will be parsed according to channel
specific schemas and handed over to other packages or main
installation as events.

## Available events

The following events are dispatched during the incoming channel data
cycle.

- `\TromsFylkestrafikk\Siri\Events\ChannelSchema`: The schema used to
  map data from XML to array.  Use this modify what elements to harvest.
- `\TromsFylkestrafikk\Siri\Events\EtJourney`: A parsed
  `EstimatedJourneyVersionFrame` is available with additional sibling
  elements.
- `\TromsFylkestrafikk\Siri\Events\EtJourneys`: An array of the same
  item as above.
- `\TromsFylkestrafikk\Siri\Events\VmActivity`: A parsed
  `VehicleActivity` item is available, with additional sibling elements.
- `\TromsFylkestrafikk\Siri\Events\VmActivities`: An array of above
  mentioned items.
- `\TromsFylkestrafikk\Siri\Events\SxPtSituation`: A parsed
  `PtSituationElement` is available with additional generic channel
  data.
- `\TromsFylkestrafikk\Siri\Events\SxRoadSituation`: A parsed
  `RoadSituationElement` item is available.
- `\TromsFylkestrafikk\Siri\Events\SxSituations`: An array of
  situations are available.
  
## Schema and mapped data

Incoming XMLs are parsed using our own ChristmasTreeParser XML parser.
This uses a combination of XMLReader an SimpleXmlElement to extract
XML data, to cope with really large dumps.

The 'meat' of channel data are emitted using events, but for really
large xml's the data are split up in chunks. The chunk size can be
configured per siri channel in `config/siri.php`.

The schema used to harvest channel data must map the exact element
name and path in order to retrieve the value from the xml, but the
case style for the destination keys can be configured using the
`xml_element_case_style` configuration.

Data within the schema definition are provided exactly as retrieved,
but some surrounding elements are not.

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
