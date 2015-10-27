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

class cookie
{

    public static $contents;


    public static function initialize()
    {
        $ipinga = \ipinga\ipinga::getInstance();

        if (isset(static::$contents) == false) {

            // start with nothing
            static::$contents = array();

            // if the request came in with a cookie, decrypt it into our contents
            if (isset($_COOKIE[$ipinga->config('cookie.name')]) == true) {
                $clearText = \ipinga\crypto::decrypt( $_COOKIE[$ipinga->config('cookie.name')] );
                $a = json_decode($clearText, true);
                static::$contents = $a['kludge'];
            }

        }
    }

    // this should only be called once per program execution.  Currently I call it in the HandleShutdown() function
    public static function set()
    {
        static::initialize();

        $ipinga = \ipinga\ipinga::getInstance();

        if (count(static::$contents) == 0) {
            // expire it now
            if (isset($_COOKIE[$ipinga->config('cookie.name')]) == true) {
                setcookie($ipinga->config('cookie.name'), '', 1, '/');
            }   // no need for an else branch, as it wasn't there to begin with
        } else {
            $a = array('kludge' => static::$contents);
            $encrypted = \ipinga\crypto::encrypt(json_encode($a));
            setcookie($ipinga->config('cookie.name'), $encrypted, $ipinga->config('cookie.expiration_time'),'/');
        }
    }


    // add is really add and replace
    public static function add($key, $value)
    {
        static::initialize();
        static::$contents[$key] = $value;
    }

    public static function drop($key)
    {
        static::initialize();
        if (isset(static::$contents[$key]) == true) {
            unset(static::$contents[$key]);
        }
    }

    public static function keyExists($key)
    {
        static::initialize();
        $retval = false;
        if (isset(static::$contents[$key]) == true) {
            $retval = true;
        }
        return $retval;
    }

    public static function keyValue($key)
    {
        static::initialize();
        $retval = NULL;
        if (isset(static::$contents[$key]) == true) {
            $retval = static::$contents[$key];
        }
        return $retval;
    }

    public static function clear()
    {
        static::$contents = array();
    }

    // put all the contents in the header for debugging
    public static function debug($suffix = '')
    {
        foreach (static::$contents as $k => $v) {
            header('X-ipingaCryptoCookie-' . $suffix . $k . ': ' . json_encode($v));
        }
    }

    // easy to see what was in a cookie this way
    public static function decrypt($encryptedString)
    {
        $clearText = \ipinga\crypto::decrypt($encryptedString);
        $a = json_decode($clearText, true);
        return $a;
    }

}

?>