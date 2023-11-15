<?php

namespace WHSymfony\WHItemPaginatorBundle\Filter;

use Symfony\Component\HttpFoundation\Request;

/**
 * A default implementation of ItemFilter::isApplicable() for filters implementing the HasRequestQuery interface.
 *
 * @author Will Herzog <willherzog@gmail.com>
 */
trait IsApplicableRequestQueryTrait
{
	/**
	 * Set this property to TRUE to require the request query value to not be empty (instead of merely not NULL).
	 */
	protected bool $requireNotEmpty = false;

	/**
	 * Value from request query, which will be set by this trait's ->isApplicable() method.
	 */
	protected ?mixed $requestQueryValue = null;

	/**
	 * @inheritDoc
	 */
	public function isApplicable(Request $request): bool
	{
		if( !($this instanceof HasRequestQuery) ) {
			throw new \LogicException(sprintf('This trait can only be used with an ItemFilter class implementing %s.', HasRequestQuery::class));
		}

		$queryName = $this->getRequestQueryName();

		if( !$request->query->has($queryName) ) {
			return false;
		}

		$this->requestQueryValue = $request->query->get($queryName);

		if( $this->requireNotEmpty ) {
			return !empty($this->requestQueryValue);
		}

		return $this->requestQueryValue !== null;
	}
}
