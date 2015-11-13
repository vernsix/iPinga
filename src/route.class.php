<?php
namespace ipinga;

class route
{

    /**
     * @var bool
     */
    public $fired = false;

    /**
     * @var string
     */
    public $urlToMatch = '';

    /**
     * @var string
     */
    public $controller = '';

    /**
     * @var string
     */
    public $method = '';

    /**
     * @var string
     */
    public $middleware = '';


    public static function launchController($controller, $method, $params)
    {
        $ipinga = \ipinga\ipinga::getInstance();
        $controllerFile = $ipinga->config('path.controllers') . '/' . $controller . '.controller.php';

        // include the controller
        include $controllerFile;

        // a new controller class instance
        $class = $controller . 'Controller';

        $controller = new $class;
        call_user_func_array(array($controller, $method), $params);

    }


    public function __construct($urlToMatch, $controller, $method, $middleware = null)
    {
        $this->urlToMatch = $urlToMatch;
        $this->controller = $controller;
        $this->method = $method;
        $this->middleware = $middleware;
    }


    /**
     * See if the url can be handled by this route
     *
     * @param string $route
     *
     * @return bool
     */
    public function handled($route = '')
    {
        $uriSegmentsInThisRoute = explode('/', $this->urlToMatch);
        $uriSegmentsInActualRoute = explode('/', $route);

        if (count($uriSegmentsInActualRoute) == count($uriSegmentsInThisRoute)) {

            $ThisUrlUpToFirstDollarSign = explode('$',$this->urlToMatch)[0];

            if ($ThisUrlUpToFirstDollarSign == substr($route, 0, strlen($ThisUrlUpToFirstDollarSign))) {

                if ($this->processMiddleWare()==true) {

                    // have to explode these two again, in case middleware changed anything
                    $uriSegmentsInThisRoute = explode('/',$this->urlToMatch);
                    $uriSegmentsInActualRoute = explode('/', $route);

                    $NumberOfParams = count(explode('$',$this->urlToMatch)) - 1;
                    $params = array();
                    for ($i = count($uriSegmentsInThisRoute) - $NumberOfParams; $i < count($uriSegmentsInThisRoute); $i++) {
                        $params[] = $uriSegmentsInActualRoute[$i];
                    }

                    self::launchController($this->controller, $this->method, $params);

                    $this->fired = true;

                    return true;

                }


            }
        }

        return false;

    }

    private function processMiddleWare()
    {
        $middlewareList = explode('|', $this->middleware);

        $ipinga = \ipinga\ipinga::getInstance();

        $result = true;
        foreach ($middlewareList as $mw) {

            if (empty($mw) == false) {

                $middlewareFile = $ipinga->config('path.middleware') . '/' . $mw . '.middleware.php';

                // include the middleware
                require_once $middlewareFile;

                // a new controller class instance
                $class = $mw . 'Middleware';

                $middleware = new $class;
                $result = call_user_func_array(array($middleware, 'call'), array($ipinga));

                if ($result === false) {
                    break;
                }

            }

        }

        return $result;

    }


}

?>