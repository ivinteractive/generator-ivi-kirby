<?php

/*
 * The action to send the form data as an email.
 */
uniform::$actions['email'] = function ($form, $actionOptions) {
    // the form could contain arrays which are incompatible with the template function
    $templatableItems = array_filter($form, function ($item) {
        return is_scalar($item);
    });

    $options = [
        // apply the dynamic subject (insert form data)
        'subject' => str::template(
            a::get($actionOptions, 'subject', l::get('uniform-email-subject')),
            $templatableItems
        ),
        'snippet' => a::get($actionOptions, 'snippet', false),
        'to' => a::get($actionOptions, 'to'),
        'replyTo' => a::get($actionOptions, 'replyTo'),
        'sender' => a::get($actionOptions, 'sender'),
        'bcc' => a::get($actionOptions, 'bcc', false),
        'service' => a::get($actionOptions, 'service', 'mail'),
        'service-options' => a::get($actionOptions, 'service-options', []),
    ];

    // remove newlines to prevent malicious modifications of the email
    // header
    $options['subject'] = str_replace("\n", '', $options['subject']);

    $mailBody = '';
    $snippet = $options['snippet'];

    if (empty($snippet)) {
        foreach ($form as $key => $value) {
            if (str::startsWith($key, '_')) {
                continue;
            }
            if (is_array($value)) {
                $value = implode(', ', array_filter($value, function ($i) {
                    return $i !== '';
                }));
            }
            $mailBody .= ucfirst($key).': '.$value."\n\n";
        }
    } else {
        $mailBody = snippet($snippet, compact('form', 'options'), true);
        if ($mailBody === false) {
            throw new Exception('Uniform email action: The email snippet "'.
                $snippet.'" does not exist!');
        }
    }

    $email = mailgun();

    $params = [
        'to' => $options['to'],
        'from' => $options['sender'],
        'replyTo' => isset($options['replyTo']) ? $options['replyTo'] : a::get($form, '_from'),
        'subject' => $options['subject'],
        'body' => $mailBody
    ];

    if($options['bcc']):
        $mgOptions = $email->options;
        $mgOptions['bcc'] = $options['bcc'];
        $email->set('options', $mgOptions);
    endif;

    try {
        if (!$email->send($params))
            throw new Error('The email could not be sent');

    } catch (Error $e) {
        return [
            'success' => false,
            'message' => l::get('uniform-email-error').' '.$e->getMessage(),
        ];
    }

    return [
        'success' => true,
        'message' => l::get('uniform-email-success'),
    ];
};
