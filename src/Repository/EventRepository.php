<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\Status;
use App\Entity\User;
use App\Model\EventsFilterModel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 *
 * @method Event|null find($id, $lockMode = null, $lockVersion = null)
 * @method Event|null findOneBy(array $criteria, array $orderBy = null)
 * @method Event[]    findAll()
 * @method Event[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    /**
     * Récupère les sorties en fonction des critères de recherche sélectionnés
     *
     * @param EventsFilterModel $data Contient les critères de recherche choisis par l'utilisateur
     * @param User $connectedUser Contient les informations relatives à l'utilisateur connecté
     * @return Event[]
     */
    public function getEventList(EventsFilterModel $data, User $connectedUser): array
    {
        $queryBuilder = $this->createQueryBuilder('e')
            ->andWhere('e.campus = :campus')
            ->setParameter('campus', $data->campus)
            ->join('e.status', 'stat')
            ->addSelect('stat')
            ->andwhere("stat.wording != :canceled")
            ->setParameter('canceled',Status::CANCELED)
            ->andWhere('e.startAt > :minDate ')
            ->leftJoin('e.registration', 'reg')
            ->addSelect('reg');
        if (isset($data->searchBar)) {
            $queryBuilder
                ->andWhere('e.name LIKE :searchBar')
                ->setParameter('searchBar', '%'.$data->searchBar.'%');
        }
        if (isset($data->minDate)) {
            $queryBuilder->setParameter('minDate', $data->minDate);
        } else {
            $queryBuilder->setParameter('minDate', new \DateTimeImmutable('-1 month'));
        }
        if (isset($data->maxDate)) {
            $queryBuilder
                ->andWhere('e.startAt < :maxDate')
                ->setParameter('maxDate', $data->maxDate);
        }
        if ($data->isOrganizer) {
            $queryBuilder
                ->andWhere('e.organizer = :connectedUser')
                ->setParameter('connectedUser', $connectedUser);
        }
        if ($data->isRegistred) {
            $queryBuilder
                ->andWhere('e.id IN (:connectedUserRegistration)')
                ->setParameter('connectedUserRegistration', $connectedUser->getEventsRegistration());
        }
       
        if ($data->isNotRegistred && ($connectedUser->getEventsRegistration()->count() !=0)) {
            $queryBuilder
                ->andWhere('e.id NOT IN (:connectedUserRegistration)')
                ->setParameter('connectedUserRegistration', $connectedUser->getEventsRegistration());
        }
        if ($data->isPassed) {
            $queryBuilder->andWhere("stat.wording = :past")
            ->setParameter('past',Status::PAST);
        } else {
            $queryBuilder->andWhere("stat.wording != :past")
                ->setParameter('past',Status::PAST);;
        }

        return $queryBuilder
            ->getQuery()
            ->getResult();
    }

    public function add(Event $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Event $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Récupère les événements actifs en base de donnée.
     */
    public function getActiveEvents(array $params): array
    {
        return $this->createQueryBuilder('e')
            ->select('e')
            ->join('e.status', 's')
            ->andWhere('s.wording IN (:status)')
            ->setParameter('status', $params)
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les événements actifs et le nombre d'inscriptions sur chaque événement.
     */
    public function getOpenAndCloseEvents(array $params): array
    {
        return $this->createQueryBuilder('e')
            ->select('e as event')
            ->join('e.status', 's')
            ->join('e.registration', 'r')
            ->addSelect('count(r) as totalUserRegistered')
            ->andWhere('s.wording IN (:status)')
            ->setParameter('status', $params)
            ->groupBy('e')
            ->getQuery()
            ->getResult();
    }

    public function countAllEvent(){
        $queryBuilder = $this->createQueryBuilder('e');
        $queryBuilder->select('COUNT(e.id) as value');

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }
}
