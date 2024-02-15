<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CreateAdminCommand extends Command
{
    protected static $defaultName = 'app:create-admin';
    protected static $defaultDescription = 'Add a short description for your command';
    
    private SymfonyStyle $io;
    private $userRepository;
    private $hasher;
    private $em;

    public function __construct(EntityManagerInterface $entityManager, UserRepository $userRepository, UserPasswordHasherInterface $hasher) 
    {
        $this->em = $entityManager;
        $this->userRepository = $userRepository;
        $this->hasher = $hasher;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::OPTIONAL, 'email')
            ->addArgument('password', InputArgument::OPTIONAL, 'password')
            ->addArgument('firstname', InputArgument::OPTIONAL, 'firstname')
            ->addArgument('lastname', InputArgument::OPTIONAL, 'lastname')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        if( null != $input->getArgument('email') && null != $input->getArgument('password')) 
        {
            return;
        }

        $this->io->text('admin command iteractive');
        $this->askArgument($input, 'email');
        $this->askArgument($input, 'password', true);
        $this->askArgument($input, 'firstname');
        $this->askArgument($input, 'lastname');
    }

    private function askArgument(InputInterface $input, string $name, bool $hidden = false): void
    {
        $value = strval($input->getArgument($name));
        if('' != $value) 
        {
            $this->io->text((sprintf('> <info>%s</info>: %s', $name, $value)));
        } else {
            if ($hidden === false) {
                $value = $this->io->ask($name);
            } else {
                $value = $this->io->askHidden($name);
            }
            $input->setArgument($name, $value);
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $user = new User();
        $user->setEmail(strval($input->getArgument('email')));
        $user->setPassword($this->hasher->hashPassword($user, strval($input->getArgument('password'))));
        $user->setFirstname(strval($input->getArgument('firstname')));
        $user->setLastname(strval($input->getArgument('lastname')));
        $user->setRoles(['ROLE_ADMIN']);
        
        // Save user
        $this->em->persist($user);
        $this->em->flush();

        return Command::SUCCESS;
    }
}
