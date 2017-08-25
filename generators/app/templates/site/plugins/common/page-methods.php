<?php

page::$methods['titleTag'] = function($page) {

	$text = ($page->title_tag()->isEmpty()) ? $page->title()->value() .' | '.site()->title_tag()->value() : $page->title_tag()->value();

	return brick('title', html($text));

};


page::$methods['metaDescription'] = function($page) {

	$text = ($page->meta_description()->isEmpty()) ? $page->title()->value().' '.site()->meta_description()->value() : $page->meta_description()->value();

	return brick('meta', false, ['content'=>html($text), 'name'=>'description']);

};


page::$methods['metaKeywords'] = function($page) {

	$keywords = ($page->keywords()->isEmpty()) ? site()->keywords() : $page->keywords();

	return brick('meta', false, ['content'=>$keywords, 'name'=>'keywords']);

};


page::$methods['ogTitle'] = function($page) {

	$text = ($page->title_tag()->isEmpty()) ? site()->title_tag() : $page->title_tag();

	return brick('meta', false, ['content'=>$text->html(), 'name'=>'og:title']);

};


page::$methods['ogDescription'] = function($page) {

	$text = ($page->meta_description()->isEmpty()) ? site()->meta_description() : $page->meta_description();

	return brick('meta', false, ['content'=>$text->html(), 'name'=>'og:description']);

};


page::$methods['metaTags'] = function($page) {

	return $page->titleTag().$page->metaDescription().$page->metaKeywords();

};


page::$methods['openGraph'] = function($page) {

	return $page->ogTitle().$page->ogDescription();

};


page::$methods['ancestor'] = function($page) {

	if($page->parents()->last())
		return $page->parents()->last();

	return $page;

};


page::$methods['crumb'] = function($page, $link, $index) {

	$crumb = '<li'.(($link) ? '' : ' class="title"').' itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">';

	$inner = '<span itemprop="name">'.(($page->isHomePage()) ? 'Home' : $page->heroTitle()).'</span>';
	$inner.= '<meta itemprop="position" content="'.$index.'" />';

	if($link):
		$crumb.= '<a href="'.$page->url().'" itemprop="item">'.$inner.'</a>';
	else:
		$crumb.= '<span itemprop="item">'.$inner.'</span>';
	endif;

	return $crumb;

};


page::$methods['hero'] = function($page) {

	$content = brick('div', brick('div', brick('div', $page->heroContent(), ['class'=>'purple'])), ['class'=>'container']);

	$options = [
		'id' => 'hero'
	];

	if($page->content()->video()->isNotEmpty()):

		$videoTag = $page->videoTag();

		$content.= $videoTag['content'];
		$options['style'] = 'background-image:url("'.$videoTag['poster'].'");';

	else:
		if($bg = $page->background())
			$options['style'] = 'background-image:url("'.$bg.'");';
	endif;

	if($page->isLanding())
		$options['class'] = 'landing';

	return brick('section', $content, $options);

};


page::$methods['background'] = function($page) {

	if($bg = $page->bg())
		return $bg;

};


page::$methods['videoTag'] = function($page) {

	$file = $page->content()->video()->toFile();
	$name = $file->name();
	$webm = '<source type="video/webm" data-src="'.$page->file($name.'.webm')->url().'" />';
	$mp4 = '<source type="video/mp4" data-src="'.$file->url().'" />';
	$ogv = '<source type="video/ogg" data-src="'.$page->file($name.'.ogv')->url().'" />';
	$poster = $page->image($name.'-poster.jpg')->url();

	$content = brick('div', '<video id="hero-video" loop muted poster="'.$poster.'">'.$webm.$mp4.$ogv.'</video>', ['class'=>'video-container']);

	return compact('content', 'poster');

};


page::$methods['bg'] = function($page, $field='bg') {

	$site = site();

	if($page->content()->{$field}()->isNotEmpty())
		return $site->file($page->content()->{$field}())->url();

	$ancestor = $page->ancestor();

	if($ancestor->content()->{$field}()->isNotEmpty())
		return $site->file($ancestor->content()->{$field}())->url();

	if($site->content()->{$field}()->isNotEmpty())
		return $site->content()->{$field}()->toFile()->url();

	return false;

};


