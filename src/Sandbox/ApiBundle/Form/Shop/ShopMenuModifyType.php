<?php

namespace Sandbox\ApiBundle\Form\Shop;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ShopMenuModifyType extends AbstractType
{
    use HasMenuName;
    use HasMenuId;

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->addMenuId($builder);
        $this->addMenuName($builder);
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sandbox\ShopApiBundle\Data\Shop\ShopMenuItem',
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
