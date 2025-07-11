<?php

namespace App\Http\Requests;

use App\Models\Translation;
use App\Services\TranslationManagerService;
use Illuminate\Validation\Rule;

class TranslationUpdateRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $locale = config('translatable.locales');
        $groups = app(TranslationManagerService::class)->getGroups($this->get('module'));

        $uniqueRule = $this->getUniqueRule();

        return [
            'translations.*.locale' => [
                'required',
                'max: 15',
                Rule::in($locale)
            ],
            'translations.*.group' => [
                'nullable',
                'max: 127',
                Rule::in($groups)
            ],
            'translations.*.key' => array_filter([
                'required',
                'distinct',
                'max: 1024',
                $uniqueRule
            ]),
            'translations.*.value' => [
                'nullable',
                'max: 65535',
            ],
        ];
    }

    protected function customAttributes(): array
    {
        $attr = [];
        $columns = [
            'locale',
            'group',
            'key',
            'value',
        ];

        foreach ($columns as $column) {
            foreach ($this['translations'] as $index => $value) {
                $attr["translations.".$index.".".$column] = ucwords(str_replace('_', ' ', $column));
            }
        }

        return $attr;
    }

    private function getUniqueRule()
    {
        if (!$this->hasEmptyGroup()) {
            return null;
        }

        $ignoreIds = $this->input('translations.*.id');
        $groups = $this->input('translations.*.group');
        $keys = $this->input('translations.*.key');
        $module = $this->route('module');

        $ignoreIds = $this->getIgnoreIds($ignoreIds);

        return Rule::unique('translations')
            ->where(function ($query) use ($ignoreIds, $groups, $keys) {
                return $query->whereNotIn('id', $ignoreIds)
                    ->where('group', $groups)
                    ->where('key', $keys);
            })
            ->when($module, function ($query, $module) {
                $query->where('module', $module);
            }, function ($query) {
                $query->whereNull('module');
            });
    }

    private function hasEmptyGroup(): bool
    {
        foreach ($this->translations as $translation) {
            if ($translation['group'] == null) {
                return true;
            }
        }

        return false;
    }

    private function getIgnoreIds($ids)
    {
        $ignoreIds = [];
        $translations = Translation::whereIn('id', $ids)->get();

        foreach ($translations as $translation) {
            $resultIds = Translation::where('group', $translation->group)
                ->where('key', $translation->key)
                ->get('id')
                ->pluck('id')
                ->toArray();

            $ignoreIds = array_merge($ignoreIds, $resultIds);
        }

        return $ignoreIds;
    }
}
