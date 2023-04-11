<?php

namespace App\Providers;

use App\Entities\Telescope\DatabaseEntriesRepository;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Laravel\Telescope\IncomingEntry;
use Laravel\Telescope\Telescope;
use Laravel\Telescope\TelescopeApplicationServiceProvider;
use Laravel\Telescope\Contracts\EntriesRepository;

class TelescopeServiceProvider extends TelescopeApplicationServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Telescope::night();

        $this->app->singleton(
            EntriesRepository::class, DatabaseEntriesRepository::class
        );

        $this->app->when(DatabaseEntriesRepository::class)
            ->needs('$connection')
            ->give(config('telescope.storage.database.connection'));

        $this->app->when(DatabaseEntriesRepository::class)
            ->needs('$chunkSize')
            ->give(config('telescope.storage.database.chunk'));

        $this->hideSensitiveRequestDetails();

        Telescope::filter(function (IncomingEntry $entry) {
            if ($this->app->environment('local')) {
                return true;
            }

            return $entry->isReportableException() ||
                   $entry->isFailedRequest() ||
                   $entry->isFailedJob() ||
                   $this->isFilteredRequest($entry);
        });
    }

    private function isFilteredRequest(IncomingEntry $entry): bool
    {
        $isRequest = $entry->type == 'request';

        if (config('telescope.all_requests_enabled')) {
            return $isRequest;
        }

        return (
            $isRequest &&
            !Str::endsWith($entry->content['uri'], '.js.map') &&
            !Str::startsWith($entry->content['uri'], '/admin/system-log') &&
            (
                $this->isBackendRequest($entry) ||
                $this->isAuthenticationRequest($entry) ||
                $this->isSuccessOauthRequest($entry)
            )
        );
    }

    private function isBackendRequest(IncomingEntry $entry): bool
    {
        return in_array('auth:sanctum', $entry->content['middleware'] ?? []) &&
            Str::startsWith($entry->content['uri'], [
                '/admin/',
                '/logout',
            ]);
    }

    private function isAuthenticationRequest(IncomingEntry $entry): bool
    {
        return $entry->content['method'] == "POST"
            && Str::startsWith($entry->content['uri'], [
                '/register',
                '/login',
            ]);
    }

    private function isSuccessOauthRequest(IncomingEntry $entry): bool
    {
        return $entry->content['response_status'] < 400
            && $entry->content['response_status'] > 199
            && Str::startsWith($entry->content['uri'], '/oauth');
    }

    /**
     * Prevent sensitive request details from being logged by Telescope.
     *
     * @return void
     */
    protected function hideSensitiveRequestDetails()
    {
        if ($this->app->environment('local')) {
            return;
        }

        Telescope::hideRequestParameters(['_token']);

        Telescope::hideRequestHeaders([
            'cookie',
            'x-csrf-token',
            'x-xsrf-token',
        ]);
    }

    /**
     * Register the Telescope gate.
     *
     * This gate determines who can access Telescope in non-local environments.
     *
     * @return void
     */
    protected function gate()
    {
        Gate::define('viewTelescope', function ($user) {
            if ($this->app->environment('local')) {
                return true;
            }

            return $user->can('system.log');
        });
    }
}
