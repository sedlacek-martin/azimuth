<?php

namespace Cocorico\CoreBundle\Admin;

use Cocorico\CoreBundle\Entity\VerifiedDomain;
use Cocorico\CoreBundle\Repository\MemberOrganizationRepository;
use Cocorico\SonataAdminBundle\Admin\BaseAdmin;
use Doctrine\ORM\QueryBuilder;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class VerifiedDomainAdmin extends BaseAdmin
{
    protected $translationDomain = 'SonataAdminBundle';
    protected $baseRoutePattern = 'verified-domain';
    protected $baseRouteName = 'verified-domain';
    protected $locales;

    // setup the default sort column and order
    protected $datagridValues = array(
        '_sort_order' => 'DESC',
        '_sort_by' => 'íd'
    );

    public function setLocales($locales)
    {
        $this->locales = $locales;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper

            ->addIdentifier('id', null, [])
            ->add('domain', null, array())
            ->add('memberOrganization', null, []);

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
        $moFieldOptions = ['required' => true];

        if ($this->getUser()) {
            $userMoId = $this->getUser()->getMemberOrganization()->getId();
            if (!$this->authIsGranted('ROLE_SUPER_ADMIN')) {
                $moFieldOptions['query_builder'] = function (MemberOrganizationRepository $repository) use ($userMoId) {
                    return $repository->createQueryBuilder('mo')
                        ->where('mo.id = :moId')
                        ->setParameter('moId', $userMoId);
                };
                $moFieldOptions['disabled'] = true;
            }
        }

        $formMapper
            ->add('domain', TextType::class, [
                'label' => 'Domain (without @)',
            ])
            ->add('memberOrganization', null, $moFieldOptions);
    }

    protected function configureDatagridFilters(DatagridMapper $filter)
    {
        $filter
            ->add('memberOrganization', null,  []);
    }


    public function prePersist($object)
    {
        if (!$object instanceof VerifiedDomain) {
            return;
        }

        if ($object->getMemberOrganization() === null) {
            $object->setMemberOrganization($this->getUser()->getMemberOrganization());
        }
    }


    public function createQuery($context = 'list')
    {
        /** @var QueryBuilder $query */
        $query = parent::createQuery($context);
        if (!$this->authIsGranted('ROLE_SUPER_ADMIN') && $this->getUser() !== null) {
            $query
                ->join($query->getRootAliases()[0] . '.memberOrganization', 'mo')
                ->andWhere($query->expr()->eq('mo.id', ':moId'))
                ->setParameter(':moId', $this->getUser()->getMemberOrganization()->getId());
        }

        return $query;
    }


}