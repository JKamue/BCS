<?php


class Session
{
    public function __construct()
    {
        // Start Session
        $this->startSession();
        // Validate session if its already existing
        if ($this->isActive()) {
            if (!$this->isValid()) {
                $this->destroy();
                $this->createSession();
            }
        } else {
            $this->createSession();
        }
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