<?php

namespace WHSymfony\WHItemPaginatorBundle\Filter;

use WHSymfony\WHItemPaginatorBundle\Filter\RequestQuery\RequestQueryDefinition;
use WHSymfony\WHItemPaginatorBundle\Paginator\ItemPaginator;
use WHSymfony\WHItemPaginatorBundle\Paginator\SearchableItemPaginator;

use WHSymfony\WHItemPaginatorBundle\Exception\InvalidFilterParameterException;

/**
 * @author Will Herzog <willherzog@gmail.com>
 */
class ItemSearchFilter extends AbstractRequestQueryFilter
{
	public const REQUEST_QUERY = 'search';

	public function __construct(protected readonly array $searchColumns)
	{
		if( empty($this->searchColumns) ) {
			throw new InvalidFilterParameterException('Please specify at least one search column.');
		}
	}

	protected function getRequestQueryDefinition(): RequestQueryDefinition
	{
		return new RequestQueryDefinition(self::REQUEST_QUERY);
	}

	public function supports(ItemPaginator $paginator): bool
	{
		return $paginator instanceof SearchableItemPaginator;
	}

	/**
	 * @param ItemPaginator&SearchableItemPaginator $paginator
	 */
	public function apply(ItemPaginator $paginator): void
	{
		$paginator->setSearchTermAndColumns($this->getRequestQueryValue(), $this->searchColumns);
	}
}
