<?php

namespace Guni\Comments\HTMLForm;

use \Guni\Comments\HTMLForm\FormModel;
use \Guni\Comments\HTMLForm\FormHelper;
use \Anax\DI\DIInterface;
use \Anax\HTMLForm\FormElementFactory;

/**
 * A utility class to easy creating and handling of forms
 */
class Form implements \ArrayAccess
{
    /**
     * @var array $form       settings for the form            class
     * @var array $elements   all form elements
     * @var array $output     messages to display together with the form
     * @var array $sessionKey key values for the session
     * @var array $values     holds values in check-functions
     */
    protected $form;
    protected $elements;
    protected $output;
    protected $sessionKey;
    protected $values;

    /**
     * @var boolean $rememberValues remember values in the session.
     */
    protected $rememberValues;


    /**
     * @var array $callbackStatus.
     */
    protected $callbackStatus;

    /**
     * extra class to make this file some smaller.
     */
    protected $formhelper;

    /**
     * @var Anax\DI\DIInterface $di the DI service container.
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
        $this->formhelper = new FormHelper($di);
    }



    /**
     * Implementing ArrayAccess for this->elements
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->elements[] = $value;
        } else {
            $this->elements[$offset] = $value;
        }
    }
    
    public function offsetExists($offset)
    {
        return isset($this->elements[$offset]);
    }
    
    public function offsetUnset($offset)
    {
        unset($this->elements[$offset]);
    }
    
    public function offsetGet($offset)
    {
        return isset($this->elements[$offset]) ? $this->elements[$offset] : null;
    }



    /**
     * Add a form element
     *
     * @param array $form     details for the form
     * @param array $elements all the elements
     *
     * @return $this
     */
    public function create($form = [], $elements = [])
    {
        $this->form = $this->formhelper->createform($form);
        $this->elements = $this->formhelper->createelements($elements, $this->form);
        $this->output = [];
        $this->sessionKey = $this->formhelper->createoutput($this->form);
        return $this;
    }



    /**
     * Add a form element
     *
     * @param FormElement $element the formelement to add.
     *
     * @return $this
     */
    public function addElement($element)
    {
        $name = $element;
        if (isset($this->elements[$name])) {
            throw new Exception("Form element '$name' already exists, do not add it twice.");
        }
        $this[$element['name']] = $name;
        return $this;
    }



    /**
     * Get a form element
     *
     * @param string $name the name of the element.
     *
     * @return \Anax\HTMLForm\FormElement
     */
    public function getElement($name)
    {
        if (!isset($this->elements[$name])) {
            throw new Exception("Form element '$name' is not found.");
        }
        return $this->elements[$name];
    }



    /**
     * Remove an form element
     *
     * @param string $name the name of the element.
     *
     * @return $this
     */
    public function removeElement($name)
    {
        if (!isset($this->elements[$name])) {
            throw new Exception("Form element '$name' is not found.");
        }
        unset($this->elements[$name]);
        return $this;
    }



    /**
     * Set validation to a form element
     *
     * @param string $element the name of the formelement to add validation rules to.
     * @param array  $rules   array of validation rules.
     *
     * @return $this
     */
    public function setValidation($element, $rules)
    {
        $this[$element]['validation'] = $rules;
        return $this;
    }



    /**
     * Add output to display to the user for what happened whith the form and
     * optionally add a CSS class attribute.
     *
     * @param string $str   the string to add as output.
     * @param string $class a class attribute to set.
     *
     * @return $this.
     */
    public function addOutput($str, $class = null)
    {
        return $this->formhelper->helpOutput($str, $class, $this->sessionKey["output"]);
    }



    /**
     * Set a CSS class attribute for the <output> element.
     *
     * @param string $class a class attribute to set.
     *
     * @return $this.
     */
    public function setOutputClass($class)
    {
        return $this->formhelper->setOutputHelper($class, $this->sessionKey["output"]);
    }



    /**
     * Remember current values in session, useful for storing values of
     * current form when submitting it.
     *
     * @return $this.
     */
    public function rememberValues()
    {
        $this->rememberValues = true;
        return $this;
    }




    /**
     * Get value of a form element
     *
     * @param string $name the name of the formelement.
     *
     * @return mixed the value of the element.
     */
    public function value($name)
    {
        return isset($this->elements[$name]) ? $this->elements[$name]->value() : null;
    }



    /**
     * Check if a element is checked
     *
     * @param string $name the name of the formelement.
     *
     * @return mixed the value of the element.
     */
    public function checked($name)
    {
        return isset($this->elements[$name]) ? $this->elements[$name]->checked() : null;
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
        $options = $this->formhelper->getoptions($options);
        $form = array_merge($this->form, $options);

        $elementsArray  = $this->getHTMLForElements($options);
        $elements       = $this->getHTMLLayoutForElements($elementsArray, $options);
        $output         = $this->getOutput();

        return $this->formhelper->htmlhelper($form, $options, $elements, $output);
    }



    /**
    * @param string $key - key and value pair in list
    * @param object $element - value in pair
    *
    * @return string $html for the form
    */
    public function elementhtml($key, $element)
    {
        $html = "<p class='buttonbar'>\n" . $element->GetHTML() . '&nbsp;';

        while (list($key, $element) = each($this->elements)) {
            if (in_array($element['type'], array('submit', 'reset', 'button'))) {
                $html .= $element->GetHTML();
            } else {
                prev($this->elements);
                break;
            }
        }
        return $html . "\n</p>";
    }



