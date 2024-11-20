<?php

namespace App\Entity;

use Cocur\Slugify\Slugify;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;
use App\Repository\AdRepository;
use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: AdRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields:['title'], message:"Une autre annonce posséde déjà ce titre, merci de le modifier")]
class Ad
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\Length(min:10, max:255, minMessage:"Le titre doit faire plus de 10 caractères", maxMessage:"Le titre ne doit pas faire plus de 255 caractères")]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "Le champs prix ne peut pas être vide")]
    private ?float $price = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\Length(min:20, max:255, minMessage:"L'introduction doit faire plus de 20 caractères", maxMessage:"L'introduction ne doit pas faire plus de 255 caractères")]
    private ?string $introduction = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\Length(min:100, minMessage:"La doiscription doit faire plus de 100 caractères")]
    private ?string $content = null;

    #[ORM\Column(length: 255)]
    #[Assert\Url()]
    private ?string $coverImage = null;

    #[ORM\Column]
    private ?int $rooms = null;

    /**
     * @var Collection<int, Image>
     */
    #[ORM\OneToMany(targetEntity: Image::class, mappedBy: 'ad', orphanRemoval: true)]
    #[Assert\Valid()]
    private Collection $images;

    #[ORM\ManyToOne(inversedBy: 'ads')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $author = null;

    /**
     * @var Collection<int, Booking>
     */
    #[ORM\OneToMany(targetEntity: Booking::class, mappedBy: 'ad')]
    private Collection $bookings;

    public function __construct()
    {
        $this->images = new ArrayCollection();
        $this->bookings = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function initializeSlug(): void
    {
        if(empty($this->slug))
        {
            $slugify = new Slugify();
            $this->slug = $slugify->slugify($this->title);
        }
    }

    /**
     * Permet d'obtenir un tableau des jours qui ne sont pas disponible pour l'annonce
     *
     * @return array|null Un tableau d'objet DateTime représentant les jours d'occupation
     */
    public function getNotAvailableDays(): ?array
    {
        $notAvailableDays = [];
        // boucler les réservation liés à l'annonce (collection)
        foreach($this->bookings as $booking)
        {
            // calculer les jours qui se trouvent entre startDate et endDate
            // la fonction range() de php permet de créer un tableau qui contient chaque étape existante entre deux nombre
            // $result = range(10,20,2)
            // réponse: [10,12,14,16,18,20]
            // 1 jour en timestamp 24h*60min*60s
            $resultat = range($booking->getStartDate()->getTimestamp(),$booking->getEndDate()->getTimestamp(), 24*60*60);
            // réponse [23132123,2131256161,121615126,1516156165]
            $days = array_map(function($dayTimestamp){
                return new \DateTime(date('Y-m-d',$dayTimestamp));
            },$resultat);
            // $days = [2024-11-20,2024-11-21,...]
            $notAvailableDays = array_merge($notAvailableDays,$days);
        }
        return $notAvailableDays;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getIntroduction(): ?string
    {
        return $this->introduction;
    }

    public function setIntroduction(string $introduction): static
    {
        $this->introduction = $introduction;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getCoverImage(): ?string
    {
        return $this->coverImage;
    }

    public function setCoverImage(string $coverImage): static
    {
        $this->coverImage = $coverImage;

        return $this;
    }

    public function getRooms(): ?int
    {
        return $this->rooms;
    }

    public function setRooms(int $rooms): static
    {
        $this->rooms = $rooms;

        return $this;
    }

    /**
     * @return Collection<int, Image>
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(Image $image): static
    {
        if (!$this->images->contains($image)) {
            $this->images->add($image);
            $image->setAd($this);
        }

        return $this;
    }

    public function removeImage(Image $image): static
    {
        if ($this->images->removeElement($image)) {
            // set the owning side to null (unless already changed)
            if ($image->getAd() === $this) {
                $image->setAd(null);
            }
        }

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): static
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @return Collection<int, Booking>
     */
    public function getBookings(): Collection
    {
        return $this->bookings;
    }

    public function addBooking(Booking $booking): static
    {
        if (!$this->bookings->contains($booking)) {
            $this->bookings->add($booking);
            $booking->setAd($this);
        }

        return $this;
    }

    public function removeBooking(Booking $booking): static
    {
        if ($this->bookings->removeElement($booking)) {
            // set the owning side to null (unless already changed)
            if ($booking->getAd() === $this) {
                $booking->setAd(null);
            }
        }

        return $this;
    }

}
