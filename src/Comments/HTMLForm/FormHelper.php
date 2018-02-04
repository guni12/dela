<?php

namespace Guni\Comments\HTMLForm;

use \Anax\DI\DIInterface;
use \Anax\HTMLForm\FormElementFactory;

class FormHelper extends Form
{
    protected $di;
    protected $session;


    /**
     * Constructor injects with DI container.
     *
     * @param Anax\DI\DIInterface $di a service container
     */
    public function __construct(DIInterface $di)
    {
        $this->di = $di;
        $this->session = $this->di->get("session");
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
            return $this->session->getOnce($failed);
        } elseif ($save) {
            return $this->session->getOnce($save);
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


    /**
     * Add output to display to the user for what happened whith the form and
     * optionally add a CSS class attribute.
     *
     * @param string $str   the string to add as output.
     * @param string $class a class attribute to set.
     * @param string $key - sessionkey
     *
     * @return $this.
     */
    public function helpOutput($str, $class = null, $key)
    {
        $output  = $this->session->get($key);

        $output["message"] = isset($output["message"]) ? $output["message"] . " $str" : $str;

        if ($class) {
            $output["class"] = $class;
        }
        $this->session->set($key, $output);

        return $this;
    }



    /**
    *
     * @param string $class a class attribute to set.
     * @param string $key - sessionkey
    */
    public function setOutputHelper($class, $key)
    {
        $output  = $this->session->get($key);
        $output["class"] = $class;
        $this->session->set($key, $output);
        return $this;
    }



    /**
    *
    * @param array $form - 
    * @param array $options -
    * 
    * @return string - the htmlcode
    */
    public function htmlhelper($form, $options, $elements, $output)
    {
        $wmd     = isset($form['wmd'])     ? "<div id='wmd-button-bar'></div>" : null;
        $preview = isset($form['preview']) ? '<div id="wmd-preview" class="wmd-panel wmd-preview"></div>' : null;
        $id      = isset($form['id'])      ? " id='{$form['id']}'" : null;
        $class   = isset($form['class'])   ? " class='{$form['class']}'" : null;
        $name    = isset($form['name'])    ? " name='{$form['name']}'" : null;
        $action  = isset($form['action'])  ? " action='{$form['action']}'" : null;
        $method  = isset($form['method'])  ? " method='{$form['method']}'" : " method='post'";
        $enctype = isset($form['enctype']) ? " enctype='{$form['enctype']}'" : null;
        $cformId = isset($form['id'])      ? "{$form['id']}" : null;

        if ($options['start']) {
            return "<form{$wmd}{$preview}{$id}{$class}{$name}{$action}{$method}>\n";
        }

        $fieldsetStart  = '<fieldset>';
        $legend         = null;
        $fieldsetEnd    = '</fieldset>';
        if (!$form['use_fieldset']) {
            $fieldsetStart = $fieldsetEnd = null;
        }

        if ($form['use_fieldset'] && $form['legend']) {
            $legend = "<legend>{$form['legend']}</legend>";
        }

        $html = <<< EOD
\n{$wmd}<form{$id}{$class}{$name}{$action}{$method}{$enctype}>
<input type="hidden" name="anax/htmlform-id" value="$cformId" />
{$fieldsetStart}
{$legend}
{$elements}
{$output}
{$fieldsetEnd}
</form>\n
{$preview}
EOD;

        return $html;
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


    /**
    * @param array $key - formpart with key $key
    * 
    * @return array $key - updated
    */
    public function inithelper($key, $val)
    {
        if (isset($val['values'])) {
            $key['checked'] = $val['values'];
        } elseif (isset($val['value'])) {
            $key['value'] = $val['value'];
        }

        if ($key['type'] === 'checkbox') {
            $key['checked'] = true;
        } elseif ($key['type'] === 'radio') {
            $key['checked'] = $val['value'];
        }

        if (isset($val['validation-messages'])) {
            $key['validation-messages'] = $val['validation-messages'];
            $key['validation-pass'] = false;
        }
        return $key;
    }
}