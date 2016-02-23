<?php

namespace Sandbox\ApiBundle\Form\Shop;

use Symfony\Component\Form\FormBuilderInterface;

trait HasMenuId
{
    /**
     * @param FormBuilderInterface $builder     Builder to modify
     * @param string               $application
     */
    protected function addMenuId(FormBuilderInterface $builder)
    {
        $builder
            ->add('id');
    }
}
