<?php
namespace ipinga;

Class template
{

    /**
     * @var \ipinga\template
     */
    protected static $instance;

    public $vars = array();
    public $showViewFilename = false;


    /**
     * Get instance of the template object
     *
     * @return \ipinga\template|null
     */
    public static function getInstance()
    {
        if (isset(static::$instance) == true) {
            return static::$instance;
        } else {
            return null;
        }
    }

    public function __construct( $htmlGenerator = null )
    {
        // set some defaults
        $this->vars['skin'] = 'cupertino';
        $this->vars['header'] = array();
        $this->vars['json'] = array();

        if (isset($htmlGenerator)==true) {
            $this->html = $htmlGenerator;
        } else {
            $this->html = new \ipinga\defaultHtmlGenerator();
        }

        // so outside functions can get ahold of the template object
        static::$instance = $this;
    }

    /**
     * Add vars to the templateset undefined vars
     *
     * @param string $index
     * @param mixed  $value
     *
     * @return void
     */
    public function __set($index, $value)
    {
        $this->vars[$index] = $value;
    }

    public function &__get($index)
    {
        if (isset($this->vars[$index]) == true) {
            return $this->vars[$index];
        } else {
            throw new \Exception('Unknown var name in template: ' . $index);
        }

    }


    /**
     * @param string $filename
     * @param null   $newvars
     *
     * @throws \Exception
     */
    public function show($filename, &$newvars = NULL)
    {

        if (is_array($newvars) == true) {
            array_merge($this->vars,$newvars);
        }

        $ipinga = \ipinga\ipinga::getInstance();

        $viewFilename = $ipinga->config('path.views') . '/' . $filename . '.view.php';

        if ($this->showViewFilename == true) {
            echo '<!-- ' . $viewFilename . ' -->' . PHP_EOL . PHP_EOL;
        }

        if (file_exists($viewFilename) == false) {
            throw new \Exception('View not found: ' . $viewFilename);
        }

        // Load variables so template code has easier access
        foreach ($this->vars as $key => $value) {
            $$key = $value;
        }

        // if sysmaint.view.php exists, then the system is down for maintenance and I should show that view instead
        $sysmaint = $ipinga->config('path.views') . '/sysmaint.view.php';
        if (file_exists($sysmaint) == true) {
            include_once($sysmaint);
        } else {
            include_once($viewFilename);
        }

    }   // show


    /**
     * @param $filename
     *
     * @throws \Exception
     */
    public function include_file($filename)
    {
        $ipinga = \ipinga\ipinga::getInstance();

        $fullFilename = $ipinga->config('path.views') . '/' . $filename . '.php';
        if (file_exists($fullFilename) == false) {
            throw new \Exception('View not found in ' . $fullFilename);
        }

        // Load variables so template code has easier access. This is redundant so it does cause a slight performance hit, but not much.
        foreach ($this->vars as $key => $value) {
            $$key = $value;
        }

        include_once($fullFilename);
    }


}
?>
