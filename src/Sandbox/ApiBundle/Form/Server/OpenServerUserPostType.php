<?php

namespace Sandbox\ApiBundle\Form\Server;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class OpenServerUserPostType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email')
            ->add('password')
            ->add('name');
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sandbox\ApiBundle\Entity\Server\OpenServerUser',
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
