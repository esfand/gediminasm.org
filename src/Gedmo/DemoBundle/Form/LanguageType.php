<?php

namespace Gedmo\DemoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class LanguageType extends AbstractType
{
    public function getName()
    {
        return 'language';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('title', 'text', array('required' => false));
    }
}
