<?php

namespace WHSymfony\WHItemPaginatorBundle\Paginator;

use Doctrine\ORM\QueryBuilder;

/**
 * A default implementation of abstract ItemPaginator methods ->initialize() and ->getQueryBuilder().
 *
 * @author Will Herzog <willherzog@gmail.com>
 */
trait DefaultQueryBuilderTrait
{
	protected readonly QueryBuilder $queryBuilder;

	protected function createQueryBuilder(): static
	{
		/** @var QueryBuilder */
		$this->queryBuilder = $this->entityManager->getRepository($this->itemType->getEntityClass())->createQueryBuilder($this->entityAlias);

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	protected function initialize(): void
	{
		$this->createQueryBuilder();
	}

	/**
	 * @inheritDoc
	 */
	public function getQueryBuilder(): QueryBuilder
	{
		return $this->queryBuilder;
	}
}
