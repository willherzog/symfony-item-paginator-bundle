<?php

namespace WHSymfony\WHItemPaginatorBundle\Paginator;

use Doctrine\ORM\QueryBuilder;

/**
 * A default implementation of SearchableItemPaginator.
 *
 * @author Will Herzog <willherzog@gmail.com>
 */
trait SearchableItemTrait
{
	/**
	 * @param string[]|string $columns
	 */
	public function setSearchTermAndColumns(string $term, array|string $columns): static
	{
		if( !is_array($columns) ) {
			$columns = (array) $columns;
		} elseif( empty($columns) ) {
			throw new \BadMethodCallException('At least one search column must be specified.');
		}

		/** @var QueryBuilder */
		$qb = $this->queryBuilder;

		if( 1 < count($columns) ) {
			$columnExpressions = [];

			foreach( $columns as $column ) {
				if( empty($column) ) {
					throw new \InvalidArgumentException('Search column name cannot be empty.');
				}

				$columnExpressions[] = $qb->expr()->like($this->entityAlias . '.' . $column, ':term');
			}

			$whereExpression = $qb->expr()->orX(...$columnExpressions);
		} else {
			$column = array_shift($columns);

			if( empty($column) ) {
				throw new \InvalidArgumentException('Search column name cannot be empty.');
			}

			$whereExpression = $qb->expr()->like($this->entityAlias . '.' . $column, ':term');
		}

		$this->queryBuilder
			->andWhere($whereExpression)
			->setParameter('term', sprintf('%%%s%%', $term))
		;

		return $this;
	}
}
