<?php

namespace WHSymfony\WHItemPaginatorBundle\Config;

final class PaginatorConfigDisplayFlags
{
	private array $flags = [
		'display_item_total' => true,
		'display_bookend_actions' => true,
		'display_current_page' => true,
		'display_page_count' => false,
		'display_placeholders' => false
	];

	private function throwException(string $flag): void
	{
		throw new \OutOfBoundsException(sprintf('"%s" is not one of the supported flags: %s.', $flag, implode(', ', array_keys($this->flags))));
	}

	public function get(string $flag): bool
	{
		if( key_exists($flag, $this->flags) ) {
			return $this->flags[$flag];
		}

		$this->throwException($flag);
	}

	public function set(string $flag, bool $value): void
	{
		if( key_exists($flag, $this->flags) ) {
			$this->flags[$flag] = $value;
		}

		$this->throwException($flag);
	}
}
