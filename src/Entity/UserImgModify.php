<?php
namespace App\Entity;
use Symfony\component\Validator\Constraints as Assert;


class UserImgModify{

    #[Assert\NotBlank(message: "veuillez ajouter une image")]
    #[Assert\Image(mimeTypes:['image/png','image/jpeg','image/jpg','image/gif'], mimeTypesMessage:"Vous devez upload un fichier jpg, jpeg, png ou gif")]
    #[Assert\File(maxSize:"1024k", maxSizeMessage:"La taille du fichier est trop grande")]
    private $newPicture;

    public function getNewPicture(): ?string
    {
        return $this->newPicture;
    }

    public function setNewPicture(?string $newPicture): self
    {
        $this->newPicture = $newPicture;
        return $this;
    }


}