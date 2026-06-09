<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Country;
use App\Services\CountryService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CityController extends Controller
{
    public function index(Request $request)
    {
        return response()->json($this->searchResults($request, 50));
    }

    public function search(Request $request)
    {
        $search = trim((string) $request->input('search', ''));

        if (strlen($search) < 2) {
            return response()->json([]);
        }

        return response()->json($this->searchResults($request, 100));
    }

    /**
     * @return \Illuminate\Support\Collection<int, City>
     */
    private function searchResults(Request $request, int $limit)
    {
        $search = trim((string) $request->input('search', ''));

        return $this->searchQuery($request)
            ->when(
                $search !== '',
                fn (Builder $query) => $query
                    ->orderByRaw('CASE WHEN name ILIKE ? THEN 0 ELSE 1 END', ["{$search}%"])
                    ->orderBy('name')
            )
            ->limit($limit)
            ->get();
    }

    private function searchQuery(Request $request): Builder
    {
        $search = trim((string) $request->input('search', ''));
        $query = City::query();
        $countryCodes = $this->resolveCountryCodesForSearch($search);

        if ($search !== '') {
            $query->where(function (Builder $q) use ($search, $countryCodes) {
                $this->applyNameSearch($q, $search);

                $q->orWhere('country_code', 'ilike', $search);

                if ($countryCodes !== []) {
                    $q->orWhereIn('country_code', $countryCodes);
                }
            });
        }

        if ($request->filled('country_code')) {
            $alpha2 = app(CountryService::class)->toAlpha2($request->input('country_code'));

            if ($alpha2) {
                $query->where('country_code', $alpha2);
            }
        }

        return $query;
    }

    /**
     * @return array<int, string>
     */
    private function resolveCountryCodesForSearch(string $search): array
    {
        $normalized = Str::lower(trim($search));

        if (strlen($normalized) < 2) {
            return [];
        }

        return Country::query()
            ->where(function (Builder $q) use ($normalized) {
                $q->whereRaw('LOWER(display_name) LIKE ?', ["%{$normalized}%"])
                    ->orWhereRaw('LOWER(alpha2) = ?', [$normalized])
                    ->orWhereRaw('LOWER(alpha3) LIKE ?', ["%{$normalized}%"]);
            })
            ->pluck('alpha2')
            ->all();
    }

    private function applyNameSearch(Builder $query, string $search): void
    {
        $ascii = Str::ascii($search);
        $normalized = Str::lower($ascii);

        $query->where(function (Builder $q) use ($search, $ascii, $normalized) {
            $q->where('name', 'ilike', "%{$search}%");

            if ($ascii !== $search) {
                $q->orWhere('name', 'ilike', "%{$ascii}%");
            }

            $q->orWhereRaw(
                $this->normalizedNameSql().' LIKE ?',
                ["%{$normalized}%"]
            );
        });
    }

    private function normalizedNameSql(): string
    {
        $replacements = [
            ['Å', 'A'], ['Ä', 'A'], ['Ö', 'O'], ['Ü', 'U'],
            ['É', 'E'], ['È', 'E'], ['Ê', 'E'], ['Ë', 'E'],
            ['Á', 'A'], ['À', 'A'], ['Â', 'A'], ['Ã', 'A'],
            ['Í', 'I'], ['Ì', 'I'], ['Î', 'I'], ['Ï', 'I'],
            ['Ó', 'O'], ['Ò', 'O'], ['Ô', 'O'], ['Õ', 'O'],
            ['Ú', 'U'], ['Ù', 'U'], ['Û', 'U'],
            ['Ç', 'C'], ['Ñ', 'N'],
            ['å', 'a'], ['ä', 'a'], ['ö', 'o'], ['ü', 'u'],
            ['é', 'e'], ['è', 'e'], ['ê', 'e'], ['ë', 'e'],
            ['á', 'a'], ['à', 'a'], ['â', 'a'], ['ã', 'a'],
            ['í', 'i'], ['ì', 'i'], ['î', 'i'], ['ï', 'i'],
            ['ó', 'o'], ['ò', 'o'], ['ô', 'o'], ['õ', 'o'],
            ['ú', 'u'], ['ù', 'u'], ['û', 'u'],
            ['ç', 'c'], ['ñ', 'n'],
        ];

        $expression = 'name';

        foreach ($replacements as [$from, $to]) {
            $expression = "REPLACE({$expression}, '{$from}', '{$to}')";
        }

        return "LOWER({$expression})";
    }
}
