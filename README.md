# WHItemPaginatorBundle
 A bundle to provide pagination for Doctrine ORM entities within the Symfony framework.

Installation
============

Make sure Composer is installed globally, as explained in the
[installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Applications that use Symfony Flex
----------------------------------

Open a command console, enter your project directory and execute:

```console
$ composer require willherzog/symfony-item-paginator-bundle
```

Applications that don't use Symfony Flex
----------------------------------------

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require willherzog/symfony-item-paginator-bundle
```

### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `config/bundles.php` file of your project:

```php
// config/bundles.php

return [
    // ...
    WHSymfony\WHItemPaginatorBundle\WHItemPaginatorBundle::class => ['all' => true],
];
```

Basic Usage
===========

As you'll see, using this bundle ties closely with the Symfony framework, together with Doctrine ORM (its QueryBuilder feature in particular) and the Twig templating engine.

Step 1: Create a paginator class
--------------------------------

To allow pagination of an entity (or "item"), it must have its own paginator class extending from abstract class `WHSymfony\WHItemPaginatorBundle\Paginator\ItemPaginator` with definitions for the required methods, as seen below:

```php
<?php

namespace App\Pagination;

use WHSymfony\WHItemPaginatorBundle\Paginator\ItemPaginator;

use App\Entity\ExampleItem;

class ExampleItemPaginator extends ItemPaginator
{
    protected function getEntityClass(): string
    {
        return ExampleItem::class; // The fully-qualified class name of your entity
    }

    protected function getEntityAlias(): string
    {
        return 'i'; // A simple alias for referencing your entity
    }

    protected function initialize(): void
    {
        /*
            Custom setup logic goes here, such as adding any unconditional where statement(s) to the
            class instance of the Doctrine ORM QueryBuilder (accessible via $this->queryBuilder).
        */
    }
}
```

See <https://www.doctrine-project.org/projects/doctrine-orm/en/current/reference/query-builder.html> for documentation of the QueryBuilder API.

(Optional) Step 2A: Create filter classes
----------------------------------------

To conditionally control the result set of your paginator(s), create filter classes implementing interface `WHSymfony\WHItemPaginatorBundle\Filter\ItemFilter` with its required methods, as seen below:

```php
<?php

namespace App\Pagination\Filter;

use Symfony\Component\HttpFoundation\Request;

use WHSymfony\WHItemPaginatorBundle\Filter\ItemFilter;

use App\Pagination\ExampleItemPaginator;

class ExampleFilter implements ItemFilter
{
    private readonly string $searchTerm; // Example value to filter by

    public function supports(ItemPaginator $paginator): bool
    {
        // Determine which paginator(s) this filter supports
        return $paginator instanceof ExampleItemPaginator;
    }

    public function isApplicable(Request $request): bool
    {
        // Check whether this filter is applicable to the current request
        if( $request->query->has('searchterm') ) {
            $this->searchTerm = $request->query->get('searchterm');

            return true;
        }

        return false;
    }

    public function apply(ItemPaginator $paginator): void
    {
        // Filter the results (usually via the paginator's query builder*)
        $paginator->getQueryBuilder()
            ->andWhere(sprintf('%s.name = :term', $paginator->entityAlias))
            ->setParameter('term', $this->searchTerm)
        ;
    }
}
```

### Filtering By Search Term

Note that straightforward filtering based on search terms is already supported with the built-in filter `WHSymfony\WHItemPaginatorBundle\Filter\ItemSearchFilter`. This requires your paginator class to implement interface `WHSymfony\WHItemPaginatorBundle\Paginator\SearchableItemPaginator` (which can be easily achieved by using trait `WHSymfony\WHItemPaginatorBundle\Paginator\SearchableItemTrait`), and when an instance of this filter is created, an array containing one or more names of entity properties to search within must be provided to the constructor:

```php
<?php

// ExampleController.php

use WHSymfony\WHItemPaginatorBundle\Filter\ItemSearchFilter;
use WHSymfony\WHItemPaginatorBundle\Paginator\ItemPaginator;

/* ... */

/** @var ItemPaginator $paginator */
$paginator->addFilter(new ItemSearchFilter(['name'])); // "name" should be replaced with a property on your entity
```

### Additional Functionality

_\* Besides using the QueryBuilder API directly, paginator classes have some additional methods for altering the resulting database query:_

```php
<?php

/** @var ItemPaginator $paginator */
$paginator->addSelect(); // Add an entity property name to the item select statements
$paginator->setSelect(); // Set an entity property name for the item select statement*
$paginator->addOrderBy(); // Add an entity property name to the item order-by statements (using ascending order unless second argument is FALSE)
$paginator->setOrderBy(); // Set an entity property name for the item order-by statement* (using ascending order unless second argument is FALSE)

// * Overwrites any previous ones
```

_It is recommended to use the above methods_ (instead of the QueryBuilder API) _when altering select and/or order-by statements._

With custom filters you can also implement one or both of the interfaces `WHSymfony\WHItemPaginatorBundle\Filter\HasRequestQuery`** and/or `WHSymfony\WHItemPaginatorBundle\Filter\HasDefaultValue` to improve interoperability with filter forms.

_** If implementing this interface, you can use the trait `WHSymfony\WHItemPaginatorBundle\Filter\IsApplicableRequestQueryTrait` for defining the `->isApplicable()` method that is required for all filters._

(Optional) Step 2B: Create filter form(s)
-----------------------------------------

This bundle can easily be used without the Symfony Form component—even with filters. The main reason to make use of the Symfony Form component (besides the inherent reusability of creating a form class) is so that user input can easily be reflected in your filter form(s) after they have been submitted and the page has reloaded. However, form validation is most likely unnecessary since nothing will be modified in the database.

With those things in mind, this is a very basic example of a filter form class:

```php
<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExampleFilterForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('search', SearchType::class, [
            'label' => false,
            'attr' => [
                'placeholder' => 'Search items...'
            ]
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'method' => 'GET',
            'csrf_protection' => false
        ]);
    }
}
```

Filter forms must use the `GET` method for submission as this bundle currently only supports checking request queries. And, this being the case, it is probably undesirable to have a CSRF protection value appear in the URL following form submission. To ease setting these default options, this bundle provides the trait `WHSymfony\WHItemPaginatorBundle\Form\FilterFormOptionsTrait`. The same example form again, now using this trait:

```php
<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormBuilderInterface;

