<?php

namespace SimpleSSO\CommonBundle\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class AuthenticationController
{
    private const TARGET_PATH = '_security.main.target_path';

    public function authenticate(SessionInterface $session): Response
    {
        if ($session->has(self::TARGET_PATH)) {
            $url = $session->get(self::TARGET_PATH);
            $session->remove(self::TARGET_PATH);
        } else {
            $url = '/';
        }

        return new RedirectResponse($url);
    }
}
