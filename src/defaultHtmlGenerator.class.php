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

    public function defaults($settings)
    {
        $this->defaultSettings = $settings;
    }

    /**
     * @param $settings array
     * @param $attrName string
     *
     * @return null
     */
    public function echoAttribute($settings, $attrName)
    {
        $theSettings = array_merge($this->defaultSettings,$settings);
        if (isset($theSettings[$attrName])) {
            echo ' ' . $attrName . '="' . $theSettings[$attrName] . '"';
        }
    }

    /**
     * @param $settings
     *
     * @return string $fieldName
     */
    public function echoCoreAttributes($settings)
    {

        $theSettings = array_merge($this->defaultSettings,$settings);

        if (isset($theSettings['name'])) {
            $fieldName = $theSettings['name'];
        } else {
            $fieldName = $theSettings['field_name'];
        }
        echo ' name="' . $fieldName . '"';

        $this->echoAttribute($theSettings, 'id');

        $this->echoAttribute($theSettings, 'disabled');

        if (isset($theSettings['class'])) {
            echo ' class="' . $theSettings['class'] . '"';
        } else {
            if ((!isset($theSettings['type'])) || ($theSettings['type'] !== 'hidden')) {
                echo ' class="text ui-widget-content ui-corner-all"';
            }
        }

        $this->echoAttribute($theSettings, 'style');

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
        $theSettings = array_merge($this->defaultSettings,$settings);

        // did the developer provide a specific hint?
        $this->hint($theSettings);

        // regardless if a specific hint, should we display one based on the $vars model in the template?
        if (isset($theSettings['showhints']) && ($theSettings['showhints'] == true)) {
            $hintVarName = $varName . '_hint'; // cleaner to look at below
            if (isset(template::getInstance()->vars[$hintVarName]) && (!empty(template::getInstance()->vars[$hintVarName]))) {
                $this->hint(array_merge($theSettings, array(
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
        $theSettings = array_merge($this->defaultSettings,$settings);

        // draw the label first
        if (isset($theSettings['label'])) {
            $this->label($theSettings);
        }

        echo '<textarea';
        $this->echoAttribute($theSettings, 'rows');
        $this->echoAttribute($theSettings, 'cols');
        $varName = $this->echoCoreAttributes($theSettings);
        echo '>';
        echo $this->varValue($theSettings, $varName);
        echo '</textarea>'. PHP_EOL;

        $this->echoHints($theSettings, $varName);
        $this->clearAtEnd($theSettings);
    }


    /**
     * @param $settings
     *
     * @return null
     */
    public function field($settings)
    {
        $theSettings = array_merge($this->defaultSettings,$settings);
        // draw the label first
        if (isset($theSettings['label'])) {
            $this->label($theSettings);
        }

        echo '<input';

        if (isset($theSettings['type'])) {
            echo ' type="' . $theSettings['type'] . '"';
        } else {
            echo ' type="text"';
        }
        $varName = $this->echoCoreAttributes($theSettings);

        echo ' value="' . $this->varValue($theSettings, $varName) . '"';

        if (isset($theSettings['type']) && ($theSettings['type'] == 'checkbox')) {
            if ($this->varValue($theSettings, $varName) == true) {
                echo ' checked="checked"';
            }
        }

        // some people might belly ache about this, but I rarely find a need to specify size differently than maxlength.
        // you can change it if you like, obviously.
        if (isset($theSettings['maxlength'])) {
            echo ' size="' . $theSettings['maxlength'] . '"';
            echo ' maxlength="' . $theSettings['maxlength'] . '"';
        }

        echo '>'. PHP_EOL;

        $this->echoHints($theSettings, $varName);
        $this->clearAtEnd($theSettings);
    }

    /**
     * @param $settings
     *
     * @return null
     */
    public function select($settings)
    {
        $theSettings = array_merge($this->defaultSettings,$settings);
        // draw the label first
        if (isset($theSettings['label'])) {
            $this->label($theSettings);
        }

        // I used to use class="ui-selectmenu ui-selectmenu-menu-dropdown ui-widget ui-state-default ui-corner-all"'
        // keeping this comment here for quick reference is all.

        echo '<select';
        if (isset(template::getInstance()->vars['class'])) {
            $varName = $this->echoCoreAttributes($theSettings);
        } else {
            $varName = $this->echoCoreAttributes(array_merge($theSettings,array('class'=>'text ui-widget ui-corner-all')));
        }
        echo '>';

        if (isset($theSettings['addfirst']) && ($theSettings['addfirst'] == true)) {
            if (isset($theSettings['selected'])) {

                if (empty($theSettings['selected'])) {
                    echo '<option value="" selected="selected">Select one...</option>'.PHP_EOL;
                } else {
                    echo '<option value="">Select one...</option>'. PHP_EOL;
                }

            } else {

                if (empty($theSettings['table']->$varName)) {
                    echo '<option value="" selected="selected">Select one...</option>'. PHP_EOL;
                } else {
                    echo '<option value="">Select one...</option>'. PHP_EOL;
                }

            }
        }

        foreach ($theSettings['choices'] as $key => $value) {

            echo '<option value="' . $key . '"';
            if (isset($theSettings['selected'])) {
                if ($key == $theSettings['selected']) {
                    echo ' selected="selected"';
                }
            } else {
                if (isset($theSettings['table'])) {
                    if ($key == $theSettings['table']->$theSettings['field_name']) {
                        echo ' selected="selected"';
                    }
                }
            }

            echo '>' . $value . '</option>'. PHP_EOL;
        }

        echo '</select>'. PHP_EOL;
        $this->clearAtEnd($theSettings);

    }

    /**
     * @param $settings
     *
     * @return null
     */
    public function hint($settings)
    {
        $theSettings = array_merge($this->defaultSettings,$settings);

        // this is hokey, but it should keep things lined up when labels are left of input cells.
        if (isset($theSettings['label'])) {
            $this->label(array_merge($theSettings,array('label'=>'')));
        }


        if (isset($theSettings['hint']) && (!empty($theSettings['hint']))) {
            echo '<span';
            // $this->echoAttribute($theSettings, 'name');
            // $this->echoAttribute($theSettings, 'id');
            if (isset($theSettings['class'])) {
                echo ' class="' . $theSettings['class'] . '"';
            } else {
                echo ' class="hint"';
            }
            if (isset($theSettings['hint_style'])) {
                echo ' style="' . $theSettings['hint_style'] . '"';
            } else {
                $this->echoAttribute($theSettings, 'style');
            }

            echo '>' . $theSettings['hint'] . '</span>'. PHP_EOL;
            $this->clearAtEnd($theSettings);

        }
    }


    public function label($settings)
    {
        $theSettings = array_merge($this->defaultSettings,$settings);
        echo '<label';

        if (isset($theSettings['name'])) {
            echo ' for="' . $theSettings['name'] . '"';
        } else {
            echo ' for="' . $theSettings['field_name'] . '"';
        }

        if (isset($theSettings['id'])) {
            echo ' id="' . $theSettings['id'] . '"';
        }

        if (isset($theSettings['disabled'])) {
            echo ' disabled="disabled"';
        }

        if (isset($theSettings['label_class'])) {
            echo ' class="' . $theSettings['label_class'] . '"';
        } else {
            if (isset($theSettings['class'])) {
                echo ' class="'. $theSettings['class'] . '"';
            } else {
                echo ' class="text"';
            }
        }

        if (isset($theSettings['label_style'])) {
            echo ' style="' . $theSettings['label_style'] . '"';
        } else {
            if (isset($theSettings['style'])) {
                echo ' style="' . $theSettings['style'] . '"';
            }
        }

        echo '>' . $theSettings['label'] . '</label>'. PHP_EOL;
        $this->clearAtEnd($theSettings);

    }


    protected function varValue($theSettings, $varName)
    {

        // was it specifically set?
        if (isset($theSettings['value'])) {
            $value = $theSettings['value'];
        };

        // is it in the $_POST array?
        if (!isset($value)) {
            if (isset($theSettings['checkpostvars']) && ($theSettings['checkpostvars'] == true)) {
                if (isset($_POST[$varName])) {
                    $value = $_POST[$varName];
                }
            }
        }

        // is it in the template array?
        if (!isset($value)) {
            if (isset($theSettings['checktemplatevars']) && ($theSettings['checktemplatevars'] == true)) {
                if (isset(template::getInstance()->vars[$varName]) == true) {
                    $value = template::getInstance()->vars[$varName];
                }
            }
        }

        // ok... give up and take it from whatever database it was supposed to be in
        if (!isset($value)) {
            $value = $settings['table']->$theSettings['field_name'];
        }

        return $value;

    }

    protected function clearAtEnd($theSettings)
    {
        if ( (isset($theSettings['clearAtEnd'])==true) && ($theSettings['clearAtEnd']==true) ) {
            echo '<div style="clear: both;"></div>'. PHP_EOL;
        }
    }


}

?>