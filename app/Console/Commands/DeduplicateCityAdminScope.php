<?php

namespace App\Console\Commands;

use App\Models\City;
use App\Models\User;
use App\Services\UserRoleService;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Remove duplicate city_administrator rows in user_scope that block the
 * partial unique index user_scope_one_city_admin_per_city.
 *
 * Run BEFORE migrating 2026_06_04_100001_add_one_city_admin_per_city_scope_index:
 *   sail artisan user-scope:dedupe-city-admins
 *   sail artisan user-scope:dedupe-city-admins --execute
 */
class DeduplicateCityAdminScope extends Command
{
    protected $signature = 'user-scope:dedupe-city-admins
                            {--execute : Delete duplicate rows (default is dry-run only)}';

    protected $description = 'Remove duplicate city_administrator user_scope rows (one admin per city)';

    public function __construct(private UserRoleService $userRoleService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $role = config('permission.role_names.city_admin', 'city_administrator');
        $dryRun = ! $this->option('execute');
        $hasCityUser = Schema::hasTable('city_user');

        $duplicateCityIds = DB::table('user_scope')
            ->select('scope_id')
            ->where('role', $role)
            ->where('scope_type', 'city')
            ->groupBy('scope_id')
            ->havingRaw('COUNT(*) > 1')
            ->orderBy('scope_id')
            ->pluck('scope_id')
            ->map(fn ($id) => (int) $id);

        if ($duplicateCityIds->isEmpty()) {
            $this->info('No duplicate city_administrator scope rows found.');

            return self::SUCCESS;
        }

        $this->warn(sprintf(
            'Found %d cities with multiple city administrators.',
            $duplicateCityIds->count()
        ));

        if ($dryRun) {
            $this->line('Dry-run only — pass --execute to apply deletions.');
        }

        $cityNames = City::whereIn('id', $duplicateCityIds)->pluck('name', 'id');
        $removedScopeIds = [];
        $removedUserIds = collect();

        foreach ($duplicateCityIds as $cityId) {
            $scopeRows = DB::table('user_scope')
                ->where('role', $role)
                ->where('scope_type', 'city')
                ->where('scope_id', $cityId)
                ->orderBy('id')
                ->get();

            $activeUserIds = User::whereIn('id', $scopeRows->pluck('user_id'))
                ->pluck('id')
                ->flip();

            $keeperScopeId = $this->pickKeeperScopeId($scopeRows, $cityId, $activeUserIds, $hasCityUser);
            $keeper = $scopeRows->firstWhere('id', $keeperScopeId);
            $toRemove = $scopeRows->reject(fn ($row) => (int) $row->id === $keeperScopeId);

            $removedUsers = User::withTrashed()
                ->whereIn('id', $toRemove->pluck('user_id'))
                ->get(['id', 'email', 'first_name', 'last_name', 'deleted_at'])
                ->keyBy('id');

            $keeperUser = User::withTrashed()->find($keeper->user_id);

            $this->table(
                ['City', 'Scope ID', 'Keep user', 'Remove users'],
                [[
                    ($cityNames[$cityId] ?? '?')." (#{$cityId})",
                    $keeperScopeId,
                    $this->formatUser($keeperUser),
                    $toRemove->map(fn ($row) => $this->formatUser($removedUsers->get($row->user_id)))->implode('; '),
                ]]
            );

            if ($dryRun) {
                continue;
            }

            $this->line("Applying deletions for city #{$cityId}…");

            foreach ($toRemove as $row) {
                DB::table('user_scope')->where('id', $row->id)->delete();
                $removedScopeIds[] = (int) $row->id;
                $removedUserIds->push((int) $row->user_id);

                if ($hasCityUser) {
                    DB::table('city_user')
                        ->where('city_id', $cityId)
                        ->where('user_id', $row->user_id)
                        ->delete();
                }
            }

            if ($hasCityUser) {
                $this->syncLegacyCityUser($cityId, $keeperUser);
            }

            $this->info("City #{$cityId} done.");
        }

        if ($dryRun) {
            $this->newLine();
            $this->info('Dry-run complete. Re-run with --execute to apply.');

            return self::SUCCESS;
        }

        $this->line('Revoking orphaned city_administrator roles…');
        $this->revokeOrphanedCityAdmins($role, $removedUserIds->unique()->values());

        $this->newLine();
        $this->info(sprintf(
            'Removed %d duplicate user_scope rows across %d cities.',
            count($removedScopeIds),
            $duplicateCityIds->count()
        ));
        $this->info('You can now run: ./scripts/db-etl.sh safe-migrate');

        return self::SUCCESS;
    }

    /**
     * Prefer earliest legacy city_user with an active user, then any active scope user,
     * otherwise the oldest scope row (may reference a deleted user).
     *
     * @param  Collection<int, int>  $activeUserIds
     */
    private function pickKeeperScopeId(
        Collection $scopeRows,
        int $cityId,
        Collection $activeUserIds,
        bool $hasCityUser
    ): int {
        if ($hasCityUser) {
            $legacyUserIds = DB::table('city_user')
                ->where('city_id', $cityId)
                ->orderBy('id')
                ->pluck('user_id');

            foreach ($legacyUserIds as $userId) {
                $userId = (int) $userId;
                if (! isset($activeUserIds[$userId])) {
                    continue;
                }

                $match = $scopeRows->firstWhere('user_id', $userId);
                if ($match) {
                    return (int) $match->id;
                }
            }
        }

        $withActiveUser = $scopeRows->first(fn ($row) => isset($activeUserIds[(int) $row->user_id]));
        if ($withActiveUser) {
            return (int) $withActiveUser->id;
        }

        return (int) $scopeRows->sortBy('id')->first()->id;
    }

    private function syncLegacyCityUser(int $cityId, ?User $keeperUser): void
    {
        if ($keeperUser && ! $keeperUser->trashed()) {
            $legacyExists = DB::table('city_user')
                ->where('city_id', $cityId)
                ->where('user_id', $keeperUser->id)
                ->exists();

            if (! $legacyExists) {
                DB::table('city_user')->insert([
                    'user_id' => $keeperUser->id,
                    'city_id' => $cityId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('city_user')
                ->where('city_id', $cityId)
                ->where('user_id', '!=', $keeperUser->id)
                ->delete();

            return;
        }

        // Keeper references a missing or soft-deleted user — drop all legacy rows for this city.
        DB::table('city_user')->where('city_id', $cityId)->delete();
    }

    /**
     * @param  Collection<int, int>  $userIds
     */
    private function revokeOrphanedCityAdmins(string $role, Collection $userIds): void
    {
        foreach ($userIds as $userId) {
            $user = User::find($userId);
            if (! $user || ! $user->isCityAdministrator()) {
                continue;
            }

            $remainingScope = DB::table('user_scope')
                ->where('user_id', $userId)
                ->where('role', $role)
                ->where('scope_type', 'city')
                ->count();

            $remainingLegacy = Schema::hasTable('city_user')
                ? DB::table('city_user')->where('user_id', $userId)->count()
                : 0;

            if ($remainingScope === 0 && $remainingLegacy === 0) {
                $this->line("Revoking city_administrator role from user #{$userId} ({$user->email}) — no cities left.");
                $this->userRoleService->syncSingleRole($user, null);
            }
        }
    }

    private function formatUser(?User $user): string
    {
        if (! $user) {
            return '(missing user)';
        }

        $suffix = $user->trashed() ? ' [deleted]' : '';

        return "#{$user->id} {$user->email}{$suffix}";
    }
}
