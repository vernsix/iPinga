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

class options
{

    /*

    CREATE TABLE IF NOT EXISTS `options` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `option_name` varchar(100) NOT NULL,
        `option_value` varchar(2048) NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

    or

    CREATE TABLE IF NOT EXISTS `options` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `environment` varchar(100) NOT NULL,
        `option_name` varchar(100) NOT NULL,
        `option_value` varchar(2048) NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

    */

    /**
     * @var \ipinga\table
     */
    public static $optionsTable = null;


    /**
     * @var string
     */
    public static $environment = null;


    /**
     * @var string
     */
    public static $optionsTableName = 'options';


    public static function get($key)
    {
        if (isset(self::$optionsTable) == false) {
            self::$optionsTable = new \ipinga\table(self::$optionsTableName);
        }

        $fieldsToMatch = array('option_name' => $key);

        if (isset(self::$environment) == true) {

            $fieldsToMatch['environment'] = self::$environment;
            if (self::$optionsTable->loadByFieldsMatching($fieldsToMatch) == true) {
                return self::$optionsTable->option_value;
            } else {
                if (self::$optionsTable->loadByFieldsMatching(array('option_name' => $key)) == true) {
                    return self::$optionsTable->option_value;
                } else {
                    return false;
                }
            }

        } else {

            if (self::$optionsTable->loadByFieldsMatching($fieldsToMatch) == true) {
                return self::$optionsTable->option_value;
            } else {
                return false;
            }

        }

    }

    public static function set($key, $value)
    {
        $oldValue = self::get($key);    // just to load all the internals
        if (isset(self::$environment)==true) {  // we want the environmental option
            $tableEnvironment = self::$optionsTable->field['environment'];  // have to assign it to a variable so empty doesn't spew
            if (empty($tableEnvironment)==true) {   // we read the default, but are saving it to our environment
                self::$optionsTable->id = 0;
            }
            self::$optionsTable->environment = self::$environment;
        } else {
            self::$optionsTable->environment = '';
        }
        self::$optionsTable->option_name = $key;
        self::$optionsTable->option_value = $value;
        self::$optionsTable->save();
        return $oldValue;
    }

}

?>

