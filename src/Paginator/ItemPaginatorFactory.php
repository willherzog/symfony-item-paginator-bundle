<?php

namespace WHSymfony\WHItemPaginatorBundle\Paginator;

use Doctrine\ORM\EntityManagerInterface;

use WHSymfony\WHItemPaginatorBundle\Config\{PaginatorConfigBuilder,PaginatorConfigBuilderFactory};

class ItemPaginatorFactory
{
	public function __construct(
		protected readonly EntityManagerInterface $entityManager,
		protected readonly PaginatorConfigBuilderFactory $configBuilderFactory
	) {}

	public function create(string $class, PaginatorConfigBuilder $configBuilder = null): ItemPaginator
	{
		if( !class_exists($class) ) {
			throw new \InvalidArgumentException(sprintf('Class "%s" does not exist.', $class));
		}

		if( !is_subclass_of($class, ItemPaginator::class, true) ) {
			throw new \InvalidArgumentException(sprintf('Class "%s" exists but does not extend from "%s".', $class, ItemPaginator::class));
		}

		if( $configBuilder === null ) {
			$configBuilder = $this->configBuilderFactory->createBuilder();
		}

		return new $class($configBuilder->build(), $this->entityManager);
	}
}
