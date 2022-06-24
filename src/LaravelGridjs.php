<?php

namespace Throwexceptions\LaravelGridjs;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use JetBrains\PhpStorm\Pure;

abstract class LaravelGridjs
{
    private Builder $model;

    private array $columns;

    private int $limit;

    private int $offset;

    private string $route;

    public bool $searchKeyword = true;

    public ?string $keyword = null;

    public ?string $sortedColumn = null;

    public ?string $sortedDirection = null;

    public bool $fixedHeader = false;

    public function make(string $route): static
    {
        $this->config();
        $this->route   = $route;
        $this->columns = $this->columns();

        return $this;
    }

    public function fetch(Request $request): array
    {
        $this->columns = $this->columns();

        $this->config();
        $this->setRoute($request->fullUrl());
        $this->setLimit($request->get('limit'));
        $this->setOffset($request->get('offset'));

        if ($request->has('order')) {
            $this->setSorter($request->get('order'), $request->get('dir'));
        }

        if ($request->has('search')) {
            $this->keyword = $request->get('search');
        }

        return [
            'searchKeyword' => $this->searchStatus(),
            'total'         => $this->getTotal(),
            'route'         => $this->getRoute(),
            'limit'         => $this->getLimit(),
            'offset'        => $this->getOffset(),
            'data'          => $this->getBuilderQuery(),
        ];
    }

    public function setQuery(Builder $model, $limit = 10): static
    {
        $this->model = $model;
        $this->limit = $limit;

        return $this;
    }

    public function getBuilderQuery(): array
    {
        $result = [];
        $model  = $this->model
            ->when($this->sortedColumn, function ($q) {
                $q->orderBy($this->sortedColumn, $this->sortedDirection);
            })
            ->when($this->keyword, function ($q) {
                foreach ($this->columns as $key => $value) {
                    $q->orWhere($key, "LIKE", "%$this->keyword%");
                }
            })
            ->skip($this->offset)
            ->take($this->limit);

        foreach ($model->cursor() as $values) {
            $row = $values->toArray();
            foreach ($this->columns as $key => $values) {
                if (! isset($row[$key])) {
                    $row[$key] = $this->columns[$key]($row);
                }
            }

            $result[] = $row;
        }

        return $result;
    }

    public function columns(): array
    {
        return [];
    }

    #[Pure] public function getColumns(): array
    {
        return $this->columns;
    }

    public function getTotal(): int
    {
        return $this->model->count();
    }

    public function getRoute(): string
    {
        return $this->route;
    }

    public function setRoute($route): static
    {
        $this->route = $route;

        return $this;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function setLimit($limit): static
    {
        $this->limit = $limit;

        return $this;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function setOffset($offset): static
    {
        $this->offset = $offset;

        return $this;
    }

    public function editColumn($columnName, callable $callback): static
    {
        $this->columns[$columnName] = $callback;

        return $this;
    }

    public function setSorter($column, $direction)
    {
        $this->sortedColumn    = $column;
        $this->sortedDirection = $direction;
    }

    public function searchStatus(): bool
    {
        return $this->searchKeyword;
    }

    public function enableFixedHeader(): static
    {
        $this->fixedHeader = true;

        return $this;
    }

    public function isFixedHeader(): bool
    {
        return $this->fixedHeader;
    }

    public function getHeight()
    {
        return $this->getHeight();
    }
}
