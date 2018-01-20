<?php

namespace Guni\User\HTMLForm;

use \Anax\HTMLForm\FormModel;
use \Anax\DI\DIInterface;
use \Guni\User\User;

/**
 * Example of FormModel implementation.
 */
class UserLoginForm extends FormModel
{
    /**
     * Constructor injects with DI container.
     *
     * @param Anax\DI\DIInterface $di a service container
     */
    public function __construct(DIInterface $di)
    {
        parent::__construct($di);

        $this->form->create(
            [
                "id" => __CLASS__,
                "legend" => "Logga in"
            ],
            [
                "user" => [
                    "type"        => "text",
                    //"description" => "Here you can place a description.",
                    "label"      => "Användarnamn eller email",
                ],
                        
                "password" => [
                    "type"        => "password",
                    //"description" => "Here you can place a description.",
                    "placeholder" => "Lösenord",
                    "label"      => "Lösenord",
                ],

                "submit" => [
                    "type" => "submit",
                    "value" => "Logga in",
                    "callback" => [$this, "callbackSubmit"]
                ],
            ]
        );
    }



    /**
     * Callback for submit-button which should return true if it could
     * carry out its work and false if something failed.
     *
     * @return boolean true if okey, false if something went wrong.
     */
    public function callbackSubmit()
    {
        // Get values from the submitted form
        $acronym       = $this->form->value("user");
        $password      = $this->form->value("password");
        $inlogg = [];
        $red = false;

        $session = $this->di->get("session");
        //var_dump($session);

        $user = new User();
        $user->setDb($this->di->get("db"));
        $res = $user->verifyPassword($acronym, $password);

        //'anders@electrotest.se' => string 'loggedin'

        if (!$res) {
            $this->form->rememberValues();
            $this->form->addOutput("Vi hittar inte användarnamnet och/eller lösenordet du angav. Tips: Många har sin e-postadress som användarnamn. Tänk även på att det görs skillnad på stora och små bokstäver i lösenordet.");
            return false;
        }

        $sess = $session->get('user');
        //var_dump($sess);
        $key = $user->acronym;

        if ($sess[$key] && $sess[$key] == 'loggedin') {
            $inlogg = [
                'loggedin' => true,
                'id' => $user->id,
                'acronym' => $user->acronym,
                'isadmin' => $user->isadmin,
                'email' => $user->email
            ];
            $session->set('user', $inlogg);
            //var_dump($sess[$key]);
            $this->form->addOutput("Användare " . $user->acronym . " är redan inloggad.");
        } else {
            $inlogg = [
                'loggedin' => true,
                'id' => $user->id,
                'acronym' => $user->acronym,
                'isadmin' => $user->isadmin,
                'email' => $user->email
            ];
            $session->set('user', $inlogg);
            $red == true;
            //$this->form->addOutput("Användare " . $user->acronym . " loggade in.");
            $this->di->get("response")->redirect("comm/front");
        }
        
        return true;
    }
}
