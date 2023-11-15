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
		private readonly int $maxNumericLinks,
		private readonly string $shortcutKeyPrev,
		private readonly string $shortcutKeyNext,
		private readonly string $shortcutKeyFirst,
		private readonly string $shortcutKeyLast
	) {}

	public function createBuilder(int $itemsPerPage = null): PaginatorConfigBuilder
	{
		return new PaginatorConfigBuilder(
			$this->pageRequestQuery,
			$itemsPerPage ?? $this->itemsPerPage,
			$this->maxNumericLinks,
			$this->shortcutKeyPrev,
			$this->shortcutKeyNext,
			$this->shortcutKeyFirst,
			$this->shortcutKeyLast
		);
	}
}
