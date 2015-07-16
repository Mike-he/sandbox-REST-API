<?php

namespace Sandbox\ApiBundle\Form\Company;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CompanyPortfolioType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('content')
            ->add('attachment_type')
            ->add('file_name')
            ->add('preview')
            ->add('size')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sandbox\ApiBundle\Entity\Company\CompanyPortfolio',
        ));
    }

    public function getName()
    {
        return '';
    }
}
