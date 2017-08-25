<?php

if(!function_exists('dd')):

  function dd($variable) {

  	exit(var_dump($variable));

  }

endif;

function ktNoPar($string) {
  return preg_replace('!^<p>(.*?)</p>$!i', '$1', kirbytext($string));
}

function studlyCase($string) {

  $split = str::ucwords(str_replace(['-','_'], ' ', $string));

  return str_replace(' ', '', $split);

}

function collect(array $array=[]) {

  return new Collection($array);

}

kirbytext::$pre[] = function($kirbytext, $value) {
  // use mapping in config to replace {{strings}} with useful text
  $snippets = c::get('kirbytext.snippets.pre', array());
  $values   = array_values($snippets);
  $keys     = array_map(function($key) {
    return '{{' . $key . '}}';
  }, array_keys($snippets));

  return str_replace($keys, $values, $value);

};

// Use brackets in place of parentheses in tags - imperfect, but probably the best workaround for kirbytext right now
kirbytext::$post[] = function($kirbytext, $value) {
  $snippets = c::get('kirbytext.snippets.post');
  $keys     = array_keys($snippets);
  $values   = array_values($snippets);
  return str_replace($keys, $values, $value);
};