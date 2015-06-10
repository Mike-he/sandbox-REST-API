<?php

namespace Sandbox\ApiBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MenuType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id')
            ->add('companyid')
            ->add('tagid')
            ->add('parentid')
            ->add('ename')
            ->add('cname')
            ->add('url')
            ->add('menutype')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sandbox\ApiBundle\Entity\Menu',
        ));
    }

    public function getName()
    {
        return 'Sandbox_apibundle_menu';
    }
}
