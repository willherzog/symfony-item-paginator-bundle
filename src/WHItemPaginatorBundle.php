<?php

namespace WHSymfony\WHItemPaginatorBundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

use WHSymfony\WHItemPaginatorBundle\Config\PaginatorConfigBuilderFactory;
use WHSymfony\WHItemPaginatorBundle\Paginator\ItemPaginatorFactory;
use WHSymfony\WHItemPaginatorBundle\Twig\ItemPaginatorExtension;

/**
 * @author Will Herzog <willherzog@gmail.com>
 */
class WHItemPaginatorBundle extends AbstractBundle
{
	protected string $extensionAlias = 'wh_paginator';

	public function configure(DefinitionConfigurator $definition): void
	{
		$definition->rootNode()
			->children()
				->scalarNode('page_request_query')
					->cannotBeEmpty()
					->defaultValue('page')
					->info('The request query added to the URL to determine the current pagination page (e.g. "?page=2").')
				->end()
				->integerNode('items_per_page')
					->defaultValue(10)
					->min(1)->max(999)
					->info('The default maximum items shown per pagination page.')
				->end()
				->arrayNode('display_options')
					->addDefaultsIfNotSet()
					->children()
						->booleanNode('show_item_total')
							->defaultTrue()
							->info('Whether to display the total item count as a translation string before the actions/links.')
						->end()
						->booleanNode('show_bookend_actions')
							->defaultTrue()
							->info('Whether to display actions for going to the first/last pages.')
						->end()
						->booleanNode('show_placeholders')
							->defaultFalse()
							->info('Whether to show placeholers for next/previous/first/last actions when they are not applicable (e.g. previous/first on the first page).')
						->end()
						->integerNode('max_numeric_links')
							->defaultValue(4)
							->min(0)->max(99)
							->info('The maximum amount of page number actions (i.e. not including next/previous/first/last).')
						->end()
						->booleanNode('show_current_page')
							->defaultTrue()
							->info('Whether to display the current page number (placed in numeric order within the numeric links, if there are any)')
						->end()
						->booleanNode('show_page_count')
							->defaultFalse()
							->info('Whether to display the total page count after the current page number (show_current_page must also be enabled).')
						->end()
						->scalarNode('separator')
							->defaultValue('/')
							->info('String to output, if any, between the current page number and the total page count.')
						->end()
					->end()
				->end()
				->arrayNode('shortcut_keys')
					->addDefaultsIfNotSet()
					->children()
						->scalarNode('previous')
							->cannotBeEmpty()
							->defaultValue('p')
						->end()
						->scalarNode('next')
							->cannotBeEmpty()
							->defaultValue('n')
						->end()
						->scalarNode('first')
							->cannotBeEmpty()
							->defaultValue('f')
						->end()
						->scalarNode('last')
							->cannotBeEmpty()
							->defaultValue('l')
						->end()
					->end()
				->end()
			->end()
		;
	}

	public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
	{
		$container->services()
			->set('wh_item_paginator.config_builder_factory', PaginatorConfigBuilderFactory::class)
				->args([
					$config['page_request_query'],
					$config['items_per_page'],
					$config['display_options'],
					$config['shortcut_keys']
				])
			->alias(PaginatorConfigBuilderFactory::class, 'wh_item_paginator.config_builder_factory')
		;

		$container->services()
			->set('wh_item_paginator.paginator_factory', ItemPaginatorFactory::class)
				->args([
					service('doctrine.orm.default_entity_manager'),
					service('wh_item_paginator.config_builder_factory')
				])
			->alias(ItemPaginatorFactory::class, 'wh_item_paginator.paginator_factory')
		;

		$container->services()
			->set('wh_item_paginator.twig.extension', ItemPaginatorExtension::class)
				->args([$config['page_request_query']])
				->tag('twig.extension')
		;
	}
}