use WHSymfony\WHItemPaginatorBundle\Form\FilterFormOptionsTrait;

class ExampleFilterForm extends AbstractType
{
    use FilterFormOptionsTrait;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('search', SearchType::class, [
            'label' => false,
            'attr' => [
                'placeholder' => 'Search items...'
            ]
        ]);
    }
}
```

See subsequent steps for how to make further use of filter forms.

Step 3: Apply pagination in a controller
----------------------------------------

Inject the `WHSymfony\WHItemPaginatorBundle\Paginator\ItemPaginatorFactory` as an argument for your controller action (in addition to the Symfony Request object) and use it to create an instance of your paginator class. Add any desired filters to the new paginator instance, then call its ->handleRequest() method with the Request object. When rendering the output using a Twig template, add the paginator instance as one of the context parameters:

```php
<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{Request,Response};

use WHSymfony\WHItemPaginatorBundle\Paginator\ItemPaginatorFactory;

use App\Pagination\ExampleItemPaginator;
use App\Pagination\Filter\ExampleFilter;

class ExampleController extends AbstractController
{
    public function index(Request $request, ItemPaginatorFactory $paginatorFactory): Response
    {
        $paginator = $paginatorFactory->create(ExampleItemPaginator::class);

        // (Optional) Add filters to the paginator
        $paginator->addFilter(new ExampleFilter());

        try {
            $paginator->handleRequest($request);
        } catch( \OutOfBoundsException $e ) { // This exception is thrown if the page number is outside of the possible range
            throw $this->createNotFoundException($e->getMessage());
        }

        return $this->render('index.html.twig', [
            'paginator' => $paginator
        ]);
    }
}
```

If you have created a form to go with your filter(s), you can incorporate it into your controller action like this:

```php
<?php

/* ... */

use App\Form\Type\ExampleFilterForm;

/* ... */

    public function index(Request $request, ItemPaginatorFactory $paginatorFactory): Response
    {
        /* ... */

        $filterForm = $this->createForm(ExampleFilterForm::class);

        $filterForm->handleRequest($request); // This will update the current value(s) of any relevant field(s)

        return $this->render('index.html.twig', [
            'paginator' => $paginator,
            'filter_form' => $filterForm
        ]); // ^^^^ Add a parameter for the form to the template context
    }
