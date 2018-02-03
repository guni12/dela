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
     */
    protected $form;
    protected $elements;
    protected $output;
    protected $sessionKey;

    /**
     * @var boolean $rememberValues remember values in the session.
     */
    protected $rememberValues;

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
        return isset($this->elements[$offset])
            ? $this->elements[$offset]
            : null;
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
        $key     = $this->sessionKey["output"];
        $session = $this->di->get("session");
        $output  = $session->get($key);

        $output["message"] = isset($output["message"])
            ? $output["message"] . " $str"
            : $str;

        if ($class) {
            $output["class"] = $class;
        }
        $session->set($key, $output);

        return $this;
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
        $key     = $this->sessionKey["output"];
        $session = $this->di->get("session");
        $output  = $session->get($key);
        $output["class"] = $class;
        $session->set($key, $output);
        return $this;
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
        return isset($this->elements[$name])
            ? $this->elements[$name]->value()
            : null;
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
        return isset($this->elements[$name])
            ? $this->elements[$name]->checked()
            : null;
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

        $elementsArray  = $this->getHTMLForElements($options);
        $elements       = $this->getHTMLLayoutForElements($elementsArray, $options);
        $output         = $this->getOutput();

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
            if (in_array($element['type'], array('submit', 'reset', 'button'))
                && $options['use_buttonbar']
            ) {
                // Create a buttonbar
                $name = 'buttonbar';
                $html = "<p class='buttonbar'>\n" . $element->GetHTML() . '&nbsp;';

                // Get all following submits (and buttons)
                while (list($key, $element) = each($this->elements)) {
                    if (in_array($element['type'], array('submit', 'reset', 'button'))) {
                        $html .= $element->GetHTML();
                    } else {
                        prev($this->elements);
                        break;
                    }
                }
                $html .= "\n</p>";
            } else {
                // Just add the element
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
        $output = $this->output;
        $message = isset($output["message"]) && !empty($output["message"])
            ? $output["message"]
            : null;

        $class = isset($output["class"]) && !empty($output["class"])
            ? " class=\"{$output["class"]}\""
            : null;

        return $message
            ? "<output{$class}>{$message}</output>"
            : null;
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
            if (isset($val['values'])) {
                $this[$key]['checked'] = $val['values'];
            } elseif (isset($val['value'])) {
                $this[$key]['value'] = $val['value'];
            }

            if ($this[$key]['type'] === 'checkbox') {
                $this[$key]['checked'] = true;
            } elseif ($this[$key]['type'] === 'radio') {
                $this[$key]['checked'] = $val['value'];
            }

            if (isset($val['validation-messages'])) {
                $this[$key]['validation-messages'] = $val['validation-messages'];
                $this[$key]['validation-pass'] = false;
            }
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
     * @throws \Anax\HTMLForm\Exception
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
        $validates = null;
        $callbackStatus = null;
        $values = [];

        $output = $this->sessionKey["output"];
        $session = $this->di->get("session");
        $this->output = $session->getOnce($output, []);

        $requestMethod = $this->di->get("request")->getServer("REQUEST_METHOD");
        if ($requestMethod !== "POST") {

            $failed   = $this->sessionKey["failed"];
            $remember = $this->sessionKey["remember"];
            $save     = $this->sessionKey["save"];

            $session->has($failed) || $session->has($save) ? $this->InitElements($this->formhelper->notPosted($failed, $save)) : "";
            
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

        $session->delete($this->sessionKey["failed"]);
        $validates = true;
        foreach ($this->elements as $element) {
            $elementName = $element['name'];
            $elementType = $element['type'];

            $postElement = $request->getPost($elementName);
            if ($postElement) {
                if (is_array($postElement)) {
                    $values[$elementName]['values'] = $element['checked'] = $postElement;
                } else {
                    $values[$elementName]['value'] = $element['value'] = $postElement;
                }

                if ($elementType === 'password') {
                    $values[$elementName]['value'] = null;
                }

                if ($elementType === 'checkbox') {
                    $element['checked'] = true;
                }

                if ($elementType === 'radio') {
                    $element['checked'] = $element['value'];
                }
                $validates = $this->formhelper->doValidation($element);

                if (isset($element['remember'])
                    && $element['remember']
                ) {
                    $values[$elementName] = ['value' => $element['value']];
                    $remember = true;
                }

                if (isset($element['callback'])
                    && $validates
                ) {
                    if (isset($element['callback-args'])) {
                        $callbackStatus = call_user_func_array(
                            $element['callback'],
                            array_merge([$this]),
                            $element['callback-args']
                        );
                    } else {
                        $callbackStatus = call_user_func($element['callback'], $this);
                    }
                }
            } else {
                if ($element['type'] === 'checkbox'
                    || $element['type'] === 'checkbox-multiple'
                ) {
                    $element['checked'] = false;
                }
                $validates = $this->formhelper->doValidation($element);
            }
        }

        if ($validates === false
            || $callbackStatus === false
        ) {
            $session->set($this->sessionKey["failed"], $values);
        } elseif ($remember) {
            $session->set($this->sessionKey["remember"], $values);
        }

        if ($this->rememberValues) {
            // Remember all posted values
            $session->set($this->sessionKey["save"], $values);
        }

        $ret = $validates
            ? $callbackStatus
            : $validates;


        if ($ret === true && isset($callIfSuccess)) {
            if (!is_callable($callIfSuccess)) {
                throw new Exception("Form, success-method is not callable.");
            }
            call_user_func_array($callIfSuccess, [$this]);
        } elseif ($ret === false && isset($callIfFail)) {
            if (!is_callable($callIfFail)) {
                throw new Exception("Form, success-method is not callable.");
            }
            call_user_func_array($callIfFail, [$this]);
        }

        return $ret;
    }
}
