<?php

field::$methods['excerpt'] = function($field, $chars = 140, $mode = 'chars') {
  
  $excerpt = excerpt($field, $chars, $mode);

  return str_replace(' ...', '...', $excerpt);

};

field::$methods['ktNoPar'] = function($field) {
	return ktNoPar($field->value());
};

field::$methods['siteFile'] = function($field) {

	if($file = site()->file($field->value()))
		return $file;

	return null;

};