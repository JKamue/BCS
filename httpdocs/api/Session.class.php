<?php

namespace Utility;

/**
 * Class Session
 * Manages Session
 */
class Session
{
    private bool $newSession;

    public function __construct()
    {
        $this->startSession();

        $print = $this->generateFingerprint();

        // Check if Session is new
        if ($this->isNew()) {
            $_SESSION['fingerprint'] = $print;
        } else {
            if ($_SESSION['fingerprint'] !== $this->generateFingerprint()) {
                $this->dropSession();
                $this->startSession();
                $this->isNew();
            }
        }
    }

    public function isNewSession(): bool
    {
        return $this->newSession;
    }

    private function isNew(): bool
    {
        if (!isset($_SESSION['new_session'])) {
            $_SESSION['new_session'] = true;

            $this->addUserAgent();
            $this->addReferer();

            $this->newSession = true;
            return true;
        } else {
            $_SESSION['new_session'] = false;
        }
        $this->newSession = false;
        return false;
    }

    private static function addUserAgent(): void
    {
        try {
            $user_agent = parse_user_agent();
        } catch (\InvalidArgumentException $e) {
            $user_agent = array( 'platform' => "unknown", 'browser' => "unknown", 'version' => "unknown" );
        }
        $_SESSION['user_agent'] = $user_agent;
    }

    private static function addReferer(): void
    {
        if (isset($_SERVER['HTTP_REFERER'])) {
            $previous_url = parse_url($_SERVER['HTTP_REFERER']);
            $_SESSION['referrer']['domain'] = str_ireplace('www.', '', $previous_url['host']);
            $_SESSION['referrer']['path'] = $previous_url["path"];
        } else {
            $_SESSION['referrer']['domain'] = "0";
            $_SESSION['referrer']['path'] = "0";
        }
    }

    private function generateFingerprint(): string
    {
        return md5($_SERVER['HTTP_USER_AGENT'] .
            $_SERVER['REMOTE_ADDR']);
    }

    public function closeSession(): void
    {
        session_gc();
        session_commit();
        session_abort();
    }

    private function dropSession(): void
    {
        session_gc();
        session_regenerate_id(false);
        session_unset();
        session_destroy();
        session_abort();
    }

    private function startSession(): void
    {
        echo "ok";
        echo getRequestedDomain();
        session_set_cookie_params([
            "lifetime" => 0,
            "path" => ini_get('session.cookie_path'),
            "domain" => getDomainWithoutProtocol(),
            "secure" => isset($_SERVER['HTTPS']),
            "httponly" => true,
            "samesite" => "Strict"
        ]);
        session_start();
    }
}