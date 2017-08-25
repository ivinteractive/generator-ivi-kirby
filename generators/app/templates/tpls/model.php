<?php

class DefaultPage extends Page {


	public function sectionBuild()
	{

		$content = '';

		$sections = $this->sections()->toStructure();

		foreach($sections as $section):

			$options = [
				'class' => trim($section->type()->value().' '.str_replace(',', ' ', $section->classes()->value()))
			];

			if($section->section_id()->isNotEmpty())
				$options['id'] = $section->section_id()->value();

			switch($section->type()->value()):
				case 'split':
					$sectionContent = $this->split($section, $options);
					break;
				case 'related':
					$sectionContent = $this->related($section);
					break;
				default:
					$sectionContent = brick('section', brick('div', $section->text()->kt(), ['class'=>'container']), $options);
					break;
			endswitch;

			$content.= $sectionContent;

		endforeach;

		return $content;

	}
	

	public function split($section, $options=[])
	{

		$text = brick('div', brick('div', $section->text()->kt(), ['class'=>'container']), ['class'=>'inner']);
		$video = r($section->split_video()->isNotEmpty(), brick('a', brick('div', brick('span'), ['class'=>'play']), ['href'=>'#', 'class'=>'trigger-video', 'data-video'=>$section->split_video()->value()]), '');
		$textBG = $section->bg()->toFile()->url();
		$videoBG = $section->bg_video()->toFile()->url();

		if($section->split_side()->value()=='left'):
			$left = brick('div', $text, ['class'=>'text left', 'style'=>'background-image:url("'.$textBG.'");']);
			$right = brick('div', $video, ['class'=>'video right', 'style'=>'background-image:url("'.$videoBG.'");']);
			$right.= brick('div', $text, ['class'=>'text right mobile', 'style'=>'background-image:url("'.$textBG.'");']);
		else:
			$left = brick('div', $video, ['class'=>'video left', 'style'=>'background-image:url("'.$videoBG.'");']);
			$right = brick('div', $text, ['class'=>'text right', 'style'=>'background-image:url("'.$textBG.'");']);
		endif;

		return brick('section', $left.$right, $options);

	}

}