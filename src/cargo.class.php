<?php
/*
    Vern Six MVC Framework version 3.0

    Copyright (c) 2007-2015 by Vernon E. Six, Jr.
    Author's websites: http://www.iPinga.com and http://www.VernSix.com

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
namespace iPinga;


Class cargo
{

    /**
     * @var array
     */
    public static $instances = array();

    /**
     * @param string $name
     *
     * @return \iPinga\cargo|null
     */
    public static function getInstance($name = 'main')
    {
        if (isset(static::$instances[$name])==true) {
            return static::$instances[$name];
        } else {
            return null;
        }
    }

    /**
     * @var array
     */
    public $vars = array();

    /**
     * @var string
     */
    public $name = '';

    /**
     * @param string $name
     */
    public function __construct($name='main')
    {
        $this->setName($name);
    }

    /**
     * @param string $name
     */
    public function setName($name='main')
    {
        $this->name = $name;
        static::$instances[$name] = $this;
    }

    /**
     * set variables
     *
     * @param string $index
     * @param mixed $value
     * @returns void
     */
    public function __set($index, $value)
    {
        $this->vars[$index] = $value;
    }

    /**
     * get variables
     *
     * @param string $index
     * @return mixed
     */
    public function __get($index)
    {
        if (isset($this->vars[$index])==true) {
            return $this->vars[$index];
        } else {
            return null;
        }
    }


    /**
     * @param string $index
     */
    public function clear($index)
    {
        if (isset($this->vars[$index])==true) {
            unset($this->vars[$index]);
        }
    }

    /**
     * @return string
     */
    function asJson()
    {
        return json_encode($this->vars);
    }

    /**
     * @param $json
     *
     * @return array|mixed
     */
    function loadFromJson($json)
    {
        $this->vars = json_decode($json);
        return $this->vars;
    }


    /**
     * @return array List of variables in this cargo instance
     */
    public function keys()
    {
        return array_keys($this->vars);
    }

}




