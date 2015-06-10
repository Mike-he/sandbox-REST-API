<?php

namespace Sandbox\ApiBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class InvitationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id')
            ->add('companyid')
            ->add('userid')
            ->add('name')
            ->add('email')
            ->add('countrycode')
            ->add('phone')
            ->add('role')
            ->add('invitedby')
            ->add('tagid')
            ->add('status')
            ->add('token')
            ->add('creationdate')
            ->add('modificationdate')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sandbox\ApiBundle\Entity\Invitation',
        ));
    }

    public function getName()
    {
        return '';
    }
}
