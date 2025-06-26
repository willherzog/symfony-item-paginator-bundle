<?php

namespace WHSymfony\WHItemPaginatorBundle\Exception;

/**
 * @author Will Herzog <willherzog@gmail.com>
 */
class OutOfPaginationRangeException extends \OutOfBoundsException
{
	public function __construct(int $currentPage, int $firstPage, int $lastPage, int $code = 0, \Throwable|null $previous = null)
	{
		$message = sprintf('The requested page number (%d) is outside of the possible range (%d-%d).', $currentPage, $firstPage, $lastPage);

		parent::__construct($message, $code, $previous);
	}
}
