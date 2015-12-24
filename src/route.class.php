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
        // $controllerFile = $ipinga->config('path.controllers') . '/' . $controller . '.controller.php';

        // include the controller
        // include $controllerFile;

        // a new controller class instance
        $class = $controller . 'Controller';

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
        \ipinga\log::debug('(RH1) Route {'. $this->identifier .'} ('. $this->urlToMatch. ') checking to handle '. $rt);

        $uriSegmentsInThisRoute = explode('/', $this->urlToMatch);
        $uriSegmentsInRequestedRoute = explode('/', $rt);

        \ipinga\log::debug('(RH8) $uriSegmentsInThisRoute == '. var_export($uriSegmentsInThisRoute,true));
        \ipinga\log::debug('(RH9) $uriSegmentsInRequestedRoute == '. var_export($uriSegmentsInRequestedRoute,true));

        if (count($uriSegmentsInRequestedRoute) == count($uriSegmentsInThisRoute)) {

            $thisUrlUpToFirstDollarSign = explode('/$',$this->urlToMatch)[0];
            \ipinga\log::debug('(RH10) $thisUrlUpToFirstDollarSign == '. var_export($thisUrlUpToFirstDollarSign,true));

            $numberOfSegmentsUpToFirstDollarSign = count( explode('/',$thisUrlUpToFirstDollarSign) );
            \ipinga\log::debug('(RH11) $numberOfSegmentsUpToFirstDollarSign == '. $numberOfSegmentsUpToFirstDollarSign);

            $theyMatch = true;
            for( $i=0; $i<$numberOfSegmentsUpToFirstDollarSign; $i++ ) {
                if ( strcmp($uriSegmentsInThisRoute[$i],$uriSegmentsInRequestedRoute[$i]) <> 0 ) {
                    \ipinga\log::debug('(RH2) Segments did not match: '. $i. ' -- '. $uriSegmentsInThisRoute[$i] .' -- '. $uriSegmentsInRequestedRoute[$i] );
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

                    \ipinga\log::info('(RH3) Route {'. $this->identifier .'} ('. $this->urlToMatch. ') fired!');
                    self::launchController($this->controller, $this->method, $params);
                    \ipinga\log::debug('(RH4) Route {'. $this->identifier .'} ('. $this->urlToMatch. ') back from controller');

                    $this->fired = true;
                    return true;

                } else {
                    \ipinga\log::debug('(RH5) Route {'. $this->identifier .'} middcleware refused');
                }

            }

        } else {
            \ipinga\log::debug('(RH6) Route {'. $this->identifier .'} segment counts not the same');
        }

        \ipinga\log::debug('(RH7) Route {'. $this->identifier .'} ('. $this->urlToMatch. ') NOT fired!');
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

        \ipinga\log::debug('middleware '. $this->middleware. ' is returning '. $result);
        return $result;

    }


}

?>