<?php

namespace Guni\Comments\HTMLForm;

use \Anax\DI\DIInterface;
use \Anax\HTMLForm\FormElementFactory;

class FormHelper extends Form
{
    protected $di;
    protected $session;
    protected $validates;
    protected $values;
    protected $callbackStatus;
    protected $elements;


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
    * @param object $element - the form that we check
    * @param array $values - the formvalues we check
    * @return array $values - updated
    */
    public function doValidation($element, $values)
    {
        if (isset($element['validation'])) {
            $element['validation-pass'] = $element->Validate($element['validation'], $this);

            if ($element['validation-pass'] === false) {
                $values[$element['name']] = [
                    'value' => $element['value'],
                    'validation-messages' => $element['validation-messages']
                ];
                $this->validates = false;
            }
        }
        $this->values = $values;
        return $values;
    }



    /**
    * @return boolean $validates - if form validates
    */
    public function getValidate()
    {
        return $this->validates;
    }



    /**
     * @param string $postElement
     * @param object $element
     * @param array $values
     * @return object $element
     */
    public function postElementFill($postElement, $element, $values)
    {
        $this->callbackStatus = null;
        $this->validates = true;
        $values = $this->fillValArr($postElement, $element, $values);
        $element = $this->updateElement($element);

        $values = $this->doValidation($element, $values);
        $this->validates = $this->getValidate();

        if (isset($element['remember']) && $element['remember']) {
            $values[$element['name']] = ['value' => $element['value']];
            $element['remember'] = true;
        }
        if (isset($element['callback']) && $this->validates) {
            if (isset($element['callback-args'])) {
                $this->callbackStatus = call_user_func_array(
                    $element['callback'],
                    array_merge([$this]),
                    $element['callback-args']
                );
            } else {
                $this->callbackStatus = call_user_func($element['callback'], $this);
            }
        }
        $this->values = $values;
        return $element;
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
    *
    * @param array $form - what we have in the form this far
    * @param array $options - extra info for the form, if columns etc.
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

        $allvar = [$wmd, $preview, $id, $class, $name, $action, $method, $enctype, $cformId];

        return $this->formhtml($allvar, $form, $elements, $output);
    }



    /**
    *
    * @param array $all - all variables for the form
    * @param array $form - whats already saved in the form
    * 
    * @return string - the htmlcode
    */
    public function formhtml($all, $form, $elements, $output)
    {
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
\n{$all[0]}<form{$all[2]}{$all[3]}{$all[4]}{$all[5]}{$all[6]}{$all[7]}>
<input type="hidden" name="anax/htmlform-id" value="$all[8]" />
{$fieldsetStart}
{$legend}
{$elements}
{$output}
{$fieldsetEnd}
</form>\n
{$all[1]}
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



     /**
     * @param $keys
     * @param $validates
     * @param $callbackStatus
     * @param array $values - 
     */
    public function updateSession($keys, $validates, $callbackStatus, $values)
    {
        $session = $this->di->get("session");
        $session->delete($keys[0]);
        $this->values = $values;

        if ($validates === false || $callbackStatus === false) {
            $session->set($keys[0], $this->values);
        } elseif ($keys[1]) {
            $session->set($keys[1], $this->values);
        }

        if ($keys[3]) {
            $session->set($keys[2], $this->values);
        }
    }



     /**
     * @param array $arr containing callable $callIfSuccess and callable $callIfFail - handlers to call if function returns true. And containing boolean $callbackStatus - if form is submitted and validates..
     * @param boolean $validates
     * @param array $keys - the sessionskeys (string) failed, remember, save
     * @param array $values - the $this->values array for check
     *
     * @throws \Anax\HTMLForm\Exception
     */
    public function retresult($arr, $validates, $keys, $values)
    {
        $this->updateSession($keys, $validates, $arr[2], $values);

        $ret = $validates ? $arr[2] : $validates;
        if ($ret === true && isset($arr[0])) {
            if (!is_callable($arr[0])) {
                throw new Exception("Form, success-method is not callable.");
            }
            call_user_func_array($arr[0], [$this]);
        } elseif ($ret === false && isset($arr[1])) {
            if (!is_callable($arr[1])) {
                throw new Exception("Form, success-method is not callable.");
            }
            call_user_func_array($arr[1], [$this]);
        }
        return $ret;
    }



    /**
    * @param array|null $postElement - the posted element
    * @param object $element - the formelement
    * @return array $arr - add to $this-values
    */
    public function fillValArr($postElement, $element, $arr)
    {
        if (is_array($postElement)) {
            $arr[$element['name']]['values'] = $element['checked'] = $postElement;
        } else {
            $arr[$element['name']]['value'] = $element['value'] = $postElement;
        }
        if ($element['type'] === 'password') {
            $arr[$element['name']]['value'] = null;
        }
        return $arr;
    }



    /**
    * @param object $element
    * @return object $element - updated
    */
    public function updateElement($element)
    {
        if ($element['type'] === 'checkbox') {
            $element['checked'] = true;
        }

        if ($element['type'] === 'radio') {
            $element['checked'] = $element['value'];
        }
        return $element;
    }



    /**
    *
    * @return array $this->values - updated
    */
    public function getValues()
    {
        return $this->values;
    }



    /**
    *
    * @return array $this->callbackStatus - updated
    */
    public function getcallbackStatus()
    {
        return $this->callbackStatus;
    }
}
