<?php

$robots = 'robots.txt';

kirby()->routes(array(
	array(
		'pattern' => $robots,
		'action'	=> function() use($robots) {
			if(!file_exists($robots)) {
				$content = 'User-agent: *'.PHP_EOL
						  .'Disallow: /content/*.txt$'.PHP_EOL
						  .'Disallow: /kirby/'.PHP_EOL
						  .'Disallow: /site/'.PHP_EOL
						  .'Disallow: /panel/'.PHP_EOL
						  .'Disallow: /*.md$'.PHP_EOL
						  .'Sitemap: ' . u('sitemap.xml');

				file_put_contents(kirby()->roots()->index() .  DS . $robots, $content);
			}
		}
	)
));
