<?php

namespace Modules\Geo\Actions;

use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Geo\Models\Country;

class GetCountriesAction
{
    public function execute(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = Country::query();

        // 1. High-Performance Search Inside JSONB Array
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $connectionDriver = DB::connection()->getDriverName();

            $query->where(function ($q) use ($search, $connectionDriver) {
                $q->where('id', 'LIKE', "%{$search}%")
                  ->orWhere('iso_code', 'LIKE', "%{$search}%");

                if ($connectionDriver === 'pgsql') {
                    $q->orWhereRaw("exists (select 1 from jsonb_to_recordset(name_translations) as x(locale text, value text) where x.value ILIKE ?)", ["%{$search}%"]);
                } else {
                    $q->orWhereRaw("JSON_SEARCH(name_translations, 'one', ?, null, '$[*].value') IS NOT NULL", ["%{$search}%"]);
                }
            });
        }

        // 2. High-Performance Native Sorting by Translation Name
        $locale = $filters['locale'] ?? 'en';
        $connectionDriver = DB::connection()->getDriverName();

        if ($connectionDriver === 'pgsql') {
            // Highly optimized subquery sorting using PostgreSQL jsonb_to_recordset
            $query->orderBy(function ($subQuery) use ($locale) {
                $subQuery->selectRaw("x.value")
                    ->fromRaw("jsonb_to_recordset(name_translations) as x(locale text, value text)")
                    ->whereRaw("x.locale = ?", [$locale])
                    ->limit(1);
            }, 'asc');
        } else {
            // MySQL 8+ fallback path to extract and sort by translation value match
            $query->orderByRaw(
                "JSON_UNQUOTE(JSON_EXTRACT(name_translations, JSON_UNQUOTE(JSON_SEARCH(name_translations, 'one', ?, NULL, '$[*].locale')))) asc",
                [$locale]
            );
        }

        return $query->paginate($perPage);
    }
}