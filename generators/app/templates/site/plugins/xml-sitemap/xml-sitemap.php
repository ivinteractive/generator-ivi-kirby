<?php

$exclude = c::get('sitemap.exclude', ['error']);
$templateExclude = c::get('sitemap.exclude.template', ['app','event','redirects']);
$important = c::get('sitemap.important', ['contact']);

kirby()->routes([
	[
		'pattern' => 'sitemap.xml',
		'action'  => function() use ($exclude, $templateExclude, $important) {

			$sitemap = '<?xml version="1.0" encoding="utf-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
			foreach(site()->pages()->index() as $p){
				if(!in_array($p->uri(), $exclude) && !in_array($p->template(), $templateExclude) && !$p->norobot()->isTrue()){
					$sitemap .= '<url><loc>' . html($p->url());
					$sitemap .= '</loc><lastmod>' . $p->modified('c') . '</lastmod><priority>';
					$sitemap .= ($p->isHomePage()||in_array($p->uri(), $important)) ? 1 : 0.6/$p->depth();
					$sitemap .= '</priority></url>';
				}
			}
			$sitemap .= '</urlset>';

			return new Response($sitemap, 'xml');

		}
	]
]);