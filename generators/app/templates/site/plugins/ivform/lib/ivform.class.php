<?php

require_once(kirby()->roots()->index() . DS . '..' . DS . 'bootstrap.php');
require_once(__DIR__ . DS . '..' . DS . 'uniform' . DS . 'uniform.php');

class ivForm extends UniForm {

    /**
     * Creates a new ivForm instance.
     *
     * @param string $id      The unique ID of this form.
     * @param array  $options Array of uniform options, including the actions.
     */
    public function __construct($id, $options)
    {
        if (empty($id)) {
            throw new Error('No Uniform ID was given.');
        }

        $this->id = $id;

        $this->erroneousFields = [];

        $this->options = [
            // spam protection mechanism to use, default is 'honeypot'
            'guard' => a::get($options, 'guard', 'honeypot'),
            // required field names
            'required' => a::get($options, 'required', []),
            // field names to be validated
            'validate' => a::get($options, 'validate', []),
            // action arrays
            'actions' => a::get($options, 'actions', []),
            // ignore fields
            'ignores' => a::get($options, 'ignores', ['/'.url::path()]),
            // log fields
            'logFields' => a::get($options, 'logFields', ['_submit']),
            // spam fields
            'spamFields' => a::get($options, 'spamFields', [
                'website' => 'honeypot',
                'c_time' => 'spam_timer'
            ]),
            // non-logged fields
            'noLog' => a::get($options, 'noLog', []),
            // error messages
            'messages' => a::get($options, 'messages', [])
        ];

        // required fields will also be validated by default
        $this->options['validate'] = a::merge(
            $this->options['validate'],
            $this->options['required']
        );

        // initialize output array with the output of the plugin itself
        $this->actionOutput = [
            '_uniform' => [
                'success' => false,
                'message' => '',
            ],
        ];

        // the token is stored as session variable until the form is sent
        // successfully
        $this->token = s::get($this->id);

        if (!$this->token) {
            $this->generateToken();
        }

        // get the data to be sent (if there is any)
        $this->data = r::get();

        if(r::method()=='POST'):

            if ($this->requestValid()) {
                if (empty($this->options['actions'])) {
                    throw new Error('No Uniform actions were given.');
                }

                if ($this->dataValid()) {
                    // uniform is done, now it's the actions turn
                    $this->actionOutput['_uniform']['success'] = true;
                }
            }

            $this->logInfo();

        endif;

    }

    /**
     * Checks if all required data is present to send the form.
     *
     * @return bool
     */
    protected function dataValid()
    {
        // check if all required fields are there
        $this->erroneousFields = static::missing(
            $this->data,
            array_keys($this->options['required'])
        );

        // perform validation for all fields with a given validation method
        foreach ($this->options['validate'] as $field => $method) {
            $value = a::get($this->data, $field);
            // validate only if a method is given and the field contains data
            if (!empty($method) && !empty($value) && !call('v::'.$method, $value)) {
                array_push($this->erroneousFields, $field);
            }
        }

        if (!empty($this->erroneousFields)) {
            $this->actionOutput['_uniform']['message'] = l::get('uniform-fields-not-valid');

            return false;
        }

        return true;
    }

    /**
     * Quickly decides if the request is valid so the server is minimally
     * stressed by scripted attacks.
     *
     * @return bool
     */
    protected function requestValid()
    {

        $this->removeIgnoredFields();

        // Don't regenerate the token if the form isn't actually being submitted
        if(empty($this->data))
            return false;

        if (a::get($this->data, '_submit') !== $this->token) {

            // If it's an AJAX request and the token session value is set, we'll let it through (had been causing false positives with spam detection)
            if(s::get($this->id) && r::ajax()):
                $this->data['_submit'] = s::get($this->id);
            elseif(c::get('ivform-token-skip', true)):
                s::set($this->id, '');
                $this->data['_submit'] = '';
            else:
                // clear the data array, too
                // see https://github.com/mzur/kirby-uniform/issues/48
                $this->reset();

                return false;
            endif;
        }

        // remove uniform specific fields from form data
        $this->removeField('_submit');

        $guards = $this->options['guard'];

        if (empty($guards)) {
            // disabled spam protection
            return true;
        }

        // multiple guards can be defines as array
        if (!is_array($guards)) {
            $guards = [$guards];
        }

        foreach ($guards as $guard) {
            if (!array_key_exists($guard, static::$guards)) {
                throw new Error("Uniform guard '{$guard}' is not defined!");
            }

            $check = call_user_func(static::$guards[$guard], $this);

            if (!$check['success']) {
                // display validation error message
                $this->actionOutput['_uniform']['message'] = a::get($check, 'message', '');

                if (array_key_exists('fields', $check) && is_array($check['fields'])) {
                    // mark field(s) as erroneous
                    $this->erroneousFields = array_merge($this->erroneousFields, $check['fields']);
                }

                // reset the form but let the guard choose whether to clear the data
                // see https://github.com/mzur/kirby-uniform/issues/54
                $this->reset(a::get($check, 'clear', true));

                if($guard=='honeypot')
                    $this->data['c_honeypot'] = 1;

                return false;
            }
        }

        return true;
    }

