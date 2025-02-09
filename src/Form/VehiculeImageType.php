<?php

namespace App\Form;

use App\Entity\VehiculeImage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichFileType;

class VehiculeImageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('imageFile', VichFileType::class, [
            'label' => 'Image (JPEG/PNG)',
            'required' => true,
            'allow_delete' => true,
            'download_uri' => true,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => VehiculeImage::class,
        ]);
    }
}
