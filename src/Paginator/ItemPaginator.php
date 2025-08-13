<?php

namespace WHSymfony\WHItemPaginatorBundle\Paginator;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

use Symfony\Component\HttpFoundation\Request;

use WHSymfony\WHItemPaginatorBundle\Config\PaginatorConfig;
use WHSymfony\WHItemPaginatorBundle\Exception\IncompatibleEntityManagerException;
use WHSymfony\WHItemPaginatorBundle\Exception\InvalidArgumentException;
use WHSymfony\WHItemPaginatorBundle\Exception\LogicException;
use WHSymfony\WHItemPaginatorBundle\Exception\OutOfPaginationRangeException;
use WHSymfony\WHItemPaginatorBundle\Filter\ExcludeFromActiveFiltersCount;
use WHSymfony\WHItemPaginatorBundle\Filter\{HasDefaultValue,HasRequestQuery};
use WHSymfony\WHItemPaginatorBundle\Filter\ItemFilter;
use WHSymfony\WHItemPaginatorBundle\Util\StringUtil;

/**
 * @author Will Herzog <willherzog@gmail.com>
 */
abstract class ItemPaginator
{
	public readonly string $entityClass;
	public readonly string $entityAlias;
	private readonly string $countProperty;

	protected readonly EntityManagerInterface $entityManager;
	protected readonly QueryBuilder $queryBuilder;

	// These properties are not available until ->handleRequest() has been called
	public readonly int $itemTotal;
	public readonly int $firstPage;
	public readonly int $lastPage;
	public readonly int $currentPage;

	private ?int $appliedFilters = null;
	private bool $calculatedItemTotalAndPageCount = false;

	private array $selectStatements = [];
	private array $orderByProps = [];
	/** @var ItemFilter[] */
	private array $filters = [];
	private ?iterable $items = null;

	final public function __construct(
		public readonly PaginatorConfig $config,
		ManagerRegistry $managerRegistry
	) {
		$this->entityClass = $this->getEntityClass();
		$this->entityAlias = $this->getEntityAlias();
		$this->countProperty = $this->getCountProperty();

		$this->selectStatements = [$this->entityAlias]; // i.e. the default select statement

		if( !class_exists($this->entityClass) ) {
			throw new LogicException(sprintf('Entity class "%s" does not exist.', $this->entityClass));
		}

		$objectManager = $managerRegistry->getManagerForClass($this->entityClass);

		// Need to require EntityManagerInterface specifically in order to use Doctrine ORM's query building features
		if( $objectManager instanceof EntityManagerInterface ) {
			$this->entityManager = $objectManager;
		} else {
			throw new IncompatibleEntityManagerException($this->entityClass);
		}

		$this->queryBuilder = $this->entityManager->getRepository($this->entityClass)->createQueryBuilder($this->entityAlias);

		$this->initialize();
	}

	/**
	 * Get FQCN of main entity for Doctrine query building.
	 */
	abstract protected function getEntityClass(): string;

	/**
	 * Get alias of main entity for Doctrine query building.
	 */
	abstract protected function getEntityAlias(): string;

	/**
	 * Get property on main entity to use for count queries.
	 * Override this method if needed to return a property other than "id".
	 */
	protected function getCountProperty(): string
	{
		return 'id';
	}

	/**
	 * Called once in constructor; use for one-time setup logic.
	 */
	protected function initialize(): void
	{
	}

	/**
	 * Called from `->handleRequest()` after filters have been applied.
	 */
	protected function finalize(): void
	{
	}

	/**
	 * Get translation string to use for the item total.
	 * Override this method to return one other than this bundle's default;
	 * the translation string should support pluralization using a "count" parameter.
	 */
	public function getItemTotalLabel(): ?string
	{
		return null;
	}

