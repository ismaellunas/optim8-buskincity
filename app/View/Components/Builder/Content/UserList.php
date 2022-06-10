<?php

namespace App\View\Components\Builder\Content;

use App\Services\CountryService;
use App\Services\PageBuilderService;
use Illuminate\Support\Facades\Crypt;

class UserList extends BaseContent
{
    public $countries = [];
    public $countryOptions = [];
    public $defaultOrderBy = null;
    public $orderByOptions = [];

    public function __construct($entity)
    {
        parent::__construct($entity);

        $this->countries = $this->entity['config']['list']['countries'] ?? [];
        $this->countryOptions = app(CountryService::class)->getUserCountryOptions();
        $this->defaultOrderBy = $this->entity['config']['list']['orderBy'] ?? null;
        $this->orderByOptions = app(PageBuilderService::class)->userListOrderOptions();
    }

    public function url(): string
    {
        return route('api.page-builder.components.user-list');
    }

    public function encryptedExcludedId(): ?string
    {
        $excludedId = $this->entity['config']['list']['excludedId'];

        if ($excludedId) {
            return Crypt::encryptString($excludedId);
        }

        return null;
    }

    public function roles(): ?string
    {
        $roleIds = $this->entity['config']['list']['roles'] ?? [];

        if (!empty($roleIds)) {
            return Crypt::encrypt($roleIds);
        }

        return null;
    }
}
