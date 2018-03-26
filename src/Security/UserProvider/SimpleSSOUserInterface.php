<?php

namespace SimpleSSO\CommonBundle\Security\UserProvider;

interface SimpleSSOUserInterface
{
    /**
     * Inject profile data into the user.
     *
     * @param string $id
     * @param string $emailAddress
     * @param bool   $emailAddressVerified
     * @param string $firstName
     * @param string $lastName
     * @param array  $roles
     * @param bool   $enabled
     * @param array  $extraData
     * @return mixed
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
    );

    /**
     * Get the user id.
     *
     * @return string
     */
    public function getId(): string;
}
