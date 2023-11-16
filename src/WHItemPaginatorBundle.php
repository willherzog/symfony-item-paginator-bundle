<?php

namespace WHSymfony\WHItemPaginatorBundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

use WHSymfony\WHItemPaginatorBundle\Config\PaginatorConfigBuilderFactory;
use WHSymfony\WHItemPaginatorBundle\Paginator\ItemPaginatorFactory;

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
				->end()
				->integerNode('items_per_page')
					->defaultValue(10)
					->min(1)->max(999)
					->info('Maximum items shown per pagination page.')
				->end()
				->integerNode('max_numeric_links')
					->defaultValue(4)
					->min(1)->max(99)
					->info('The maximum amount of page number actions (i.e. not including next/previous/first/last).')
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
					$config['max_numeric_links'],
					$config['shortcut_keys']['previous'],
					$config['shortcut_keys']['next'],
					$config['shortcut_keys']['first'],
					$config['shortcut_keys']['last']
				])
			->set('wh_item_paginator.paginator_factory', ItemPaginatorFactory::class)
				->args([service('doctrine.orm.default_entity_manager')])
		;

		$container->parameters()
			->set('wh_item_paginator.page_request_query', $config['page_request_query'])
		;
	}

	public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
	{
		$container->extension('twig', [
			'globals' => ['paginator_request_query' => param('%wh_item_paginator.page_request_query%')]
		]);
	}
}
