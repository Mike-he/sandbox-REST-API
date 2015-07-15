<?php

namespace Sandbox\ApiBundle\Form\Company;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CompanyType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('description')
            ->add('address')
            ->add('phone')
            ->add('fax')
            ->add('email')
            ->add('website')
            ->add('sina_weibo')
            ->add('tencent_weibo')
            ->add('facebook')
            ->add('linkedin')
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sandbox\ApiBundle\Entity\Company\Company',
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
