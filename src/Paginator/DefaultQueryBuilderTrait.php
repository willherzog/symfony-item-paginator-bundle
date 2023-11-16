<?php

namespace WHSymfony\WHItemPaginatorBundle\Paginator;

use Doctrine\ORM\QueryBuilder;

/**
 * Provides a ->createQueryBuilder() helper method and a default implementation of abstract ItemPaginator method ->getQueryBuilder().
 *
 * @author Will Herzog <willherzog@gmail.com>
 */
trait DefaultQueryBuilderTrait
{
	protected readonly QueryBuilder $queryBuilder;

	protected function createQueryBuilder(string $entityClass): static
	{
		/** @var QueryBuilder */
		$this->queryBuilder = $this->entityManager->getRepository($entityClass)->createQueryBuilder($this->entityAlias);

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function getQueryBuilder(): QueryBuilder
	{
		return $this->queryBuilder;
	}
}
