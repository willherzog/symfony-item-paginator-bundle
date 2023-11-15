<?php

namespace WHSymfony\WHItemPaginatorBundle\Config;

/**
 * @author Will Herzog <willherzog@gmail.com>
 */
final class PaginatorConfigBuilder
{
	public function __construct(
		private string $pageRequestQuery,
		private int $itemsPerPage,
		private int $maxNumericLinks,
		private string $shortcutKeyPrev,
		private string $shortcutKeyNext,
		private string $shortcutKeyFirst,
		private string $shortcutKeyLast
	) {}

	public function setItemsPerPage(int $itemsPerPage): static
	{
		$this->itemsPerPage = $itemsPerPage;

		return $this;
	}

	public function setPageRequestQuery(string $pageRequestQuery): static
	{
		$this->pageRequestQuery = $pageRequestQuery;

		return $this;
	}

	public function setMaxNumericLinks(int $maxNumericLinks): static
	{
		$this->maxNumericLinks = $maxNumericLinks;

		return $this;
	}

	public function setShortcutKeyPrev(string $shortcutKey): static
	{
		$this->shortcutKeyPrev = $shortcutKey;

		return $this;
	}

	public function setShortcutKeyNextv(string $shortcutKey): static
	{
		$this->shortcutKeyNext = $shortcutKey;

		return $this;
	}

	public function setShortcutKeyFirst(string $shortcutKey): static
	{
		$this->shortcutKeyFirst = $shortcutKey;

		return $this;
	}

	public function setShortcutKeyLast(string $shortcutKey): static
	{
		$this->shortcutKeyLast = $shortcutKey;

		return $this;
	}

	public function build(): PaginatorConfig
	{
		return new PaginatorConfig(
			$this->pageRequestQuery,
			$this->itemsPerPage,
			$this->maxNumericLinks,
			$this->shortcutKeyPrev,
			$this->shortcutKeyNext,
			$this->shortcutKeyFirst,
			$this->shortcutKeyLast
		);
	}
}
