<?php

namespace WHSymfony\WHItemPaginatorBundle\Config;

/**
 * @author Will Herzog <willherzog@gmail.com>
 */
final readonly class PaginatorConfig
{
	public function __construct(
		public string $pageRequestQuery,
		public int $itemsPerPage,
		public array $displayOption,
		public array $shortcutKey
	) {}
}
