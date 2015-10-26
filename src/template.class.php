<?php
namespace iPinga;

/**
 * @throws Exception
 * @property string        $skin
 * @property int           $status
 * @property string        $message
 * @property array         $ajax_array
 * @property v6_user_table $user
 * @property v6_table      $client
 * @property bool          $is_admin
 * @property bool          $is_almighty
 * @property string        $droppage
 * @property string        $menu_html
 * @property v6_manager    $mgr
 * @property array         $header
 * @property array         $json
 * @property v6_acl        $acl
 * @property string         $title
 */
Class template
{

    public $vars = array();
    public $showViewFilename = false;

    public function __construct()
    {
        // set some defaults
        $this->vars['skin'] = 'cupertino';
        $this->vars['header'] = array();
        $this->vars['json'] = array();
    }

    /**
     * Add vars to the templateset undefined vars
     *
     * @param string $index
     * @param mixed  $value
     *
     * @return void
     */
    public function __set($index, $value)
    {
        $this->vars[$index] = $value;
    }

    public function &__get($index)
    {
        if (isset($this->vars[$index]) == true) {
            return $this->vars[$index];
        } else {
            throw new Exception('Unknown var name in template: ' . $index);
        }

    }


    /**
     * @param string $filename
     * @param null   $newvars
     *
     * @throws \Exception
     */
    public function show($filename, &$newvars = NULL)
    {

        if (is_array($newvars) == true) {
            array_merge($this->vars,$newvars);
        }

        $iPinga = \iPinga\iPinga::getInstance();

        $viewFilename = $iPinga->config('path.views') . '/' . $filename . '.view.php';

        if ($this->showViewFilename == true) {
            echo '<!-- ' . $viewFilename . ' -->' . PHP_EOL . PHP_EOL;
        }

        if (file_exists($viewFilename) == false) {
            throw new \Exception('View not found: ' . $viewFilename);
        }

        // Load variables so template code has easier access
        foreach ($this->vars as $key => $value) {
            $$key = $value;
        }

        // if sysmaint.view.php exists, then the system is down for maintenance and I should show that view instead
        $sysmaint = $iPinga->config('path.views') . '/sysmaint.view.php';
        if (file_exists($sysmaint) == true) {
            include_once($sysmaint);
        } else {
            include_once($viewFilename);
        }

    }   // show


    /**
     * @param $filename
     *
     * @throws \Exception
     */
    public function include_file($filename)
    {
        $iPinga = \iPinga\iPinga::getInstance();

        $fullFilename = $iPinga->config('path.views') . '/' . $filename . '.php';
        if (file_exists($fullFilename) == false) {
            throw new \Exception('View not found in ' . $fullFilename);
        }

        // Load variables so template code has easier access. This is redundant so it does cause a slight performance hit, but not much.
        foreach ($this->vars as $key => $value) {
            $$key = $value;
        }

        include_once($fullFilename);
    }


    /**********************************************************************************************************
     * the following is all the code for creating html from arrays, etc
     *********************************************************************************************************/

    public function echo_attr($v, $attr_name)
    {
        if (isset($v[$attr_name])) {
            echo ' ' . $attr_name . '="' . $v[$attr_name] . '"';
        }
    }

    public function echo_core_attrs($v)
    {

        if (isset($v['name'])) {
            echo ' name="' . $v['name'] . '"';
            $_var = $v['name'];
        } else {
            echo ' name="' . $v['field_name'] . '"';
            $_var = $v['field_name'];
        }

        $this->echo_attr($v, 'id');
        $this->echo_attr($v, 'disabled');

        if (isset($v['class'])) {
            echo ' class="' . $v['class'] . '"';
        } else {
            if ((!isset($v['type'])) || ($v['type'] !== 'hidden')) {
                echo ' class="text ui-widget-content ui-corner-all"';
            }
        }
        $this->echo_attr($v, 'style');
        return $_var;

    }

    public function echo_hints($v, $_var)
    {

        if (isset($v['hint'])) {
            $this->form_hint($v['hint']);
        }

        if (isset($v['showhints']) && ($v['showhints'] == true)) {
            if (isset($this->vars[$_var . '_hint']) && (!empty($this->vars[$_var . '_hint']))) {
                $this->form_hint(array(
                    'hint' => $this->vars[$_var . '_hint']
                ));
            }
        }

    }


    /*

    $v is an array of key-value pairs.  Each of the form_* functions uses these key-value pairs
    to display itself on the vistor's browser.   Below is a list of all the possible key-value
    pairs and which functions can use them.


Most

    checkpostvars
        boolean: If no value is set for the field, and this value is set to true, then
        the system will look at the $_POST vars to get a value.  If none is set there, then
        checktemplatevars is considered.

    checktemplatevars
        boolean: If no value is set for the field, and this value is set to true, then the
        system will see if you have set the variable in template variables.  If none is set there,
        then the table and field_name are used to get a value for the field.

    class
        string: html class value. default: "text ui-widget-content ui-corner-all" for
        everything but form_select() which has a default of "text ui-widget ui-corner-all"

    disabled
        string: html disabled value. default: none

    field_name
        string: (required) name of the database field.

    hint
        array: key-value pairs that define a hint.  default: none

    id
        string: html id value. default: none

    label
        String: label text

    label_class
        String: html class string for the label. default: 'text'

    label_style
        String: html style string for the label. default: none

    name
        string: html name value. default: same as field_name key-value pair.
        name overrides field_name as the html name value if specified

    showhints
        boolean: show input hints? if a variable is named "whatever" then there would
        need to be another variable named "whatever_hint" in order for showhints to fire

    style
        string: html stryle value. default: none

    table
        v6_table(): (required) an instance of a v5_table() object where the data is coming from

    value
        string: value to display in the input element.  If none is specified, then the default
        behavior defined in this order: checkpostvars, checktemplatevars, database



form_field

    maxlength
        integer: html size and maxlength value. default: none

    type
        string: html type value. default: "text"



form_textarea

    rows
        Integer: html rows value. default: none

    cols
        Integer: html cols value. default: none



form_select

    addfirst
        Boolean: true to add the option "Select One..." to the beginning of the options.

    selected
        String: determines which VALUE is selected

    choices
        array: key-value pair of choices for the select



form_hint

    hint
        string: string to be displayed as a hint to the input field.

    name, id, class, style are the same as above, but must be in the array here as well.



form_label


    */

    public function form_textarea($v)
    {
        // draw the label first
        if (isset($v['label'])) {
            $this->form_label($v);
        }

        echo '<textarea';
        $this->echo_attr($v, 'rows');
        $this->echo_attr($v, 'cols');
        $_var = $this->echo_core_attrs($v);
        echo '>';
        echo $this->_value($v, $_var);
        echo '</textarea>';

        $this->echo_hints($v, $_var);

    }

    public function form_field($v)
    {
        // draw the label first
        if (isset($v['label'])) {
            $this->form_label($v);
        }

        echo '<input';
        if (isset($v['type'])) {
            echo ' type="' . $v['type'] . '"';
        } else {
            echo ' type="text"';
        }

        $_var = $this->echo_core_attrs($v);

        echo ' value="' . $this->_value($v, $_var) . '"';

        if (isset($v['type']) && ($v['type'] == 'checkbox')) {
            if ($this->_value($v, $_var) == true) {
                echo ' checked="checked"';
            }
        }

        if (isset($v['maxlength'])) {
            echo ' size="' . $v['maxlength'] . '"';
            echo ' maxlength="' . $v['maxlength'] . '"';
        }
        echo '>';

        $this->echo_hints($v, $_var);

    }

    public function form_select($v)
    {
        // draw the label first
        if (isset($v['label'])) {
            $this->form_label($v);
        }

        echo '<select';
        if (isset($v['name'])) {
            echo ' name="' . $v['name'] . '"';
        } else {
            echo ' name="' . $v['field_name'] . '"';
        }
        if (isset($v['id'])) {
            echo ' id="' . $v['id'] . '"';
        }

        if (isset($v['disabled'])) {
            echo ' disabled="disabled"';
        }

        if (isset($v['class'])) {
            echo ' class="' . $v['class'] . '"';
        } else {
            echo ' class="text ui-widget ui-corner-all"';
            //echo ' class="ui-selectmenu ui-selectmenu-menu-dropdown ui-widget ui-state-default ui-corner-all"';
        }
        if (isset($v['style'])) {
            echo ' style="' . $v['style'] . '"';
        }
        echo '>';

        if (isset($v['addfirst']) && ($v['addfirst'] == true)) {
            if (isset($v['selected'])) {
                if (empty($v['selected'])) {
                    echo '<option value="" selected="selected">Select one...</option>';
                } else {
                    echo '<option value="">Select one...</option>';
                }
            } else {
                if (empty($v['table']->$v['field_name'])) {
                    echo '<option value="" selected="selected">Select one...</option>';
                } else {
                    echo '<option value="">Select one...</option>';
                }
            }
        }

        foreach ($v['choices'] as $key => $value) {
            echo '<option value="' . $key . '"';
            if (isset($v['selected'])) {
                if ($key == $v['selected']) {
                    echo ' selected="selected"';
                }
            } else {
                if (isset($v['table'])) {
                    if ($key == $v['table']->$v['field_name']) {
                        echo ' selected="selected"';
                    }
                }
            }

            echo '>' . $value . '</option>';
        }

        echo '</select>';

    }

    protected function _value($v, $_var)
    {
        // was it specifically set?
        if (isset($v['value'])) {
            $value = $v['value'];
        };

        // is it in the $_POST array?
        if (!isset($value)) {
            if (isset($v['checkpostvars']) && ($v['checkpostvars'] == true)) {
                if (isset($_POST[$_var])) {
                    $value = $_POST[$_var];
                }
            }
        }

        // is it in the template array?
        if (!isset($value)) {
            if (isset($v['checktemplatevars']) && ($v['checktemplatevars'] == true)) {
                if (isset($this->vars[$_var]) == true) {
                    $value = $this->vars[$_var];
                }
            }
        }

        // ok... give up and take it from whatever database it was supposed to be in
        if (!isset($value)) {
            $value = $v['table']->$v['field_name'];
        }
        return $value;
    }

    public function form_hint($v)
    {
        if (isset($v['hint']) && (!empty($v['hint']))) {
            echo '<span';
            $this->echo_attr($v, 'name');
            $this->echo_attr($v, 'id');
            if (isset($v['class'])) {
                echo ' class="' . $v['class'] . '"';
            } else {
                echo ' class="hint"';
            }
            $this->echo_attr($v, 'style');
            echo '>' . $v['hint'] . '</span>';
        }
    }


    public function form_label($v)
    {
        echo '<label';

        if (isset($v['name'])) {
            echo ' for="' . $v['name'] . '"';
        } else {
            echo ' for="' . $v['field_name'] . '"';
        }
        if (isset($v['id'])) {
            echo ' id="' . $v['id'] . '"';
        }
        if (isset($v['disabled'])) {
            echo ' disabled="disabled"';
        }

        if (isset($v['label_class'])) {
            echo ' class="' . $v['label_class'] . '"';
        } else {
            echo ' class="text"';
        }
        if (isset($v['label_style'])) {
            echo ' style="' . $v['label_style'] . '"';
        }
        echo '>' . $v['label'] . '</label>';
    }



}
?>