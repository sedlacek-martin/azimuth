<?php
//
///*
// * This file is part of the Cocorico package.
// *
// * (c) Cocolabs SAS <contact@cocolabs.io>
// *
// * For the full copyright and license information, please view the LICENSE
// * file that was distributed with this source code.
// */
//
//namespace Cocorico\MessageBundle\Admin;
//
//use Cocorico\MessageBundle\Entity\Message;
//use Cocorico\SonataAdminBundle\Admin\BaseAdmin;
//use Cocorico\UserBundle\Repository\UserRepository;
//use Sonata\AdminBundle\Datagrid\ListMapper;
//use Sonata\AdminBundle\Form\FormMapper;
//use Sonata\AdminBundle\Route\RouteCollection;
//
//
//class MessageAdmin extends BaseAdmin
//{
//    protected $translationDomain = 'SonataAdminBundle';
//    protected $baseRoutePattern = 'message';
//    protected $locales;
//
//    // setup the default sort column and order
//    protected $datagridValues = array(
//        '_sort_order' => 'DESC',
//        '_sort_by' => 'thread'
//    );
//
//    public function setLocales($locales)
//    {
//        $this->locales = $locales;
//    }
//
//    /** @inheritdoc */
//    protected function configureFormFields(FormMapper $formMapper)
//    {
//        /** @var Message $message */
//        $message = $this->getSubject();
//
//        $senderQuery = null;
//        if ($message) {
//            /** @var UserRepository $userRepository */
//            $userRepository = $this->modelManager->getEntityManager('CocoricoUserBundle:User')
//                ->getRepository('CocoricoUserBundle:User');
//
//            $senderQuery = $userRepository->getFindOneQueryBuilder($message->getSender()->getId());
//        }
//
//        $formMapper
//            ->add(
//                'sender',
//                'sonata_type_model',
//                array(
//                    'query' => $senderQuery,
//                    'disabled' => true,
//                )
//            )
//            ->add(
//                'createdAt',
//                null,
//                array(
//                    'disabled' => true,
//                )
//            )
//            ->add(
//                'body',
//                null,
//                array(
//                    'disabled' => true,
//                )
//            )
//            ->end();
//    }
//
//    protected function configureListFields(ListMapper $listMapper)
//    {
//        $listMapper
//            ->add('thread')
//            ->add('sender')
//            ->add('body', null, [
//                'truncate' => [
//                    'length' => 150,
//                    'preserve' => true
//                ]
//            ])
//            ->add('verified')
//            ->add('createdAt');
//    }
//
//
//    protected function configureRoutes(RouteCollection $collection)
//    {
//        $collection->remove('create');
//        $collection->remove('delete');
//    }
//}
