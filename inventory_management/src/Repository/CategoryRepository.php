<?php

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Category>
 *
 * @method Category|null find($id, $lockMode = null, $lockVersion = null)
 * @method Category|null findOneBy(array $criteria, array $orderBy = null)
 * @method Category[]    findAll()
 * @method Category[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Category $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(Category $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    // /**
    //  * @return Category[] Returns an array of Category objects
    //  */
    
    public function searchCategoryByTitle($keyword)
    {
        return $this->createQueryBuilder('category')
        ->andWhere('category.name LIKE :key')
        ->setParameter('key', '%' . $keyword . '%')
        ->orderBy('category.name', 'ASC')
        ->setMaxResults(5)
        ->getQuery()
        ->getResult();
    }
    
    public function sortCategoryAsc()
    {
        return $this->createQueryBuilder('category')
            ->orderBy('category.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return category[]  
     */
    public function sortCategoryDesc()
    {
        return $this->createQueryBuilder('category')
              ->orderBy('category.id', 'DESC')
              ->getQuery()
              ->getResult()
          ;
    }
}
