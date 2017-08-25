<?php

require_once(kirby()->roots()->index() . DS . '..' . DS . 'bootstrap.php');

/*

---------------------------------------
Kirby Configuration
---------------------------------------

By default you don't have to configure anything to
make Kirby work. For more fine-grained configuration
of the system, please check out http://getkirby.com/docs/advanced/options

*/

c::set('license', EnvHelper::env('KIRBY_LICENSE', null));

c::set('cache', false);
c::set('cache.driver', 'memcached');

c::set('cache.ignore', []);

c::set('sitemap.exclude', ['error', 'search']);

c::set('panel.stylesheet', 'assets/css/panel.css');

c::set('contact-recipient', 'support@ivinteractive.com');

c::set('languages', [
    [
      'code'    => 'en',
      'name'    => 'English',
      'locale'  => 'en_US',
      'default' => true,
      'url'     => '/'
    ],
]);

c::set('form-log', 'contact_log');
c::set('log-error-email', 'cs@ivinteractive.com');

c::set('kirbytext.snippets.pre', [
  'domain' => url()
]);

c::set('kirbytext.snippets.post',array(
  '{' => '(',
  '}' => ')'
));

c::set('language.detect', true);

c::set('html.sitemap.exclude', array());

c::set('redirecty',true);

if(function_exists('panel')) { c::set('MinifyHTML', false); }

c::set('timezone', 'America/New_York');