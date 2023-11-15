<?php

namespace WHSymfony\WHItemPaginatorBundle\Config;

/**
 * @author Will Herzog <willherzog@gmail.com>
 */
final readonly class PaginatorConfig
{
	public PaginatorConfigDisplayFlags $flags;

	public function __construct(
		public string $pageRequestQuery,
		public int $itemsPerPage,
		public int $maxNumericLinks,
		public string $shortcutKeyPrev,
		public string $shortcutKeyNext,
		public string $shortcutKeyFirst,
		public string $shortcutKeyLast
	) {
		$this->flags = new PaginatorConfigDisplayFlags();
	}
}
