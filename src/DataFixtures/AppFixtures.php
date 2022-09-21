<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\City;
use App\Entity\Event;
use App\Entity\Location;
use App\Entity\Status;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

class AppFixtures extends Fixture
{
    private Generator $faker;

    public function __construct()
    {
        $this->faker = Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager): void
    {
        // Creation des campus
        $allCampus = [];
        $allCampusName = ['SAINT-HERBLAIN', 'CHARTRES DE BRETAGNE', 'LA ROCHE SUR YON'];
        foreach ($allCampusName as $name) {
            $campus = new Campus();
            $campus->setName($name);
            $allCampus[] = $campus;
            $manager->persist($campus);
        }
        // Creation des villes
        $citys = [];
        for ($i = 0; $i < 10; ++$i) {
            $city = new City();
            $city->setName($this->faker->city());
            $city->setZipCode($this->faker->postcode());
            $citys[] = $city;
            $manager->persist($city);
        }
        // Creation des Etats des sorties
        $status = [];
        $allState = [Status::CREATE, Status::OPEN, Status::IN_PROGRESS, Status::CLOSE, Status::PAST, Status::CANCELED];
        foreach ($allState as $wording) {
            $statu = new Status();
            $statu->setWording($wording);
            $status[] = $statu;
            $manager->persist($statu);
        }
        // Creation des lieux
        $locations = [];
        for ($i = 0; $i < 20; ++$i) {
            $location = new Location();
            $location->setName($this->faker->title())
            ->setLongitude($this->faker->longitude())
            ->setLatitude($this->faker->longitude())
            ->setCity($citys[mt_rand(0, count($citys) - 1)])
            ->setStreet($this->faker->streetName());
            $locations[] = $location;
            $manager->persist($location);
        }
        // Creation de l 'admin
        $admin = new User();
        $admin->setFirstName('admin')
            ->setLastName('admin')
            ->setEmail('admin@admin.fr')
            ->setCampus($allCampus[mt_rand(0, count($allCampus) - 1)])
            ->setIsAdmin(true)
            ->setIsActive(true)
            ->setPseudo('SuperTOTO')
            ->setPhoneNumber('0606060606');
        $admin->setPlainPassword('admin');
        $manager->persist($admin);
        // Creation des users
        $users = [];
        for ($i = 0; $i < 10; ++$i) {
            $user = new User();
            $user->setFirstName($this->faker->firstName())
                ->setLastName($this->faker->lastName())
                ->setEmail($this->faker->email())
                ->setCampus($allCampus[mt_rand(0, count($allCampus) - 1)])
                ->setPseudo($this->faker->userName());
            $user->setPlainPassword('password');
            $users[] = $user;
            $manager->persist($user);
        }
        // Creation des users inactifs
        for ($i = 0; $i < 5; ++$i) {
            $unactiveUser = new User();
            $unactiveUser->setFirstName($this->faker->firstName())
                ->setLastName($this->faker->lastName())
                ->setEmail($this->faker->email())
                ->setCampus($allCampus[mt_rand(0, count($allCampus) - 1)])
                ->setPseudo($this->faker->userName())
                ->setIsActive(false);
            $unactiveUser->setPlainPassword('password');
            // $users[] = $unactiveUser;
            $manager->persist($unactiveUser);
        }
        // Creation des sorties
        $events = [];
        for ($i = 0; $i < 20; ++$i) {
            $event = new Event();
            $dateStartAt = \DateTimeImmutable::createFromMutable($this->faker->dateTimeBetween('-1 week', '+3 week', 'Europe/Paris'));
            $dateDeadLine = $dateStartAt->modify('-3 days');
            $event->setName($this->faker->sentence(3))
                ->setStartAt($dateStartAt)
                ->setDeadLineInscriptionAt($dateDeadLine)
                ->setCampus($allCampus[mt_rand(0, count($allCampus) - 1)])
                ->setOrganizer($users[mt_rand(0, count($users) - 1)])
                ->setDuration(mt_rand(10, 180))
                ->setMaxPeople(mt_rand(9, 49))
                ->setLocation($locations[mt_rand(0, count($locations) - 1)])
                ->setDescription($this->faker->text(75));
            if ($dateStartAt < new \DateTimeImmutable()){
                $event->setStatus($status[4]);
            }else{
                if($dateDeadLine < new \DateTimeImmutable()){
                    $event->setStatus($status[3]);
                }else{
                    while( in_array(($r= mt_rand(0,5)), array(2,3,4))){
                    }
                        $event->setStatus($status[$r]);
                }
            }
            $events[] = $event;
            $manager->persist($event);
        }
        // Creation des inscriptions aux sorties
        foreach ($events as $event) {
            for ($i = 0; $i < count($users) - 1; ++$i) {
                $event->addRegistration($users[mt_rand(0, count($users) - 1)]);
                $manager->persist($event);
            }
        }

        $manager->flush();
    }
}
