<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;


class UserModificationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('pseudo',TextType::class,[
                'attr' => [
                    'class' => 'form-control',
                    'minlength' => '2',
                    'maxlength' => '100',
                ],
                'label' => 'Pseudo',
                'label_attr' => [
                    'class' => 'form-label mt-4',
                ],
                'constraints' => [
                    new Assert\Length(['min' => 2, 'max' => 100]),
                    new Assert\NotBlank(),
                    new Assert\NotNull()
                ],
            ])
            ->add('firstName', TextType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'minlength' => '2',
                    'maxlength' => '100',
                ],
                'label' => 'Prenom',
                'label_attr' => [
                    'class' => 'form-label mt-4',
                ],
                'constraints' => [
                    new Assert\Length(['min' => 2, 'max' => 100]),
                    new Assert\NotBlank(),
                    new Assert\NotNull()
                ],
            ])
            ->add('lastName',TextType::class,[
                'attr' => [
                    'class' => 'form-control',
                    'minlength' => '2',
                    'maxlength' => '100',
                ],
                'label' => 'Nom',
                'label_attr' => [
                    'class' => 'form-label mt-4',
                ],
                'constraints' => [
                    new Assert\Length(['min' => 2, 'max' => 100]),
                    new Assert\NotBlank(),
                    new Assert\NotNull()
                ],
            ])
            ->add('phoneNumber', TextType::class,[
                'attr'=>[
                    'class' => 'form-control',
                    'maxlength' => '12',
                ],
                'label' => 'telephone',
                'label_attr' => [
                    'class' => 'form-label mt-4',
                ],
                'constraints' => [
                    new Assert\Length(['max' => 12]),
                ],
            ])
            ->add('email', EmailType::class, [
                'attr'=>[
                    'class' => 'form-control',
                    'minlength' => '2',
                    'maxlength' => '180',
                ],
                'label' => 'email',
                'label_attr' => [
                    'class' => 'form-label mt-4',
                ],
                'constraints' => [
                    new Assert\Length(['min' => 2, 'max' => 180]),
                    new Assert\NotBlank(),
                    new Assert\NotNull()
                ],
            ])
            ->add('plainPassword',RepeatedType::class, [
                'type'=>PasswordType::class,
                'first_options'=>[
                    'attr' => [
                    'class' => 'form-control',
                ],
                    'label' => 'Mot de Passe',
                    'label_attr' => [
                        'class' => 'form-label mt-4',
                    ],
                ],
                'second_options'=>[
                    'attr' => [
                        'class' => 'form-control',
                    ],
                    'label' => 'Confirmer mot de passe',
                    'label_attr' => [
                        'class' => 'form-label mt-4',
                    ],
                'invalid_message'=>'les mots de passe sont differents',
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\NotNull()
                ],
                ],
            ])
            //TODO completer champ par defaut valeur null
            ->add('campus', EntityType::class, [
                'class'=> Campus::class,
                'label'=>'Campus'
            ])
        ;
        //TODO Inserer photo ?
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
