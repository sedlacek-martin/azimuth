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
use JMS\TranslationBundle\Annotation\Ignore;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserAdmin extends BaseUserAdmin
{
    public const RELEVANT_ROLES = [
        'ROLE_FACILITATOR' => 'Facilitator',
        'ROLE_ACTIVATOR' => 'Activator',
        'ROLE_USER' => 'User',
        'ROLE_SUPER_ADMIN' => 'Super admin',
        'DEVELOPER' => 'Developer',
    ];

    public const REGISTRATION_TYPES = [
        '1' => 'registration_type_verified_domain',
        '0' => 'registration_type_normal',
    ];

    protected $baseRoutePattern = 'user';

    protected $bundles;

    protected $locales;

    protected $datagridValues = [
        '_sort_order' => 'DESC',
        '_sort_by' => 'createdAt',
    ];

    public function setBundlesEnabled($bundles)
    {
        $this->bundles = $bundles;
    }

    public function setLocales($locales)
    {
        $this->locales = $locales;
    }

    /**
     * @inheritdoc
     */
    protected function configureFormFields(FormMapper $formMapper): void
    {
        /* @var $subject \Cocorico\UserBundle\Entity\User */
        $subject = $this->getSubject();

        $container = $this->getConfigurationPool()->getContainer();
        $roles = $container->getParameter('security.role_hierarchy.roles');

        $rolesChoices = User::flattenRoles($roles);

        if (!$this->authIsGranted('ROLE_DEVELOPER')) {
            $rolesChoices = array_filter($rolesChoices, function ($val) {
                if (in_array($val, array_keys(self::RELEVANT_ROLES))) {
                    return true;
                }

                return false;
            });
        }

        $isSuperAdmin = $this->authIsGranted('ROLE_SUPER_ADMIN');

        $formMapper
            ->with('Main information')
            ->add(
                'enabled',
                null,
                [
                    'required' => false,
                ]
            )
            ->add(
                'trusted',
                null,
                [
                    'required' => false,
                ]
            )
            ->add(
                'reconfirmRequested',
                null,
                []
            )
            ->add(
                'emailVerified',
                null,
                [
                    'required' => false,
                ]
            )
            ->add(
                'id',
                null,
                [
                    'required' => true,
                    'disabled' => true,
                ]
            )
            ->add(
                'firstName',
                null,
                [
                    'required' => true,
                ]
            );

        $formMapper->add(
                'lastName',
                null,
                [
                    'required' => true,
                ]
            )
            ->add(
                'email',
                null,
                [
                    'required' => true,
                    'disabled' => !$isSuperAdmin,
                ]
            )
            ->add(
                'plainPassword',
                'text',
                [
                    'required' => (!$subject || is_null($subject->getId())),
                ]
            )
            ->add(
                'motherTongue',
                'language',
                [
                    'required' => true,
                    'disabled' => true,
                ]
            );

        if ($isSuperAdmin) {
            $formMapper
                    ->add('roles', 'choice', [
                            'choices' => $rolesChoices,
                            'multiple' => true,
                            'expanded' => true,
                            'help' => 'admin.user.roles.help',
                        ]
                    );
        }
        $formMapper->end();

        //Translations fields
        $descriptions = [];
        foreach ($this->locales as $i => $locale) {
            $descriptions[$locale] = [
                'label' => 'Description (about me)',
                'constraints' => [new NotBlank()],
            ];
        }
        $formMapper->with('Additional information')
            ->add(
                'country',
                'country',
                [
                    'disabled' => !$isSuperAdmin,
                ]
            )
            ->add(
                'memberOrganization',
                null,
                [
                    'disabled' => !$isSuperAdmin,
                ]
            )
            ->add(
                'translations',
                TranslationsType::class,
                [
                    'locales' => $this->locales,
                    'required_locales' => $this->locales,
                    'fields' => [
                        'description' => [
                            'field_type' => 'textarea',
                            'locale_options' => $descriptions,
                            'required' => false,
                        ],
                    ],
                    /* @Ignore */
                    'label' => false,
                ]
            )
            ->add(
                'birthday',
                'birthday',
                [
                    'format' => 'dd MMMM yyyy',
                    'years' => range(date('Y') - 18, date('Y') - 80),
                    'disabled' => !$isSuperAdmin,
                ]
            )
            ->add(
                'timeZone',
                'timezone',
                [
                    'label' => 'form.time_zone',
                    'required' => true,
                    'disabled' => !$isSuperAdmin,
                ],
                [
                    'translation_domain' => 'cocorico_user',
                ]
            )
            ->add(
                'expiryDate',
                null,
                [
                    'format' => 'dd MMMM yyyy',
                    'years' => range(date('Y') - 1, date('Y') + 50),
                ]
            )
            ->add(
                'createdAt',
                null,
                [
                    'disabled' => true,
                ]
            )
            ->end();
    }

    /**
     * @inheritdoc
     */
    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper
            ->addIdentifier(
                'id',
                null,
                []
            );

        $listMapper
            ->addIdentifier('fullname')
            ->add('email', null, [])
            ->add('verifiedDomainRegistration', null, [
                'label' => 'Registration type',
                'template' => 'CocoricoSonataAdminBundle::list_field_registration_type.html.twig',
            ])
            ->add('enabled', null, [
                'label' => 'Enabled (email confirmed)',
            ]);

        if ($this->authIsGranted('ROLE_SUPER_ADMIN')) {
            $listMapper
                ->add('roles', null, [
                    'template' => 'CocoricoSonataAdminBundle::list_field_roles.html.twig',
                ]);
        }

        $listMapper
            ->add('createdAt', null, []);

        if ($this->authIsGranted('ROLE_SUPER_ADMIN')) {
            $listMapper
                ->add('memberOrganization', null, [
                    'label' => 'form.label_member_organization',
                ]);
        }

        $actions = [
            'actions' => [
                'edit' => [],
            ],
        ];

        if ($this->authIsGranted('ROLE_ALLOWED_TO_SWITCH')) {
            $actions['actions']['impersonate'] = ['template' => 'CocoricoSonataAdminBundle::list_action_impersonate.html.twig'];
        }

        $listMapper
            ->add(
                '_action',
                'actions',
                $actions
            );
    }

    /**
     * @inheritdoc
     */
    protected function configureDatagridFilters(DatagridMapper $filterMapper): void
    {
        $filterMapper
            ->add('id')
            ->add(
                'fullname',
                'doctrine_orm_callback',
                [
                    'callback' => [$this, 'getFullNameFilter'],
                    'field_type' => 'text',
                    'operator_type' => 'hidden',
                    'operator_options' => [],
                ]
            );

        if ($this->authIsGranted('ROLE_SUPER_ADMIN')) {
            $filterMapper->add(
                'roles',
                'doctrine_orm_string',
                [],
                ChoiceType::class,
                ['choices' => array_flip(self::RELEVANT_ROLES)]
            );
        }

        $filterMapper
            ->add('email')
            ->add('trusted')
            ->add('reconfirmRequested')
            ->add('enabled')
            ->add('verifiedDomainRegistration',
                'doctrine_orm_string',
                [],
                ChoiceType::class,
                [
                    'choices' => array_flip(self::REGISTRATION_TYPES),
                    'translation_domain' => 'SonataAdminBundle',
                    'label' => 'Registration type',
                ])
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
        $fields = [
            'Id' => 'id',
            'First name' => 'firstName',
            'Last name' => 'lastName',
            'Email' => 'email',
            'Enabled' => 'enabled',
            'Created At' => 'createdAt',
        ];

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

    public function getBatchActions()
    {
        $actions = parent::getBatchActions();
        unset($actions['delete']);

        return $actions;
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('create');
        $collection->remove('show');
    }
}
