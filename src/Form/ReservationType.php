<?php

namespace App\Form;

use App\Entity\Reservation;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormError;
use App\Entity\Vehicule;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date_debut', DateType::class, [
                'widget' => 'single_text',
            ])
            ->add('date_fin', DateType::class, [
                'widget' => 'single_text',
            ])
            ->add('voiture', EntityType::class, [
                'class' => Vehicule::class,
                'choice_label' => 'marque',
            ])
            ->addEventListener(FormEvents::POST_SUBMIT, [$this, 'validateDates']);
    }

    public function validateDates(FormEvent $event): void
    {
        $form = $event->getForm();
        $data = $form->getData();

        if ($data->getDateFin() < $data->getDateDebut()) {
            $form->get('date_fin')->addError(new FormError('La date de fin doit être postérieure ou égale à la date de début.'));
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
        ]);
    }
}
