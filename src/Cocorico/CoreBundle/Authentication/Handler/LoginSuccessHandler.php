<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Cocorico\CoreBundle\Authentication\Handler;

use Cocorico\CoreBundle\Entity\UserLogin;
use Cocorico\UserBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler;
use Symfony\Component\Security\Http\HttpUtils;

class LoginSuccessHandler extends DefaultAuthenticationSuccessHandler
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @param HttpUtils $httpUtils
     * @param array     $options
     */
    public function __construct(HttpUtils $httpUtils, EntityManagerInterface $entityManager, array $options)
    {
        parent::__construct($httpUtils, $options);
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritDoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        /** @var User $user */
        $user = $token->getUser();

        $response = parent::onAuthenticationSuccess($request, $token);

        // create entry about user login
        $ip = $request->getClientIp();
        $login = UserLogin::create($user, $ip);

        $request->getSession()->set('loggedIn', 1);

        $this->entityManager->persist($login);
        $this->entityManager->flush();

        return $response;
    }
}
