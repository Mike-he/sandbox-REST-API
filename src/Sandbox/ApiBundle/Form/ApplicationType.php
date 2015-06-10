<?php

namespace Sandbox\ApiBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ApplicationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id')
            ->add('appid')
            ->add('companyid')
            ->add('ename')
            ->add('cname')
            ->add('icon')
            ->add('description')
            ->add('baseurl')
            ->add('params')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sandbox\ApiBundle\Entity\Approval',
        ));
    }

    public function getName()
    {
        return 'Sandbox_apibundle_approval';
    }
}
