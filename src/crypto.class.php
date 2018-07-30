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

/*
 * CHANGELOG
 *
 * 2/6/2018 - Well, it was finally time to get rid of mcrypt().  This function has been deprecated for some time
 * and it just needed to go away.  I have chosen to go with AES-256-CBC encryption method because it should be
 * available to all platforms.  There are some nuances for others that would make this wrapper class a bit more
 * cluttered.  If you need something different, it's easy to add and I may at some point. :)
 *
 */


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
        $key = hash('sha256', self::$settings['encryption.key'] );
        $iv = substr( hash('sha256', self::$settings['encryption.iv']), 0, 16 ); // has to be 16 chars
        return base64_encode( openssl_encrypt( $clearText, 'AES-256-CBC', $key, 0, $iv) );
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
        $key = hash('sha256', self::$settings['encryption.key'] );
        $iv = substr( hash('sha256', self::$settings['encryption.iv']), 0, 16 ); // has to be 16 chars
        return openssl_decrypt(base64_decode($encryptedString), 'AES-256-CBC', $key, 0, $iv);
    }

    /**
     * @param array $arrayToEncrypt
     *
     * @return string printable encrypted string
     */
    public static function printableEncrypt($mixedToEncrypt)
    {
        $a = array('k' => $mixedToEncrypt);
        return  bin2hex(\ipinga\crypto::encrypt(json_encode($a)));
    }

    /**
     * @param string $encryptedString (created by printableEncrypt())
     *
     * @return array Decrypted original value
     */
    public static function printableDecrypt($encryptedString)
    {
        $a = json_decode( \ipinga\crypto::decrypt(hex2bin($encryptedString)), true );
        return $a['k'];
    }

}

