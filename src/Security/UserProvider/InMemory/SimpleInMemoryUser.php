<?php

namespace SimpleSSO\CommonBundle\Security\UserProvider\InMemory;

use SimpleSSO\CommonBundle\Security\UserProvider\AbstractSimpleSSOUser;

class SimpleInMemoryUser extends AbstractSimpleSSOUser
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $emailAddress;

    /**
     * @var bool
     */
    private $emailAddressVerified;

    /**
     * @var string
     */
    private $firstName;

    /**
     * @var string
     */
    private $lastName;

    /**
     * @var array
     */
    private $roles;

    /**
     * @var bool
     */
    private $enabled;

    /**
     * @var array
     */
    private $extraData;

    /**
     * {@inheritdoc}
     */
    public function setProfile(
        string $id,
        string $emailAddress,
        bool $emailAddressVerified,
        string $firstName,
        string $lastName,
        array $roles,
        bool $enabled,
        array $extraData = []
    ) {
        $this->id = $id;
        $this->emailAddress = $emailAddress;
        $this->emailAddressVerified = $emailAddressVerified;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->roles = $roles;
        $this->enabled = $enabled;
        $this->extraData = $extraData;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getEmailAddress(): string
    {
        return $this->emailAddress;
    }

    /**
     * @return bool
     */
    public function isEmailAddressVerified(): bool
    {
        return $this->emailAddressVerified;
    }

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * @return string
     */
    public function getDisplayName(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->getDisplayName() . ' <' . $this->emailAddress . '>';
    }

    /**
     * @return array
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @return array
     */
    public function getExtraData(): array
    {
        return $this->extraData;
    }
}
