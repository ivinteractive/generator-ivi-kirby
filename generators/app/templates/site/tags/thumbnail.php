<?php

kirbytext::$tags['thumbnail'] = [
  'attr' => [
  	'class',
    'width',
    'height',
    'crop',
    'link',
    'alt',
    'popup',
    'figure',
    'caption'
  ],
  'html' => function($tag) {

    if(gettype($tag->attr('thumbnail'))=='string'):
      $file = $tag->page()->file($tag->attr('thumbnail'));
    else:
      $file = $tag->attr('thumbnail');
    endif;

    if(!$file)
      return '';

    $image = new Media ($file->root(), $file->url());

  	$class = $tag->attr('class');
  	$width = r($tag->attr('width'),intval($tag->attr('width')), 720);
  	$height = r($tag->attr('width'),intval($tag->attr('height')), null);
    $alt  = r($tag->attr('alt'), $tag->attr('alt'), $image->name());
  	$crop = r($tag->attr('crop')=='yes',true,false);
  	$link = $tag->attr('link');
  	$popup = r($tag->attr('popup')=='yes',true,false);
    $figure = r($tag->attr('figure')=='false',false,true);
    $caption = $tag->attr('caption', false);

  	$options = array(
      'width' => $width,
      'alt'   => $alt
    );

  	if($height):
  		$options['height'] = $height;
    endif;

    if($crop):
      $options['crop'] = true;
    endif;

    $options = a::merge($options, [
      'driver' => 'gd',
      'root' => kirby()->roots()->thumbs() . '/'
    ]);

    if(in_array($image->extension(), ['jpg','jpeg'])):
      $image =  ThumbExt($image, $options);
    else:
      $options = array_unshift($options, ['image'=>$image->url()]);
      $image = kirbytag($options);
    endif;

    if($figure):

      $thumb = '<figure'.r($class!='',' class="'.$class.'"').' role="group">';
      if($link!=''):
        $thumb.= '<a href="'.url($link).'"'.r($popup,' target="_blank"').'>';
      endif;
      $thumb.= $image;
      if($caption)
        $thumb.= brick('figcaption', $caption);
      if($link!=''):
        $thumb.= '</a>';
      endif;
      $thumb.= '</figure>';

    else:

      $thumb = '';
      if($link!=''):
        $thumb.= '<a href="'.url($link).'"'.r($popup,' target="_blank"').'>';
      endif;
      if($caption)
        $thumb.= brick('figcaption', $caption);
      $thumb.= $image;
      if($link!=''):
        $thumb.= '</a>';
      endif;

    endif;


  	return $thumb;

  }

];