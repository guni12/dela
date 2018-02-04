<?php

namespace Guni\Comments\HTMLForm;

use Anax\DI\DIInterface;
use Guni\Comments\HTMLForm\FormHelper;
use Guni\Comments\HTMLForm\FormHelper2;

/**
 * Base class for form model classes.
 */
abstract class FormModel
{
    /**
     * @var Anax\DI\DIInterface $di   the DI/service container.
     * @var class               $form the main form class.
     */
    protected $di;
    protected $form;
    protected $formhelper;
    protected $formhelper2;



    /**
     * Constructor injects with DI container.
     *
     * @param Anax\DI\DIInterface $di a service container
     */
    public function __construct(DIInterface $di)
    {
        $this->di = $di;
        $this->form = new Form($di);
        $this->formhelper = new FormHelper($di);
        $this->formhelper2 = new FormHelper2($di);
    }



    /**
     * Customise the check() method to use own methods.
     *
     * @return boolean|null $callbackStatus if submitted&validates, false if
     *                                      not validate, null if not submitted.
     *                                      If submitted the callback function
     *                                      will return the actual value which
     *                                      should be true or false.
     */
    public function check()
    {
        return $this->form->check(
            [$this, "callbackSuccess"],
            [$this, "callbackFail"]
        );
    }



    /**
     * Return HTML for the form.
     *
     * @param array $options with options affecting the form output.
     *
     * @return string with HTML for the form.
     */
    public function getHTML($options = [])
    {
        return $this->form->getHTML($options);
    }



    /**
     * Callback what to do if the form was successfully submitted, this
     * happen when the submit callback method returns true. This method
     * can/should be implemented by the subclass for a different behaviour.
     */
    public function callbackSuccess()
    {
        $this->di->get("response")->redirectSelf();
    }



    /**
     * Callback what to do if the form was unsuccessfully submitted, this
     * happen when the submit callback method returns false or if validation
     * fails. This method can/should be implemented by the subclass for a
     * different behaviour.
     */
    public function callbackFail()
    {
        $this->di->get("response")->redirectSelf();
    }
}
