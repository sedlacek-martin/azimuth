<?php

namespace Cocorico\SonataAdminBundle\Menu;

use Cocorico\UserBundle\Entity\User;
use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class MenuBuilder implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function menu(FactoryInterface $factory)
    {
        /** @var RequestStack $requestStack */
        $requestStack = $this->container->get('request_stack');
        $request = $requestStack->getCurrentRequest();

        /** @var AuthorizationCheckerInterface $authChecker */
        $authChecker = $this->container->get('security.authorization_checker');

        /** @var TokenStorage $tokenStorage */
        $tokenStorage = $this->container->get('security.token_storage');
        /** @var User $user */
        $user = $tokenStorage->getToken()->getUser();

        $menu = $factory->createItem('root');

        if ($authChecker->isGranted('ROLE_FACILITATOR')) {
            $facilitation = $menu->addChild('Facilitation');
            $facilitation->addChild('Settings', ['route' => 'cocorico_admin__facilitator_settings']);
            $facilitation->addChild('MO Contents', [
                'route' => 'cocorico_admin__edit_mo',
                'routeParameters' => ['id' => $user->getMemberOrganization()->getId()],
            ]);
            $facilitation->addChild('Posts validation', ['route' => 'listing-validation_list']);
            $facilitation->addChild('Messages validation', ['route' => 'admin_cocorico_message_thread_list']);
            $facilitation->addChild('All posts', ['route' => 'admin_cocorico_core_listing_list']);
        }

        if ($authChecker->isGranted('ROLE_ACTIVATOR')) {
            $activation = $menu->addChild('Activation');
            $activation->addChild('Settings', ['route' => 'cocorico_admin__activator_settings']);
            $pendingActivations = $activation->addChild('Pending activations', [
                'route' => 'verification_list',
            ]);
            $activation->addChild('Invite new', ['route' => 'invitations_create']);
            $activation->addChild('All invitations', ['route' => 'invitations_list']);
            $activation->addChild('Verified domains', ['route' => 'verified-domain_list']);
        }
        $menu->addChild('All Users', ['route' => 'admin_cocorico_user_user_list']);
        $menu->addChild('Contact messages', [
            'route' => 'admin_cocorico_contact_contact_list',
            'attributes' => ['class' => 'test-alert'],
        ]);

        return $menu;
    }
}
