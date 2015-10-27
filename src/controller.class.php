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

Abstract Class controller
{

    /**
     * @var \ipinga\template
     */
    public $template;

    /**
     * I like having this, but it's really not needed by every controller by any means.
     * @var array
     */
    public $json = array();



    function __construct()
    {
        $this->template = new \ipinga\template();
    }


    /**
     * all controllers must contain an index method
     */
    abstract function index();



    public function SendJSON( $arrayToSendAsJson = null ) {

        // If you get careless, this next line can be uncommented to wipe clean all the output buffer prior to
        // setting the value in the header.   But if you are going to send a json response, it really should be
        // your only response and therefore the buffer shouldn't have anything in it.  Uncommenting this line
        // is only a suggestion for lazy programmers   :)
        //
        // ob_end_clean();

        header ("Content-Type:text/json");
        echo json_encode( isset($arrayToSendAsJson) ? $arrayToSendAsJson : $this->json );
        exit();
    }





}

?>