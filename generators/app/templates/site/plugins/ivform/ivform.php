<?php

/**
 * A customized extension of the Kirby Uniform plugin (https://github.com/mzur/kirby-uniform)
 */

require_once __DIR__.DS.'lib'.DS.'ivform.class.php';

function ivform($id, $options = [])
{
    // loads plugin language files dynamically
    // see https://github.com/getkirby/kirby/issues/168
    $lang = site()->multilang() ? site()->language()->code() : c::get('uniform.language', 'en');
    require_once __DIR__.DS.'languages'.DS.$lang.'.php';

    // // load actions
    require_once __DIR__.DS.'actions'.DS.'db_insert.php';
    
    require_once __DIR__.DS.'actions'.DS.'email.php';
    require_once __DIR__.DS.'actions'.DS.'email-select.php';
    require_once __DIR__.DS.'actions'.DS.'log.php';
    require_once __DIR__.DS.'actions'.DS.'login.php';
    require_once __DIR__.DS.'actions'.DS.'webhook.php';

    require_once __DIR__.DS.'guards'.DS.'honeypot.php';
    require_once __DIR__.DS.'guards'.DS.'calc.php';

    $form = new ivForm($id, $options);
    $form->execute();

    return $form;
}