    /**
     * If an `$action` was given, returns the success/error feedback message of
     * the action.
     * If no `$action` was given, returns the feedback messages of all actions;
     * one per line.
     *
     * @param mixed $action (optional) the index of the action to get the
     *                      feedback message from
     *
     * @return string
     */
    public function message($action = false)
    {
        $message = '';
        if (!is_int($action) && !is_string($action)) {
            foreach ($this->actionOutput as $output) {
                $message .= a::get($output, 'message', '')."\n";
            }
        } elseif (array_key_exists($action, $this->actionOutput)) {
            $message = a::get($this->actionOutput[$action], 'message', '');
        }

        if(is_array($message))
            return $message;

        return trim($message);
    }

    protected function removeIgnoredFields() {

        if(array_key_exists('ignores',$this->options)):
            foreach($this->options['ignores'] as $ignore):
                $this->removeField($ignore);
            endforeach;
        endif;

    }

    public function showErrors() {

        $errors = [];

        $messages = $this->options['messages'];

        foreach($this->erroneousFields as $f)
            $errors[$f] = (array_key_exists($f, $messages)) ? $messages[$f] : true;

        return $errors;

    }

    public function showActions() {
        return $this->actionOutput;
    }

    public static function remoteAddress() {

        if(server::get('HTTP_CF_CONNECTING_IP'))
            return server::get('HTTP_CF_CONNECTING_IP');

        return server::get('REMOTE_ADDR');

    }

    protected function logInfo() {

        if(empty($this->data))
            return false;     

        try {

            require_once(kirby()->roots()->plugins() . DS . 'mobiledetect' . DS . 'mobiledetect.php');

            $logData = $this->data;

            foreach($this->options['noLog'] as $noLog):
                unset($logData[$noLog]);
            endforeach;

            if(count($this->erroneousFields)):
                $errors = a::json($this->erroneousFields);
            else:
                $errors = '';

                if(!$this->successful())
                    $errors = a::json($this->message());
            endif;

            $browserInfo = browserInfo();

            $formData = [
                'form_id' => $this->id,
                'form_data' => a::json($logData),
                'errors' => $errors,
                'referer' => r::referer(),
                'useragent' => server::get('HTTP_USER_AGENT'),
                'ip' => $browserInfo->ip,
                'mobile' => ($browserInfo->mobile) ? 1 : 0,
                'browser' => $browserInfo->browser,
                'platform' => $browserInfo->platform,
                'device' => $browserInfo->device,
                'successful' => r($this->successful(),1,0)
            ];

            foreach($this->options['logFields'] as $field):
                $formData[$field] = r::data($field);
            endforeach;

            foreach($this->options['spamFields'] as $formField => $column):
                $formData[$column] = r::data($formField);
            endforeach;

            foreach($formData as $key => $value):
                if(is_null($value))
                    $formData[$key] = '';
            endforeach;

            $this->db = new Database(array(
                'host'     => EnvHelper::env('DB_HOST', 'localhost'),
                'database' => EnvHelper::env('DB_NAME'),
                'user'     => EnvHelper::env('DB_USER'),
                'password' => EnvHelper::env('DB_PASS')
            ));

            $tableName = c::get('form-log', 'form_log');
            $table = $this->db->table($tableName);

            if(!$id = $table->insert($formData))
                throw new Exception($this->db->lastError());

            $this->dbID = $id;            

        } catch (Exception $e) {

            dd($e);

            if(c::get('log-error-email')):
                $email = mailgun(true);

                $email->send([
                    'to' => c::get('log-error-email'),
                    'from' => 'forms@ivinteractive.com',
                    'subject' => 'Log error on '.url(),
                    'body' => $e->getMessage()
                ]);
            endif;

        }

    }

    public function logUpdate($errors) {

        try {

            if(!$this->db)
                throw new Exception('No db set');

            $tableName = c::get('form-log', 'form_log');
            if(!$table = $this->db->table($tableName))
                throw new Exception('Not able to set a table');

            if(!$updated = $table->where('id','=',$this->dbID)->update(array(
                'errors'=>a::json($errors),
                'successful'=>r(count($errors),0,1)
            )))
                throw new Exception('Failed to update the form log');
            

        } catch (Exception $e) {

            $email = mailgun(true);

            $email->send([
                'to' => c::get('log-error-email'),
                'from' => 'forms@ivinteractive.com',
                'subject' => 'Log error on '.url().' - '.$this->dbID,
                'body' => $e->getMessage()
            ]);

        }

    }

}