<?php

namespace SimpleSSO\CommonBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Security\Core\User\UserInterface;

class UserEvent extends Event
{
    /**
     * @var array
     */
    private $profileData;

    /**
     * @var UserInterface|null
     */
    private $user;

    /**
     * UserEvent constructor.
     *
     * @param array $profileData
     */
    public function __construct(array $profileData)
    {
        $this->profileData = $profileData;
    }

    /**
     * @param $user
     */
    public function setUser(UserInterface $user): void
    {
        $this->user = $user;
    }

    /**
     * @return array
     */
    public function getProfileData(): array
    {
        return $this->profileData;
    }

    /**
     * @return UserInterface|null
     */
    public function getUser(): ?UserInterface
    {
        return $this->user;
    }
}
