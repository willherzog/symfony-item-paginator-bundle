<?php

namespace WHSymfony\WHItemPaginatorBundle\Paginator;

/**
 * @author Will Herzog <willherzog@gmail.com>
 */
interface SearchableItemPaginator
{
	public function setSearchTermAndColumns(string $term, array|string $columns): static;
}
