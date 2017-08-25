<?php

require_once(kirby()->roots()->index() . DS . '..' . DS . 'bootstrap.php');

uniform::$actions['db_insert'] = function($form, $actionOptions) {

	$options = array(
		'table'			=> a::get($actionOptions, 'table', false),
		'nonSerts'		=> a::get($actionOptions, 'nonSerts', array()),
		'spamLog'		=> a::get($actionOptions, 'spamLog', false),
		'subject'		=> a::get($actionOptions, 'subject', false),
		'landing'		=> a::get($actionOptions, 'landing', false),
		'snippet'		=> a::get($actionOptions, 'snippet', null)
	);

	$db = new Database(array(
        'host'     => EnvHelper::env('DB_HOST', 'localhost'),
        'database' => EnvHelper::env('DB_NAME'),
        'user'     => EnvHelper::env('DB_USER'),
        'password' => EnvHelper::env('DB_PASS')
	));

	if(!$options['table']) {
	    throw new Exception('You must choose a table to insert into.');
	}

	$table = $db->table($options['table']);
	$nonSerts = $options['nonSerts'];

	$insert_array = [];

	// do all the db insertion actions

	if($options['spamLog']):

		$spamLog = $options['spamLog'];

		foreach($spamLog as $key => $value):
			if (str::startsWith($key, '_')) {
				continue;
			}
			if (in_array($key,$nonSerts)) {
				continue;
			}
			if($key=='name') {
				// do the first/name last name split
				$parts = explode(' ', $value);
				$insert_array['last'] = array_pop($parts);
				$insert_array['first'] = implode(' ', $parts);				
				continue;
			}
			$insert_array[$key] = $value;
		endforeach;

	else:

		foreach($form as $key => $value):
			if (str::startsWith($key, '_')) {
				continue;
			}
			if (in_array($key,$nonSerts)) {
				continue;
			}
			if($key=='name') {
				// do the first/name last name split
				$parts = explode(' ', $value);
				$insert_array['last'] = array_pop($parts);
				$insert_array['first'] = implode(' ', $parts);				
				continue;
			}
			$insert_array[$key] = $value;
		endforeach;

	endif;

	$browserInfo = browserInfo();

	foreach(['ip','browser','platform','device'] as $attr)
		$insert_array[$attr] = $browserInfo->{$attr};

	$insert_array['mobile'] = ($browserInfo->mobile) ? 1 : 0;
	$insert_array['useragent'] = server::get('HTTP_USER_AGENT');
	$insert_array['referer'] = r::referer();
	    
	if(!$id = $table->insert($insert_array)):
	    throw new Exception($db->lastError());
	else:

		$userTable = $db->table('users');
		$users = $userTable->where((($options['landing'])?'landing':'contact'), '=', 1)->all();

		foreach($users as $user)
			call_user_func(uniform::$actions['email'], a::merge($form, compact('id')), [
				'to' => $user->name.' <'.$user->email.'>',
				// 'to' => implode(', ', c::get('email.recipients', [])),
				'sender' => EnvHelper::env('EMAIL_SENDER', 'info@nefertility.com'),
				// 'bcc' => 'IV Interactive <forms@ivinteractive.com>',
				'subject' => r($options['subject'], $options['subject'], get('subject')),
				'replyTo' => get('email'),
				'snippet' => ($options['snippet'])
			]);

	    $return = [
			'success' => true,
			'message' => 'Inserted to database successfully.'
		];
	endif;

	$return_str = json_encode($return);
	$options_str = json_encode($options);
	$insert_str = json_encode($insert_array);
	if(!$return['success']) { 
		mail("support@ivinteractive.com","DB Insert Error - ".url(),$return_str."\n\n".$options_str."\n\n".$insert_str);
	}
	return $return;
};