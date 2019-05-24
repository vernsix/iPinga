<?php
namespace ipinga;

class manager
{


    /**
     * @var array
     */
    public $settings = array();

    /** @var string */
    public $newUrl = '';

    /** @var bool */
    public $isLoggedIn = false;

    /** @var string */
    public $message = '';

    /** @var array */
    public $loggedInDetails = array();

    /**
     * @param array $params
     */
    function __construct($overrideSettings = array())
    {
        $ipinga = \ipinga\ipinga::getInstance();

        // params override global settings
        $this->settings['manager.max_minutes'] = (isset($overrideSettings['manager.max_minutes'])) ? $overrideSettings['manager.max_minutes'] : $ipinga->config('manager.max_minutes');
        $this->settings['manager.login_url'] = (isset($overrideSettings['manager.login_url'])) ? $overrideSettings['manager.login_url'] : $ipinga->config('manager.login_url');
        $this->settings['manager.expired_url'] = (isset($overrideSettings['manager.expired_url'])) ? $overrideSettings['manager.expired_url'] : $ipinga->config('manager.expired_url');
        $this->settings['manager.ip_changed_url'] = (isset($overrideSettings['manager.ip_changed_url'])) ? $overrideSettings['manager.ip_changed_url'] : $ipinga->config('manager.ip_changed_url');

        $this->newUrl = '';
    }

    private function loadFromCookie()
    {
        if (\ipinga\cookie::keyExists('loggedInDetails') == true) {
            $this->loggedInDetails = \ipinga\cookie::keyValue('loggedInDetails');
        } else {
            $this->loggedInDetails = array();
        }
    }

    private function shutdown()
    {
        \ipinga\cookie::add('loggedInDetails', $this->loggedInDetails);
    }


    function logout()
    {
        $this->loggedInDetails = array();
        $this->isLoggedIn = false;
        $this->shutdown();
    }


    /**
     * @param int $UserId
     */
    function update($userId)
    {
        $this->loadFromCookie();
        $this->loggedInDetails['LAST_ACTIVITY'] = strtotime("now");
        $this->loggedInDetails['REMOTE_ADDR'] = $_SERVER['REMOTE_ADDR'];
        $this->loggedInDetails['USER_ID'] = $userId;
        $this->shutdown();
    }


    /**
     * @param bool|true $redirectIfNotLoggedIn   If true and the user is not logged in, then redirect elsewhere right now
     *
     * @return bool $isLoggedIn
     */
    function userIsLoggedIn($redirectIfNotLoggedIn = true)
    {
        $this->loadFromCookie();

        $this->newUrl = '';    // just in case we are called twice by mistake, start over.  It happens.

        if ( !isset($this->loggedInDetails['USER_ID']) ) {

            $this->newUrl = $this->settings['manager.login_url'];
            $this->message = 'You are not logged in';

        } else {

            $currentTime   = strtotime("now");
            $lastTime      = $this->loggedInDetails['LAST_ACTIVITY'];
            $difference    = $currentTime - $lastTime;

            if ($difference > ($this->settings['manager.max_minutes'] * 60)) {
                $this->newUrl = $this->settings['manager.expired_url'];
                $this->message = 'You have been logged out due to inactivity';
            } else {
                if (!$this->loggedInDetails['REMOTE_ADDR'] == $_SERVER['REMOTE_ADDR']) {
                    $this->newUrl = $this->settings['manager.ip_changed_url'];
                    $this->message = 'You have been logged out because your ip address changed';
                }
            }

        }

        if (empty($this->newUrl)) {

            // we didn't redirect them anywhere, so they must be logged in...
            $this->isLoggedIn = true;
            $this->shutdown();

        } else {

            $this->isLoggedIn = false;

            // I dislike unclean exits, but a user begged me to put this in here, so...
            if ($redirectIfNotLoggedIn) {
                $this->loggedInDetails = array();
                $this->shutdown();
                header('location: ' . $this->newUrl);
                exit(); // I hate this!
            }

            $this->shutdown();
        }

        return $this->isLoggedIn;

    }

}
