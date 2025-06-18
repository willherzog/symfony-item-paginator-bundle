<?php

namespace WHSymfony\WHItemPaginatorBundle\Config;

/**
 * @author Will Herzog <willherzog@gmail.com>
 */
final class PaginatorConfigBuilderFactory
{
	public function __construct(
		private readonly string $pageRequestQuery,
		private readonly int $itemsPerPage,
		private readonly array $displayOptions,
		private readonly array $shortcutKeys
	) {}

	public function createBuilder(?int $itemsPerPage = null): PaginatorConfigBuilder
	{
		return new PaginatorConfigBuilder(
			$this->pageRequestQuery,
			$itemsPerPage ?? $this->itemsPerPage,
			$this->displayOptions,
			$this->shortcutKeys
		);
	}
}
