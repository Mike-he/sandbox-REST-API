<?php

namespace Sandbox\ApiBundle\Repository\SalesAdmin;

use Doctrine\ORM\EntityRepository;

class SalesCompanyServiceInfosRepository extends EntityRepository
{
    /**
     * @param $company
     * @param $type
     *
     * @return mixed
     */
    public function getCollectionMethod(
        $company,
        $type
    ) {
        $companyService = $this->createQueryBuilder('scs')
            ->where('scs.company = :company')
            ->andWhere('scs.roomTypes = :type')
            ->andWhere('scs.status = :status')
            ->setParameter('company', $company)
            ->setParameter('type', $type)
            ->setParameter('status', true)
            ->getQuery()
            ->getOneOrNullResult();

        $result = $companyService ? $companyService->getCollectionMethod() : null;

        return $result;
    }

    /**
     * @param $company
     *
     * @return array
     */
    public function getCompanyService(
        $company
    ) {
        $query = $this->createQueryBuilder('scs')
            ->where('scs.company = :company')
            ->andWhere('scs.status = :status')
            ->setParameter('company', $company)
            ->setParameter('status', true);

        $result = $query->getQuery()->getResult();

        return $result;
    }

    /**
     * @param $company
     * @param $type
     *
     * @return mixed
     */
    public function getCompanyServiceByType(
        $company,
        $type
    ) {
        $query = $this->createQueryBuilder('scs')
            ->where('scs.company = :company')
            ->andWhere('scs.roomTypes = :type')
            ->andWhere('scs.status = :status')
            ->setParameter('company', $company)
            ->setParameter('type', $type)
            ->setParameter('status', true);

        $result = $query->getQuery()->getSingleResult();

        return $result;
    }

    /**
     * @return array
     */
    public function getAdminInvoiceCategories()
    {
        $query = $this->createQueryBuilder('s')
            ->select('DISTINCT(s.invoicingSubjects)')
            ->where('s.invoicingSubjects IS NOT NULL');

        $categories = $query->getQuery()->getResult();
        $categories = array_map('current', $categories);

        return $categories;
    }
}