	/**
	 * Add an item filter definition.
	 */
	final public function addFilter(ItemFilter $filter): static
	{
		if( $this->appliedFilters !== null ) {
			throw new LogicException('Filters have already been applied: filters can no longer be added.');
		}

		if( !$filter->supports($this) ) {
			throw new InvalidArgumentException(sprintf('Item filter of class "%s" cannot be used with item paginator of class "%s".', $filter::class, $this::class));
		}

		$this->filters[] = $filter;

		return $this;
	}

	/**
	 * Get paginator-specific query builder.
	 */
	final public function getQueryBuilder(): QueryBuilder
	{
		return $this->queryBuilder;
	}

	/**
	 * Get reference for an associated entity.
	 */
	final public function getEntityReference(string $entityClass, int $entityId): object
	{
		return $this->entityManager->getReference($entityClass, $entityId);
	}

	/**
	 * Add to item select statements; if none are added, the main item entity is hydrated as a whole.
	 */
	final public function addSelect(string $selectStatement): static
	{
		$this->selectStatements[] = $selectStatement;

		return $this;
	}

	/**
	 * Set an item select statement; if none have been set, the main item entity is hydrated as a whole.
	 */
	final public function setSelect(string $selectStatement): static
	{
		$this->selectStatements = [$selectStatement];

		return $this;
	}

	private function normalizePropertyName(string $propName): string
	{
		if( !strpos($propName, '.') ) {
			$propName = sprintf('%s.%s', $this->entityAlias, $propName);
		}

		return $propName;
	}

	private function getOrderByDirection(bool $ascending): string
	{
		return $ascending ? 'ASC' : 'DESC';
	}

	/**
	 * Add to item order-by statements.
	 *
	 * @param string $propName Name of item property by which items should be sorted
	 * @param bool $ascending Direction is ASC if TRUE, DESC if FALSE; defaults to TRUE
	 */
	final public function addOrderBy(string $propName, bool $ascending = true): static
	{
		$propName = $this->normalizePropertyName($propName);
		$direction = $this->getOrderByDirection($ascending);

		$this->orderByProps[$propName] = $direction;

		return $this;
	}

	/**
	 * Set an item order-by statement.
	 *
	 * @param string $propName Name of item property by which items should be sorted
	 * @param bool $ascending Direction is ASC if TRUE, DESC if FALSE; defaults to TRUE
	 */
	final public function setOrderBy(string $propName, bool $ascending = true): static
	{
		$propName = $this->normalizePropertyName($propName);
		$direction = $this->getOrderByDirection($ascending);

		$this->orderByProps = [$propName => $direction];

		return $this;
	}

	/**
	 * @internal Retrieve the request data for applicable filters.
	 */
	final public function collectFilterData(): array
	{
		$filterData = [];
		$i = 0;

		foreach( $this->filters as $filter ) {
			if( $filter instanceof HasRequestQuery ) {
				$filterName = StringUtil::fqcnToFilterName($filter::class) ?? 'filter_' . $i;

				$filterData[$filterName]['query'] = $filter->getRequestQueryName();

				if( $filter instanceof HasDefaultValue ) {
					$filterData[$filterName]['default'] = $filter->getDefaultValue();
				}

				$i++;
			}
		}

		return $filterData;
	}

	private function finalizeQueryBuilder(): void
	{
		$this->finalize();

		foreach( $this->selectStatements as $selectStatement ) {
			$this->queryBuilder->addSelect($selectStatement);
		}

		foreach( $this->orderByProps as $propName => $direction ) {
			$this->queryBuilder->addOrderBy($propName, $direction);
		}

		$start = $this->config->itemsPerPage * ($this->currentPage - 1);

		$this->queryBuilder
			->setFirstResult($start)
			->setMaxResults($this->config->itemsPerPage)
		;
	}

