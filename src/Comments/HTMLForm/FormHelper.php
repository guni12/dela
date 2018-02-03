<?php

namespace Guni\Comments\HTMLForm;

use \Anax\DI\DIInterface;
use \Anax\HTMLForm\FormElementFactory;

class FormHelper extends Form
{
    /**
     * @var boolean $rememberValues remember values in the session.
     */
    protected $di;


    /**
     * Constructor injects with DI container.
     *
     * @param Anax\DI\DIInterface $di a service container
     */
    public function __construct(DIInterface $di)
    {
        $this->di = $di;
    }



    /**
     * Add a form element
     *
     * @param array $form     details for the form
     *
     * @return merged $formarray
     */
    public function createform($form = [])
    {
        $defaults = [
            // Always have an id
            "id" => "anax/htmlform",

            // Use a default class on <form> to ease styling
            "class" => "htmlform",

            // Wrap fields within <fieldset>
            "use_fieldset"  => true,

            // Use legend for fieldset, set it to string value
            "legend"        => null,

            // Default wrapper element around form elements
            "wrapper-element" => "div",

            // Use a <br> after the label, where suitable
            "br-after-label" => true,

            "wmd"           => "div",

            "preview"       => "div",
        ];
        return array_merge($defaults, $form);
    }


    /**
     * Add a form element
     *
     * @param array $elements     details for the form
     *
     * @return merged $elementsarray
     */
    public function createelements($elements = [], $form = [])
    {
        $elemts = [];
        if (!empty($elements)) {
            foreach ($elements as $key => $element) {
                $elemts[$key] = FormElementFactory::create($key, $element);
                $elemts[$key]->setDefault([
                    "wrapper-element" => $form["wrapper-element"],
                    "br-after-label"  => $form["br-after-label"],
                ]);
            }
        }
        return $elemts;
    }



    /**
     * Add a form element
     *
     * @param array $form     details for the form
     *
     * @return array $sessionKey
     */
    public function createoutput($form = [])
    {
        // Default values for <output>
        $this->output = [];

        // Setting keys used in the session
        $generalKey = "anax/htmlform-" . $form["id"] . "#";
        $this->sessionKey = [
            "save"      => $generalKey . "save",
            "output"    => $generalKey . "output",
            "failed"    => $generalKey . "failed",
            "remember"  => $generalKey . "remember",
        ];
        return $this->sessionKey;
    }


    /**
    *
    * @return merged options;
    */
    public function getoptions($options)
    {
        $defaults = [
            // Only return the start of the form element
            'start'         => false,
            
            // Layout all elements in one column
            'columns'       => 1,
            
            // Layout consequtive buttons as one element wrapped in <p>
            'use_buttonbar' => true,
        ];
        return array_merge($defaults, $options);
    }



    /**
    *
    * @return merged options;
    */
    public function layoutoptions($options)
    {
        $defaults = [
            'columns' => 1,
            'wrap_at_element' => false,  // Wraps column in equal size or at the set number of elements
        ];
        return array_merge($defaults, $options);
    }


    /**
    *
    *
    */
    public function notPosted($failed, $save)
    {
        if ($failed) {
            return $session->getOnce($failed);
        } elseif ($save) {
            return $session->getOnce($save);
        } 
    }



    /**
    *
    *
    */
    public function doValidation($element)
    {
        $validates = true;
        if (isset($element['validation'])) {
            $element['validation-pass'] = $element->Validate($element['validation'], $this);

            if ($element['validation-pass'] === false) {
                $values[$elementName] = [
                    'value' => $element['value'],
                    'validation-messages' => $element['validation-messages']
                ];
                $validates = false;
            }
        }
        return $validates;
    }
}