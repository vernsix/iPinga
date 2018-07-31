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

    /**
     * @var string
     */
    public $identifier = '';


    public static function launchController($controller, $method, $params)
    {
        $ipinga = \ipinga\ipinga::getInstance();
        $class = $controller . 'Controller';
        \ipinga\log::ipinga('Launching controller: '. $class);
        $controller = new $class;
        call_user_func_array(array($controller, $method), $params);

    }


    public function __construct($urlToMatch, $controller, $method, $middleware = null, $identifier='')
    {
        $this->urlToMatch = $urlToMatch;
        $this->controller = $controller;
        $this->method = $method;
        $this->middleware = $middleware;
        $this->identifier = $identifier;
    }


    /**
     * See if the url can be handled by this route
     *
     * @param string $rt
     *
     * @return bool
     */
    public function handled($rt = '')
    {
        $uriSegmentsInThisRoute = explode('/', $this->urlToMatch);
        $uriSegmentsInRequestedRoute = explode('/', $rt);

        if (count($uriSegmentsInRequestedRoute) == count($uriSegmentsInThisRoute)) {
            $thisUrlUpToFirstDollarSign = explode('/$',$this->urlToMatch)[0];
            $numberOfSegmentsUpToFirstDollarSign = count( explode('/',$thisUrlUpToFirstDollarSign) );
            $theyMatch = true;
            for( $i=0; $i<$numberOfSegmentsUpToFirstDollarSign; $i++ ) {
                if ( strcmp($uriSegmentsInThisRoute[$i],$uriSegmentsInRequestedRoute[$i]) <> 0 ) {
                    $theyMatch = false;
                    break;
                }
            }

            if ( $theyMatch ) {
                if ($this->processMiddleWare()==true) {
                    // have to explode these two again, in case middleware changed anything
                    $uriSegmentsInThisRoute = explode('/',$this->urlToMatch);
                    $uriSegmentsInRequestedRoute = explode('/', $rt);
                    $NumberOfParams = count(explode('$',$this->urlToMatch)) - 1;
                    $params = array();
                    for ($i = count($uriSegmentsInThisRoute) - $NumberOfParams; $i < count($uriSegmentsInThisRoute); $i++) {
                        $params[] = $uriSegmentsInRequestedRoute[$i];
                    }
                    \ipinga\log::ipinga('Route {'. $this->identifier .'} ('. $this->urlToMatch. ') fired!');
                    self::launchController($this->controller, $this->method, $params);
                    $this->fired = true;
                    return true;
                } else {
                    \ipinga\log::ipinga('Route {'. $this->identifier .'} middcleware refused');
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

