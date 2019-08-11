<?php


class Session
{
    public function __construct()
    {
        // Start Session
        $this->startSession();
        // Validate session if its already existing
        if (!$this->isActive() || isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 1800)) {
            $this->createSession();
        } else {
            if (!$this->isValid()) {
                $this->destroy();
                $this->createSession();
            }
        }
        $_SESSION['LAST_ACTIVITY'] = time();
    }

    private function startSession()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    private function isActive()
    {
        if (isset($_SESSION['active'])) {
            return true;
        } else {
            return false;
        }
    }

    private function isValid()
    {
        if ($_SESSION['hash'] == $this->genHash()) {
            return true;
        } else {
            return false;
        }
    }

    public function isLogedIn()
    {
        if (isset($_SESSION['logedin'])) {
            return true;
        } else {
            return false;
        }
    }

    public function createSession()
    {
        $_SESSION['active'] = true;
        $_SESSION['hash'] = $this->genHash();
    }

    private static function genHash()
    {
        return sha1($_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR']);
    }

    public function login($username, $rank)
    {
        $_SESSION['logedin'] = 1;
        $_SESSION['name'] = $username;
        $_SESSION['rank'] = $rank;
    }

    public function destroy()
    {
        session_unset();
        session_destroy();
    }
}