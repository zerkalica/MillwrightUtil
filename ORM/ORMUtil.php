<?php
namespace Millwright\Util\ORM;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityRepository;

/**
 * Static orm util class
 */
class ORMUtil
{

    /**
     * Gets ids from domains
     *
     * @param object[] $domains
     *
     * @return integer[]
     */
    public static function getIds(array $domains)
    {
        $result = array();
        foreach ($domains as $domain)
        {
            $result[] = $domain->getId();
        }

        return $result;
    }

    /**
     * Get ids string wor include in SQL IN query
     *
     * @param object[] $domains
     *
     * @return string
     */
    static public function getIdString(array $domains)
    {
        return implode(', ', self::getIds($domains));
    }

    /**
     * Get alias by repository
     *
     * @param EntityRepository $repository
     *
     * @return string
     */
    public static function getAlias(EntityRepository $repository)
    {
        $parts = explode('\\', $repository->getClassName());

        return lcfirst($parts[count($parts) - 1]);
    }

    /**
     * Get update query builder
     *
     * @param EntityRepository $repository
     * @param string|null      $alias
     *
     * @return QueryBuilder
     */
    public static function createUpdateQueryBuilder(EntityRepository $repository, $alias = null)
    {
        if (!$alias) {
            $alias = self::getAlias($repository);
        }

        $qb = $repository->createQueryBuilder($alias);

        return $qb->update($className, $alias);
    }

    /**
     * Create select query builder
     *
     * @param EntityRepository $repository
     * @param null|string      $alias
     *
     * @return QueryBuilder
     */
    public static function createSelectQueryBuilder(EntityRepository $repository, $alias = null)
    {
        if (!$alias) {
            $alias = self::getAlias($repository);
        }

        return $repository->createQueryBuilder($alias);
    }

    /**
     * Create query builder for delete
     *
     * @param EntityRepository $repository
     * @param null|string      $alias
     *
     * @return QueryBuilder
     */
    public static function createDeleteQueryBuilder(EntityRepository $repository, $alias = null)
    {
        if (!$alias) {
            $alias = self::getAlias($repository);
        }

        $qb = $repository->createQueryBuilder($alias);

        return $qb->delete($className, $alias);
    }

}
