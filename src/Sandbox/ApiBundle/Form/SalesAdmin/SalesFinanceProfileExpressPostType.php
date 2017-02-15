<?php

namespace Sandbox\ApiBundle\Form\SalesAdmin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SalesFinanceProfileExpressPostType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('recipient')
            ->add('address')
            ->add('phone')
            ->add('zip_code');
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sandbox\ApiBundle\Entity\SalesAdmin\SalesCompanyProfileExpress',
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
