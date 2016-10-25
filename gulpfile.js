var elixir = require('laravel-elixir');
var gulp = require('gulp');

elixir(function(mix) { 

    mix.scripts([
      './assets/src/embed-api.js',
      './assets/src/components/active-users.js',
      './assets/src/components/hero-metric.js',
    ], './assets/bundle.min.js');

    mix.scripts([
      './assets/src/dashboard/settings.js',
    ],'./assets/settings.min.js');

    mix.scripts([
      './assets/src/dashboard/overview.js',
    ],'./assets/overview.min.js');

    mix.scripts([
      './assets/src/toolbar-button.js',
    ],'./assets/toolbar-button.min.js');

    mix.less([
      './assets/src/toolbar-button.less',
      './assets/src/dashboard/core.less',
      './assets/src/dashboard/overview.less',
      './assets/src/dashboard/settings.less',
    ],'./assets/bundle.min.css');

});