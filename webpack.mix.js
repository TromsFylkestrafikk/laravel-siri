const mix = require('laravel-mix');

/**
 * Compile assets.
 */

mix.js('resources/js/app.js', 'public/siri/js')
    .vue()
    .extract([
        'axios',
        'vue',
    ]);
