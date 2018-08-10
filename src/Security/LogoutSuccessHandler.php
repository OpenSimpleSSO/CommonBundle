<?php

namespace SimpleSSO\CommonBundle\Security;

use SimpleSSO\CommonBundle\Model\AuthServerModel;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;

class LogoutSuccessHandler implements LogoutSuccessHandlerInterface
{
    /**
     * @var AuthServerModel
     */
    private $model;

    /**
     * LogoutSuccessHandler constructor.
     *
     * @param AuthServerModel $model
     */
    public function __construct(AuthServerModel $model)
    {
        $this->model = $model;
    }

    /**
     * {@inheritdoc}
     */
    public function onLogoutSuccess(Request $request)
    {
        return new RedirectResponse($this->model->getLogoutEndPoint());
    }
}
