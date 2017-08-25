<?php

kirbytext::$tags['communipsum'] = [
  'attr' => [
    'html',
    'words'
  ],
  'html' => function($tag) {

    $paragraphs = $tag->attr('communipsum', 1);
    $words = $tag->attr('words', 50);
    $html = r($tag->attr('html')=='yes', true, false);

    $response = remote::post('https://communism.cool/api', [
      'data' => compact('paragraphs', 'words', 'html')
    ]);

    return $response;

    $text = $response;

    if($chars):
      $split = str::lines($content);
      $content = '';
      foreach($split as $graph):
        $content.= str::excerpt($graph, $chars, true, '.').PHP_EOL.PHP_EOL;
      endforeach;
    endif;

    return $content;

  }
];