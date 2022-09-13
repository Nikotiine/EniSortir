<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\Status;
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
//Solution 1
//    public function getEventsWithParams(int $statusPassee, int $statusAnnulee)
//    {
//        $queryBuilder = $this->createQueryBuilder('e')
//                ->andWhere('e.status != :statusPassee')
//                ->setParameter('statusPassee',$statusPassee)
//
//                ->andWhere('e.status!= :statusAnnulee')
//                ->setParameter('statusAnnulee',$statusAnnulee)
//
//                ->orderBy('e.startAt','ASC');
//        $query=$queryBuilder->getQuery();
//        $result = $query->getResult();
//        return $result;
//    }
    public function getEventsWithParams($parameters,User $fakeUser)
    {
        $queryBuilder = $this->createQueryBuilder('e');
        if(isset($parameters['asOrganizer'])){
            $queryBuilder
                ->orWhere('e.organizer = :fakeUser')
                ->setParameter('fakeUser', $fakeUser);

        }
        if(isset($parameters['registred'])){
            $queryBuilder
                ->join('e.registration','u')
                ->orWhere('u = :fakeUser')
                ->setParameter('fakeUser', $fakeUser);

        }
        if(isset($parameters['notRegistred'])){
            $queryBuilder
                ->join('e.registration','us')
                ->orWhere('us = :fakeUser')
                ->setParameter('fakeUser', $fakeUser);

        }
        $queryBuilder->orderBy('e.startAt','ASC');
        $query=$queryBuilder->getQuery();
        $result = $query->getResult();
        return $result;

//            ->andWhere('e.status != :statusPassee')
//            ->setParameter('statusPassee',$statusPassee)
//
//            ->andWhere('e.status!= :statusAnnulee')
//            ->setParameter('statusAnnulee',$statusAnnulee)
//

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

//    /**
//     * @return Event[] Returns an array of Event objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('e.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Event
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
