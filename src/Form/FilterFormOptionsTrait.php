<?php

namespace WHSymfony\WHItemPaginatorBundle\Form;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Will Herzog <willherzog@gmail.com>
 */
trait FilterFormOptionsTrait
{
	public function configureOptions(OptionsResolver $resolver): void
	{
		$resolver->setDefaults(['method' => 'GET', 'csrf_protection' => false]);
	}
}
