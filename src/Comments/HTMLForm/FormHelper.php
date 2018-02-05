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
        $this->validates = true;
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
        $values = $this->fillValArr($postElement, $element, $values);
        $element = $this->updateElement($element);

        $values = $this->doValidation($element, $values);
        $this->validates = $this->getValidate();

        if (isset($element['remember']) && $element['remember']) {
            $values[$element['name']] = ['value' => $element['value']];
            $element['remember'] = true;
        }

        $this->callbackStatus = $this->checkCallbackElement($element);

        $this->values = $values;
        return $element;
    }




    /**
    * @param object $element
    * @return boolean $callbackStatus
    */
    public function checkCallbackElement($element)
    {
        if (isset($element['callback']) && $this->validates) {
            if (isset($element['callback-args'])) {
                return call_user_func_array($element['callback'], array_merge([$this]), $element['callback-args']);
            } else {
                return call_user_func($element['callback'], $this);
            }
        }
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
    * @param object $key - formpart including attributes, type, label etc
    * @param array $val - written content and validation-messages
    * 
    * @return array $key - updated
    */
    public function inithelper($key, $val)
    {
        $key = $this->keyinit($key, $val);

        $key['checked'] = $key['type'] === 'checkbox' ? true : ($key['type'] === 'radio' ? $val['value'] : null);

        if (isset($val['validation-messages'])) {
            $key['validation-messages'] = $val['validation-messages'];
            $key['validation-pass'] = false;
        }
        return $key;
    }



    /**
    * @param object $key - formpart including attributes, type, label etc
    * @param array $val - written content and validation-messages
    *
    * @return array $key - updated
    */
    public function keyinit($key, $val)
    {
        if (isset($val['values'])) {
            $key['checked'] = $val['values'];
        } elseif (isset($val['value'])) {
            $key['value'] = $val['value'];
        }
        return $key;
    }



     /**
     * @param array $keys
     * @param boolean $validates
     * @param boolean $callbackStatus
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
     * @param array $arr[0] - callable $callIfSuccess 
     * @param array $arr[1] - callable $callIfFail - handlers to call if function returns true. 
     * @param array $arr[2] - boolean $callbackStatus - if form is submitted and validates..
     * @param boolean $validates
     * @param array $keys - the sessionskeys (string) 0:failed, 1:remember, 2:save and boolean 3:rememberValues
     * @param array $values - the $this->values array for check
     *
     * @throws \Anax\HTMLForm\Exception
     *
     * @return boolean $ret - callbackStatus or not
     */
    public function retresult($arr, $validates, $keys, $values)
    {
        $this->updateSession($keys, $validates, $arr[2], $values);
        $ret = $validates ? $arr[2] : $validates;
        $this->checkExceptions($ret, $arr[0], $arr[1]);

        return $ret;
    }



     /**
     * @param boolean $ret - callbackStatus or not
     * @param callable $callIfSuccess
     * @param callable $callIfFail
     *
     * @throws \Anax\HTMLForm\Exception
     */
    public function checkExceptions($ret, $callIfSuccess, $callIfFail)
    {
        $ret === true && isset($callIfSuccess) ? $this->retIsTrue($callIfSuccess) : $this->retIsFalse($callIfFail);
    }




     /**
     * @param callable $callIfSuccess
     *
     * @throws \Anax\HTMLForm\Exception
     */
    public function retIsTrue($callIfSuccess)
    {
        if (!is_callable($callIfSuccess)) {
            throw new Exception("Form, success-method is not callable.");
        }
        call_user_func_array($callIfSuccess, [$this]);
    }



     /**
     * @param callable $callIfFail
     *
     * @throws \Anax\HTMLForm\Exception
     */
    public function retIsFalse($callIfFail)
    {
        if (!is_callable($callIfFail)) {
            throw new Exception("Form, success-method is not callable.");
        }
        call_user_func_array($callIfFail, [$this]);
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



    /**
     * @param object $element
     * @param array $values - for checking
     * @return object $element
     */
    public function noPostElement($element, $values)
    {
        if ($element['type'] === 'checkbox'
            || $element['type'] === 'checkbox-multiple'
        ) {
            $element['checked'] = false;
        }
        $this->doValidation($element, $values);
        return $element;
    }



    /**
    * @param object $elements - this->elements
    * @return object $elements - updated
    */
    public function inithelper2($elements)
    {
        foreach ($elements as $key => $val) {
            if (in_array($elements[$key]['type'], array('submit', 'reset', 'button'))) {
                continue;
            }

            $elements[$key]['value'] = null;
            $elements[$key]['checked'] = isset($elements[$key]['checked'] ? false : null;
        }
        return $elements;
    }
}
