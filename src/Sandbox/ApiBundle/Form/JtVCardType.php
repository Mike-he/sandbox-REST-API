<?php

namespace Sandbox\ApiBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class JtVCardType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id')
            ->add('userid')
            ->add('companyid')
            ->add('name')
            ->add('email')
            ->add('phone')
            ->add('location')
            ->add('gender')
            ->add('aboutme')
            ->add('hobbies')
            ->add('skills')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sandbox\ApiBundle\Entity\JtVCard',
        ));
    }

    public function getName()
    {
        return '';
    }
}
