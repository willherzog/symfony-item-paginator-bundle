<?php

namespace WHSymfony\WHItemPaginatorBundle\Filter;

/**
 * @author Will Herzog <willherzog@gmail.com>
 */
interface HasDefaultValue
{
	public function getDefaultValue(): mixed;
}
