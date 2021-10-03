<?php

namespace Cocorico\CoreBundle\Admin;

use Cocorico\CoreBundle\Entity\UserInvitation;
use Cocorico\SonataAdminBundle\Admin\BaseAdmin;
use Cocorico\UserBundle\Mailer\TwigSwiftMailer;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

class InvitationAdmin extends BaseAdmin
{
    protected $translationDomain = 'SonataAdminBundle';
    protected $baseRoutePattern = 'invitations';
    protected $baseRouteName = 'invitations';
    protected $locales;

    // setup the default sort column and order
    protected $datagridValues = array(
        '_sort_order' => 'DESC',
        '_sort_by' => 'createdAt'
    );

    public function setLocales($locales)
    {
        $this->locales = $locales;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper

            ->addIdentifier('id', null, [])
            ->add('email', null, array())
            ->add('used', null, array())
            ->add('expiration', null, array())
            ->add('createdAt', null, array());

        $listMapper->add(
            '_action',
            'actions',
            array(
                'actions' => array(
                    'delete' => array(),
                    'edit' => array(),
                )
            )
        );
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('email', EmailType::class)
            ->add('expiration', DateTimeType::class)
            ->add('used', CheckboxType::class, [
                'required' => false,
                'help' => 'invite.used.help'
            ]);
    }

    /**
     * @param UserInvitation $object
     */
    public function postPersist($object)
    {
        /** @var TwigSwiftMailer $mailer */
        $mailer = $this->getContainer()->get('cocorico_user.mailer.twig_swift');
        $mailer->sendUserInvited($object->getEmail());
    }


    protected function configureDatagridFilters(DatagridMapper $filter)
    {
        $filter
            ->add('used', null,  []);
    }

    protected function configureRoutes(RouteCollection $collection)
    {
    }

}