<?php

namespace App\Repository;

use App\Entity\Event;
use App\Model\EventsFilterModel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

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
    public function getEvents(EventsFilterModel $data, ?UserInterface $connectedUser)
    {
        $queryBuilder = $this->createQueryBuilder('e')
            ->andWhere('e.campus = :campus')
            ->setParameter('campus', $connectedUser->getCampus())
            ->join('e.status', 's')
            ->andWhere('s.id != 6')
            ->andWhere('e.startAt > :minDate ');
        if(isset($data->campus)){
        //TODO : gérer le campus et les sorties passées
        }else{

        }
        if (isset($data->searchBar)) {
            $queryBuilder
                ->andWhere('e.name LIKE :searchBar')
                ->setParameter('searchBar', '%'.$data->searchBar.'%');
        }
        if (isset($data->minDate)) {
            $queryBuilder->setParameter('minDate', $data->minDate);
        } else {
            $queryBuilder->setParameter('minDate', new \DateTime('now'));
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
                ->join('e.registration', 'u')
                ->addSelect('u')
                ->andWhere('u = :connectedUser')
                ->setParameter('connectedUser', $connectedUser);
        }else{
            $queryBuilder
                ->join('e.registration', 'u')
                ->addSelect('u')
                ->andWhere('u != :connectedUser')
                ->setParameter('connectedUser', $connectedUser);
        }
        if ($data->isPassed) {
            //TODO : gérer les sorties passées
//            $queryBuilder->andWhere('s.id = 5');
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



}
