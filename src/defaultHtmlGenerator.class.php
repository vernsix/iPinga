<?php
/*
    Vern Six MVC Framework version 3.0

    Copyright (c) 2007-2015 by Vernon E. Six, Jr.
    Author's websites: http://www.ipinga.com and http://www.VernSix.com

    Permission is hereby granted, free of charge, to any person obtaining a copy
    of this software and associated documentation files (the "Software"), to use
    the Software without restriction, including without limitation the rights
    to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
    copies of the Software, and to permit persons to whom the Software is
    furnished to do so, subject to the following conditions:

    The above copyright notice, author's websites and this permission notice
    shall be included in all copies or substantial portions of the Software.

    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
    IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
    FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
    AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
    LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
    FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
    IN THE SOFTWARE.
*/
namespace ipinga;

Class defaultHtmlGenerator extends \ipinga\htmlGenerator
{

    /**
     * @param $settings array
     * @param $attrName string
     *
     * @return null
     */
    public function echoAttribute($settings, $attrName)
    {
        if (isset($settings[$attrName])) {
            echo ' ' . $attrName . '="' . $settings[$attrName] . '"';
        }
    }

    /**
     * @param $settings
     *
     * @return string $fieldName
     */
    public function echoCoreAttributes($settings)
    {

        if (isset($settings['name'])) {
            $fieldName = $settings['name'];
        } else {
            $fieldName = $settings['field_name'];
        }
        echo ' name="' . $fieldName . '"';

        $this->echoAttribute($settings, 'id');

        $this->echoAttribute($settings, 'disabled');

        if (isset($settings['class'])) {
            echo ' class="' . $settings['class'] . '"';
        } else {
            if ((!isset($settings['type'])) || ($settings['type'] !== 'hidden')) {
                echo ' class="text ui-widget-content ui-corner-all"';
            }
        }

        $this->echoAttribute($settings, 'style');

        return $fieldName;

    }

    /**
     * @param $settings
     * @param $varName
     *
     * @return null
     */
    public function echoHints($settings, $varName)
    {
        // did the developer provide a specific hint?
        if (isset($settings['hint'])) {
            $this->hint($settings['hint']);
        }

        // regardless if a specific hint, should we display one based on the $vars model in the template?
        if (isset($settings['showhints']) && ($settings['showhints'] == true)) {
            $hintVarName = $varName . '_hint'; // cleaner to look at below
            if (isset(template::getInstance()->vars[$hintVarName]) && (!empty(template::getInstance()->vars[$hintVarName]))) {
                $this->hint(array_merge($settings, array(
                        'hint' => template::getInstance()->vars[$hintVarName]
                    )
                ));
            }
        }
    }

    /**
     * @param $settings
     *
     * @return null
     */
    public function textarea($settings)
    {
        // draw the label first
        if (isset($settings['label'])) {
            $this->label($settings);
        }

        echo '<textarea';
        $this->echoAttribute($settings, 'rows');
        $this->echoAttribute($settings, 'cols');
        $varName = $this->echoCoreAttributes($settings);
        echo '>';
        echo $this->varValue($settings, $varName);
        echo '</textarea>';

        $this->echoHints($settings, $varName);
    }


    /**
     * @param $settings
     *
     * @return null
     */
    public function field($settings)
    {
        // draw the label first
        if (isset($settings['label'])) {
            $this->label($settings);
        }

        echo '<input';

        if (isset($settings['type'])) {
            echo ' type="' . $settings['type'] . '"';
        } else {
            echo ' type="text"';
        }
        $varName = $this->echoCoreAttributes($settings);

        echo ' value="' . $this->varValue($settings, $varName) . '"';

        if (isset($settings['type']) && ($settings['type'] == 'checkbox')) {
            if ($this->varValue($settings, $varName) == true) {
                echo ' checked="checked"';
            }
        }

        // some people might belly ache about this, but I rarely find a need to specify size differently than maxlength.
        // you can change it if you like, obviously.
        if (isset($settings['maxlength'])) {
            echo ' size="' . $settings['maxlength'] . '"';
            echo ' maxlength="' . $settings['maxlength'] . '"';
        }

        echo '>';

        $this->echoHints($settings, $varName);
    }

    /**
     * @param $settings
     *
     * @return null
     */
    public function select($settings)
    {
        // draw the label first
        if (isset($settings['label'])) {
            $this->label($settings);
        }

        // I used to use class="ui-selectmenu ui-selectmenu-menu-dropdown ui-widget ui-state-default ui-corner-all"'
        // keeping this comment here for quick reference is all.

        echo '<select';
        if (isset(template::getInstance()->vars['class'])) {
            $varName = $this->echoCoreAttributes($settings);
        } else {
            $varName = $this->echoCoreAttributes(array_merge($settings,array('class'=>'text ui-widget ui-corner-all')));
        }
        echo '>';

        if (isset($settings['addfirst']) && ($settings['addfirst'] == true)) {
            if (isset($settings['selected'])) {

                if (empty($settings['selected'])) {
                    echo '<option value="" selected="selected">Select one...</option>';
                } else {
                    echo '<option value="">Select one...</option>';
                }

            } else {

                if (empty($settings['table']->$settings['field_name'])) {
                    echo '<option value="" selected="selected">Select one...</option>';
                } else {
                    echo '<option value="">Select one...</option>';
                }

            }
        }

        foreach ($settings['choices'] as $key => $value) {

            echo '<option value="' . $key . '"';
            if (isset($settings['selected'])) {
                if ($key == $settings['selected']) {
                    echo ' selected="selected"';
                }
            } else {
                if (isset($settings['table'])) {
                    if ($key == $settings['table']->$settings['field_name']) {
                        echo ' selected="selected"';
                    }
                }
            }

            echo '>' . $value . '</option>';
        }

        echo '</select>';
    }

    /**
     * @param $settings
     *
     * @return null
     */
    public function hint($settings)
    {
        if (isset($settings['hint']) && (!empty($settings['hint']))) {

            echo '<span';

            $this->echoAttribute($settings, 'name');

            $this->echoAttribute($settings, 'id');

            if (isset($settings['class'])) {
                echo ' class="' . $settings['class'] . '"';
            } else {
                echo ' class="hint"';
            }

            $this->echoAttribute($settings, 'style');

            echo '>' . $settings['hint'] . '</span>';

        }
    }


    public function label($settings)
    {
        echo '<label';

        if (isset($settings['name'])) {
            echo ' for="' . $settings['name'] . '"';
        } else {
            echo ' for="' . $settings['field_name'] . '"';
        }

        if (isset($settings['id'])) {
            echo ' id="' . $settings['id'] . '"';
        }

        if (isset($settings['disabled'])) {
            echo ' disabled="disabled"';
        }

        if (isset($settings['label_class'])) {
            echo ' class="' . $settings['label_class'] . '"';
        } else {
            if (isset($settings['class'])) {
                echo ' class="'. $settings['class'] . '"';
            } else {
                echo ' class="text"';
            }
        }

        if (isset($settings['label_style'])) {
            echo ' style="' . $settings['label_style'] . '"';
        } else {
            if (isset($settings['style'])) {
                echo ' style="' . $settings['style'] . '"';
            }
        }

        echo '>' . $settings['label'] . '</label>';
    }


    protected function varValue($settings, $varName)
    {

        // was it specifically set?
        if (isset($settings['value'])) {
            $value = $settings['value'];
        };

        // is it in the $_POST array?
        if (!isset($value)) {
            if (isset($settings['checkpostvars']) && ($settings['checkpostvars'] == true)) {
                if (isset($_POST[$varName])) {
                    $value = $_POST[$varName];
                }
            }
        }

        // is it in the template array?
        if (!isset($value)) {
            if (isset($settings['checktemplatevars']) && ($settings['checktemplatevars'] == true)) {
                if (isset(template::getInstance()->vars[$varName]) == true) {
                    $value = template::getInstance()->vars[$varName];
                }
            }
        }

        // ok... give up and take it from whatever database it was supposed to be in
        if (!isset($value)) {
            $value = $settings['table']->$settings['field_name'];
        }

        return $value;

    }

}

?>