    /**
     * Return HTML for the elements
     *
     * @param array $options with options affecting the form output.
     *
     * @return array with HTML for the formelements.
     */
    public function getHTMLForElements($options = [])
    {
        $defaults = [
            'use_buttonbar' => true,
        ];
        $options = array_merge($defaults, $options);

        $elements = array();
        reset($this->elements);
        while (list($key, $element) = each($this->elements)) {
            if (in_array($element['type'], array('submit', 'reset', 'button')) && $options['use_buttonbar']) {
                $name = 'buttonbar';
                $html = $this->elementhtml($key, $element);
            } else {
                $name = $element['name'];
                $html = $element->GetHTML();
            }

            $elements[] = array('name'=>$name, 'html'=> $html);
        }

        return $elements;
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
        $options = $this->formhelper->layoutoptions($options);
        $html = null;
        if ($options['columns'] === 1) {
            foreach ($elements as $element) {
                $html .= $element['html'];
            }
        }

        return $html;
    }



    /**
     * Get an array with all elements that failed validation together with their id and validation message.
     *
     * @return array with elements that failed validation.
     */
    public function getValidationErrors()
    {
        $errors = [];
        foreach ($this->elements as $name => $element) {
            if ($element['validation-pass'] === false) {
                $errors[$name] = [
                    'id' => $element->GetElementId(),
                    'label' => $element['label'],
                    'message' => implode(' ', $element['validation-messages'])
                ];
            }
        }
        return $errors;
    }



    /**
     * Get output messages as <output>.
     *
     * @return string|null with the complete <output> element or null if no output.
     */
    public function getOutput()
    {
            return $this->formhelper->outputhelper($this->output);
    }



    /**
     * Init all element with values from session, clear all and fill in with values from the session.
     *
     * @param array $values retrieved from session
     *
     * @return void
     */
    protected function initElements($values)
    {
        foreach ($this->elements as $key => $val) {
            if (in_array($this[$key]['type'], array('submit', 'reset', 'button'))) {
                continue;
            }

            $this[$key]['value'] = null;

            if (isset($this[$key]['checked'])) {
                $this[$key]['checked'] = false;
            }
        }

        foreach ($values as $key => $val) {
            $keyarray = $this[$key];
            $this[$key] = $this->formhelper->inithelper($keyarray, $val);
        }
    }




    /**
     * @param di-connection $request
     * @return boolean $validates
     */
    public function postElementFill($postElement, $element)
    {
        $validates = true;
        $this->values = $this->formhelper->fillValArr($postElement, $element);
        $element = $this->formhelper->updateElement($element);

        $this->values = $this->formhelper->doValidation($element, $this->values);
        $this->validates = $this->formhelper->getValidate();

        if (isset($element['remember']) && $element['remember']) {
            $this->values[$element['name']] = ['value' => $element['value']];
            $remember = true;
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
    }




    /**
     * @param di-connection $request
     * @return boolean $validates
     */
    public function noPostElement($element)
    {
        if ($element['type'] === 'checkbox'
            || $element['type'] === 'checkbox-multiple'
        ) {
            $element['checked'] = false;
        }
        $this->values = $this->formhelper->doValidation($element, $this->values);
        $this->validates = $this->formhelper->getValidate();
    }




    /**
     * @param di-connection $request
     * @return boolean $validates
     */
    public function elementworker($request)
    {
        foreach ($this->elements as $element) {
            $postElement = $request->getPost($element['name']);
            $postElement ? $this->postElementFill($postElement, $element) : $this->noPostElement($element);
        }
    }



    /**
     * Check if a form was submitted and perform validation and call callbacks.
     * The form is stored in the session if validation or callback fails. The
     * page should then be redirected to the original form page, the form
     * will populate from the session and should be rendered again.
     * Form elements may remember their value if 'remember' is set and true.
     *
     * @param callable $callIfSuccess handler to call if function returns true.
     * @param callable $callIfFail    handler to call if function returns true.
     *
     * @return boolean|null $callbackStatus if submitted&validates, false if
     *                                      not validate, null if not submitted.
     *                                      If submitted the callback function
     *                                      will return the actual value which
     *                                      should be true or false.
     */
    public function check($callIfSuccess = null, $callIfFail = null)
    {
        $remember = null;
        $this->callbackStatus = null;
        $this->values = [];

        $output = $this->sessionKey["output"];
        $session = $this->di->get("session");
        $this->output = $session->getOnce($output, []);

        $requestMethod = $this->di->get("request")->getServer("REQUEST_METHOD");
        if ($requestMethod !== "POST") {

            $failed   = $this->sessionKey["failed"];
            $remember = $this->sessionKey["remember"];
            $save     = $this->sessionKey["save"];

            $session->has($failed) || $session->has($save) ? $this->InitElements($this->formhelper->notPosted($failed, $save, $session)) : "";
            
            if ($session->has($remember)) {
                foreach ($session->getOnce($remember) as $key => $val) {
                    $this[$key]['value'] = $val['value'];
                }
            }

            return null;
        }

        $request = $this->di->get("request");
        $formid = $request->getPost("anax/htmlform-id");
        if (!$formid || $this->form["id"] !== $formid) {
            return null;
        }
        $validates = $this->elementworker($request);

        $ret = $this->formhelper->retresult([$callIfSuccess, $callIfFail, $this->callbackStatus], $validates, [$this->sessionKey["failed"], $this->sessionKey["remember"], $this->sessionKey["save"], $this->rememberValues], $this->values);
        $this->values = $this->formhelper->getValues();
        return $ret;
    }
}
