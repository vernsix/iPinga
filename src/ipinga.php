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
namespace ipinga {

    class ipinga
    {

        protected $configOptions = array();

        /**
         * @var \ipinga\ipinga
         */
        protected static $instance;

        /**
         * @var /PDO
         */
        protected $pdoHandle;

        /**
         * @var array
         */
        public $routes = array();

        /**
         * @var array
         */
        public $getRoutes = array();

        /**
         * @var array
         */
        public $postRoutes = array();

        /**
         * @var array
         */
        public $defaultRoute = array();

        /**
         * @var \ipinga\manager
         */
        public $manager;


        /**
         * Get instance of the ipinga object
         *
         * @return \ipinga\ipinga|null
         */
        public static function getInstance()
        {
            if (isset(static::$instance) == true) {
                return static::$instance;
            } else {
                return null;
            }
        }


        /**
         * @param array $overrideConfigOptions
         */
        public function __construct($overrideConfigOptions = array())
        {
            // start with defaults
            $this->setConfigOptionsToDefault();

            // allow the developer to override all the defaults.  Notice: no checking is performed !
            $this->configOptions = array_merge($this->configOptions,$overrideConfigOptions);

            // php acts stupid without setting the timezone
            date_default_timezone_set($this->configOptions['time.timezone']);

            // so outside functions can get ahold of the ipinga object
            static::$instance = $this;

            $this->manager = new \ipinga\manager($this->configOptions);

        }


        public function setConfigOptionsToDefault()
        {

            $this->configOptions['cookie.name'] = 'ipinga';
            $this->configOptions['cookie.expiration_time'] = time() + (60 * 60 * 24 * 30);   //   30 days from now

            $this->configOptions['encryption.algorithm'] = MCRYPT_RIJNDAEL_128;
            $this->configOptions['encryption.iv'] = md5('ipinga');
            $this->configOptions['encryption.key'] = 'you should change this'; // bcb04b7e103a0cd8b54763051cef08bc55abe029fdebae5e1d417e2ffb2a00a3';
            $this->configOptions['encryption.mode'] = MCRYPT_MODE_CBC;

            $this->configOptions['manager.expired_url'] = '/index/expired';
            $this->configOptions['manager.ip_changed_url'] = '/index/ip_changed';
            $this->configOptions['manager.login_url'] = '/index/login';
            $this->configOptions['manager.max_minutes'] = 10;

            $this->configOptions['mysql.database'] = 'your_db_name';
            $this->configOptions['mysql.host'] = 'localhost';
            $this->configOptions['mysql.password'] = 'your_db_password';
            $this->configOptions['mysql.user'] = 'your_db_user';

            $this->configOptions['path.classes'] = getcwd() . '/classes';
            $this->configOptions['path.controllers'] = getcwd() . '/controllers';
            $this->configOptions['path.cwd'] = getcwd();    // Presumably the public_html folder
            $this->configOptions['path.framework'] = __DIR__;
            $this->configOptions['path.middleware'] = getcwd() . '/middleware';
            $this->configOptions['path.interfaces'] = getcwd() . '/interfaces';
            $this->configOptions['path.models'] = getcwd() . '/models';
            $this->configOptions['path.views'] = getcwd() . '/views';

            $this->configOptions['logfile'] = getcwd() . '/logfile.php';

            $this->configOptions['time.timezone'] = 'America/Chicago';
            // php acts stupid without setting the timezone
            date_default_timezone_set($this->configOptions['time.timezone']);

        }


        /**
         * Configure Settings
         *
         * Getter and setter for application settings
         *
         * If only one argument is specified and that argument is a string, the value
         * of the setting identified by the first argument will be returned, or NULL if
         * that setting does not exist.
         *
         * If only one argument is specified and that argument is an associative array,
         * the array will be merged into the existing application settings.
         *
         * If two arguments are provided, the first argument is the name of the setting
         * to be created or updated, and the second argument is the setting value.
         *
         * @param  string|array $array_or_key If a string, the key of the setting to set or retrieve. Else an associated array of setting keys and values
         * @param  mixed        $value If name is a string, the value of the setting identified by $name
         *
         * @return mixed        The value of a setting
         */
        public function config($array_or_key, $value = '')
        {
            if (is_array($array_or_key) == true) {
                $this->configOptions = array_merge($this->configOptions, $array_or_key);
                $result = $this->configOptions;
            } elseif (func_num_args() === 1) {
                if (isset($this->configOptions[$array_or_key]) == true) {
                    $result = $this->configOptions[$array_or_key];
                } else {
                    $result = null;
                }
            } else {
                $this->configOptions[$array_or_key] = $value;
                $result = $value;
            }

            return $result;
        }

        public function pdo()
        {
            if (isset($this->pdoHandle)==false) {
               $this->pdoHandle = new \PDO('mysql:host=' . $this->config('mysql.host') . ';dbname=' . $this->config('mysql.database'),
                   $this->config('mysql.user'), $this->config('mysql.password'));
               $this->pdoHandle->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            }
            return $this->pdoHandle;
        }


