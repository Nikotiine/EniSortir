<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Event;
use App\Entity\Location;
use App\Entity\Status;

use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name',TextType::class,[
                'attr'=>[
                    'class'=>'form-control'
                ],
                'label'=>'Nom de la sortie',
                'label_attr'=>[
                    'class'=>'form-label'
                ]
            ])
            ->add('startAt',DateTimeType::class,[
                "attr" => [
                    "class" => "form-control mt-2",
                ],
                "input" => "datetime_immutable",
                "label" => "Date de la sortie",
                "label_attr" => [
                    "class" => "form-label mt-3",
                ],
                "html5" => false,
            ])
            ->add('deadLineInscriptionAt',DateTimeType::class,[
                "attr" => [
                    "class" => "form-control mt-2",
                ],
                "input" => "datetime_immutable",
                "label" => "Date de cloture",
                "label_attr" => [
                    "class" => "form-label mt-3",
                ],
                "html5" => false,
            ])
            ->add('maxPeople', IntegerType::class,[
                "attr" => [
                    "class" => "form-control",
                    "min" => 1,
                    "max" => 99,
                ],
                "label" => "Nombre max de personne",
                "label_attr" => [
                    "class" => "form-label mt-3",
                ],
            ])
            ->add('description',TextareaType::class,[
                "attr" => [
                    "class" => "form-control",
                ],
                "label" => "description de la sortie",
                "label_attr" => [
                    "class" => "form-label mt-3",
                ],
            ])

            ->add('duration',IntegerType::class,[
                "attr" => [
                    "class" => "form-control",
                    "min" => 1,
                    "max" => 99999,
                ],
                "label" => "Duree",
                "label_attr" => [
                    "class" => "form-label mt-3",
                ],
            ])
            /*->add('status',EntityType::class,[
                'class'=>Campus::class,
                "attr" => [
                    "class" => "form-select read-only",
                ],
                "label" => "Status",
                "label_attr" => [
                    "class" => "form-label mt-3",
                ],
                "choice_label" => "name",
                "multiple" => false,
                "expanded" => false,
            ])*/
            ->add('location',EntityType::class,[
                'class'=>Location::class,
                "attr" => ["disabled"=>true,
                    "class" => "form-select",
                    "id"=>"location"
                ],
                "label" => "Lieu",
                "label_attr" => [
                    "class" => "form-label mt-3",
                ],
                "choice_label" => "name",
                "multiple" => false,
                "expanded" => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}
