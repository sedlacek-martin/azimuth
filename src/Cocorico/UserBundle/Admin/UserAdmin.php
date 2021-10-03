<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\UserBundle\Admin;

use A2lix\TranslationFormBundle\Form\Type\TranslationsType;
use Cocorico\UserBundle\Entity\User;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use JMS\TranslationBundle\Annotation\Ignore;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserAdmin extends BaseUserAdmin
{
    public const RELEVANT_ROLES = [
        'ROLE_FACILITATOR',
        'ROLE_ACTIVATOR',
        'ROLE_USER',
        'ROLE_SUPER_ADMIN',
    ];

    protected $baseRoutePattern = 'user';
    protected $bundles;
    protected $locales;

    protected $datagridValues = array(
        '_sort_order' => 'DESC',
        '_sort_by' => 'createdAt',
    );

    public function setBundlesEnabled($bundles)
    {
        $this->bundles = $bundles;
    }

    public function setLocales($locales)
    {
        $this->locales = $locales;
    }

    /** @inheritdoc */
    protected function configureFormFields(FormMapper $formMapper): void
    {
        /* @var $subject \Cocorico\UserBundle\Entity\User */
        $subject = $this->getSubject();

        $container = $this->getConfigurationPool()->getContainer();
        $roles = $container->getParameter('security.role_hierarchy.roles');

        $rolesChoices = User::flattenRoles($roles);

        if (!$this->authIsGranted('ROLE_DEVELOPER')) {
            $rolesChoices = array_filter($rolesChoices, function ($val) {
                if (in_array($val, self::RELEVANT_ROLES)) {
                    return true;
                }

                return false;
            });
        }

        $formMapper
            ->with('Main information')
            ->add(
                'enabled',
                null,
                array(
                    'required' => false,
                )
            )
            ->add(
                'trusted',
                null,
                array(
                    'required' => false,
                )
            )
            ->add(
                'reconfirmRequested',
                null,
                []
            )
            ->add(
                'id',
                null,
                array(
                    'required' => true,
                    'disabled' => true,
                )
            )
            ->add(
                'firstName',
                null,
                array(
                    'required' => true,
                    'disabled' => true,
                )
            );

        $formMapper->add(
                'lastName',
                null,
                array(
                    'required' => true,
                    'disabled' => true,
                )
            )
            ->add(
                'email',
                null,
                array(
                    'required' => true,
                    'disabled' => true,
                )
            )
            ->add(
                'plainPassword',
                'text',
                array(
                    'required' => (!$subject || is_null($subject->getId())),
                )
            )
            ->add(
                'motherTongue',
                'language',
                array(
                    'required' => true,
                    'disabled' => true
                )
            )
            ->add(
                'memberOrganization',
                null,
                []
            );

            if ($this->authIsGranted('ROLE_SUPER_ADMIN')) {
                $formMapper
                    ->add('roles', 'choice', array(
                            'choices'  => $rolesChoices,
                            'multiple' => true,
                            'expanded' => true,
                            'help' => 'admin.user.roles.help'
                        )
                    );
            }
            $formMapper->end();

        //Translations fields
        $descriptions = array();
        foreach ($this->locales as $i => $locale) {
            $descriptions[$locale] = array(
                'label' => 'Description (about me)',
                'constraints' => array(new NotBlank())
            );
        }
        $formMapper->with('Additional information')
            ->add(
                'translations',
                TranslationsType::class,
                array(
                    'locales' => $this->locales,
                    'required_locales' => $this->locales,
                    'fields' => array(
                        'description' => array(
                            'field_type' => 'textarea',
                            'locale_options' => $descriptions,
                            'required' => false
                        ),
                    ),
                    /** @Ignore */
                    'label' => false,
                )
            )
            ->add(
                'birthday',
                'birthday',
                array(
                    'format' => 'dd - MMMM - yyyy',
                    'years' => range(date('Y') - 18, date('Y') - 80),
                    'disabled' => true,
                )
            )
            ->add(
                'timeZone',
                'timezone',
                array(
                    'label' => 'form.time_zone',
                    'required' => true,
                    'disabled' => false,
                ),
                array(
                    'translation_domain' => 'cocorico_user',
                )
            )
            ->add(
                'country',
                'country',
                array(
                    'disabled' => true,
                )
            )
            ->add(
                'emailVerified',
                null,
                array(
                    'required' => false,
                )
            )
            ->add(
                'nbBookingsOfferer',
                null,
                array(
                    'required' => false,
                    'disabled' => true,
                )
            )
            ->add(
                'nbBookingsAsker',
                null,
                array(
                    'required' => false,
                    'disabled' => true,
                )
            )
            ->add(
                'expiryDate',
                null,
                [
                    'years' => range(date('Y') - 1, date('Y') + 50),
                    ]
            )
            ->add(
                'createdAt',
                null,
                array(
                    'disabled' => true,
                )
            )
            ->end();
    }


    /** @inheritdoc */
    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper
            ->addIdentifier(
                'id',
                null,
                array()
            );

        $listMapper
            ->addIdentifier('fullname')
            ->add('email', null, [])
            ->add('verifiedDomainRegistration', null, [
                'label' => 'Registration type',
                'template' => 'CocoricoSonataAdminBundle::list_field_registration_type.html.twig',
            ])
            ->add('enabled', null, [
                'label' => 'Enabled (email confirmed)'
            ])
            ->add('createdAt', null, []);


        if ($this->authIsGranted('ROLE_SUPER_ADMIN')) {
            $listMapper
                ->add('memberOrganization', null, []);
        }

        $actions = [
            'actions' => [
                'edit' => [],
            ],
        ];

        if ($this->authIsGranted('ROLE_ALLOWED_TO_SWITCH')) {
            $actions['actions']['impersonate'] = ['template' => 'CocoricoSonataAdminBundle::list_action_impersonate.html.twig',];
        }

        $listMapper
            ->add(
                '_action',
                'actions',
                $actions
            );
    }


    /** @inheritdoc */
    protected function configureDatagridFilters(DatagridMapper $filterMapper): void
    {
        $filterMapper
            ->add('id')
            ->add(
                'fullname',
                'doctrine_orm_callback',
                array(
                    'callback' => array($this, 'getFullNameFilter'),
                    'field_type' => 'text',
                    'operator_type' => 'hidden',
                    'operator_options' => array(),
                )
            )
            ->add('email')
            ->add('trusted')
            ->add('reconfirmRequested')
            ->add('enabled')
            ->add('memberOrganization');
    }

    public function getFullNameFilter($queryBuilder, $alias, $field, $value)
    {
        if (!$value['value']) {
            return false;
        }

        $exp = new Expr();
        $queryBuilder
            ->andWhere(
                $exp->orX(
                    $exp->like($alias . '.firstName', $exp->literal('%' . $value['value'] . '%')),
                    $exp->like($alias . '.lastName', $exp->literal('%' . $value['value'] . '%')),
                    $exp->like(
                        $exp->concat(
                            $alias . '.firstName',
                            $exp->concat($exp->literal(' '), $alias . '.lastName')
                        ),
                        $exp->literal('%' . $value['value'] . '%')
                    )
                )
            );

        return true;
    }

    public function createQuery($context = 'list'): ProxyQueryInterface
    {
        $query = parent::createQuery($context);
        if (!$this->authIsGranted('ROLE_SUPER_ADMIN') && $this->getUser() !== null) {
            $query
                ->join($query->getRootAliases()[0] . '.memberOrganization', 'mo')
                ->andWhere($query->expr()->eq('mo.id', ':moId'))
                ->setParameter(':moId', $this->getUser()->getMemberOrganization()->getId());
        }

        return $query;
    }

    public function getExportFields()
    {
        $fields = array(
            'Id' => 'id',
            'First name' => 'firstName',
            'Last name' => 'lastName',
            'Email' => 'email',
            'Enabled' => 'enabled',
            'Created At' => 'createdAt',
        );

        if (array_key_exists('CocoricoMangoPayBundle', $this->bundles)) {
            $mangopayFields = array(
                'Mangopay Id' => 'mangopayId',
            );

            $fields = array_merge($fields, $mangopayFields);
        }

        return $fields;
    }

    public function getDataSourceIterator()
    {
        $datagrid = $this->getDatagrid();
        $datagrid->buildPager();

        $dataSourceIt = $this->getModelManager()->getDataSourceIterator($datagrid, $this->getExportFields());
        $dataSourceIt->setDateTimeFormat('d M Y');

        return $dataSourceIt;
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('create');
        $collection->remove('show');
    }
}
