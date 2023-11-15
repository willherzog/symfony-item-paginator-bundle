<?php

namespace WHSymfony\WHItemPaginatorBundle\Filter;

/**
 * @author Will Herzog <willherzog@gmail.com>
 */
interface HasRequestQuery
{
	public function getRequestQueryName(): string;
}
