<?php

namespace WHSymfony\WHItemPaginatorBundle\Util;

/**
 * @author Will Herzog <willherzog@gmail.com>
 */
final class StringUtil
{
	private function __construct()
	{}

	/**
	 * Modified version of Symfony\Component\Form\Util\StringUtil::fqcnToBlockPrefix() for use with pagination filters.
	 */
	static public function fqcnToFilterName(string $fqcn): ?string
	{
		if (preg_match('~([^\\\\]+?)(filter)?$~i', $fqcn, $matches)) {
			return strtolower(preg_replace(['/([A-Z]+)([A-Z][a-z])/', '/([a-z\d])([A-Z])/'], ['\\1_\\2', '\\1_\\2'], $matches[1]));
		}

		return null;
	}
}
