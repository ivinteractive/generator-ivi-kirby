<?php

/**
 * The guard to check if a honeypot form field got filled in.
 * Removes the field if the check passes.
 */
uniform::$guards['honeypot'] = function (UniForm $form) {
    $field = $form->options('honeypot');

    if (!$field) {
        // default honeypot name is 'website'
        $field = 'website';
    }
    
    // remove honeypot field from form data
    $form->removeField($field);

    if ($form->value($field)) {
        return [
            'success' => true,
            'message' => l::get('uniform-filled-potty'),
            'clear' => false
        ];
    }

    return ['success' => true];
};
