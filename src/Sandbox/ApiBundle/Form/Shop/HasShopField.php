<?php

namespace Sandbox\ApiBundle\Form\Shop;

use Symfony\Component\Form\FormBuilderInterface;

trait HasShopField
{
    /**
     * @param FormBuilderInterface $builder     Builder to modify
     * @param string               $application
     */
    protected function addShopField(FormBuilderInterface $builder)
    {
        $builder
            ->add(
                'name',
                'text',
                array(
                    'required' => false,
                )
            )
            ->add(
                'description',
                'text',
                array(
                    'required' => false,
                )
            )
            ->add(
                'start',
                'text',
                array(
                    'required' => false,
                )
            )
            ->add(
                'end',
                'text',
                array(
                    'required' => false,
                )
            )
            ->add(
                'attachments',
                null,
                array(
                    'required' => false,
                )
            );
    }
}
