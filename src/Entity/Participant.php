<?php

namespace App\Entity;

use App\Repository\ParticipantRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=ParticipantRepository::class)
 * @UniqueEntity(fields={"email"})
 * @UniqueEntity(fields={"pseudo"})
 */
class Participant implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $email;


    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $motPasse; //motPasse

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $prenom;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $telephone;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $pseudo;

    /**
     * @ORM\Column(type="boolean")
     */
    private $actif;

    /**
     * @ORM\ManyToMany(targetEntity=Sortie::class, inversedBy="participants")
     */
    private $sorties; //pluriel

    /**
     * @ORM\OneToMany(targetEntity=Sortie::class, mappedBy="organisateur")
     */
    private $organisateurDeSorties;

    /**
     * @ORM\ManyToOne(targetEntity=Campus::class, inversedBy="stagiaires")
     * @ORM\JoinColumn(nullable=false)
     */
    private $campus;

    /**
     * @ORM\Column(type="boolean")
     */
    private $administrateur;

    //rajouter un attribut administrateur

    public function __construct()
    {
        $this->sorties = new ArrayCollection();
        $this->organisateurDeSorties = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */
    public function getUsername(): string //dépréciée
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array //se servir de l'attribut administrateur
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string //motPasse
    {
        return $this->motPasse;
    }

    public function getMotPasse(): string //motPasse
    {
        return $this->motPasse;
    }

    public function setMotPasse(String $motPasse): self //motPasse
    {
        $this->motPasse = $motPasse;
        return $this;
    }



    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
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

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): self
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    public function isActif(): ?bool
    {
        return $this->actif;
    }

    public function setActif(bool $actif): self
    {
        $this->actif = $actif;

        return $this;
    }

    /**
     * @return Collection<int, Sortie>
     */
    public function getSortie(): Collection
    {
        return $this->sorties;
    }

    public function addSortie(Sortie $sortie): self
    {
        if (!$this->sorties->contains($sortie)) {
            $this->sorties[] = $sortie;
        }

        return $this;
    }

    public function removeSortie(Sortie $sortie): self
    {
        $this->sorties->removeElement($sortie);

        return $this;
    }

    /**
     * @return Collection<int, Sortie>
     */
    public function getOrganisateurDeSorties(): Collection
    {
        return $this->organisateurDeSorties;
    }

    public function addOrganisateurDeSorty(Sortie $organisateurDeSorty): self
    {
        if (!$this->organisateurDeSorties->contains($organisateurDeSorty)) {
            $this->organisateurDeSorties[] = $organisateurDeSorty;
            $organisateurDeSorty->setOrganisateur($this);
        }

        return $this;
    }

    public function removeOrganisateurDeSorty(Sortie $organisateurDeSorty): self
    {
        if ($this->organisateurDeSorties->removeElement($organisateurDeSorty)) {
            // set the owning side to null (unless already changed)
            if ($organisateurDeSorty->getOrganisateur() === $this) {
                $organisateurDeSorty->setOrganisateur(null);
            }
        }

        return $this;
    }

    public function getCampus(): ?Campus
    {
        return $this->campus;
    }

    public function setCampus(?Campus $campus): self
    {
        $this->campus = $campus;

        return $this;
    }

    public function isAdministrateur(): ?bool
    {
        return $this->administrateur;
    }

    public function setAdministrateur(bool $administrateur): self
    {
        $this->administrateur = $administrateur;

        return $this;
    }
}
