<?php

namespace SpecShaper\EncryptBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use SpecShaper\EncryptBundle\BlindIndex\BlindIndexMetadataProvider;
use SpecShaper\EncryptBundle\BlindIndex\BlindIndexUpdater;
use SpecShaper\EncryptBundle\Exception\EncryptException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * bin/console encrypt:blind-index --manager=default
 */
#[AsCommand(
    name: 'encrypt:blind-index',
    description: 'Builds or rebuilds blind index columns'
)]
class BlindIndexDatabaseCommand extends Command
{
    public function __construct(
        private readonly ManagerRegistry $registry,
        private readonly BlindIndexMetadataProvider $blindIndexMetadataProvider,
        private readonly BlindIndexUpdater $blindIndexUpdater
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('manager', null, InputOption::VALUE_OPTIONAL, 'Nominate the database connection manager name.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $managerName = $this->registry->getDefaultManagerName();
        $managerNameOption = $input->getOption('manager');

        if (!empty($managerNameOption)) {
            $managerName = $managerNameOption;
        }

        $em = $this->registry->getManager($managerName);

        if (!$em instanceof EntityManagerInterface) {
            throw new EncryptException(sprintf('Manager "%s" is not a Doctrine ORM entity manager.', $managerName));
        }

        $blindIndexFields = $this->blindIndexMetadataProvider->getAllForObjectManager($em);

        $io->title('Building blind indexes');

        $classes = count($blindIndexFields);

        $io->writeln($classes.' classes to update');
        $io->progressStart($classes);

        foreach ($blindIndexFields as $className => $fieldArray) {
            $this->updateEntities($em, $className, $fieldArray);
            $io->progressAdvance();
        }

        $io->progressFinish();

        return Command::SUCCESS;
    }

    private function updateEntities(EntityManagerInterface $em, string $className, array $fieldArray): void
    {
        $query = $em->createQueryBuilder()
            ->select('entity')
            ->from($className, 'entity')
            ->getQuery()
        ;

        foreach ($query->toIterable() as $entity) {
            $this->blindIndexUpdater->update($entity, $fieldArray);

            $em->flush();
            $em->detach($entity);
        }
    }
}
