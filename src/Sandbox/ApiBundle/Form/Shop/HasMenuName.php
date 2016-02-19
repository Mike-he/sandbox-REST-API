<?php

namespace Sandbox\ApiBundle\Form\Shop;

use Symfony\Component\Form\FormBuilderInterface;

trait HasMenuName
{
    /**
     * @param FormBuilderInterface $builder     Builder to modify
     * @param string               $application
     */
    protected function addMenuName(FormBuilderInterface $builder)
    {
        $builder
            ->add('name');
    }
}
