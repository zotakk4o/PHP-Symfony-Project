<?php

namespace VehturiinikShopBundle\Repository;
use VehturiinikShopBundle\Entity\Purchase;

/**
 * PurchaseRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class PurchaseRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * @param $productId
     * @param $userId
     * @return Purchase
     */
    public function findOneByUserIdAndProductId($productId, $userId)
    {
        $query = $this->getEntityManager()
            ->getRepository(Purchase::class)
            ->createQueryBuilder('purchase')
            ->select('purchase')
            ->where('purchase.productId = ?1')
            ->andWhere('purchase.userId = ?2')
            ->setParameter(1, $productId)
            ->setParameter(2, $userId)
            ->getQuery()
            ->getResult();


        return empty($query) == true ? null : $query[0];
    }


}
