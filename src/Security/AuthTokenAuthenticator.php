<?php

namespace SimpleSSO\CommonBundle\Security;

use SimpleSSO\CommonBundle\Exception\InvalidTokenException;
use SimpleSSO\CommonBundle\Model\ClientTokenModel;
use SimpleSSO\CommonBundle\Model\Data\SignedToken;
use SimpleSSO\CommonBundle\Event\UserEvent;
use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

class AuthTokenAuthenticator extends AbstractGuardAuthenticator
{
    private const SESSION_NONCE = 'security.authentication.nonce';

    /**
     * @var ClientTokenModel
     */
    private $tokenModel;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * AuthTokenAuthenticator constructor.
     *
     * @param ClientTokenModel         $tokenModel
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        ClientTokenModel $tokenModel,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->tokenModel = $tokenModel;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Request $request)
    {
        return $request->getPathInfo() === '/authenticate';
    }

    /**
     * {@inheritdoc}
     */
    public function getCredentials(Request $request)
    {
        try {
            $data = $this->tokenModel->receiveTokenFromAuthServer(
                SignedToken::FromRequest($request),
                [ 'userId', 'nonce' ]
            );
        } catch (InvalidTokenException $exception) {
            throw new AuthenticationException('Authentication failed.', 0, $exception);
        }

        // Check the credentials here.
        // This avoid creating an unneeded user in getUser() in case this token is a fraud and the user does not exists
        // yet in the service (checkCredentials() is called after getUser() and getUser() do create the user if it does
        // not exists).
        $this->checkNonce($request->getSession(), $data['nonce']);

        return $data;
    }

    /**
     * @param SessionInterface $session
     * @param string           $nonce
     */
    private function checkNonce(SessionInterface $session, string $nonce): void
    {
        if (!$session->has(self::SESSION_NONCE)) {
            throw new AuthenticationException('Authentication failed', 0, new InvalidTokenException('The user session is missing the nonce attribute.'));
        }
        if ($session->get(self::SESSION_NONCE) !== $nonce) {
            throw new AuthenticationException('Authentication failed', 0, new InvalidTokenException('The nonce in token does not match the nonce in session.'));
        }
        $session->remove(self::SESSION_NONCE);
    }

    /**
     * {@inheritdoc}
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $user = $userProvider->loadUserByUsername($credentials['userId']);
        if (!$user) {
            $event = new UserEvent();
            $this->eventDispatcher->dispatch('authentication.unknownUserAuthenticated', $event);

            $user = $event->getUser();
        }

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        // Already checked in getCredentials()
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        throw new AccessDeniedHttpException('Authentication failed.', $exception);
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsRememberMe()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        $nonce = Uuid::uuid4()->toString();
        $request->getSession()->set(self::SESSION_NONCE, $nonce);
        $token = $this->tokenModel->emitAccessToken($nonce);

        return new RedirectResponse($this->tokenModel->generateAuthenticationUrl($token));
    }
}
