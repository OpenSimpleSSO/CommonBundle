<?php

namespace SimpleSSO\CommonBundle\Security\UserProvider\InMemory;

use LogicException;
use SimpleSSO\CommonBundle\Model\ApiRequestModel;
use SimpleSSO\CommonBundle\Security\UserProvider\SimpleSSOUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProvider implements UserProviderInterface
{
    /**
     * @var ApiRequestModel
     */
    private $apiRequestModel;

    /**
     * @var string|null
     */
    private $userClass;

    /**
     * UserProvider constructor.
     *
     * @param ApiRequestModel $apiRequestModel
     * @param string|null     $userClass
     */
    public function __construct(ApiRequestModel $apiRequestModel, string $userClass = null)
    {
        $this->apiRequestModel = $apiRequestModel;
        if ($userClass && !in_array(SimpleSSOUserInterface::class, class_implements($userClass))) {
            throw new LogicException('User class must implement ' . SimpleSSOUserInterface::class . '.');
        }
        $this->userClass = $userClass;
    }

    public function loadUserByUsername($username)
    {
        $userData = $this->apiRequestModel->getUserProfile($username);

        $userClass = $this->userClass ?? SimpleInMemoryUser::class;
        /** @var SimpleSSOUserInterface $user */
        $user = new $userClass();
        $user->setProfile(
            $this->extractFromData('id', $userData),
            $this->extractFromData('emailAddress', $userData),
            $this->extractFromData('emailAddressVerified', $userData),
            $this->extractFromData('firstName', $userData),
            $this->extractFromData('lastName', $userData),
            $this->extractFromData('roles', $userData),
            $this->extractFromData('enabled', $userData),
            $userData
        );

        return $user;
    }

    public function refreshUser(UserInterface $user)
    {
        return $user;
    }

    public function supportsClass($class)
    {
        return $class instanceof SimpleSSOUserInterface;
    }

    private function extractFromData(string $key, array &$data)
    {
        if (!key_exists($key, $data)) {
            return null;
        }
        $value = $data[$key];
        unset($data[$key]);

        return $value;
    }
}
