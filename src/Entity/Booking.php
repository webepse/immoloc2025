<?php

namespace App\Entity;

use App\Repository\BookingRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: BookingRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Booking
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'bookings')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $booker = null;

    #[ORM\ManyToOne(inversedBy: 'bookings')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Ad $ad = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\GreaterThan("today", message:"La date d'arrivée doit être ultérieurs à la date d'aujourd'hui", groups:['front'])]
    #[Assert\Type('datetime')]
    private ?\DateTimeInterface $startDate = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\GreaterThan(propertyPath:"startDate", message:"La date de départ doit être plus éloignée que la date d'arrivée")]
    #[Assert\Type('datetime')]
    private ?\DateTimeInterface $endDate = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column]
    private ?float $amount = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $comment = null;

    /**
     * Permet d'ajouter le montant du séjour ainsi que la date de réservation
     *
     * @return void
     */
    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function prePersist(): void
    {
        if(empty($this->createdAt))
        {
            $this->createdAt = new \DateTime();
        }

        if(empty($this->amount))
        {
            // prix de l'annonce * nombre jour
            $this->amount = $this->ad->getPrice() * $this->getDuration();
        }

    }

    /**
     * Permet de récup le nombre de jour d'une réservation
     *
     * @return integer|null
     */
    public function getDuration(): ?int
    {
        // la méthode diff des objets datetime fait la différence entre 2 date et renvoie un objet de type DateInterval
        $diff = $this->endDate->diff($this->startDate);
        // recup un objt de type DateInterval et je veux récup le nombre de jour ->days;
        return $diff->days;
    }

    /**
     * Permet de vérifier si les dates sont réservalbes
     *
     * @return boolean|null
     */
    public function isBookableDates(): ?bool
    {
        // connaitre les dates impossible à réserver pour l'annonce
        $notAvailableDays = $this->ad->getNotAvailableDays();
        // comparer les dates choisies avec les dates impossible
        $bookingDays = $this->getDays();
        // transformation des objets dateTime en tableau de chaine de caractères pour les journées (faciliter la comparaison)
        $days = array_map(function($day){
            return $day->format('Y-m-d');
        },$bookingDays);
        $notAvailable = array_map(function($day){
            return $day->format('Y-m-d');
        },$notAvailableDays);
        foreach($days as $day){
            if(array_search($day,$notAvailable) !== false) return false;
        }

        return true;
    }

    /**
     * Permet de récupérer un tableau des journées qui correspondent à une réservation
     *
     * @return array|null un tableau d'objets DateTime représentant les jours de la réservation
     */
    public function getDays(): ?array
    {
        $resultat = range(
            $this->startDate->getTimestamp(),
            $this->endDate->getTimestamp(),
            24*60*60
        );
        $days = array_map(function($dayTimestamp){
            return new \DateTime(date('Y-m-d',$dayTimestamp));
        },$resultat);

        return $days;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBooker(): ?User
    {
        return $this->booker;
    }

    public function setBooker(?User $booker): static
    {
        $this->booker = $booker;

        return $this;
    }

    public function getAd(): ?Ad
    {
        return $this->ad;
    }

    public function setAd(?Ad $ad): static
    {
        $this->ad = $ad;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface $endDate): static
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): static
    {
        $this->comment = $comment;

        return $this;
    }
}



