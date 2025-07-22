<?php

namespace WHSymfony\WHItemPaginatorBundle\Exception;

use Doctrine\ORM\EntityManagerInterface;

class IncompatibleEntityManagerException extends \UnexpectedValueException implements ItemPaginatorException
{
	public function __construct(string $entityClass, int $code = 0, \Throwable|null $previous = null)
	{
		$message = sprintf('Did not receive an instance of "%s" as the object manager for entity class "%s".', EntityManagerInterface::class, $entityClass);

		parent::__construct($message, $code, $previous);
	}
}
