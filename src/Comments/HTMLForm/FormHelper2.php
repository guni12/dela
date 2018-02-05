<?php

namespace Guni\Comments\HTMLForm;

use \Anax\DI\DIInterface;
use \Anax\HTMLForm\FormElementFactory;

class FormHelper2 extends Form
{
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
     * @return array $elementsarray - merged
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
        $this->output = [];

        $generalKey = "anax/htmlform-" . $form["id"] . "#";
        $sessionKey = [
            "save"      => $generalKey . "save",
            "output"    => $generalKey . "output",
            "failed"    => $generalKey . "failed",
            "remember"  => $generalKey . "remember",
        ];
        return $sessionKey;
    }



    /**
    * @param string $failed - sessionkeys
    * @param string $save
    */
    public function notPosted($failed, $save, $sess)
    {
        if ($failed) {
            return $sess->getOnce($failed);
        } elseif ($save) {
            return $sess->getOnce($save);
        } 
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
    * @param string $key - key and value pair in list
    * @param object $element - value in pair
    *
    * @return string $html for the form
    */
    public function elementhtml($key, $element, $elements, $htmlForElement)
    {
        $html = "<p class='buttonbar'>\n" . $htmlForElement . '&nbsp;';

        while (list($key, $element) = each($elements)) {
            if (in_array($element['type'], array('submit', 'reset', 'button'))) {
                $html .= $htmlForElement;
            } else {
                prev($elements);
                break;
            }
        }
        return $html . "\n</p>";
    }



    /**
     * Place the elements according to a layout and return the HTML
     *
     * @param array $elements as returned from GetHTMLForElements().
     * @param array $options  with options affecting the layout.
     *
     * @return array with HTML for the formelements.
     */
    public function getHTMLLayoutForElements($elements, $options = [])
    {
        $options = $this->layoutoptions($options);
        $html = null;
        if ($options['columns'] === 1) {
            foreach ($elements as $element) {
                $html .= $element['html'];
            }
        }

        return $html;
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
     * Add output to display to the user for what happened whith the form and
     * optionally add a CSS class attribute.
     *
     * @param string $str   the string to add as output.
     * @param string $class a class attribute to set.
     * @param string $key - sessionkey
     *
     * @return array $output.
     */
    public function helpOutput($str, $class = null, $key)
    {
        $output  = $this->session->get($key);

        $output["message"] = isset($output["message"]) ? $output["message"] . " $str" : $str;

        if ($class) {
            $output["class"] = $class;
        }
        $this->session->set($key, $output);

        return $output;
    }



    /**
    *
     * @param string $class a class attribute to set.
     * @param string $key - sessionkey
     * @return array $output
    */
    public function setOutputHelper($class, $key)
    {
        $output  = $this->session->get($key);
        $output["class"] = $class;
        $this->session->set($key, $output);
        return $output;
    }



    /**
    * @param array $output - info to print out in form
    * @return string $message - if exist
    */
    public function outputhelper($output)
    {
        $message = isset($output["message"]) && !empty($output["message"]) ? $output["message"] : null;
        $class = isset($output["class"]) && !empty($output["class"]) ? " class=\"{$output["class"]}\"" : null;

        return $message ? "<output{$class}>{$message}</output>" : null;
    }
}