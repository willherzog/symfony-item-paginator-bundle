<?php

namespace WHSymfony\WHItemPaginatorBundle\Config;

/**
 * @author Will Herzog <willherzog@gmail.com>
 */
final class PaginatorConfigBuilder
{
	public const DISPLAY_OPTIONS = [
		'show_item_total' => 'bool',
		'symbol_based_labels' => 'bool',
		'show_bookend_actions' => 'bool',
		'show_placeholders' => 'bool',
		'max_numeric_links' => 'int',
		'show_current_page' => 'bool',
		'show_page_count' => 'bool',
		'separator' => 'string'
	];

	public const SHORTCUT_KEYS = ['previous','next','first','last'];

	public function __construct(
		private string $pageRequestQuery,
		private int $itemsPerPage,
		private array $displayOptions,
		private array $shortcutKeys
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

	public function setDisplayOption(string $option, mixed $value): static
	{
		if( !key_exists($option, self::DISPLAY_OPTIONS) ) {
			throw new \InvalidArgumentException(sprintf('"%s" is not one of the supported display options.'));
		}

		$expectedType = self::DISPLAY_OPTIONS[$option];
		$actualType = get_debug_type($value);

		if( $actualType !== $expectedType ) {
			throw new \InvalidArgumentException(sprintf('Expected value of type "%s" for display option "%s" but got "%s" instead.', $expectedType, $option, $actualType));
		}

		$this->displayOptions[$option] = $value;

		return $this;
	}

	public function setShortcutKey(string $shortcut, string $key): static
	{
		if( !in_array($shortcut, self::SHORTCUT_KEYS, true) ) {
			throw new \InvalidArgumentException(sprintf('"%s" is not one of the supported shortcut keys.'));
		}

		if( strlen($key) !== 1 ) {
			throw new \InvalidArgumentException('Shortcut key must a string with exactly 1 character.');
		}

		$this->shortcutKeys[$shortcut] = $key;

		return $this;
	}

	public function build(): PaginatorConfig
	{
		return new PaginatorConfig(
			$this->pageRequestQuery,
			$this->itemsPerPage,
			$this->displayOptions,
			$this->shortcutKeys
		);
	}
}
