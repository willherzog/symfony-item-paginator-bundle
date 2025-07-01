<?php

namespace WHSymfony\WHItemPaginatorBundle\Filter;

use Symfony\Component\HttpFoundation\Request;

use WHSymfony\WHItemPaginatorBundle\Filter\RequestQuery\RequestQueryDefinition;

/**
 * A default implementation of {@link ItemFilter::isApplicable()} for filters relying on single request queries.
 *
 * @uses RequestQueryDefinition
 *
 * @author Will Herzog <willherzog@gmail.com>
 */
abstract class AbstractRequestQueryFilter implements ItemFilter, HasRequestQuery
{
	private string|array|null $requestQueryValue;

	abstract protected function getRequestQueryDefinition(): RequestQueryDefinition;

	final public function getRequestQueryName(): string
	{
		return $this->getRequestQueryDefinition()->name;
	}

	final public function isApplicable(Request $request): bool
	{
		$requestQuery = $this->getRequestQueryDefinition();

		if( !$request->query->has($requestQuery->name) ) {
			return false;
		}

		if( $requestQuery->multiValue ) {
			$this->requestQueryValue = $request->query->all($requestQuery->name);
		} else {
			$this->requestQueryValue = $request->query->get($requestQuery->name);
		}

		if( !$requestQuery->allowEmpty ) {
			if( ($requestQuery->multiValue && $this->requestQueryValue === []) || $this->requestQueryValue === '' ) {
				return false;
			}
		}

		if( $requestQuery->numericOnly ) {
			if( $requestQuery->multiValue ) {
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

	/**
	 * When called from {@link ItemFilter::apply()} (which itself is only called if {@link ItemFilter::isApplicable()} returns `true`),
	 * this method will never return `null`. Whether it returns `string` or `string[]` depends on what the calling filter class has for
	 * {@link RequestQueryDefinition->multiValue} (array of strings if `true`, a single string if `false`).
	 *
	 * @return string|string[]|null
	 */
	final public function getRequestQueryValue(): string|array|null
	{
		return $this->requestQueryValue ?? null;
	}
}
