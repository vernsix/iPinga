<?php
namespace ipinga;

class validator
{

    /**
     * @var string
     */
    public $message = '';

    /**
     * @var array
     */
    public $vars = array();

    /**
     * @var bool
     */
    public $setTemplateHints = false;

    /**
     * @var array
     */
    public $queue = array();

    /**
     * @param array      $vars $_POST or $_GET normally
     * @param bool|false $setTemplateHints
     */
    public function __construct($vars, $setTemplateHints = false)
    {
        $this->vars = $vars;
        $this->setTemplateHints = $setTemplateHints;
    }


    public function queueTemplateHint($varName, $hint)
    {
        $this->queue[] = array('TemplateHint', $varName, $hint);
    }

    public function setTemplateHint($varName, $hint)
    {
        if ($this->setTemplateHints == true) {
            \ipinga\template::getInstance()->__set($varName . '_hint', $hint);
        }
    }


    public function processQueue()
    {
        foreach ($this->queue as $q) {
            $type = $q[0];

            switch ($type) {
                case "number":
                    $this->checkNumber($q[1], $q[2], $q[3], $q[4], $q[5]);
                    break;
                case "array":
                    $this->checkArray($q[1], $q[2], $q[3], $q[4]);
                    break;
                case "string":
                    $this->checkString($q[1], $q[2], $q[3], $q[4], $q[5], $q[6]);
                    break;
                case "date":
                    $this->checkDate($q[1], $q[2], $q[3], $q[4], $q[5]);
                    break;
                case "time":
                    $this->checkTime($q[1], $q[2], $q[3], $q[4], $q[5]);
                    break;
                case "password":
                    $this->checkPassword($q[1], $q[2], $q[3], $q[4], $q[5], $q[6]);
                    break;
                case "match":
                    $this->checkMatch($q[1], $q[2], $q[3]);
                    break;
                case "email":
                    $this->checkMatch($q[1], $q[2], $q[3]);
                    break;
                case "TemplateHint":
                    $this->setTemplateHint($q[1], $q[2]);
                    break;
            }

        }
    }


    public function queueNumber($varName, $varDescrip, $min, $max, $required = true)
    {
        $this->queue[] = array('number', $varName, $varDescrip, $min, $max, $required);
    }

    function checkNumber($varName, $varDescrip, $min, $max, $required = true)
    {
        $message = '';
        if ($required == true || ((isset($this->vars[$varName])) && (strlen($this->vars[$varName]) > 0))) {
            if (isset($this->vars[$varName])) {
                if (!is_numeric($this->vars[$varName])) {
                    $message = $varDescrip . ' must be a number.';
                } elseif ($this->vars[$varName] < $min) {
                    $message = $varDescrip . ' must be greater than or equal to ' . $min . '.';
                } elseif ($this->vars[$varName] > $max) {
                    $message = $varDescrip . ' must be less than or equal to ' . $max . '.';
                }
            } else {
                $message = $varDescrip . ' is undefined<br>';
            }
        }
        $this->setTemplateHint($varName, $message);
        if (strlen($message) > 0) {
            $this->message .= $message . '<br>';
        }
    }


    public function queueArray($varName, $varDescrip, $validChoices, $required = true)
    {
        $this->queue[] = array('array', $varName, $varDescrip, $validChoices, $required);
    }

    function checkArray($varName, $varDescrip, $validChoices, $required = true)
    {
        $message = '';

        // if it's required or if it's set and the length > 0
        if ($required == true || ((isset($this->vars[$varName])) && (strlen($this->vars[$varName]) > 0))) {
            if (isset($this->vars[$varName])) {
                if (array_search($this->vars[$varName], $validChoices) === false) {
                    $message = $varDescrip . ' is invalid';
                }
            } else {
                $message = $varDescrip . ' is undefined<br>';
            }
        }
        $this->SetTemplateHint($varName, $message);
        if (strlen($message) > 0) {
            $this->message .= $message . '<br>';
        }
    }


    function queueString($varName, $varDescrip, $minLength, $maxLength, $required = true, $regex = '/^[.!@&<>"=;$-_ 0-9a-zA-Z\f\n\r\t\']+$/')
    {
        $this->queue[] = array('string', $varName, $varDescrip, $minLength, $maxLength, $required, $regex);
    }

    function checkString($varName, $varDescrip, $minLength, $maxLength, $required = true, $regex = '/^[.!@&<>"=;$-_ 0-9a-zA-Z\f\n\r\t\']+$/', $regexHint = '')
    {
        $message = '';
        if ($required == true || ((isset($this->vars[$varName])) && (strlen($this->vars[$varName]) > 0))) {
            if (!isset($this->vars[$varName])) {
                $message = $varDescrip . ' is invalid.';
            } elseif (!is_string($this->vars[$varName])) {
                $message = $varDescrip . ' is invalid.';
            } elseif (strlen($this->vars[$varName]) < $minLength) {
                $message = $varDescrip . ' is too short.';
            } elseif (strlen($this->vars[$varName]) > $maxLength) {
                $message = $varDescrip . ' is too long.';
            } elseif (!preg_match($regex, $this->vars[$varName])) {

                if (empty($regexHint)==true) {
                    $message = $varDescrip . ' contains invalid characters.';
                } else {
                    $message = $regexHint;
                }
            }
        }
        $this->SetTemplateHint($varName, $message);
        if (strlen($message) > 0) {
            $this->message .= $message . '<br>';
        }
    }


