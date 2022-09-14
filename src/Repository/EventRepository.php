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
    public function getEventsWithParams(String $eventType, array $filters, User $fakeUser, Status $statusAnnulee)
    {
        $queryBuilder = $this->createQueryBuilder('e')
            ->andWhere('e.startAt > :minDate ')
            ->andWhere('e.status NOT IN (:statusAnnulee)')
            ->setParameter('statusAnnulee', $statusAnnulee)
            ->andWhere('e.campus = :campus');

//CheckBox choice
        if($eventType==="AsOrganizer"){
            $queryBuilder
                ->andWhere('e.organizer = :fakeUser');
            $queryBuilder->setParameter('fakeUser', $fakeUser);
        }
        if($eventType==="WhereRegistred"){
            $queryBuilder
                ->join('e.registration','u')
                ->addSelect('u')
                ->andWhere('u = :fakeUser');
            $queryBuilder->setParameter('fakeUser', $fakeUser);
        }
        if($eventType==="WhereNotRegistred"){
            $queryBuilder
                ->join('e.registration','u')
                ->addSelect('u')
                ->andWhere('u != :fakeUser');
            $queryBuilder->setParameter('fakeUser', $fakeUser);
        }
//        if($eventType==="PassedEvents"){
//            $queryBuilder
//                ->andWhere("e.status IN (':statusPassed')")
//                ->setParameter('statusPassed', $statusPassed);
//        }

//filter choices
        if(isset($filters['campus'])){
            $queryBuilder->setParameter('campus',$filters['campus']);
        }else{
            $queryBuilder->setParameter('campus', $fakeUser->getCampus());
        }
        if(isset($filters['name'])){
            $queryBuilder
                ->andWhere("e.name LIKE :name")
                ->setParameter('name','%'.$filters['name'].'%');
        }
        if(isset($filters['minDate'])){
            $queryBuilder->setParameter('minDate', $filters['minDate']);
        }else{
            $queryBuilder->setParameter('minDate', new \DateTime('now'));
        }
        if(isset($filters['maxDate'])){
            $queryBuilder
                ->andWhere("e.startAt < :maxDate")
                ->setParameter('maxDate', $filters['maxDate']);
        }
        $query=$queryBuilder->getQuery();
        $result = $query->getResult();
        return $result;
    }

    public function getEventsAsOrganizer(array $filters, User $fakeUser, Status $statusAnnulee)
    {
        $queryBuilder = $this->createQueryBuilder('e')
            ->andWhere('e.organizer = :fakeUser')
            ->setParameter('fakeUser', $fakeUser)
            ->andWhere('e.startAt > :minDate ')
            ->andWhere('e.status != :statusAnnulee')
            ->setParameter('statusAnnulee', $statusAnnulee)
            ->andWhere('e.campus = :campus');
        if(isset($filters['campus'])){
            $queryBuilder->setParameter('campus',$filters['campus']);
        }else{
            $queryBuilder->setParameter('campus', $fakeUser->getCampus());
        }
        if(isset($filters['name'])){
            $queryBuilder
                ->andWhere("e.name LIKE :name")
                ->setParameter('name','%'.$filters['name'].'%');
        }
        if(isset($filters['minDate'])){
            $queryBuilder->setParameter('minDate', $filters['minDate']);
        }else{
            $queryBuilder->setParameter('minDate', new \DateTime('now'));
        }
        if(isset($filters['maxDate'])){
            $queryBuilder
                ->andWhere("e.startAt < :maxDate")
                ->setParameter('maxDate', $filters['maxDate']);
        }
        $query=$queryBuilder->getQuery();
        $result = $query->getResult();
        return $result;

    }
    public function getEventsWhereRegistred(array $filters, User $fakeUser, Status $statusAnnulee)
    {
        $queryBuilder = $this->createQueryBuilder('e')
            ->join('e.registration','u')
            ->addSelect('u')
            ->andWhere('u = :fakeUser')
            ->setParameter('fakeUser', $fakeUser)
            ->andWhere('e.status != :statusAnnulee')
            ->setParameter('statusAnnulee', $statusAnnulee)
            ->andWhere('e.startAt > :minDate ')
            ->andWhere('e.campus = :campus');;
        if(isset($filters['campus'])){
            $queryBuilder->setParameter('campus',$filters['campus']);
        }else{
            $queryBuilder->setParameter('campus', $fakeUser->getCampus());
        }
        if(isset($filters['name'])){
            $queryBuilder
                ->andWhere("e.name LIKE :name")
                ->setParameter('name','%'.$filters['name'].'%');
        }
        if(isset($filters['minDate'])){
            $queryBuilder->setParameter('minDate', $filters['minDate']);
        }else{
            $queryBuilder->setParameter('minDate', new \DateTime('now'));
        }
        if(isset($filters['maxDate'])){
            $queryBuilder
                ->andWhere("e.startAt < :maxDate")
                ->setParameter('maxDate', $filters['maxDate']);
        }
        $query=$queryBuilder->getQuery();
        $result = $query->getResult();
        return $result;
    }
    public function getEventsWhereNotRegistred(array $filters, User $fakeUser, Status $statusAnnulee)
    {
        $queryBuilder = $this->createQueryBuilder('e')
            ->join('e.registration','u')
            ->addSelect('u')
            ->andWhere('u != :fakeUser')
            ->setParameter('fakeUser', $fakeUser)
            ->andWhere('e.status != :statusAnnulee')
            ->setParameter('statusAnnulee', $statusAnnulee)
            ->andWhere('e.startAt > :minDate ')
            ->andWhere('e.campus = :campus');;
        if(isset($filters['campus'])){
            $queryBuilder->setParameter('campus',$filters['campus']);
        }else{
            $queryBuilder->setParameter('campus', $fakeUser->getCampus());
        }
        if(isset($filters['name'])){
            $queryBuilder
                ->andWhere("e.name LIKE :name")
                ->setParameter('name','%'.$filters['name'].'%');
        }
        if(isset($filters['minDate'])){
            $queryBuilder->setParameter('minDate', $filters['minDate']);
        }else{
            $queryBuilder->setParameter('minDate', new \DateTime('now'));
        }
        if(isset($filters['maxDate'])){
            $queryBuilder
                ->andWhere("e.startAt < :maxDate")
                ->setParameter('maxDate', $filters['maxDate']);
        }
        $query=$queryBuilder->getQuery();
        $result = $query->getResult();
        return $result;
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
