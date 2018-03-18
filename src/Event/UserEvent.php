<?php

namespace SimpleSSO\CommonBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Security\Core\User\UserInterface;

class UserEvent extends Event
{
    /**
     * @var UserInterface|null
     */
    private $user;

    /**
     * @param $user
     */
    public function setUser(UserInterface $user): void
    {
        $this->user = $user;
    }

    /**
     * @return UserInterface|null
     */
    public function getUser(): ?UserInterface
    {
        return $this->user;
    }
}
