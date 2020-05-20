<?php

namespace App\Command;

use App\Entity\UserEntity;
use App\Services\Traits\DemTrait;
use App\Utils\PasswordUtils;
use App\Utils\UUID;
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
        for ($x = 1; $x <= 6; $x++) {
            try {
                $userEntity = (new UserEntity())
                    ->setToken(UUID::generate())
                    ->setName('Test' . $x)
                    ->setSurname('Testovskis' . $x)
                    ->setEmail('test@test' . $x . '.com')
                    ->setCreatedAt(new DateTimeImmutable())
                    ->setPassword(PasswordUtils::hashPassword('Test' . $x));

                $this->persist($userEntity, true);
            } catch (Exception $ex) {
                $output->writeln($ex->getMessage());
            }
        }
        $output->writeln('Done.');
    }
}