<?php

namespace App\Form;

use App\Entity\Message;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use libphonenumber\PhoneNumberFormat;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;

class MessageFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //added PhoneNumberType from package
        $builder
            ->add('phone_number', PhoneNumberType::class, [
                'widget' => PhoneNumberType::WIDGET_COUNTRY_CHOICE,
                'country_choices' => array('GB', 'JE', 'FR', 'US'),
                'preferred_country_choices' => array('GB', 'JE'),
                ])
            ->add('text', TextareaType::class, [
                'required' => true,
                'constraints' => [
                    new Length([
                        'min' => 1,
                        'max' => 140,
                        'minMessage' => 'Message is too short',
                        'maxMessage' => 'Message is too long, please limit it to 140 characters'
                    ])
                ]
            ])
            ->add('send', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Message::class,
        ]);
    }
}
