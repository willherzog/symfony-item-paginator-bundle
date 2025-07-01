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
	// Set this property to TRUE to require the request query value to not be empty (instead of merely not NULL).
	// protected bool $requireNotEmpty = true;

	// Set this property to TRUE to require the request query value to be numeric (no type conversion will be applied, however).
	// protected bool $requireNumeric = true;

	// Set this property to TRUE if the request query may have multiple values (this means $requestQueryValue will always be an array).
	// protected bool $requireArray = true;

	/**
	 * Value from request query, which will be set by this trait's ->isApplicable() method.
	 */
	protected mixed $requestQueryValue = null;

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

		$expectingArray = isset($this->requireArray) && $this->requireArray;

		if( $expectingArray ) {
			$this->requestQueryValue = $request->query->all($queryName);
		} else {
			$this->requestQueryValue = $request->query->get($queryName);
		}

		if( isset($this->requireNotEmpty) && $this->requireNotEmpty ) {
			if( ($expectingArray && $this->requestQueryValue === []) || $this->requestQueryValue === '' ) {
				return false;
			}
		}

		if( isset($this->requireNumeric) && $this->requireNumeric ) {
			if( $expectingArray ) {
				foreach( $this->requestQueryValue as $valueElement ) {
					if( !is_numeric($valueElement) ) {
						return false;
					}
				}

				return true;
			} else {
				return is_numeric($this->requestQueryValue);
			}
		}

		return $this->requestQueryValue !== null;
	}
}
