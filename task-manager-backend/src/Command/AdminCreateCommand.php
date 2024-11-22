<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'admin-create',
    description: 'Add a short description for your command',
)]
class AdminCreateCommand extends Command
{
    private $passwordHasher;
    private $entityManager;

    public function __construct(UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager)
    {
        parent::__construct();

        $this->passwordHasher = $passwordHasher;
        $this->entityManager = $entityManager;
    }

    protected static $defaultName = 'app:create-admin-user';

    protected function configure()
    {
        $this->setDescription('Create an admin user with a hashed password.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Création de l'utilisateur admin
        $adminUser = new User();
        $adminUser->setUsername('a'); // Définir le nom d'utilisateur
        $adminUser->setRoles(['ROLE_ADMIN']); // Assigner le rôle d'administrateur

        // Hachage du mot de passe
        $password = 'a'; // Le mot de passe en clair
        $hashedPassword = $this->passwordHasher->hashPassword($adminUser, $password); // Hachage du mot de passe
        $adminUser->setPassword($hashedPassword);

        // Sauvegarde de l'utilisateur en base de données
        $this->entityManager->persist($adminUser);
        $this->entityManager->flush();

        $output->writeln('Admin user created successfully.');

        return Command::SUCCESS;
    }
}