```

Note: The standard Symfony form methods for checking submission/validation (i.e. `->isSubmitted()` and `->isValid()`) are not needed here because nothing is modified in the database.

Step 4: Add pagination to a template
-----------------------------------------------

Simply include this bundle's Twig template wherever you would like to display the requisite navigation elements. Additionally, the paginator instance itself can be used to iterate over the items for the current page:

```twig
{# index.html.twig ... #}

    <table id="example-items">
        {#~ Iterate over the items for the current page #}
        {%~ for item in paginator.items %}
        <tr class="example-item">
            <td class="name">{{ item.name }}</td>
            {#~ ...additional columns... #}
        </tr>
        {%~ endfor %}
    </table>
    {#~ Output automatically generated pagination navigation (see config section for customization options) #}
    {{~ include('@WHItemPaginator/pagination.html.twig') }}
```

The navigation template (`@WHItemPaginator/pagination.html.twig`) requires the context parameter `paginator`, the value of which must be an instance of `WHSymfony\WHItemPaginatorBundle\Paginator\ItemPaginator`. If such a parameter is named something different within your template's context, make sure to specify it whenever including the navigation template:

```twig
    {{~ include('@WHItemPaginator/pagination.html.twig', {paginator: my_paginator_parameter}) }}
```

This bundle also supports using pure AJAX-based navigation. Although this topic goes beyond the scope of this document, you can toggle usage of `<button>` elements for the navigation (instead of the default `<a>` elements) by setting the `ajax_only` parameter to TRUE:

```twig
    {{~ include('@WHItemPaginator/pagination.html.twig', {ajax_only: true}) }}
```

To output a filter form, include this bundle's form template:

```twig
    {{~ include('@WHItemPaginator/filter_form.html.twig') }}
    <table id="example-items">
        {# ... #}
```

The filter form template (`@WHItemPaginator/filter_form.html.twig`) requires the context parameter `filter_form`, which must be the `Symfony\Component\Form\FormView` instance representing your form (note: Symfony builds the form view for you automatically whenever a form has been added as a context parameter for a template). As with the paginator example above, if such a parameter is named something different within your template's context, make sure to specify it when including the filter form template:

```twig
    {{~ include('@WHItemPaginator/filter_form.html.twig', {filter_form: my_form_parameter}) }}
```

Configuration
=============

Default config
--------------

```yaml
# config/packages/wh_paginator.yaml

wh_paginator:
    page_request_query: 'page'
    items_per_page: 10
    display_options:
        show_item_total: true
        symbol_based_labels: true
        show_bookend_actions: true
        show_placeholders: false
        max_numeric_links: 4
        show_current_page: true
        show_page_count: false
        separator: '/'
    shortcut_keys:
        previous: 'p'
        next: 'n'
        first: 'f'
        last: 'l'
```

Config descriptions
-------------------

`wh_paginator.page_request_query` _(string)_: The request query added to the URL to determine the current pagination page (e.g. `?page=2`, with the default value).

`wh_paginator.items_per_page` _(integer, 1-999)_: The maximum number of items to show on a single pagination page.

`wh_paginator.display_options.show_item_total` _(boolean)_: Whether to display the total item count as a translation string before the actions/links.

`wh_paginator.display_options.symbol_based_labels` _(boolean)_: Whether to use single-character symbols for the labels of the previous/next/first/last actions.

`wh_paginator.display_options.show_bookend_actions` _(boolean)_: Whether to display actions for going to the first/last pages.

`wh_paginator.display_options.show_placeholders` _(boolean)_: Whether to show placeholers for previous/next/first/last actions when they are not applicable (e.g. previous/first on the first page).

`wh_paginator.display_options.max_numeric_links` _(integer, 0-99)_: The maximum amount of page number actions (i.e. not including next/previous/first/last).

`wh_paginator.display_options.show_current_page` _(boolean)_: Whether to display the current page number (placed in numeric order within the numeric links, if there are any).

`wh_paginator.display_options.show_page_count` _(boolean)_: Whether to display the total page count after the current page number (show_current_page must also be enabled).

`wh_paginator.display_options.separator` _(string)_: String to output, if any, between the current page number and the total page count.

`wh_paginator.shortcut_keys` _(previous/next/first/last, string)_: Shortcut key (should be a single character on the keyboard) to use with each of the previous/next/first/last actions.
