<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\DocBlock\Serializer;
use PHPUnit\TextUI\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\YamlEncoder;

class ImportUsersCSV extends Command

{
    private EntityManagerInterface $entityManager;
    private string $dataDirectory;
    private UserRepository $userRepository;
    private SymfonyStyle $io;

    public function __construct(EntityManagerInterface $entityManager,
                                string                 $dataDirectory,
                                UserRepository         $userRepository)
    {
        parent::__construct();
        $this->dataDirectory = $dataDirectory;
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
    }

    protected static string $defaultName = 'app:create-user';
    protected static string $defaultDescription = 'Importer des données en provenance d\'un fichier CSV, XML ou YAMl';

    protected function configure()
    {
        $this->setDescription(self::$defaultDescription);
    }

    public function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->createUsers();

        return Command::SUCCES;
    }

    private function getDataFromFile(): array
    {
        $file = $this->dataDirectory . 'random-users.csv';
        $fileExtension = pathinfo($file, PATHINFO_EXTENSION);
        $normalizers = [new ObjetNormalizer()];
        $encoders = [
            new CsvEncoder(),
            new XmlEncoder(),
            new YamlEncoder(),
        ];
        $serializer = new Serializer($normalizers, $encoders);

        /** @var string $fileString */
        $fileString = file_get_contents($file);

        $data = $serializer->decode($fileString, $fileExtension);

        if (array_key_exists('results', $data)) {
            return $data['resultas'];
        }
        return $data;
    }

    private function createUsers(): void
    {
        $this->io->section('Création des utilisateurs à partir du fichier');
        $userCreated = 0;

        foreach ($this->getDataFromFile() as $row){
            if (array_key_exists('email',$row)&&!empty($row['email'])){
                $user = $this->userRepository->findOneBy([
                    'email'=>$row['email']
                ]);

                if (!$user){
                    $user = new User();

                    $user->setPseudo($row['pseudo'])
                        ->setFirstName($row['first_name'])
                        ->setLastName($row['last_name'])
                        ->setEmail($row['email'])
                        ->setPassword($row['password'])
                        ->setIsAdmin(false)
                        ->setIsActive(true);
                    $this->entityManager->persist($user);

                    $userCreated++;
                }
            }
        }

        $this->entityManager->flush();

        if($userCreated > 1){
            $string = "{$userCreated} utilisateurs crées en base de données;";
        }elseif ($userCreated === 1){
            $string = " 1 utilisateur a été crée en base de données;";
        }else{
            $string = "Aucun utilisateur n'a été crée en base de données;";
        }

        $this->io->success($string);
    }
}