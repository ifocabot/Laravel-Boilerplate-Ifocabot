<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     * 
     * ERP Revision for Location module:
     * - Add explicit flags for geofence, stock, and employee assignment
     * - Change type from enum to varchar for flexibility
     * - Add proper indexes for performance
     * - Change FK constraint to RESTRICT
     */
    public function up(): void
    {
        // Step 1: Add new boolean flags
        Schema::table('locations', function (Blueprint $table) {
            $table->boolean('is_geofence_enabled')->default(false)->after('radius_meters');
            $table->boolean('is_stock_location')->default(false)->after('is_geofence_enabled');
            $table->boolean('is_assignable_to_employee')->default(true)->after('is_stock_location');
            $table->json('allowed_child_types')->nullable()->after('is_assignable_to_employee');
        });

        // Step 2: Migrate existing geofence data - set is_geofence_enabled=true where lat/lng/radius exists
        DB::table('locations')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereNotNull('radius_meters')
            ->where('radius_meters', '>', 0)
            ->update(['is_geofence_enabled' => true]);

        // Step 3: Set is_stock_location=true for warehouse and bin types
        DB::table('locations')
            ->whereIn('type', ['warehouse', 'bin'])
            ->update(['is_stock_location' => true]);

        // Step 4: Change type from enum to varchar (MySQL specific)
        // First, alter the column type
        DB::statement("ALTER TABLE locations MODIFY COLUMN type VARCHAR(50) NOT NULL DEFAULT 'office'");

        // Step 5: Make code NOT NULL (update any null values first)
        DB::table('locations')
            ->whereNull('code')
            ->update(['code' => DB::raw("CONCAT('LOC-', id)")]);

        Schema::table('locations', function (Blueprint $table) {
            $table->string('code', 50)->nullable(false)->change();
        });

        // Step 6: Add indexes for performance
        Schema::table('locations', function (Blueprint $table) {
            $table->index('parent_id', 'idx_locations_parent_id');
            $table->index('type', 'idx_locations_type');
            $table->index('is_stock_location', 'idx_locations_is_stock_location');
            $table->index('is_geofence_enabled', 'idx_locations_is_geofence_enabled');
            $table->index('is_active', 'idx_locations_is_active');
        });

        // Step 7: Update FK constraint to RESTRICT
        // Drop old FK and recreate with RESTRICT
        Schema::table('locations', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
        });

        Schema::table('locations', function (Blueprint $table) {
            $table->foreign('parent_id')
                ->references('id')
                ->on('locations')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert FK constraint
        Schema::table('locations', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
        });

        Schema::table('locations', function (Blueprint $table) {
            $table->foreign('parent_id')
                ->references('id')
                ->on('locations')
                ->onDelete('set null');
        });

        // Drop indexes
        Schema::table('locations', function (Blueprint $table) {
            $table->dropIndex('idx_locations_parent_id');
            $table->dropIndex('idx_locations_type');
            $table->dropIndex('idx_locations_is_stock_location');
            $table->dropIndex('idx_locations_is_geofence_enabled');
            $table->dropIndex('idx_locations_is_active');
        });

        // Revert type to enum
        DB::statement("ALTER TABLE locations MODIFY COLUMN type ENUM('office', 'warehouse', 'store', 'bin') NOT NULL DEFAULT 'office'");

        // Drop new columns
        Schema::table('locations', function (Blueprint $table) {
            $table->dropColumn([
                'is_geofence_enabled',
                'is_stock_location',
                'is_assignable_to_employee',
                'allowed_child_types',
            ]);
        });
    }
};
