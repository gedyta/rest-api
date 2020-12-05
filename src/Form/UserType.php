<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email')
            ->add('username')
            ->add('roles')
            ->add('plainPassword', RepeatedType::class, [
                      'type' => PasswordType::class,
                      'invalid_message' => 'The password fields must match.',
                      'options' => ['attr' => ['class' => 'password-field']],
                      'required' => true,
                       'first_options'  => ['label' => 'Password'],
                       'second_options' => ['label' => 'Repeat Password'],
                   ])
            ->add('Roles', ChoiceType::class, [
                        'required' => true,
                        'multiple' => true,
                        'expanded' => false,
                        'choices'  => [
                          'User' => 'ROLE_USER',
                          'Partner' => 'ROLE_PARTNER',
                          'Admin' => 'ROLE_ADMIN',
                        ],
                    ]);

        $builder->get('roles')
            ->addModelTransformer(new CallbackTransformer(
                function ($rolesArray) {
                     // transform the array to a string
                     return count($rolesArray)? $rolesArray[0]: null;
                },
                function ($rolesString) {
                     // transform the string back to an array
                     return [$rolesString];
                }
        ));

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
