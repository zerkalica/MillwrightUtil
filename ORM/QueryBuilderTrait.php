<?php
namespace Millwright\Util\ORM;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

trait QueryBuilderTrait
{
    /**
     * @var EntityRepository
     */
    //protected $repository;

    /**
     * Get query builder for select
     *
     * @param string|null $alias builder alias
     *
     * @return QueryBuilder
     */
    protected function getSelectBuilder($alias = null)
    {
        if (!$alias) {
            $alias = ORMUtil::getAlias($this->repository);
        }

        return $this->repository->createQueryBuilder($alias);
    }

    /**
     * Get query builder for update
     *
     * @param string|null $alias builder alias
     *
     * @return QueryBuilder
     */
    protected function getUpdateBuilder($alias = null)
    {
        return ORMUtil::createUpdateQueryBuilder($this->repository, $alias);
    }
    /**
     * Get query builder for delete
     *
     * @param string|null $alias builder alias
     *
     * @return QueryBuilder
     */
    protected function getDeleteBuilder($alias = null)
    {
        return ORMUtil::createDeleteQueryBuilder($this->repository, $alias);
    }
}
