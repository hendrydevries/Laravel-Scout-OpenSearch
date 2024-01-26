<?php

namespace Hendrydevries\LaravelScoutOpenSearch\Paginator;

use Illuminate\Pagination\Cursor;
use Illuminate\Pagination\CursorPaginator;

final class ScrollPaginator extends CursorPaginator
{
    private ?Cursor $nextCursor = null;
    private ?Cursor $previousCursor = null;
    private int $total;
    private array $aggregations;

    /**
     * Create a new paginator instance.
     *
     * @param  mixed  $items
     * @param  int  $perPage
     * @param array $response
     * @param \Illuminate\Pagination\Cursor|null  $cursor
     * @param  array  $options  (path, query, fragment, pageName)
     * @return void
     */
    public function __construct(
        $items,
        $perPage,
        array $response,
        $cursor = null,
        $options = []
    ) {
        parent::__construct($items, $perPage, $cursor, $options);

        $this->setTotal($response['hits']['total']['value'] ?? 0);

        $this->initCursors(
            array_slice($response['hits']['hits'], 0, $perPage)
        );
    }

    private function initCursors(array $rawItems): void
    {
        if (! $this->onLastPage() && 
            count($rawItems) > 0
        ) {
            $nextItem = $this->pointsToPrevoiusItems()
                ? array_shift($rawItems)
                : array_pop($rawItems);
            
            $this->nextCursor = new Cursor(
                array_combine($this->parameters, $nextItem['sort'])
            );
        }

        if (! $this->onFirstPage() && 
            count($rawItems) > 0
        ) {
            $previousItem = $this->pointsToPrevoiusItems()
                ? array_pop($rawItems)
                : array_shift($rawItems);

            $this->previousCursor = new Cursor(
                array_combine($this->parameters, $previousItem['sort']), 
                false
            );
        }
    }

    private function pointsToPrevoiusItems(): bool
    {
        if (! $this->cursor) {
            return false;
        }

        return $this->cursor->pointsToPreviousItems();
    }

    /**
     * @inheritDoc
     */
    public function previousCursor()
    {
        return $this->previousCursor;
    }

    /**
     * @inheritDoc
     */
    public function nextCursor()
    {
        return $this->nextCursor;
    }

    public function setTotal(int $total): int
    {
        $this->total = $total;
        return $this->total;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function setAggregations(array $aggregations): array
    {
        $this->aggregations = $aggregations;
        return $this->aggregations;
    }

    public function addAggregation(array $aggregation): array
    {
        $this->aggregations[] = $aggregation;
        return $this->aggregations;
    }

    public function getAggregations(): array
    {
        return $this->aggregations;
    }

    public function getAggregation(string $name): ?array
    {
        return $this->aggregations[$name] ?? null;
    }
}
