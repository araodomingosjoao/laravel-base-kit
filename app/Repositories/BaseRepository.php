<?php

namespace App\Repositories;

use App\Events\AfterCreate;
use App\Events\AfterUpdate;
use App\Events\BeforeCreate;
use App\Events\BeforeUpdate;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class BaseRepository
{
    protected $model;
    protected $relationships = [];
    protected $filterable = [];
    protected $searchable = [];

    /**
     * Constructor to initialize the model.
     *
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Creates a new record.
     *
     * @param array $data
     * @return Model
     */
    public function create(array $data): Model
    {
        event(new BeforeCreate($data, $this->model));
        $model = $this->model->create($data);
        event(new AfterCreate($model));
        return $model;
    }

    /**
     * Find a record by ID.
     *
     * @param int $id
     * @return Model|null
     */
    public function find(int $id): ?Model
    {
        return $this->model->with($this->relationships)->find($id);
    }

    /**
     * Updates a record by ID.
     *
     * @param int $id
     * @param array $data
     * @return Model|bool
     */
    public function update(int $id, array $data): Model|bool
    {
        $record = $this->model->find($id);
        if ($record) {
            event(new BeforeUpdate($data, $record));
            $updated = $record->update($data);
            if ($updated) {
                event(new AfterUpdate($record));
                return $record;
            }
        }
        return false;
    }

    /**
     * Deletes a record by ID.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $record = $this->model->find($id);
        if ($record) {
            return $record->delete();
        }
        return false;
    }

    /**
     * Page with custom filters and sorting.
     *
     * @param array $filters
     * @param string $search
     * @param int $perPage
     * @param string $sortColumn
     * @param string $sortDirection
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function paginateWithFiltersAndSort($filters = [], $search = '', $perPage = 15, $sortColumn = 'id', $sortDirection = 'asc')
    {
        $query = $this->model->query();

        foreach ($filters as $key => $value) {
            if ($value) {
                if ($key === 'start_date' || $key === 'end_date') {
                    $startDate = $filters['start_date'] ?? null;
                    $endDate = $filters['end_date'] ?? null;
                    if ($startDate && $endDate) {
                        $query->whereBetween('created_at', [Carbon::parse($startDate), Carbon::parse($endDate)]);
                    } elseif ($startDate) {
                        $query->where('created_at', '>=', Carbon::parse($startDate));
                    } elseif ($endDate) {
                        $query->where('created_at', '<=', Carbon::parse($endDate));
                    }
                } else {
                    $query->where($key, $value);
                }
            }
        }

        if ($search) {
            $query->where(function (Builder $q) use ($search) {
                foreach ($this->searchable as $column) {
                    $q->orWhere($column, 'LIKE', "%$search%");
                }
            });
        }

        $paginator = $query->with($this->relationships)->orderBy($sortColumn, $sortDirection)->paginate($perPage);

        return [
            'data' => $paginator->items(),
            'current_page' => $paginator->currentPage(),
            'total' => $paginator->total(),
            'per_page' => $paginator->perPage(),
            'last_page' => $paginator->lastPage(),
            'next_page_url' => $paginator->nextPageUrl(),
            'prev_page_url' => $paginator->previousPageUrl(),
        ];
    }
}
