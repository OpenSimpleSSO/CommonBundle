<?php

namespace SimpleSSO\CommonBundle\Twig;

use SimpleSSO\CommonBundle\Model\AuthServerModel;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Vinorcola\DistantVersionedAssetsBundle\Model\VersionModel;

class Extension extends AbstractExtension
{
    /**
     * @var AuthServerModel
     */
    private $authServerModel;

    /**
     * Extension constructor.
     *
     * @param AuthServerModel $authServerModel
     */
    public function __construct(AuthServerModel $authServerModel)
    {
        $this->authServerModel = $authServerModel;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('sso_logout_url', [ $this->authServerModel, 'getLogoutEndPoint' ]),
        ];
    }
}