    function queueDate($varName, $varDescrip, $minDate, $maxDate, $required = true)
    {
        $this->queue[] = array('date', $varName, $varDescrip, $minDate, $maxDate, $required);
    }

    function checkDate($varName, $varDescrip, $minDate, $maxDate, $required = true)
    {
        $message = '';
        if ($required == true || ((isset($this->vars[$varName])) && (strlen($this->vars[$varName]) > 0))) {
            if (!isset($this->vars[$varName])) {
                $message = $varDescrip . ' is invalid.';
            } elseif (strlen($this->vars[$varName]) <> 10) {
                $message = $varDescrip . ' is invalid.';
            } else {
                $date = $this->vars[$varName];
                $yyyy = substr($date, 0, 4);
                $mm = substr($date, 5, 2);
                $dd = substr($date, 8, 2);
                if ($dd != "" && $mm != "" && $yyyy != "") {
                    if (checkdate($mm, $dd, $yyyy) == true) {
                        if ($date < $minDate) {
                            $message = $varDescrip . ' is before ' . $minDate . '.';
                        } elseif ($date > $maxDate) {
                            $message = $varDescrip . ' is after ' . $maxDate . '.';
                        }
                    } else {
                        $message = $varDescrip . ' is invalid.';
                    }
                }
            }
        }
        $this->SetTemplateHint($varName, $message);
        if (strlen($message) > 0) {
            $this->message .= $message . '<br>';
        }
    }


    function queueTime($varName, $varDescrip, $minTime, $maxTime, $required = true)
    {
        $this->queue[] = array('time', $varName, $varDescrip, $minTime, $maxTime, $required);
    }

    function checkTime($varName, $varDescrip, $minTime, $maxTime, $required = true)
    {
        $message = '';
        if ($required == true || ((isset($this->vars[$varName])) && (strlen($this->vars[$varName]) > 0))) {
            if (!isset($this->vars[$varName])) {
                $message = $varDescrip . ' is invalid.';
            } elseif (strlen($this->vars[$varName]) <> 5) {
                $message = $varDescrip . ' is invalid.';
            } elseif (preg_match("/^(([1-9]{1})|([0-1][0-9])|([1-2][0-3]))\.([0-5][0-9])$/", $this->vars[$varName]) === false) {
                $message = $varDescrip . ' is invalid.';
            } else {
                $time = $this->vars[$varName];
                if ($time < $minTime) {
                    $message = $varDescrip . ' is before ' . $minTime . '.';
                } elseif ($time > $maxTime) {
                    $message = $varDescrip . ' is after ' . $maxTime . '.';
                }
            }
        }
        $this->SetTemplateHint($varName, $message);
        if (strlen($message) > 0) {
            $this->message .= $message . '<br>';
        }
    }


    function queuePassword($varName, $varDescrip, $minLength, $maxLength, $required = true, $strong = false)
    {
        $this->queue[] = array('password', $varName, $varDescrip, $minLength, $maxLength, $required, $strong);
    }

    function checkPassword($varName, $varDescrip, $minLength, $maxLength, $required = true, $strong = false)
    {
        if ($strong == true) {
            $regex = "/^.*(?=.{8,})(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).*$/";
        } else {
            $regex = "/^[-_0-9a-zA-Z!@#$%^&*()+=~\']+$/";
        }
        $this->checkString($varName, $varDescrip, $minLength, $maxLength, $required, $regex);
    }


    function queueMatch($varName1, $varName2, $varDescrip)
    {
        $this->queue[] = array('match', $varName1, $varName2, $varDescrip);
    }

    function checkMatch($varName1, $varName2, $varDescrip)
    {
        $message = '';
        if (($this->vars[$varName1] <> $this->vars[$varName2]) || (strlen($this->vars[$varName1]) <> strlen($this->vars[$varName2]))) {
            $message = $varDescrip . ' do not match.';
            $this->SetTemplateHint($varName1, $message);
            if (strlen($message) > 0) {
                $this->message .= $message . '<br>';
            }
        }
    }


    function queueEmail($varName, $varDescrip, $required = true)
    {
        $this->queue[] = array('email', $varName, $varDescrip, $required);
    }

    function checkEmail($varName, $varDescrip, $required = true)
    {
        $message = '';
        if ($required == true || ((isset($this->vars[$varName])) && (strlen($this->vars[$varName]) > 0))) {
            if (!isset($this->vars[$varName])) {
                $message = $varDescrip . ' is invalid.';
            }
            if (!is_string($this->vars[$varName])) {
                $message = $varDescrip . ' is invalid.';
            }
            // if (!preg_match('/^([0-9a-zA-Z]([-.\w]*[0-9a-zA-Z])*@([0-9a-zA-Z][-\w]*[0-9a-zA-Z]\.)+[a-zA-Z]{2,9})$/', $this->vars[$varName])) {
            // changed the regex per http://emailregex.com/
            if (!preg_match('/^(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){255,})(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){65,}@)(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22))(?:\.(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22)))*@(?:(?:(?!.*[^.]{64,})(?:(?:(?:xn--)?[a-z0-9]+(?:-[a-z0-9]+)*\.){1,126}){1,}(?:(?:[a-z][a-z0-9]*)|(?:(?:xn--)[a-z0-9]+))(?:-[a-z0-9]+)*)|(?:\[(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9][:\]]){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?)))?(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))\]))$/iD', $this->vars[$varName])) {
                $message = $varDescrip . ' is not a valid email address';
            }
        }
        $this->SetTemplateHint($varName, $message);
        if (strlen($message) > 0) {
            $this->message .= $message . '<br>';
        }
    }


}

