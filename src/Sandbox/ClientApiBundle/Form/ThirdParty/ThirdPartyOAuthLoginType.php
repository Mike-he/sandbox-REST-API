<?php

namespace Sandbox\ClientApiBundle\Form\ThirdParty;

use Sandbox\ClientApiBundle\Form\User\UserClientType;
use Sandbox\ClientApiBundle\Form\User\UserLoginDeviceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ThirdPartyOAuthLoginType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('wechat', new ThirdPartyOAuthLoginWeChatType())
            ->add('client', new UserClientType())
            ->add('device', new UserLoginDeviceType())
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sandbox\ClientApiBundle\Data\ThirdParty\ThirdPartyOAuthLoginData',
        ));
    }

    public function getName()
    {
        return '';
    }
}
