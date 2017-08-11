<?php

namespace Sandbox\ApiBundle\Form\SalesAdmin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class SalesFinanceProfileAccountPostType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'sales_company_name',
                TextType::class,
                array(
                    'constraints' => array(
                        new NotBlank(),
                    ),
                )
            )
            ->add('business_scope')
            ->add('bank_account_name')
            ->add('bank_account_number');
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sandbox\ApiBundle\Entity\SalesAdmin\SalesCompanyProfileAccount',
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
