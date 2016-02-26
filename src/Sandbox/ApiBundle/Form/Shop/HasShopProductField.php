<?php

namespace Sandbox\ApiBundle\Form\Shop;

use Symfony\Component\Form\FormBuilderInterface;

trait HasShopProductField
{
    /**
     * @param FormBuilderInterface $builder     Builder to modify
     * @param string               $application
     */
    protected function addShopProductField(FormBuilderInterface $builder)
    {
        $builder
            ->add('menu_id', 'integer')
            ->add('name')
            ->add('description')
            ->add('attachments');
    }
}
