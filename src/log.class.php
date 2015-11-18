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

class log
{
    public static $filename = 'logfile.php';    // php extension so it can't be downloaded from web
    public static $instanceName;
    public static $threshold = 0;

    public static function instanceName($newInstanceName = '')
    {
        if (empty($newInstanceName) == true) {
            self::$instanceName = (string)time();
        } else {
            self::$instanceName = $newInstanceName;
        }
        return self::$instanceName;
    }

    public static function log( $level, $logMessage )
    {
        if ($level >= self::$threshold) {
            if ( ($level>=0) && ($level<=7) ) {
                $type = array('DEBUG', 'INFO', 'NOTICE', 'WARNING', 'ERROR', 'CRITICAL', 'ALERT', 'EMERGENCY')[$level];
            } else {
                $type = 'UNKNOWN';
            }
            $handle = fopen(self::$filename, 'a') or die("can't open file " . self::$filename);
            fseek($handle, 0, SEEK_END);
            fwrite($handle, date("Y-m-d H:i:s") . " [" . $type . "] [" . self::instanceName() . "] " . $logMessage . "\r\n");
            fflush($handle);
            fclose($handle);
        }
    }

    public static function debug($logMessage)
    {
        self::log(0,$logMessage);
    }
    public static function info($logMessage)
    {
        self::log(1,$logMessage);
    }
    public static function notice($logMessage)
    {
        self::log(2,$logMessage);
    }
    public static function warning($logMessage)
    {
        self::log(3,$logMessage);
    }
    public static function error($logMessage)
    {
        self::log(4,$logMessage);
    }
    public static function critical($logMessage)
    {
        self::log(5,$logMessage);
    }
    public static function alert($logMessage)
    {
        self::log(6,$logMessage);
    }
    public static function emergency($logMessage)
    {
        self::log(7,$logMessage);
    }

}

class logLevel
{
    const EMERGENCY = 7;
    const ALERT     = 6;
    const CRITICAL  = 5;
    const ERROR     = 4;
    const WARNING   = 3;
    const NOTICE    = 2;
    const INFO      = 1;
    const DEBUG     = 0;
}

?>
