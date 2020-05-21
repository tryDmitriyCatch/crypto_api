<?php

namespace App\Command;

use App\Entity\AssetEntity;
use App\Entity\UserEntity;
use App\Services\Traits\DemTrait;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AddAssetCommand
 * @package App\Command
 */
class AddAssetCommand extends Command
{
    use DemTrait;

    /**
     * AddUserCommand constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->dem = $entityManager;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('app:create:assets')
            ->setDescription('Create assets')
            ->setHelp('This command creates assets for predefined user');
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $userEntity = $this->getRepository(UserEntity::class)->find(1);
        for ($x = 1; $x <= 3; $x++) {
            try {
                $assetEntity = (new AssetEntity())
                    ->setLabel('Bike' . $x)
                    ->setValue($x . '.99')
                    ->setCurrency($x)
                    ->setCreatedAt(new DateTimeImmutable())
                    ->setUser($userEntity);

                $this->persist($assetEntity, true);
            } catch (Exception $ex) {
                $output->writeln($ex->getMessage());
            }
        }
        $output->writeln('Done.');
    }
}