<?php

namespace WHSymfony\WHItemPaginatorBundle\Filter;

use WHSymfony\WHItemPaginatorBundle\Paginator\ItemPaginator;
use WHSymfony\WHItemPaginatorBundle\Paginator\SearchableItemPaginator;

/**
 * @author Will Herzog <willherzog@gmail.com>
 */
class ItemSearchFilter implements ItemFilter, HasRequestQuery
{
	use IsApplicableRequestQueryTrait;

	protected bool $requireNotEmpty = true;

	public function __construct(protected readonly array $searchColumns)
	{
		if( empty($this->searchColumns) ) {
			throw new \InvalidArgumentException('Please specify at least one search column');
		}
	}

	/**
	 * @inheritDoc
	 */
	public function getRequestQueryName(): string
	{
		return 'search';
	}

	/**
	 * @inheritDoc
	 */
	public function supports(ItemPaginator $paginator): bool
	{
		return $paginator instanceof SearchableItemPaginator;
	}

	/**
	 * @inheritDoc
	 */
	public function apply(ItemPaginator $paginator): void
	{
		$paginator->setSearchTermAndColumns($this->requestQueryValue, $this->searchColumns);
	}
}
