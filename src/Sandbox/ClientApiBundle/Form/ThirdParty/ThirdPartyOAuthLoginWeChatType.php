<?php

namespace Sandbox\ClientApiBundle\Form\ThirdParty;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ThirdPartyOAuthLoginWeChatType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('code')
            ->add('from')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sandbox\ClientApiBundle\Data\ThirdParty\ThirdPartyOAuthWeChatData',
        ));
    }

    public function getName()
    {
        return '';
    }
}
