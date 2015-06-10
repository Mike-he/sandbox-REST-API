<?php

namespace Sandbox\ApiBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CompanyEmailDomainType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id')
            ->add('companyid')
            ->add('domain')
            ->add('email')
            ->add('joinoption')
            ->add('activated')
            ->add('creatorid')
            ->add('creationdate')
            ->add('modificationdate')
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sandbox\ApiBundle\Entity\CompanyEmailDomain',
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
