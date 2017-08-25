<?php

kirbytext::$tags['snippet'] = [
  'attr' => [
    'options'
  ],
  'html' => function($tag) {

    $snip = $tag->attr('snippet');
    $options = $tag->attr('options');

    $newOptions = [];

    if($options==''):
      
      $newOptions = null;

    else:

  	  foreach(json_decode($options, true) as $key => $val):
  	    $newOptions[$key] = $val;
      endforeach;

    endif;

    return snippet($snip,$newOptions,true);

  }
];