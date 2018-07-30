<?php
/*
    Vern Six MVC Framework version 3.0

    Copyright (c) 2007-2018 by Vernon E. Six, Jr.
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

    /*
     * Some devs complained that my $output change broke all their apps.  Ooops. So this is how you turn that
     * functionality on.
     */
    public $legacy = true;

    /*
     * Changed the behavior a bit.  Echo is no longer called within any of these functions.
     * You will need to echo the contents of $this->output instead
     */
    public $output = '';

    public function output($text = '')
    {
        $this->output .= $text;
        if ($this->legacy == true) {
            echo $this->output;
            $this->output = '';
        }
    }


    public $postLikeArray = array();

    public function __construct()
    {
        $this->postLikeArray = $_POST;
    }

    public function overridePostArray($newVars = null)
    {
        $return = $this->postLikeArray;
        if (isset($newVars) == true) {
            $this->postLikeArray = $newVars;
        }
        return $return;
    }


    public function defaults($settings)
    {
        $this->defaultSettings = $settings;
    }

    /**
     * @param $settings array
     * @param $attrName string
     */
    public function echoAttribute($settings, $attrName)
    {
        $theSettings = array_merge($this->defaultSettings, $settings);
        if (isset($theSettings[$attrName])) {
            $this->output(' ' . $attrName . '="' . $theSettings[$attrName] . '"');
        }
    }

    /**
     * @param $settings
     *
     * @return string $fieldName
     */
    public function echoCoreAttributes($settings)
    {

        $theSettings = array_merge($this->defaultSettings, $settings);

        if (isset($theSettings['name'])) {
            $fieldName = $theSettings['name'];
        } else {
            $fieldName = $theSettings['field_name'];
        }
        $this->output(' name="' . $fieldName . '"');

        $this->echoAttribute($theSettings, 'id');

        $this->echoAttribute($theSettings, 'disabled');

        if (isset($theSettings['class'])) {
            $this->output(' class="' . $theSettings['class'] . '"');
        } else {
            if ((!isset($theSettings['type'])) || ($theSettings['type'] !== 'hidden')) {
                $this->output(' class="text ui-widget-content ui-corner-all"');
            }
        }

        $this->echoAttribute($theSettings, 'style');

        return $fieldName;

    }

    /**
     * @param $settings
     * @param $varName
     */
    public function echoHints($settings, $varName)
    {
        $theSettings = array_merge($this->defaultSettings, $settings);

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
     */
    public function textarea($settings)
    {
        $theSettings = array_merge($this->defaultSettings, $settings);

        // draw the label first
        if (isset($theSettings['label'])) {
            $this->label($theSettings);
        }

        $this->output('<textarea');
        $this->echoAttribute($theSettings, 'rows');
        $this->echoAttribute($theSettings, 'cols');
        $varName = $this->echoCoreAttributes($theSettings);
        $this->output('>');
        $this->output($this->varValue($theSettings, $varName));
        $this->output('</textarea>' . PHP_EOL);

        $this->echoHints($theSettings, $varName);
        $this->clearAtEnd($theSettings);
    }


    /**
     * @param $settings
     */
    public function field($settings)
    {
        $theSettings = array_merge($this->defaultSettings, $settings);
        // draw the label first
        if (isset($theSettings['label'])) {
            $this->label($theSettings);
        }

        $this->output('<input');

        if (isset($theSettings['type'])) {
            $this->output(' type="' . $theSettings['type'] . '"');
        } else {
            $this->output(' type="text"');
        }
        $varName = $this->echoCoreAttributes($theSettings);

        $this->output(' value="' . $this->varValue($theSettings, $varName) . '"');

        if (isset($theSettings['type']) && ($theSettings['type'] == 'checkbox')) {
            if ($this->varValue($theSettings, $varName) == true) {
                $this->output(' checked="checked"');
            }
        }

        // some people might belly ache about this, but I rarely find a need to specify size differently than maxlength.
        // you can change it if you like, obviously.
        if (isset($theSettings['maxlength'])) {
            $this->output(' size="' . $theSettings['maxlength'] . '"');
            $this->output(' maxlength="' . $theSettings['maxlength'] . '"');
        }

        $this->output('>' . PHP_EOL);

        $this->echoHints($theSettings, $varName);
        $this->clearAtEnd($theSettings);
    }

    /**
     * @param $settings
     */
    public function select($settings)
    {
        $theSettings = array_merge($this->defaultSettings, $settings);
        // draw the label first
        if (isset($theSettings['label'])) {
            $this->label($theSettings);
        }

        // I used to use class="ui-selectmenu ui-selectmenu-menu-dropdown ui-widget ui-state-default ui-corner-all"'
        // keeping this comment here for quick reference is all.

        $this->output('<select');
        if (isset(template::getInstance()->vars['class'])) {
            $varName = $this->echoCoreAttributes($theSettings);
        } else {
            $varName = $this->echoCoreAttributes(array_merge($theSettings, array('class' => 'text ui-widget ui-corner-all')));
        }
        $this->output('>');

        if (isset($theSettings['addfirst']) && ($theSettings['addfirst'] == true)) {
            if (isset($theSettings['selected'])) {

                if (empty($theSettings['selected'])) {
                    $this->output('<option value="" selected="selected">Select one...</option>' . PHP_EOL);
                } else {
                    $this->output('<option value="">Select one...</option>' . PHP_EOL);
                }

            } else {

                if (empty($theSettings['table']->$varName)) {
                    $this->output('<option value="" selected="selected">Select one...</option>' . PHP_EOL);
                } else {
                    $this->output('<option value="">Select one...</option>' . PHP_EOL);
                }

            }
        }

        foreach ($theSettings['choices'] as $value => $description) {

            $this->output('<option value="' . $value . '"');
            if (isset($theSettings['selected'])) {
                if ($value == $theSettings['selected']) {
                    $this->output(' selected="selected"');
                }
            } else {
                if (isset($theSettings['table'])) {
                    if ($value == $theSettings['table']->$theSettings['field_name']) {
                        $this->output(' selected="selected"');
                    }
                }
            }

            $this->output('>' . $description . '</option>' . PHP_EOL);
        }

        $this->output('</select>' . PHP_EOL);
        $this->clearAtEnd($theSettings);
    }

    /**
     * @param $settings
     *
     * @return null
     */
    public function hint($settings)
    {
        $theSettings = array_merge($this->defaultSettings, $settings);

        if (isset($theSettings['hint']) && (!empty($theSettings['hint']))) {
            $this->output('<span');
            // $this->echoAttribute($theSettings, 'name');
            // $this->echoAttribute($theSettings, 'id');
            if (isset($theSettings['class'])) {
                $this->output(' class="' . $theSettings['class'] . '"');
            } else {
                $this->output(' class="hint"');
            }
            if (isset($theSettings['hint_style'])) {
                $this->output(' style="' . $theSettings['hint_style'] . '"');
            } else {
                $this->echoAttribute($theSettings, 'style');
            }

            $this->output('>' . $theSettings['hint'] . '</span>' . PHP_EOL);
            $this->clearAtEnd($theSettings);

        }
    }


    public function label($settings)
    {
        $theSettings = array_merge($this->defaultSettings, $settings);
        $this->output('<label');

        if (isset($theSettings['name'])) {
            $this->output(' for="' . $theSettings['name'] . '"');
        } else {
            $this->output(' for="' . $theSettings['field_name'] . '"');
        }

        if (isset($theSettings['id'])) {
            $this->output(' id="' . $theSettings['id'] . '"');
        }

        if (isset($theSettings['disabled'])) {
            $this->output(' disabled="disabled"');
        }

        if (isset($theSettings['label_class'])) {
            $this->output(' class="' . $theSettings['label_class'] . '"');
        } else {
            if (isset($theSettings['class'])) {
                $this->output(' class="' . $theSettings['class'] . '"');
            } else {
                $this->output(' class="text"');
            }
        }

        if (isset($theSettings['label_style'])) {
            $this->output(' style="' . $theSettings['label_style'] . '"');
        } else {
            if (isset($theSettings['style'])) {
                $this->output(' style="' . $theSettings['style'] . '"');
            }
        }

        $this->output('>' . $theSettings['label'] . '</label>' . PHP_EOL);
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
                if (isset($this->postLikeArray[$varName])) {
                    $value = $this->postLikeArray[$varName];
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
            $value = $theSettings['table']->field[$theSettings['field_name']];
        }

        return $value;

    }

    protected function clearAtEnd($theSettings)
    {
        if ((isset($theSettings['clearAtEnd']) == true) && ($theSettings['clearAtEnd'] == true)) {
            $this->output('<div style="clear: both;"></div>' . PHP_EOL);
        }
    }


}
