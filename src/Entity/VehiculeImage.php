<?php

namespace App\Entity;

use App\Repository\VehiculeImageRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File;

#[ORM\Entity(repositoryClass: VehiculeImageRepository::class)]
class VehiculeImage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Vich\UploadableField(
        mapping: 'vehicle_images',
        fileNameProperty: 'image',
        size: 'imageSize',
        mimeType: 'imageMimeType'
    )]
    #[Assert\File(
        maxSize: '2M',
        mimeTypes: ['image/jpeg', 'image/png', 'image/webp'],
        mimeTypesMessage: 'Seuls les formats JPEG, PNG et WEBP sont autorisés'
    )]
    private ?File $imageFile = null;


    #[ORM\ManyToOne(inversedBy: 'vehiculeImages')]
    private ?Vehicule $vehicule = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVehicule(): ?Vehicule
    {
        return $this->vehicule;
    }

    public function setVehicule(?Vehicule $vehicule): static
    {
        $this->vehicule = $vehicule;

        return $this;
    }


     public function setImageFile(?File $imageFile = null): void
     {
         $this->imageFile = $imageFile;
     }
}

