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

class log
{

    private static $_filter = [];

    public static function filter(string $type, bool $onOffSwitch = false)
    {
        self::$_filter[strtolower($type)] = $onOffSwitch;
    }

    public static function microtime_fixed()
    {
        list($usec, $sec) = explode(" ", microtime());
        return $sec. substr($usec,1);
    }

    public static function this( string $type = '', string $message = '', string $details = '', bool $logRequestInfo = false)
    {
        $type = strtolower($type);
        if (array_key_exists($type, self::$_filter)) {
            $logThis = self::$_filter[$type];
        } else {
            $logThis = true;
        }

        if ($logThis) {
            $l = new \ipinga\table(\ipinga\ipinga::getInstance()->config('logTableName'));
            $l->id = 0; // new record
            $l->microtime = self::microtime_fixed();
            $l->type = $type;
            $l->message = $message;
            $l->details = $details;
            $l->user_id = \ipinga\values::userId();
            $l->remote_addr = $_SERVER['REMOTE_ADDR'];
            $l->route = (isset($_GET['rt'])) ? $_GET['rt'] : '';
            $l->request_method = $_SERVER['REQUEST_METHOD'];
            $l->server_name = $_SERVER['SERVER_NAME'];
            $l->session_id = \ipinga\values::sessionId();
            if ($logRequestInfo) {
                $l->_GET = json_encode($_GET);
                $l->_POST = json_encode($_POST);
            } else {
                $l->_GET = '';
                $l->_POST = '';
            }
            $l->save();
        }
    }

    public static function ipinga(string $message = '', string $details = '', bool $logRequestInfo = false)
    {
        self::this('ipinga', $message, $details, $logRequestInfo);
    }
    public static function trace(string $message = '', string $details = '', bool $logRequestInfo = false)
    {
        self::this('trace', $message, $details, $logRequestInfo);
    }
    public static function debug(string $message = '', string $details = '', bool $logRequestInfo = false)
    {
        self::this('debug',$message, $details, $logRequestInfo);
    }
    public static function info(string $message = '', string $details = '', bool $logRequestInfo = false)
    {
        self::this('info',$message, $details, $logRequestInfo);
    }
    public static function notice(string $message = '', string $details = '', bool $logRequestInfo = false)
    {
        self::this('notice',$message, $details, $logRequestInfo);
    }
    public static function warning(string $message = '', string $details = '', bool $logRequestInfo = false)
    {
        self::this('warning',$message, $details, $logRequestInfo);
    }
    public static function error(string $message = '', string $details = '', bool $logRequestInfo = false)
    {
        self::this('error',$message, $details, $logRequestInfo);
    }
    public static function critical(string $message = '', string $details = '', bool $logRequestInfo = false)
    {
        self::this('critical',$message, $details, $logRequestInfo);
    }
    public static function alert(string $message = '', string $details = '', bool $logRequestInfo = false)
    {
        self::this('alert',$message, $details, $logRequestInfo);
    }
    public static function emergency(string $message = '', string $details = '', bool $logRequestInfo = false)
    {
        self::this('emergency',$message, $details, $logRequestInfo);
    }

}
