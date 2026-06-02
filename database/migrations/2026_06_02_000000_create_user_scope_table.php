<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Generalized, role-aware authorization scope (OQ10).
 *
 * Additive and forward-only. Backfills existing City Administrators from the
 * legacy `city_user` pivot, which is intentionally KEPT for dual-read/dual-write
 * during the transition (do not drop here).
 *
 * NOTE: The "one City Administrator per city" partial unique index is deliberately
 * NOT created here. Its conflict semantics (reject / transfer / replace) are gated
 * on OQ3, which is unanswered; enforcing it now would implicitly choose "reject"
 * and could break the existing admin UI. It will be added in the approval-flow
 * phase once OQ3 is decided.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('user_scope')) {
            Schema::create('user_scope', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')
                    ->constrained('users')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();
                $table->string('role');
                $table->string('scope_type');
                $table->unsignedBigInteger('scope_id');
                $table->timestamps();

                $table->index(['user_id', 'role']);
                $table->index(['scope_type', 'scope_id']);
                // Prevent exact duplicate scope rows (idempotent backfill / sync).
                $table->unique(
                    ['user_id', 'role', 'scope_type', 'scope_id'],
                    'user_scope_unique'
                );
            });
        }

        $this->backfillFromCityUser();
    }

    public function down(): void
    {
        Schema::dropIfExists('user_scope');
    }

    /**
     * Idempotently mirror legacy city_user rows into user_scope as
     * (role=city_administrator, scope_type=city, scope_id=city_id).
     */
    private function backfillFromCityUser(): void
    {
        if (! Schema::hasTable('city_user')) {
            return;
        }

        DB::table('city_user')->orderBy('id')->chunkById(500, function ($rows) {
            $now = now();

            foreach ($rows as $row) {
                DB::table('user_scope')->updateOrInsert(
                    [
                        'user_id' => $row->user_id,
                        'role' => 'city_administrator',
                        'scope_type' => 'city',
                        'scope_id' => $row->city_id,
                    ],
                    ['updated_at' => $now, 'created_at' => $now]
                );
            }
        });
    }
};
