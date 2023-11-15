<?php

namespace WHSymfony\WHItemPaginatorBundle\Paginator;

use Doctrine\ORM\EntityManagerInterface;

use WHSymfony\WHItemPaginatorBundle\Config\PaginatorConfigBuilder;

class ItemPaginatorFactory
{
	public function __construct(protected readonly EntityManagerInterface $entityManager)
	{}

	public function create(string $class, PaginatorConfigBuilder $configBuilder): ItemPaginator
	{
		if( !class_exists($class) ) {
			throw new \InvalidArgumentException(sprintf('Class "%s" does not exist.', $class));
		}

		if( !is_subclass_of($class, ItemPaginator::class, true) ) {
			throw new \InvalidArgumentException(sprintf('Class "%s" exists but does not extend from "%s".', $class, ItemPaginator::class));
		}

		return new $class($configBuilder->build(), $this->entityManager);
	}
}
