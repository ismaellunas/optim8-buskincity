<?php

namespace App\View\Components\Builder\Content;

use App\Entities\Caches\RecordCache;
use App\Services\PageBuilderService;
use Illuminate\Support\Facades\Crypt;

class UserList extends BaseContent
{
    public $defaultCountries = [];
    public $countryOptions = [];
    public $defaultOrderBy = null;
    public $orderByOptions = [];
    public $typeOptions = [];
    public $defaultTypes = [];

    public function __construct($entity)
    {
        parent::__construct($entity);

        $this->defaultCountries = $this->getConfig()['list']['countries'] ?? [];
        $this->defaultOrderBy = $this->getConfig()['list']['orderBy'] ?? null;
        $this->orderByOptions = app(PageBuilderService::class)->userListOrderOptions();
        $this->defaultTypes = $this->getConfig()['list']['types'] ?? [];

        app(RecordCache::class)->flush();
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
}
