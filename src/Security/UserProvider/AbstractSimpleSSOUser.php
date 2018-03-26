<?php

namespace SimpleSSO\CommonBundle\Security\UserProvider;

use Symfony\Component\Security\Core\User\UserInterface;

abstract class AbstractSimpleSSOUser implements UserInterface, SimpleSSOUserInterface
{
    // Default implementation for unneeded methods.

    /**
     * {@inheritdoc}
     */
    public function getPassword()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials()
    {

    }
}
