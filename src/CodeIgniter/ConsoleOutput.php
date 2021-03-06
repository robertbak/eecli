<?php

namespace eecli\CodeIgniter;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleOutput extends \EE_Output
{
    /**
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    protected $output;

    /**
     * Success message culled from flashdata
     * @var string|null
     */
    protected $successMessage;

    /**
     * Error message culled from flashdata
     * @var string|null
     */
    protected $errorMessage;

    /**
     * @var \Symfony\Component\Console\Application
     */
    protected $app;

    public function __construct(OutputInterface $output, Application $app)
    {
        $this->app = $app;

        $this->output = $output;

        // you need to load the template library to override the fatal error
        ee()->load->library('template', null, 'TMPL');
    }

    /**
     * Reset errorMessage and successMessage to null
     * @return void
     */
    public function resetMessages()
    {
        $this->errorMessage = null;
        $this->successMessage = null;
    }

    /**
     * Suppress any header-setting
     * @param string  $header
     * @param boolean $replace
     */
    public function set_header($header, $replace = true)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function fatal_error($errorMessage = '', $useLang = true)
    {
        $errorMessage = str_replace('&#171; Back', '', $errorMessage);

        $errorMessage = strip_tags($errorMessage);

        $this->output->writeln("<error>{$errorMessage}</error>");

        exit;
    }

    /**
     * Get a success message
     * @return string|null
     */
    public function getSuccessMessage()
    {
        return $this->successMessage;
    }

    /**
     * Get an error message
     * @return string|null
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * {@inheritdoc}
     */
    public function send_ajax_response($data, $error = false)
    {
        $this->resetMessages();

        $property = $error ? 'errorMessage' : 'successMessage';

        if (is_scalar($data)) {
            $this->{$property} = $data;
        } elseif (! empty($data['error'])) {
            $this->errorMessage = $data['error'];
        } elseif (! empty($data['message_failure'])) {
            $this->errorMessage = $data['message_failure'];
        } elseif (! empty($data['success'])) {
            $this->successMessage = $data['success'];
        } elseif (! empty($data['message_success'])) {
            $this->successMessage = $data['message_success'];
        } elseif (is_array($data) && is_string(current($data))) {
            $this->{$property} = implode(PHP_EOL, $data);
        } else {
            $this->{$property} = print_r($data, true);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function show_message($data, $xhtml = true)
    {
        $this->resetMessages();

        if (isset($data['content'])) {
            $this->successMessage = strip_tags($data['content']);
        } else {
            $this->successMessage = '';
        }
    }

    /**
     * {@inheritdoc}
     */
    public function show_user_error($type, $errors, $heading = '')
    {
        $this->resetMessages();

        if (! is_array($errors)) {
            $errors = array($errors);
        }

        foreach ($errors as $error) {
            $this->app->addError($error);
        }

        $this->errorMessage = implode(PHP_EOL, $errors);
    }
}
