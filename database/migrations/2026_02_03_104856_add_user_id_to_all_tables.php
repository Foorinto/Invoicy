<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Tables that need user_id for multi-tenant isolation.
     */
    private array $tables = [
        'clients',
        'invoices',
        'quotes',
        'expenses',
        'time_entries',
        'business_settings',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, ensure we have at least one user for migrating existing data
        $defaultUserId = $this->getOrCreateDefaultUserId();

        // Add user_id to all tables
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table) && !Schema::hasColumn($table, 'user_id')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->foreignId('user_id')
                        ->nullable()
                        ->after('id')
                        ->constrained()
                        ->cascadeOnDelete();
                    $table->index('user_id');
                });

                // Migrate existing records to default user
                if ($defaultUserId) {
                    DB::table($table)->whereNull('user_id')->update(['user_id' => $defaultUserId]);
                }

                // Now make user_id NOT nullable (SQLite doesn't support modifying columns well)
                // For SQLite, we'll handle this differently
                if (DB::getDriverName() !== 'sqlite') {
                    Schema::table($table, function (Blueprint $table) {
                        $table->foreignId('user_id')->nullable(false)->change();
                    });
                }
            }
        }

        // Make audit_exports.user_id NOT nullable if it exists
        if (Schema::hasTable('audit_exports') && Schema::hasColumn('audit_exports', 'user_id')) {
            // Migrate any null user_id records
            if ($defaultUserId) {
                DB::table('audit_exports')->whereNull('user_id')->update(['user_id' => $defaultUserId]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'user_id')) {
                Schema::table($table, function (Blueprint $blueprint) use ($table) {
                    $blueprint->dropForeign([$table === 'business_settings' ? 'user_id' : 'user_id']);
                    $blueprint->dropIndex([$table . '_user_id_index']);
                    $blueprint->dropColumn('user_id');
                });
            }
        }
    }

    /**
     * Get or create a default user for migrating existing data.
     */
    private function getOrCreateDefaultUserId(): ?int
    {
        // Check if any user exists
        $existingUser = DB::table('users')->first();

        if ($existingUser) {
            return $existingUser->id;
        }

        // No users exist and no data to migrate, return null
        $hasData = false;
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table) && DB::table($table)->exists()) {
                $hasData = true;
                break;
            }
        }

        if (!$hasData) {
            return null;
        }

        // Create a default admin user for legacy data
        return DB::table('users')->insertGetId([
            'name' => 'Admin',
            'email' => 'admin@faktur.lu',
            'password' => Hash::make(Str::random(32)),
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
};
