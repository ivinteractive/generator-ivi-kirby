<?php

kirby()->routes([
	[
		'pattern' => 'robots.txt',
		'action'	=> function() {

			$response = 'User-agent: *'.PHP_EOL
					   .'Disallow: /content/*.txt$'.PHP_EOL
					   .'Disallow: /kirby/'.PHP_EOL
					   .'Disallow: /panel/'.PHP_EOL
					   .'Disallow: /*.md$'.PHP_EOL
					   .'Disallow: /app/storage/$'.PHP_EOL
					   .'Sitemap: ' . u('sitemap.xml');

			return new Response($response, 'txt');

		}
	]
]);