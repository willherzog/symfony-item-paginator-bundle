<?php

namespace WHSymfony\WHItemPaginatorBundle\Filter;

use Symfony\Component\HttpFoundation\Request;

use WHSymfony\WHItemPaginatorBundle\Paginator\ItemPaginator;

/**
 * Interface for a filter to be used with an item paginator.
 *
 * @author Will Herzog <willherzog@gmail.com>
 */
interface ItemFilter
{
	/**
	 * Determine whether this filter can be used with the given $paginator.
	 */
	public function canBeUsedWith(ItemPaginator $paginator): bool;

	/**
	 * Determine whether this filter is applicable to the current $request.
	 */
	public function isApplicable(Request $request): bool;

	/**
	 * Apply this filter to the given $paginator.
	 */
	public function apply(ItemPaginator $paginator): void;
}
