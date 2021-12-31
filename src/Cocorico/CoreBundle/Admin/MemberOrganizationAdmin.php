<?php

namespace Cocorico\CoreBundle\Admin;

use Cocorico\CoreBundle\Entity\MemberOrganization;
use Cocorico\CoreBundle\Utils\ElFinderHelper;
use Cocorico\SonataAdminBundle\Admin\BaseAdmin;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class MemberOrganizationAdmin extends BaseAdmin
{
    protected $translationDomain = 'SonataAdminBundle';
    protected $baseRoutePattern = 'member-organization';
    protected $baseRouteName = 'member-organization';
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
            ->add('name', null, [])
            ->add('country', null, [])
            ->add('description', 'html', [
                'truncate' => [
                    'length' => 75,
                    'preserve' => true
                ]
            ]);

        $listMapper->add(
            '_action',
            'actions',
            array(
                'actions' => array(
                    'delete' => [],
                    'edit' => [],
                )
            )
        );
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        /** @var MemberOrganization $mo */
        $mo = $this->getSubject();
        $countryCode = "";
        if ($mo) {
            $countryCode = $mo->getCountry();
        }


        $formMapper
            ->add('name', TextType::class)
            ->add('country', 'country', [
                'required' => true,
                'preferred_choices' => ["CZ", "FR", "ES", "DE", "RU"],
            ])
            ->add('abstract', TextType::class)
            ->add('description', CKEditorType::class, [
                'config' => [
                    'filebrowserBrowseRoute' => 'elfinder',
                    'filebrowserBrowseRouteParameters' => [
                        'instance' => 'ckeditor',
                        'homeFolder' => ElFinderHelper::getOrCreateFolder($countryCode, $this->getKernelRoot())
                    ]
                ]
            ])
            ->add('requiresUserIdentifier')
            ->add('userIdentifierDescription');
    }

    protected function configureDatagridFilters(DatagridMapper $filter)
    {
        $filter
            ->add('country', null,  []);
    }
}