	/**
	 * @uses Doctrine\ORM\Tools\Pagination\Paginator If $usingDoctrinePaginator is TRUE (see † note below)
	 *
	 * @param bool $usingDoctrinePaginator Whether to use Doctrine's `Paginator` class to perform the actual database queries†
	 *
	 * † If the query includes one-to-many or many-to-many fetch-joins, the Doctrine `Paginator` will ensure you always end up
	 *   with the expected results for a given page.
	 *
	 * @throws OutOfPaginationRangeException If the requested page number is outside of the possible range
	 */
	final public function handleRequest(Request $request, bool $usingDoctrinePaginator = false): void
	{
		if( $this->appliedFilters === null ) {
			$this->appliedFilters = 0;

			foreach( $this->filters as $filter ) {
				if( $filter->isApplicable($request) ) {
					$filter->apply($this);

					if( !($filter instanceof ExcludeFromActiveFiltersCount) ) {
						$this->appliedFilters++;
					}
				}
			}
		}

		$this->firstPage = 1;

		if( $request->query->has($this->config->pageRequestQuery) ) {
			$this->currentPage = $request->query->getInt($this->config->pageRequestQuery);
		} else {
			$this->currentPage = $this->firstPage;
		}

		if( !$this->calculatedItemTotalAndPageCount ) {
			$this->finalizeQueryBuilder();
		}

		if( $usingDoctrinePaginator ) {
			$doctrinePaginator = new Paginator($this->queryBuilder);

			$this->itemTotal = count($doctrinePaginator);
			$this->items = $doctrinePaginator->getIterator();
		} else {
			$countQB = clone $this->queryBuilder;

			$countQB->select(sprintf('COUNT(%s.%s)', $this->entityAlias, $this->countProperty));

			$this->itemTotal = $countQB->getQuery()->getSingleScalarResult();
			$this->items = $this->queryBuilder->getQuery()->getResult();
		}

		$this->lastPage = $this->itemTotal > $this->config->itemsPerPage ? (int) ceil($this->itemTotal / $this->config->itemsPerPage) : $this->firstPage;

		if( $this->currentPage < $this->firstPage || $this->currentPage > $this->lastPage ) {
			throw new OutOfPaginationRangeException($this->currentPage, $this->firstPage, $this->lastPage);
		}

		$this->calculatedItemTotalAndPageCount = true;
	}

	final public function hasItems(): bool
	{
		return $this->items !== null && (!is_countable($this->items) || count($this->items) > 0);
	}

	final public function getItems(): iterable
	{
		return $this->items;
	}

	/**
	 * Retrieve the number of active filters for the current request (this will always be `0` prior to calling `->handleRequest()`).
	 *
	 * Note that the count could be misleading if any of the filters used always return `true` from their `->isApplicable()` methods
	 * (such filters can implement {@link WHSymfony\WHItemPaginatorBundle\Filter\ExcludeFromActiveFiltersCount} to prevent this).
	 */
	final public function getActiveFiltersCount(): int
	{
		return $this->appliedFilters ?? 0;
	}

	/**
	 * Calculate and retrieve the range of numeric actions/links to be displayed.
	 *
	 * @return int[]
	 */
	final public function getNumericActions(): array
	{
		if( !$this->calculatedItemTotalAndPageCount ) {
			throw new LogicException('This obect\'s ->handleRequest() method must be called first before calling this method.');
		}

		$maxNumericActions = $this->config->displayOption['max_numeric_links'];

		if( $maxNumericActions === 0 ) {
			return [];
		}

		$actionsLimit = min($maxNumericActions, ($this->lastPage - 1));

		$beforeCurrent = (int) floor($actionsLimit / 2);
		$afterCurrent = (int) ceil($actionsLimit / 2);

		$firstAction = $this->currentPage - $beforeCurrent;
		$lastAction = $this->currentPage + $afterCurrent;

		if( $firstAction < $this->firstPage ) {
			$firstAction = $this->firstPage;
			$lastAction = $this->firstPage + $actionsLimit;
		} elseif( $lastAction > $this->lastPage ) {
			$firstAction = $this->lastPage - $actionsLimit;
			$lastAction = $this->lastPage;
		}

		return range($firstAction, $lastAction);
	}
}
