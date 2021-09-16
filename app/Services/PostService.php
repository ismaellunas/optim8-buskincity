<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Post;
use App\Services\TranslationService;
use Illuminate\Database\Eloquent\Builder;

class PostService
{
    public function getRecords(
        string $term = null,
        array $scopeNames = [],
        int $recordsPerPage = 15
    ) {
        $query = Post::orderBy('id', 'DESC')
            ->with([
                'coverImage' => function ($query) {
                    $query->select([
                        'extension',
                        'file_name',
                        'file_url',
                        'id',
                        'version',
                    ]);
                },
                'categories' => function ($query) {
                    $tableName = Category::getTableName();
                    $query->select([$tableName.'.id']);
                    $query->with([
                        'translations' => function ($query) {
                            $query
                                ->select('id', 'name', 'category_id', 'locale')
                                ->where('locale', TranslationService::getDefaultLocale());
                        },
                    ]);
                },
            ])
            ->when($term, function (Builder $query, $term) {
                $query->where('title', 'ILIKE', '%'.$term.'%');
                $query->orWhere('content', 'ILIKE', '%'.$term.'%');
                $query->orWhere('excerpt', 'ILIKE', '%'.$term.'%');
                $query->orWhere('slug', 'ILIKE', '%'.$term.'%');
            });

        foreach ($scopeNames as $scopeName) {
            $query->{$scopeName}();
        }

        $records = $query->paginate($recordsPerPage);

        $this->transformRecords($records);

        return $records;
    }

    public function transformRecords($records)
    {
        $records->getCollection()->transform(function ($record) {
            $record->thumbnail_url = (
                $record->coverImage
                ? $record->coverImage->thumbnailUrl
                : null
            );

            return $record;
        });
    }

    public function getCategoryOptions(): array
    {
        return Category::get()->map(function ($category) {
            return [
                'id' => $category->id,
                'value' => $category->name,
            ];
        })->all();
    }
}