        public function run()
        {

            if (count($this->routes)==0) {
                if ((isset($_SERVER['REQUEST_METHOD']) == true) && ($_SERVER['REQUEST_METHOD'] == 'GET')) {
                    if (count($this->getRoutes) > 0) {
                        $this->routes = $this->getRoutes;
                    }
                } else {
                    if (count($this->postRoutes) > 0) {
                        $this->routes = $this->postRoutes;
                    }
                }
            }

            $rt = (isset($_GET['rt'])) ? $_GET['rt'] : '';

            // I use output buffering to make sure any cookies that are set in the code get handled properly.
            // ie: sent in the header instead of inline with the html, etc as they are generated.
            ob_start();

            $routeHandled = false;
            foreach( $this->routes as $route) {

                /* @var $route \ipinga\route */

                if ($route->handled($rt) == true) {
                    $routeHandled = true;
                    break;
                }

            }

            if ($routeHandled===false) {
                if (count($this->defaultRoute)==2) {

                    \ipinga\log::debug('Firing default route');
                    //if (isset($_GET['rt'])==false) {
                        \ipinga\route::launchController($this->defaultRoute[0], $this->defaultRoute[1], array());
                    //} else {
                    //    header('location: /');
                    //}

                } else {
                    echo 'No route found!' . PHP_EOL;
                }
            }

        }

        /**
         * @param      $urlToMatch
         * @param      $controller
         * @param      $method
         * @param null $middleware
         */
        public function addRoute($urlToMatch,$controller,$method,$middleware=null,$identifier='')
        {
            $this->routes[] = new \ipinga\route($urlToMatch,$controller,$method,$middleware,$identifier);
        }


        /**
         * @param      $urlToMatch
         * @param      $controller
         * @param      $method
         * @param null $middleware
         */
        public function addGetRoute($urlToMatch,$controller,$method,$middleware=null,$identifier='')
        {
            $this->getRoutes[] = new \ipinga\route($urlToMatch,$controller,$method,$middleware,$identifier);
        }

        /**
         * @param      $urlToMatch
         * @param      $controller
         * @param      $method
         * @param null $middleware
         */
        public function addPostRoute($urlToMatch,$controller,$method,$middleware=null,$identifier='')
        {
            $this->postRoutes[] = new \ipinga\route($urlToMatch,$controller,$method,$middleware,$identifier);
        }




        /**
         * @param $controller
         * @param $method
         */
        public function defaultRoute($controller,$method)
        {
            $this->defaultRoute = array($controller,$method);
        }

    }


}


namespace {

    /*
    * Autoload the developers' code, not the ipinga code per se
    */
    function ipinga_autoload($className)
    {

        $ipinga = \ipinga\ipinga::getInstance();

        // is this something in the ipinga framework?
        if (strpos($className,'ipinga\\')===0) {
            $file = $ipinga->config('path.framework'). '/' . substr($className,7) . '.class.php';
            if (file_exists($file) == true) {
                require_once $file;
                return true;
            }
        }

/*
         $c = debug_backtrace(false);
        \ipinga\log::debug(var_export($c,true));
*/

        \ipinga\log::debug('autoload $className='. $className);

        // some devs name controllers differently

        $filename = strtolower(substr($className, 0, strrpos($className, 'Controller'))) . '.controller.php';

        // part of the application controllers?
        $file = $ipinga->config('path.controllers') . '/' . $filename;
        if (file_exists($file) == true) {
            \ipinga\log::debug('autoload (controller) $file='. $file);
            require_once $file;
            return true;
        }

        // some devs name controllers with a class filename
        $filename = strtolower($className) . '.class.php';

        // part of the application controllers?
        $file = $ipinga->config('path.controllers') . '/' . $filename;
        if (file_exists($file) == true) {
            \ipinga\log::debug('autoload (class in controller directory) $file='. $file);
            require_once $file;
            return true;
        }

        // some other class?
        $file = $ipinga->config('path.classes') . '/' . $filename;
        if (file_exists($file) == true) {
            \ipinga\log::debug('autoload (class) $file='. $file);
            require_once $file;
            return true;
        }


        // an interface?
        $filename = strtolower($className) . '.interface.php';

        $file = $ipinga->config('path.interfaces') . '/' . $filename;
        if (file_exists($file) == true) {
            \ipinga\log::debug('autoload (interface) $file='. $file);
            require_once $file;
            return true;
        }


        // part of the application models?
        $filename = strtolower($className) . '.model.php';

        $file = $ipinga->config('path.models') . '/' . $filename;
        if (file_exists($file) == true) {
            \ipinga\log::debug('autoload (model) $file='. $file);
            require_once $file;
            return true;
        }


        return false;

    }

    spl_autoload_register('ipinga_autoload');


    function ipinga_shutdown()
    {
        // v6_debug::dump();

        \ipinga\cookie::set();

        @ob_end_flush();

        $error = error_get_last();
        if (($error !== NULL) && ($error['type'] == 1)) {
//        ob_end_clean();   // silently discard the output buffer contents.
//        appSendMsgToVern('Error has occurred',$error);
//        header( 'location:/fatal_error' );
            @ob_end_flush(); // output what is stored in the internal buffer  (may not want this here in production)
            \ipinga\log::info(var_export($error,true));
            echo '<pre>' . var_export($error, true);
        //    v6_BackTrace();
            die('handleShutdown(): Cannot continue!');
        } else {
            @ob_end_flush(); // output what is stored in the internal buffer
        }
    }

    register_shutdown_function('ipinga_shutdown');

}

