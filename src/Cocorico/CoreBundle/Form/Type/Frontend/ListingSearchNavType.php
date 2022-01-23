<?php

namespace Cocorico\CoreBundle\Form\Type\Frontend;

use Symfony\Component\Form\FormBuilderInterface;

class ListingSearchNavType extends ListingSearchHomeType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->remove('categories');
    }
}
