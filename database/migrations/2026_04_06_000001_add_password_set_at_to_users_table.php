<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('password_set_at')->nullable()->after('password');
        });

        // Backfill for existing users who registered with email (not OAuth)
        DB::table('users')
            ->whereNotNull('password')
            ->whereNull('password_set_at')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('social_accounts')
                    ->whereColumn('social_accounts.user_id', 'users.id');
            })
            ->update(['password_set_at' => DB::raw('created_at')]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('password_set_at');
        });
    }
};
