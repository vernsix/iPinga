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

class crypto
{

    public static $settings = array();

    /**
     * @param array $overrideDefaults
     */
    public static function applySettings($overrideDefaults = array())
    {
        if ((count(self::$settings) == 0) || (count($overrideDefaults) > 0)) {

            $ipinga = \ipinga\ipinga::getInstance();

            $defaults = array(
                'encryption.algorithm' => $ipinga->config('encryption.algorithm'),
                'encryption.mode' => $ipinga->config('encryption.mode'),
                'encryption.key' => $ipinga->config('encryption.key'),
                'encryption.iv' => $ipinga->config('encryption.iv')
            );
            self::$settings = array_merge($defaults, $overrideDefaults);

        }

    }


    /**
     * @param string $clearText
     * @param array $overrideDefaults
     *
     * @return string encryptedString
     */
    public static function encrypt($clearText, $overrideDefaults = array())
    {
        self::applySettings($overrideDefaults);

        $module = mcrypt_module_open( self::$settings['encryption.algorithm'], '', self::$settings['encryption.mode'], '');
        $ivSize = mcrypt_enc_get_iv_size($module);
        if (strlen(self::$settings['encryption.iv']) > $ivSize) {
            self::$settings['encryption.iv'] = substr(self::$settings['encryption.iv'], 0, $ivSize);
        }

        $keySize = mcrypt_enc_get_key_size($module);
        if (strlen(self::$settings['encryption.key']) > $keySize) {
            $settings['encryption.key'] = substr(self::$settings['encryption.key'], 0, $keySize);
        }

        mcrypt_generic_init($module, self::$settings['encryption.key'], self::$settings['encryption.iv']);
        $result = @mcrypt_generic($module, $clearText);
        mcrypt_generic_deinit($module);

        return $result;
    }

    /**
     * @param string $encryptedString
     * @param array $overrideDefaults
     *
     * @return string clearText
     */
    public static function decrypt($encryptedString, $overrideDefaults = array())
    {
        self::applySettings($overrideDefaults);

        $module = mcrypt_module_open(self::$settings['encryption.algorithm'], '', self::$settings['encryption.mode'], '');
        $ivSize = mcrypt_enc_get_iv_size($module);
        if (strlen(self::$settings['encryption.iv']) > $ivSize) {
            self::$settings['encryption.iv'] = substr(self::$settings['encryption.iv'], 0, $ivSize);
        }

        $keySize = mcrypt_enc_get_key_size($module);
        if (strlen(self::$settings['encryption.key']) > $keySize) {
            self::$settings['encryption.key'] = substr(self::$settings['encryption.key'], 0, $keySize);
        }

        mcrypt_generic_init($module, self::$settings['encryption.key'], self::$settings['encryption.iv']);
        $decryptedData = @mdecrypt_generic($module, $encryptedString);
        $result = rtrim($decryptedData, "\0");
        mcrypt_generic_deinit($module);

        return $result;
    }

}

?>