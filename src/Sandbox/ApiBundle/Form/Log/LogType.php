<?php

namespace Sandbox\ApiBundle\Form\Log;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class LogType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('logModule')
            ->add('logAction')
            ->add('logObjectKey')
            ->add('logObjectId')
            ->add('logObjectJson');
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sandbox\ApiBundle\Entity\Log\Log',
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
