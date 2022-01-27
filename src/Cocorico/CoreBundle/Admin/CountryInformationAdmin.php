<?php

namespace Cocorico\CoreBundle\Admin;

use Cocorico\CoreBundle\Entity\CountryInformation;
use Cocorico\CoreBundle\Utils\ElFinderHelper;
use Cocorico\SonataAdminBundle\Admin\BaseAdmin;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class CountryInformationAdmin extends BaseAdmin
{
    protected $translationDomain = 'SonataAdminBundle';

    protected $baseRoutePattern = 'country-information';

    protected $baseRouteName = 'country-information';

    protected $locales;

    // setup the default sort column and order
    protected $datagridValues = [
        '_sort_order' => 'DESC',
        '_sort_by' => 'íd',
    ];

    public function setLocales($locales)
    {
        $this->locales = $locales;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper

            ->addIdentifier('id', null, [])
            ->add('country', 'country', [])
            ->add('description', 'html', [
                'label' => 'admin.page.description.label',
                'truncate' => [
                    'length' => 75,
                    'preserve' => true,
                ],
            ]);

        $listMapper->add(
            '_action',
            'actions',
            [
                'actions' => [
                    'edit' => [],
                ],
            ]
        );
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        /** @var CountryInformation $country */
        $country = $this->getSubject();

        $countryCode = '';
        if ($country) {
            $countryCode = $country->getCountry();
        }

        $formMapper
            ->add('country', 'country')
            ->add('description', CKEditorType::class, [
                'config' => [
                    'filebrowserBrowseRoute' => 'elfinder',
                    'filebrowserBrowseRouteParameters' => [
                        'instance' => 'ckeditor',
                        'homeFolder' => ElFinderHelper::getOrCreateFolder($countryCode, $this->getKernelRoot()),
                    ],
                ],
            ]);
    }

    protected function configureDatagridFilters(DatagridMapper $filter)
    {
        $filter
            ->add('country', null, []);
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('create');
        $collection->remove('delete');
    }
}
