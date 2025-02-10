<?php

namespace App\Entity;

use App\Repository\PictureRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PictureRepository::class)]
class Picture
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 5)]
    private ?string $numero = null;

    /**
     * @var Collection<int, nft>
     */
    #[ORM\ManyToOne(targetEntity: nft::class, inversedBy: 'pictures')]
    private Collection $nft;

    public function __construct()
    {
        $this->nft = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumero(): ?string
    {
        return $this->numero;
    }

    public function setNumero(string $numero): static
    {
        $this->numero = $numero;

        return $this;
    }

    /**
     * @return Collection<int, nft>
     */
    public function getNft(): Collection
    {
        return $this->nft;
    }

    public function addNft(nft $nft): static
    {
        if (!$this->nft->contains($nft)) {
            $this->nft->add($nft);
        }

        return $this;
    }

    public function removeNft(nft $nft): static
    {
        $this->nft->removeElement($nft);

        return $this;
    }
}