page::$methods['sectionBuild'] = function($page) {

	$content = '';

	$sections = $page->sections()->toStructure();

	foreach($sections as $section):

		if($section->includeEvents()->isTrue()):
			$eventController = new Events(3);
			$events = $eventController->getEvents();
			$riotEvents = $eventController->riotEvents();

			if(!count($events))
				continue;

			$eventSlider = snippet('event-slider', compact('events','riotEvents'), true);
		else:
			$eventSlider = '';
		endif;

		$map = r($section->appendMap()->isTrue(), kirbytag(['home-map'=>true]), '');
		$content.= brick('section', brick('div', $section->text()->kt(), ['class'=>'container']).$map.$eventSlider, ['id'=>$section->section_id()->value()]);
	endforeach;

	return $content;

};


page::$methods['canonicals'] = function($page) {

	return brick('link', array('rel'=>'canonical', 'href'=>$page->canonicalURL()));

};


page::$methods['canonicalURL'] = function($page) {	

	if(get('search')): 
		$url = $page->url().'/search/'.get('search');
	elseif(get('category')): 
		$url = $page->url().'/category/'.get('category');
	elseif(get('tag')): 
		$url = $page->url().'/tag/'.get('tag');
	else: 
		$url = $page->url();
	endif; 

	return $url;

};


page::$methods['extraCSS'] = function($page) {

	if(!isset($page->css))
		return false;

	return css($page->css);

};


page::$methods['extraJS'] = function($page) {

	if(!isset($page->js))
		return false;

	return js($page->js);

};


page::$methods['shareButtons'] = function($page) {

	$buttons = brick('a', '<img src="'.site()->image('fb.svg')->url().'" width="32" height="32" alt="Facebook" /><span class="sr-only">Share on Facebook</span>', ['href'=>$page->shareFacebook(), 'alt'=>'Share on Facebook', 'target'=>'_blank']);
	$buttons.= brick('a', '<img src="'.site()->image('tw.svg')->url().'" width="32" height="32" alt="Twitter" /><span class="sr-only">Share on Twitter</span>', ['href'=>$page->shareTwitter(), 'alt'=>'Share on Twitter', 'target'=>'_blank']);
	$buttons.= brick('a', '<img src="'.site()->image('gp.svg')->url().'" width="32" height="32" alt="Google+" /><span class="sr-only">Share on Google+</span>', ['href'=>$page->shareGooglePlus(), 'alt'=>'Share on Google+', 'target'=>'_blank']);
	$buttons.= brick('a', '<img src="'.site()->image('in.svg')->url().'" width="32" height="32" alt="LinkedIn" /><span class="sr-only">Share on LinkedIn</span>', ['href'=>$page->shareLinkedIn(), 'alt'=>'Share on LinkedIn', 'target'=>'_blank']);
	$buttons.= brick('a', '<img src="'.site()->image('em.svg')->url().'" width="32" height="32" alt="Email" /><span class="sr-only">Share by Email</span>', ['href'=>$page->shareEmail(), 'alt'=>'Share by Email', 'target'=>'_blank']);

	return $buttons;

};


page::$methods['shareFacebook'] = function($page) {
	return 'https://facebook.com/sharer/sharer.php?u='.urlencode($page->url());
};


page::$methods['shareTwitter'] = function($page) {
	return 'https://twitter.com/intent/tweet/?text='.urlencode($page->title()).'&url='.urlencode($page->url());
};


page::$methods['shareGooglePlus'] = function($page) {
	return 'https://plus.google.com/share?url='.urlencode($page->url());
};


page::$methods['sharePinterest'] = function($page) {
	return 'https://pinterest.com/pin/create/button/?url='.urlencode($page->url()).'&media='.urlencode($page->url()).'&description='.urlencode($page->title());
};


page::$methods['shareLinkedIn'] = function($page) {
	$desc = ($page->meta_description()->isEmpty()) ? $page->title()->value().' '.site()->meta_description()->value() : $page->meta_description()->value();
	return 'https://www.linkedin.com/shareArticle?mini=true'.urlencode('&url='.$page->url().'&title='.$page->title().'&summary='.$desc.'&source='.site()->title());
};


page::$methods['shareEmail'] = function($page) {
	return 'mailto:?subject='.urlencode($page->title()->value()).'&body='.urlencode($page->url());
};


page::$methods['printBlank'] = function($page, $boolean=false) {

	$blank = $page->template()=='link' && $page->external()->isTrue();

	if($boolean)
		return $blank;

	return r($blank, ' target="_blank"', '');
	
};