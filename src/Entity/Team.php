namespace App\Entity;

use App\Repository\TeamRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TeamRepository::class)]
class Team
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?int $apiId = null;

    #[ORM\ManyToMany(targetEntity=User::class, mappedBy="followedTeams")]
    private Collection $followers;

    public function __construct() {
        $this->followers = new ArrayCollection();
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function getName(): ?string {
        return $this->name;
    }

    public function setName(string $name): self {
        $this->name = $name;
        return $this;
    }

    public function getApiId(): ?int {
        return $this->apiId;
    }

    public function setApiId(int $apiId): self {
        $this->apiId = $apiId;
        return $this;
    }

    public function getFollowers(): Collection {
        return $this->followers;
    }
}