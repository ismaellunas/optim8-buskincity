<?php

namespace App\View\Components\Builder\Content;

use App\Models\User;
use App\Services\CountryService;
use App\Services\GlobalOptionService;
use App\Services\PageBuilderService;
use Illuminate\Support\Facades\Crypt;

class UserList extends BaseContent
{
    public $countries = [];
    public $countryOptions = [];
    public $defaultOrderBy = null;
    public $typeOptions = [];
    public $orderByOptions = [];

    public function __construct($entity)
    {
        parent::__construct($entity);

        $this->countries = $this->getConfig()['list']['countries'] ?? [];
        $this->countryOptions = app(CountryService::class)->getUserCountryOptions();
        $this->defaultOrderBy = $this->getConfig()['list']['orderBy'] ?? null;
        $this->typeOptions = app(GlobalOptionService::class)->getUserDisciplineOptions();
        $this->orderByOptions = app(PageBuilderService::class)->userListOrderOptions();

        $this->filterOptions();
    }

    public function url(): string
    {
        return route('api.page-builder.components.user-list');
    }

    public function encryptedExcludedId(): ?string
    {
        $excludedId = $this->getConfig()['list']['excludedId'];

        if ($excludedId) {
            return Crypt::encryptString($excludedId);
        }

        return null;
    }

    public function roles(): ?string
    {
        $roleIds = $this->getConfig()['list']['roles'] ?? [];

        if (!empty($roleIds)) {
            return Crypt::encrypt($roleIds);
        }

        return null;
    }

    private function filterOptions(): void
    {
        $availableCountries = [];
        $availableType = [];
        $metaKeys = [
            'discipline',
            'country'
        ];

        $excludedIds = $this->getExcludedIds();
        $roleIds = $this->getConfig()['list']['roles'] ?? [];

        $users = User::select(['id'])
            ->with(['metas'])
            ->available()
            ->inRoles($roleIds)
            ->when($excludedIds, function ($q, $excludedIds) {
                $q->whereNotIn('id', $excludedIds);
            })
            ->get();

        foreach ($users as $user) {
            $metas = $user->getMetas($metaKeys);

            if (isset($metas['country'])) {
                $availableCountries[] = $metas['country'];
            }

            if (isset($metas['discipline'])) {
                $availableType[] = $metas['discipline'];
            }
        }

        $this->countryOptions = $this->countryOptions
            ->filter(function ($country) use ($availableCountries) {
                return in_array($country['id'], $availableCountries);
            })
            ->values();

        $this->typeOptions = $this->typeOptions
            ->filter(function ($type) use ($availableType) {
                return in_array($type['id'], $availableType);
            })
            ->values();
    }

    private function getExcludedIds(): array
    {
        $excludedId = preg_replace('/[^0-9,]+/', '', $this->getConfig()['list']['excludedId']);

        return array_filter(explode(',', $excludedId));
    }
}
