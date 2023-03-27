<?php

namespace App\Entity;

use App\Repository\CampusRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CampusRepository::class)
 */
class Campus
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nom;

    /**
     * @ORM\OneToMany(targetEntity=Participant::class, mappedBy="campus")
     */
    private $stagiaires;

    /**
     * @ORM\OneToMany(targetEntity=Sortie::class, mappedBy="siteOrganisateur")
     */
    private $sortiesCampus;

    public function __construct()
    {
        $this->stagiaires = new ArrayCollection();
        $this->sortiesCampus = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * @return Collection<int, Participant>
     */
    public function getStagiaires(): Collection
    {
        return $this->stagiaires;
    }

    public function addStagiaire(Participant $stagiaire): self
    {
        if (!$this->stagiaires->contains($stagiaire)) {
            $this->stagiaires[] = $stagiaire;
            $stagiaire->setCampus($this);
        }

        return $this;
    }

    public function removeStagiaire(Participant $stagiaire): self
    {
        if ($this->stagiaires->removeElement($stagiaire)) {
            // set the owning side to null (unless already changed)
            if ($stagiaire->getCampus() === $this) {
                $stagiaire->setCampus(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Sortie>
     */
    public function getSortiesCampus(): Collection
    {
        return $this->sortiesCampus;
    }

    public function addSortiesCampus(Sortie $sortiesCampus): self
    {
        if (!$this->sortiesCampus->contains($sortiesCampus)) {
            $this->sortiesCampus[] = $sortiesCampus;
            $sortiesCampus->setSiteOrganisateur($this);
        }

        return $this;
    }

    public function removeSortiesCampus(Sortie $sortiesCampus): self
    {
        if ($this->sortiesCampus->removeElement($sortiesCampus)) {
            // set the owning side to null (unless already changed)
            if ($sortiesCampus->getSiteOrganisateur() === $this) {
                $sortiesCampus->setSiteOrganisateur(null);
            }
        }

        return $this;
    }
}
