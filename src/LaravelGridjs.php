<?php

namespace Throwexceptions\LaravelGridjs;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

abstract class LaravelGridjs
{
    private Builder $model;

    private array $columns;

    private int $limit = 10;

    private int $offset = 20;

    private string $route;

    private string $height = '100%';

    private string $width = '100%';

    public bool $searchKeyword = true;

    public ?string $keyword = null;

    public ?string $sortedColumn = null;

    public ?string $sortedDirection = null;

    public bool $fixedHeader = false;

    public ?string $formRequest = null;

    public string $exportName;

    public function make(string $route): string
    {
        $this->config();
        $this->setRoute($route);

        return json_encode([
            'fixedHeader' => $this->isFixedHeader(),
            'columns'     => $this->getColumns(),
            'server'      => $this->getServer($route),
            'pagination'  => $this->getPagination(),
            'mapped'      => $this->getKeyColumns(),
            'search'      => $this->getSearch(),
            'sort'        => true,
            'formTarget'  => $this->getTargetForm(),
            'height'      => $this->height,
            'style'       => [
                'table' => ['width' => $this->width],
            ],
        ]);
    }

    public function getSearch()
    {
        return $this->searchStatus() ? ['server' => ['url' => '',],] : ['server' => false];
    }

    public function getPagination()
    {
        return [
            'enabled' => true,
            'limit'   => $this->getLimit(),
            'server'  => [
                'url' => $this->getRoute(),
            ],
        ];
    }

    public function getServer($route)
    {
        return [
            'url'     => $route.'?',
            'method'  => 'POST',
            "headers" => [
                'Content-Type' => 'application/json',
                'X-CSRF-TOKEN' => csrf_token(),
            ],
            "body"    => ['limit' => $this->getLimit(), 'offset' => $this->getOffset()],
            'total'   => $this->getTotal(),
        ];
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

        $model = $this->buildQuery($this->columns());

        return [
            'data'          => $this->getResults($model),
            'searchKeyword' => $this->searchStatus(),
            'route'         => $this->getRoute(),
            'limit'         => $this->getLimit(),
            'offset'        => $this->getOffset(),
            'total'         => $this->getTotal(),
        ];
    }

    public function setQuery(Builder $model, $limit = 10): static
    {
        $this->model = $model;
        $this->limit = $limit;

        return $this;
    }

    public function buildQuery($columns)
    {
         return $this->model
            ->when($this->sortedColumn, function ($q) {
                $q->orderBy($this->sortedColumn, $this->sortedDirection);
            })
            ->when($this->keyword, function ($q) use ($columns) {
                foreach ($columns as $key => $value) {
                    if(is_array($value) && isset($value['searchable']) && !$value['searchable']) {
                        continue;
                    }
                    $q->orWhere(DB::raw("lower($key)"), "LIKE", "%".strtolower($this->keyword)."%");
                }
            });
    }

    public function setQueryToSession($name)
    {
        $this->exportName = $name;

        return $this;
    }

    public function getResults($model): array
    {
        $this->querySessionInitialize($model);

        $result = [];
        foreach ($model->skip($this->offset)->take($this->limit)->cursor() as $values) {
            $row = $values->toArray();
            foreach ($this->columns as $key => $item) {
                if ($item instanceof Closure) {
                    if ($this->columns[$key]($row) instanceof View) {
                        $row[$key] = $this->columns[$key]($row)->render();
                    } else {
                        $row[$key] = $this->columns[$key]($row);
                    }
                }
            }
            $result[] = $row;
        }

        return $result;
    }

    public function querySessionInitialize($model)
    {
        $sql = vsprintf(str_replace('?', '%s', $model->toSql()), $model->getBindings());

        !$this->exportName ?: session()->put($this->exportName, $sql);
    }

    public function columns(): array
    {
        return [];
    }

    public function getColumns(): array
    {
        $this->columns = $this->columns();

        $final = [];
        foreach ($this->columns as $value) {
            $final[] = $value;
        }

        return $final;
    }

    public function getKeyColumns(): array
    {
        $this->columns = $this->columns();

        $final = [];
        foreach ($this->columns as $key => $value) {
            $final[] = $key;
        }

        return $final;
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

    public function setSearchStatus(bool $status): static
    {
        $this->searchKeyword = $status;

        return $this;
    }

    public function enableFixedHeader(string $height): static
    {
        $this->fixedHeader = true;
        $this->height      = $height;

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

    public function setTargetForm(string $id): static
    {
        $this->formRequest = $id;

        return $this;
    }

    public function getTargetForm()
    {
        return $this->formRequest;
    }
}
