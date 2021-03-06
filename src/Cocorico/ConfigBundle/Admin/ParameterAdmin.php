<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\ConfigBundle\Admin;

use Cocorico\ConfigBundle\Entity\Parameter;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class ParameterAdmin extends AbstractAdmin
{
    protected $translationDomain = 'SonataAdminBundle';

    protected $baseRoutePattern = 'parameter';

    // setup the default sort column and order
    protected $datagridValues = [
        '_sort_order' => 'ASC',
        '_sort_by' => 'name',
    ];

    /**
     * @inheritdoc
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        /** @var Parameter $parameter */
        $parameter = $this->getSubject();

        $formMapper
            ->with(
                'admin.parameter.title',
                [
                    'description' => 'admin.parameters.warning',
                    'translation_domain' => 'SonataAdminBundle',
                ]
            )
            ->add(
                'name',
                null,
                [
                    'label' => 'admin.parameter.name.label',
                    'disabled' => true,
                ]
            )
            ->add(
                'description',
                null,
                [
                    'label' => 'admin.parameter.description.label',
                    'disabled' => true,
                ]
            )
            ->add(
                'value',
                $parameter ? $parameter->getType() : null,
                [
                    'label' => 'admin.parameter.value.label',
                    'required' => false,
                ]
            )
            ->end();
    }

    /**
     * @inheritdoc
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add(
                'name',
                null,
                ['label' => 'admin.parameter.name.label']
            )
            ->add(
                'value',
                null,
                ['label' => 'admin.parameter.value.label']
            );
    }

    /**
     * @inheritdoc
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id')
            ->add(
                'name',
                null,
                ['label' => 'admin.parameter.name.label']
            )
            ->add(
                'value',
                null,
                [
                    'label' => 'admin.parameter.value.label',
                    'template' => 'CocoricoConfigBundle::list_parameter_value.html.twig',
                ]
            );

        $listMapper->add(
            '_action',
            'actions',
            [
                'actions' => [
                    'show' => [],
                    'edit' => [],
                ],
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->with(
                'Parameter',
                [
                    'class' => 'col-md-8',
                    'box_class' => 'box box-solid box-danger',
                    'description' => '',
                ]
            )
            ->add('name')
            ->add('value')
            ->end();
    }

    public function getBatchActions()
    {
        $actions = parent::getBatchActions();
        unset($actions['delete']);

        return $actions;
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('create');
        $collection->remove('delete');
    }

    /**
     * @param mixed $parameter
     * @return bool
     */
    public function postUpdate($parameter)
    {
        return $this->clearCache();
    }

    /**
     * Clear cache
     *
     * @return bool
     */
    private function clearCache()
    {
        $container = $this->getConfigurationPool()->getContainer();
        $kernel = $container->get('kernel');
        $php = $container->getParameter('cocorico_config_php_cli_path');

        //Clear cache
        $rootDir = $kernel->getRootDir();
        $command = $php . ' ../bin/console cache:clear --env=' . $kernel->getEnvironment();

        $process = new Process($command);

        try {
            $process->mustRun();
            $content = $process->getOutput();
        } catch (ProcessFailedException $e) {
            $content = $e->getMessage();

            return false;
        }

        return true;
    }
}
