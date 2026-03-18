<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if columns already exist before adding them
        if (!Schema::hasColumn('roles', 'slug')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->string('slug')->nullable()->after('name');
            });
        }

        if (!Schema::hasColumn('roles', 'description')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->text('description')->nullable()->after('slug');
            });
        }

        if (!Schema::hasColumn('roles', 'is_super_admin')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->boolean('is_super_admin')->default(false)->after('description');
            });
        }

        // Populate slugs for existing roles that don't have one
        $roles = DB::table('roles')->whereNull('slug')->orWhere('slug', '')->get();
        foreach ($roles as $role) {
            DB::table('roles')
                ->where('id', $role->id)
                ->update(['slug' => \Illuminate\Support\Str::slug($role->name)]);
        }

        // Add unique constraint if not exists (using raw SQL)
        $indexes = DB::select("SHOW INDEXES FROM roles WHERE Key_name = 'roles_slug_unique'");
        if (empty($indexes)) {
            // First make sure all slugs are unique
            $duplicates = DB::table('roles')
                ->select('slug', DB::raw('COUNT(*) as count'))
                ->whereNotNull('slug')
                ->groupBy('slug')
                ->havingRaw('COUNT(*) > 1')
                ->get();

            foreach ($duplicates as $dup) {
                $rolesWithSameSlug = DB::table('roles')
                    ->where('slug', $dup->slug)
                    ->orderBy('id')
                    ->get();

                foreach ($rolesWithSameSlug as $index => $role) {
                    if ($index > 0) {
                        DB::table('roles')
                            ->where('id', $role->id)
                            ->update(['slug' => $role->slug . '-' . $index]);
                    }
                }
            }

            // Now add unique constraint
            DB::statement('ALTER TABLE roles ADD UNIQUE roles_slug_unique(slug)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            // Check if index exists before dropping
            try {
                $table->dropUnique('roles_slug_unique');
            } catch (\Exception $e) {
                // Index might not exist, ignore
            }
            $table->dropColumn(['slug', 'description', 'is_super_admin']);
        });
    }
};
