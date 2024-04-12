const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel applications. By default, we are compiling the CSS
 | file for the application as well as bundling up all the JS files.
 |
 */
// mix.setResourceRoot("../");
// mix.sass('resources/sass/app.scss', 'public/css');
mix.copy('node_modules/font-awesome/css/font-awesome.min.css', 'public/css'); mix.copy('node_modules/font-awesome/fonts/*', 'public/fonts');
mix.js('resources/js/app.js', 'public/js').postCss('resources/css/app.css', 'public/css', [
    require('tailwindcss'),
    require('autoprefixer'),
]);
mix.js('resources/js/main.js', 'public/js').postCss('resources/css/style.css', 'public/css', [
    require('tailwindcss'),
    require('autoprefixer'),
]);
mix.js('resources/js/welcome.js', 'public/js')
mix.js('resources/js/profile.js', 'public/js')
mix.js('resources/js/ddata.js', 'public/js')
mix.js('resources/js/datepicker.js', 'public/js')
