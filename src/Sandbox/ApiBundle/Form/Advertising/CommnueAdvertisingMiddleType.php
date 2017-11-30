<?php

namespace Sandbox\ApiBundle\Form\Advertising;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CommnueAdvertisingMiddleType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('subtitle')
            ->add('cover')
            ->add('source')
            ->add(
                'source_id',
                'integer',
                array('required' => false)
            )
            ->add(
                'url',
                'url',
                array(
                    'required' => false,
                    'mapped' => false,
                )
            )

        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sandbox\ApiBundle\Entity\Advertising\CommnueAdvertisingMiddle',
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return '';
    }
}