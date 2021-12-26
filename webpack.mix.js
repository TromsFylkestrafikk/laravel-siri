const mix = require('laravel-mix');

/**
 * Compile assets.
 */

mix.js('vendor/tromsfylkestrafikk/laravel-siri/resources/js/app.js', 'public/siri/js')
    .vue()
    .extract([
        'axios',
        'vue',
    ]);
