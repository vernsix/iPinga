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

Abstract Class htmlGenerator
{

    /**
     * @var array
     */
    public $defaultSettings = array();

    /**
     * @param $settings
     *
     * @return mixed
     */
    abstract public function defaults($settings);





    /**
     * @param $settings array
     * @param $attrName string
     *
     * @return null
     */
    abstract public function echoAttribute($settings,$attrName);

    /**
     * @param $settings
     *
     * @return string $fieldName
     */
    abstract public function echoCoreAttributes($settings);

    /**
     * @param $settings
     * @param $varName
     *
     * @return null
     */
    abstract public function echoHints($settings,$varName);

    /**
     * @param $settings
     *
     * @return null
     */
    abstract public function textarea($settings);

    /**
     * @param $settings
     *
     * @return null
     */
    abstract public function field($settings);

    /**
     * @param $settings
     *
     * @return null
     */
    abstract public function select($settings);

    /**
     * @param $settings
     *
     * @return null
     */
    abstract public function hint($settings);

    /**
     * @param $settings
     *
     * @return null
     */
    abstract public function label($settings);



}

