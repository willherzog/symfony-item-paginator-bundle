<?php

namespace WHSymfony\WHItemPaginatorBundle\Filter;

/**
 * A filter implementing this interface will be (as the name suggests) excluded from a paginator's active filters count.
 *
 * This can be useful if, say, the filter's `->isApplicable()` method always returns `true`.
 */
interface ExcludeFromActiveFiltersCount
{
}
