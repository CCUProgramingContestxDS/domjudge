<?php declare(strict_types=1);
namespace App\Entity;

use App\Doctrine\Constants;
use App\Validator\Constraints\Identifier;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use OpenApi\Attributes as OA;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Programming languages in which teams can submit solutions.
 *
 * @UniqueEntity("langid")
 * @UniqueEntity("externalid")
 */
#[ORM\Table(
    name: 'language',
    options: [
        'collation' => 'utf8mb4_unicode_ci',
        'charset' => 'utf8mb4',
        'comment' => 'Programming languages in which teams can submit solutions',
    ])]
#[ORM\Index(columns: ['compile_script'], name: 'compile_script')]
#[ORM\UniqueConstraint(name: 'externalid', columns: ['externalid'], options: ['lengths' => [190]])]
#[ORM\Entity]
class Language extends BaseApiEntity
{
    /**
     * @Assert\NotBlank()
     * @Assert\NotEqualTo("add")
     * @Identifier()
     */
    #[ORM\Id]
    #[ORM\Column(
        name: 'langid',
        type: 'string',
        length: 32,
        nullable: false,
        options: ['comment' => 'Language ID (string)']
    )]
    #[Serializer\Exclude]
    protected ?string $langid = null;

    #[ORM\Column(
        name: 'externalid',
        type: 'string',
        length: Constants::LENGTH_LIMIT_TINYTEXT,
        nullable: true,
        options: ['comment' => 'Language ID to expose in the REST API']
    )]
    #[Serializer\SerializedName('id')]
    #[Serializer\Groups(['Default', 'Nonstrict'])]
    protected ?string $externalid = null;

    /**
     * @Assert\NotBlank()
     */
    #[ORM\Column(
        name: 'name',
        type: 'string',
        length: Constants::LENGTH_LIMIT_TINYTEXT,
        nullable: false,
        options: ['comment' => 'Descriptive language name']
    )]
    #[Serializer\Groups(['Default', 'Nonstrict'])]
    private string $name = '';

    /**
     * @var string[]
     * @Assert\NotBlank()
     */
    #[ORM\Column(
        name: 'extensions',
        type: 'json',
        length: Constants::LENGTH_LIMIT_LONGTEXT,
        nullable: true,
        options: ['comment' => 'List of recognized extensions (JSON encoded)']
    )]
    #[Serializer\Type('array<string>')]
    private array $extensions = [];

    #[ORM\Column(
        name: 'filter_compiler_files',
        type: 'boolean',
        nullable: false,
        options: ['comment' => 'Whether to filter the files passed to the compiler by the extension list.', 'default' => 1]
    )]
    #[Serializer\Groups(['Nonstrict'])]
    private bool $filterCompilerFiles = true;

    #[ORM\Column(
        name: 'allow_submit',
        type: 'boolean',
        nullable: false,
        options: ['comment' => 'Are submissions accepted in this language?', 'default' => 1]
    )]
    #[Serializer\Exclude]
    private bool $allowSubmit = true;

    #[ORM\Column(
        name: 'allow_judge',
        type: 'boolean',
        nullable: false,
        options: ['comment' => 'Are submissions in this language judged?', 'default' => 1]
    )]
    #[Serializer\Groups(['Nonstrict'])]
    private bool $allowJudge = true;

    /**
     * @Assert\GreaterThan(0)
     * @Assert\NotBlank()
     */
    #[ORM\Column(
        name: 'time_factor',
        type: 'float',
        nullable: false,
        options: ['comment' => 'Language-specific factor multiplied by problem run times', 'default' => 1]
    )]
    #[Serializer\Type('double')]
    #[Serializer\Groups(['Nonstrict'])]
    private float $timeFactor = 1;

    #[ORM\Column(
        name: 'require_entry_point',
        type: 'boolean',
        nullable: false,
        options: ['comment' => 'Whether submissions require a code entry point to be specified.', 'default' => 0]
    )]
    #[Serializer\SerializedName('entry_point_required')]
    private bool $require_entry_point = false;

    #[ORM\Column(
        name: 'entry_point_description',
        type: 'string',
        nullable: true,
        options: ['comment' => 'The description used in the UI for the entry point field.']
    )]
    #[OA\Property(nullable: true)]
    #[Serializer\SerializedName('entry_point_name')]
    private ?string $entry_point_description = null;

    #[ORM\JoinColumn(name: 'compile_script', referencedColumnName: 'execid', onDelete: 'SET NULL')]
    #[Serializer\Exclude]
    #[ORM\ManyToOne(targetEntity: Executable::class, inversedBy: 'languages')]
    private ?Executable $compile_executable = null;

    #[ORM\OneToMany(mappedBy: 'language', targetEntity: Submission::class)]
    #[Serializer\Exclude]
    private Collection $submissions;

    #[OA\Property(nullable: true)]
    #[Serializer\VirtualProperty]
    #[Serializer\Type('string')]
    #[Serializer\Groups(['Nonstrict'])]
    #[Serializer\SerializedName('compile_executable_hash')]
    public function getCompileExecutableHash(): ?string
    {
        return $this->compile_executable?->getImmutableExecutable()->getHash();
    }

    public function setLangid(string $langid): Language
    {
        $this->langid = $langid;
        return $this;
    }

    public function getLangid(): ?string
    {
        return $this->langid;
    }

    public function setExternalid(string $externalid): Language
    {
        $this->externalid = $externalid;
        return $this;
    }

    public function getExternalid(): ?string
    {
        return $this->externalid;
    }

    public function setName(string $name): Language
    {
        $this->name = $name;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getShortDescription(): string
    {
        return $this->getName();
    }

    public function setExtensions(array $extensions): Language
    {
        $this->extensions = $extensions;
        return $this;
    }

    public function getExtensions(): array
    {
        return $this->extensions;
    }

    public function setFilterCompilerFiles(bool $filterCompilerFiles): Language
    {
        $this->filterCompilerFiles = $filterCompilerFiles;
        return $this;
    }

    public function getFilterCompilerFiles(): bool
    {
        return $this->filterCompilerFiles;
    }

    public function setAllowSubmit(bool $allowSubmit): Language
    {
        $this->allowSubmit = $allowSubmit;
        return $this;
    }

    public function getAllowSubmit(): bool
    {
        return $this->allowSubmit;
    }

    public function setAllowJudge(bool $allowJudge): Language
    {
        $this->allowJudge = $allowJudge;
        return $this;
    }

    public function getAllowJudge(): bool
    {
        return $this->allowJudge;
    }

    public function setTimeFactor(float $timeFactor): Language
    {
        $this->timeFactor = $timeFactor;
        return $this;
    }

    public function getTimeFactor(): float
    {
        return $this->timeFactor;
    }

    public function setRequireEntryPoint(bool $requireEntryPoint): Language
    {
        $this->require_entry_point = $requireEntryPoint;
        return $this;
    }

    public function getRequireEntryPoint(): bool
    {
        return $this->require_entry_point;
    }

    public function setEntryPointDescription(?string $entryPointDescription): Language
    {
        $this->entry_point_description = $entryPointDescription;
        return $this;
    }

    public function getEntryPointDescription(): ?string
    {
        return $this->entry_point_description;
    }

    public function setCompileExecutable(?Executable $compileExecutable = null): Language
    {
        $this->compile_executable = $compileExecutable;
        return $this;
    }

    public function getCompileExecutable(): ?Executable
    {
        return $this->compile_executable;
    }

    public function __construct()
    {
        $this->submissions = new ArrayCollection();
    }

    public function addSubmission(Submission $submission): Language
    {
        $this->submissions[] = $submission;
        return $this;
    }

    public function removeSubmission(Submission $submission)
    {
        $this->submissions->removeElement($submission);
    }

    public function getSubmissions(): Collection
    {
        return $this->submissions;
    }

    public function getAceLanguage(): string
    {
        return match ($this->getLangid()) {
            'c', 'cpp', 'cxx' => 'c_cpp',
            'pas' => 'pascal',
            'hs' => 'haskell',
            'pl' => 'perl',
            'bash' => 'sh',
            'py2', 'py3' => 'python',
            'adb' => 'ada',
            'plg' => 'prolog',
            'rb' => 'ruby',
            'rs' => 'rust',
            default => $this->getLangid(),
        };
    }
}
