<?php

namespace WHSymfony\WHItemPaginatorBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

/**
 * @author Will Herzog <willherzog@gmail.com>
 */
class ItemPaginatorExtension extends AbstractExtension implements GlobalsInterface
{
	public function __construct(protected readonly string $pageRequestQuery)
	{}

	/**
	 * @inheritDoc
	 */
	public function getGlobals(): array
	{
		return ['paginator_request_query' => $this->pageRequestQuery];
	}
}
