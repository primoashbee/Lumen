const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/js/app.js', 'public/js')
mix.js('resources/js/nondeferrables.js', 'public/js')
   .sass('resources/sass/app.scss', 'public/css')
   .styles([
      'public/assets/css/bootstrap.css',
      'public/assets/css/all.css',
      'public/assets/css/main.css',
   ], 'public/css/lumen.css')
   .scripts([
      // 'public/assets/js/jquery.min.js',
      // 'public/assets/js/popper.min.js',
      // 'public/assets/js/bootstrap.min.js',
      // 'public/template/js/chartjs.min.js',
      // 'public/template/js/font-awesome.min.js',
      'public/assets/js/main.js',
   ], 'public/js/lumen.js');
