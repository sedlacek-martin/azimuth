<?php

namespace Cocorico\CoreBundle\Admin;

use Cocorico\CoreBundle\Entity\UserInvitation;
use Cocorico\CoreBundle\Entity\VerifiedDomain;
use Cocorico\CoreBundle\Repository\MemberOrganizationRepository;
use Cocorico\SonataAdminBundle\Admin\BaseAdmin;
use Cocorico\UserBundle\Mailer\TwigSwiftMailer;
use Doctrine\ORM\QueryBuilder;
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
            ->add('createdAt', null, array())
            ->add('memberOrganization', null, [
                'template' => 'CocoricoSonataAdminBundle::list_action_mo_all_on_null.html.twig',
            ]);

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
        $moFieldOptions = [
            'required' => false,
            'placeholder' => "All member organizations",
        ];

        if ($this->getUser()) {
            $userMoId = $this->getUser()->getMemberOrganization()->getId();
            if (!$this->authIsGranted('ROLE_SUPER_ADMIN')) {
                $moFieldOptions['query_builder'] = function (MemberOrganizationRepository $repository) use ($userMoId) {
                    return $repository->createQueryBuilder('mo')
                        ->where('mo.id = :moId')
                        ->setParameter('moId', $userMoId);
                };
                $moFieldOptions['required'] = true;
                $moFieldOptions['disabled'] = true;
                unset($moFieldOptions['placeholder']);
            }
        }

        $formMapper
            ->add('email', EmailType::class)
            ->add('memberOrganization', null, $moFieldOptions)
            ->add('expiration', DateTimeType::class)
            ->add('used', CheckboxType::class, [
                'required' => false,
                'help' => 'invite.used.help'
            ]);
    }

    public function prePersist($object)
    {
        if (!$object instanceof UserInvitation) {
            return;
        }

        if ($object->getMemberOrganization() === null && !$this->authIsGranted('ROLE_SUPER_ADMIN')) {
            $object->setMemberOrganization($this->getUser()->getMemberOrganization());
        }
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

    public function createQuery($context = 'list')
    {
        /** @var QueryBuilder $query */
        $query = parent::createQuery($context);
        if (!$this->authIsGranted('ROLE_SUPER_ADMIN') && $this->getUser() !== null) {
            $query
                ->leftJoin($query->getRootAliases()[0] . '.memberOrganization', 'mo')
                ->andWhere($query->expr()->orX(
                    $query->expr()->eq('mo.id', ':moId'),
                    $query->expr()->isNull('mo.id')))
                ->setParameter(':moId', $this->getUser()->getMemberOrganization()->getId());
        }

        return $query;
    }

}