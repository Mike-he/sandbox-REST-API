<?php

namespace Sandbox\ApiBundle\Form\Advertising;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CommnueAdvertisingScreenType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('url')
            ->add('source')
            ->add(
                'source_id',
                'integer',
                array('required' => false)
            )
            ->add('visible')
            ->add('attachments')
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sandbox\ApiBundle\Entity\Advertising\CommnueAdvertisingScreen',
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
