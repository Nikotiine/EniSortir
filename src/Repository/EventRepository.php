<?php

namespace App\Repository;

use App\Entity\Campus;
use App\Entity\Event;
use App\Entity\User;
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

    public function getEventsWithParams(string $eventType, array $filters, User $fakeUser)
    {
        $queryBuilder = $this->createQueryBuilder('e')
            ->andWhere('e.startAt > :minDate ')
            ->select('e,s')
            ->join('e.status', 's')
            ->andWhere('s.id != 6')
            ->andWhere('e.campus = :campus');

        // Application du filtre de sélection (organizer, registred, notRegistred, Passed)
        if ('AsOrganizer' === $eventType) {
            $queryBuilder
                ->andWhere('e.organizer = :fakeUser')
                ->setParameter('fakeUser', $fakeUser);
        }
        if ('WhereRegistred' === $eventType) {
            $queryBuilder
                ->join('e.registration', 'u')
                ->addSelect('u')
                ->andWhere('u = :fakeUser')
                ->setParameter('fakeUser', $fakeUser);
        }
        if ('WhereNotRegistred' === $eventType) {
            $queryBuilder
                ->join('e.registration', 'u')
                ->addSelect('u')
                ->andWhere('u != :fakeUser')
                ->setParameter('fakeUser', $fakeUser);
        }
        if ('PassedEvents' === $eventType) {
            $queryBuilder->andWhere('s.id = 5');
        } else {
            $queryBuilder->andWhere('s.id != 5');
        }

        // Application des filtre de recherches (campus, keywords, dates)
        if (isset($filters['campus'])) {
            $queryBuilder->setParameter('campus', $filters['campus']);
        } else {
            $queryBuilder->setParameter('campus', $fakeUser->getCampus());
        }
        if (isset($filters['searchBar'])) {
            $queryBuilder
                ->andWhere('e.name LIKE :searchBar')
                ->setParameter('searchBar', '%'.$filters['searchBar'].'%');
        }
        if (isset($filters['minDate'])) {
            $queryBuilder->setParameter('minDate', $filters['minDate']);
        } else {
            $queryBuilder->setParameter('minDate', new \DateTime('now'));
        }
        if (isset($filters['maxDate'])) {
            $queryBuilder
                ->andWhere('e.startAt < :maxDate')
                ->setParameter('maxDate', $filters['maxDate']);
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

    public function filterEvents(Campus $campus): array
    {
        return $this->createQueryBuilder('e')
            ->select('e')
            ->join('e.campus', 'c')
            ->join('e.registration', 'r')
            ->addSelect('r')
            ->where('c = :campus')
            ->setParameter('campus', $campus)
            ->getQuery()
            ->getResult();
    }

    public function oldEvents(): array
    {
        return $this->createQueryBuilder('e')
             ->select('e,s')
             ->join('e.status', 's')
             ->where('s.id = 5')
             ->getQuery()
             ->getResult();
    }

    public function filterEventsRegistered(Campus $campus, User $user): array
    {
        return $this->createQueryBuilder('e')
            ->select('e')
            ->join('e.campus', 'c')
            ->join('e.registration', 'r')
            ->addSelect('r')
            ->where('c = :campus')
            ->andWhere('r = :user')
            ->setParameter('campus', $campus)
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }

    public function filterEventsNotRegistered(Campus $campus, User $user): array
    {
        return $this->createQueryBuilder('e')
            ->select('e')
            ->join('e.campus', 'c')
            ->join('e.registration', 'r')
            ->addSelect('r')
            ->where('c = :campus')
            ->andWhere('r != :user')
            ->setParameter('campus', $campus)
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }
}
