<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\CampusRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class ImportUsersCSV extends Command
{
    private EntityManagerInterface $entityManager;

    private string $dataDirectory;
    private SymfonyStyle $io;

    private UserRepository $userRepository;
    private CampusRepository $campusRepository;

    private UserPasswordHasherInterface $userPasswordHasherInterface;

    public function __construct(EntityManagerInterface      $entityManager, //$dataDirectory,
                                UserRepository       $usersRepository, CampusRepository $campusRepository,
                                UserPasswordHasherInterface $userPasswordHasherInterface)
    {
        parent::__construct();
        //$this->dataDirectory = $dataDirectory;
        $this->entityManager = $entityManager;
        $this->userRepository = $usersRepository;
        $this->campusRepository = $campusRepository;
        $this->userPasswordHasherInterface = $userPasswordHasherInterface;

    }

    protected static $defaultName = 'app:utilisateurs-csv';
    protected static $defaultDescription = 'Add a short description for your command';

    protected function configure(): void
    {
        $this
            ->setDescription('Importer des données en provenance d\'un fichier csv');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->createUsers();

        return Command::SUCCESS;
    }

    private function getDataFromFile(): array
    {
        $file = $this->dataDirectory() . '/public/ImportCSV/test_CSV.csv';
        $fileExtension = pathinfo($file, PATHINFO_EXTENSION);

        $normalizers = [new ObjectNormalizer()];

        $encoders = [
            new CsvEncoder(),
        ];
        $serializer = new Serializer($normalizers, $encoders);
        $context = [CsvEncoder::DELIMITER_KEY => ';'];

        $fileString = file_get_contents($file);

        return $serializer->decode($fileString, $fileExtension, $context);

    }

    private function createUsers(): void
    {
        $this->io->section('CREATION DES UTILISATEURS A PARTIR DU FICHIER');
        $usersCreated = 0;

        foreach ($this->getDataFromFile() as $row) {
            if (array_key_exists('username', $row) && !empty($row['username'])) {
                $users = $this->userRepository->findOneBy([
                    'username' => $row['username']
                ]);
                if ($users) {
                    $users = new User();
                    $users->setPseudo($row['username'])
                        ->setEmail($row['email'])
                        ->setCampus($this->campusRepository->findOneBy(['id' => $row['campus_id']]))
                        ->setLastName($row['nom'])
                        ->setFirstName($row['prenom'])
                        ->setPhoneNumber($row['telephone'])
                        ->setIsActive(true)
                        ->setPassword($this->userPasswordHasherInterface->hashPassword($users, 'password'));

                    $this->entityManager->persist($users);
                    $usersCreated++;
                } elseif ($users) {
                    $this->io->caution("Utilisateur {$users->getUsername()} déjà en base");
                }
            }
        }

        $this->entityManager->flush();

        if ($usersCreated > 1) {
            $string = "{$usersCreated} utilisateurs créés en base de données.";
        } elseif ($usersCreated === 1) {
            $string = "Un utilisateur a été créé en base de données";
        } else {
            $string = "aucun utilisateur créé";
        }

        $this->io->success($string);
    }
}
