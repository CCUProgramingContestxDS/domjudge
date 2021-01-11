<?php declare(strict_types=1);

namespace App\DataFixtures\DefaultData;

use App\Entity\Executable;
use App\Entity\ImmutableExecutable;
use App\Service\DOMJudgeService;
use Doctrine\Persistence\ObjectManager;
use Psr\Log\LoggerInterface;
use ZipArchive;

class ExecutableFixture extends AbstractDefaultDataFixture
{
    /**
     * @var string
     */
    protected $sqlDir;

    /**
     * @var DOMJudgeService
     */
    protected $dj;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(string $sqlDir, DOMJudgeService $dj, LoggerInterface $logger)
    {
        $this->sqlDir = $sqlDir;
        $this->dj     = $dj;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function load(ObjectManager $manager)
    {
        $data = [
            // ID,         description,               type
            ['compare',    'default compare script', 'compare'],
            ['java_javac', 'java_javac',             'compile'],
            ['run',        'default run script',     'run'],
        ];

        foreach ($data as $item) {
            // Note: we only create the executable if it doesn't exist yet.
            // If it does, we will not update the data
            if (!($executable = $manager->getRepository(Executable::class)->find($item[0]))) {
                $file = sprintf('%s/files/defaultdata/%s.zip',
                    $this->sqlDir, $item[0]
                );
                $executable = (new Executable())
                    ->setExecid($item[0])
                    ->setDescription($item[1])
                    ->setType($item[2])
                    ->setImmutableExecutable($this->createImmutableExecutable($file));
                $manager->persist($executable);
            } else {
                $this->logger->info('Executable %s already exists, not updated', [ $item[0] ]);
            }
        }
        $manager->flush();
    }

    private function createImmutableExecutable(string $filename): ImmutableExecutable
    {
        $zip = new ZipArchive();
        $zip->open($filename, ZipArchive::CHECKCONS);
        return $this->dj->createImmutableExecutable($zip);
    }
}
