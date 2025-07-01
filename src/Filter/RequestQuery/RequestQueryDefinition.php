<?php

namespace WHSymfony\WHItemPaginatorBundle\Filter\RequestQuery;

/**
 * Definition for a request query, to specify how it should be handled for pagination filtering purposes.
 *
 * @author Will Herzog <willherzog@gmail.com>
 */
final class RequestQueryDefinition
{
	public function __construct(
		/**
		 * The "name" of the request query, e.g. "search" in `domain.tld/path?search=term`.
		 */
		public readonly string $name,
		/**
		 * Set this property to `true` to allow request query values to be empty strings or empty arrays.
		 */
		public bool $allowEmpty = false,
		/**
		 * Set this property to `true` to require request query values to be numeric (using the PHP function `is_numeric()`; no type conversion is applied, however).
		 */
		public bool $numericOnly = false,
		/**
		 * Set this property to `true` if this request query may have multiple values (this means {@link AbstractRequestQueryFilter::getRequestQueryValue()} will always return an array).
		 */
		public bool $multiValue = false
	) {}
}
