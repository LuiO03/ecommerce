<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('options', function (Blueprint $table) {
            if (!Schema::hasColumn('options', 'slug')) {
                $table->string('slug')->after('name')->unique();
            }

            if (!Schema::hasColumn('options', 'description')) {
                $position = Schema::hasColumn('options', 'slug') ? 'slug' : 'name';
                $table->text('description')->nullable()->after($position);
            }

            if (!Schema::hasColumn('options', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('description');
            }

            if (!Schema::hasColumn('options', 'updated_by')) {
                $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
            }

            if (!Schema::hasColumn('options', 'deleted_by')) {
                $table->unsignedBigInteger('deleted_by')->nullable()->after('updated_by');
            }
        });

        // Poblar slugs existentes asegurando unicidad sencilla
        $options = DB::table('options')->select('id', 'name')->get();

        foreach ($options as $option) {
            $base = Str::slug($option->name ?? '') ?: 'opcion-' . $option->id;
            $slug = $base;
            $suffix = 1;

            while (DB::table('options')->where('slug', $slug)->where('id', '!=', $option->id)->exists()) {
                $slug = $base . '-' . $suffix;
                $suffix++;
            }

            DB::table('options')->where('id', $option->id)->update(['slug' => $slug]);
        }

        Schema::table('options', function (Blueprint $table) {
            if (Schema::hasColumn('options', 'created_by')) {
                $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            }
            if (Schema::hasColumn('options', 'updated_by')) {
                $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            }
            if (Schema::hasColumn('options', 'deleted_by')) {
                $table->foreign('deleted_by')->references('id')->on('users')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('options', function (Blueprint $table) {
            if (Schema::hasColumn('options', 'created_by')) {
                $table->dropForeign(['created_by']);
            }
            if (Schema::hasColumn('options', 'updated_by')) {
                $table->dropForeign(['updated_by']);
            }
            if (Schema::hasColumn('options', 'deleted_by')) {
                $table->dropForeign(['deleted_by']);
            }
        });

        Schema::table('options', function (Blueprint $table) {
            if (Schema::hasColumn('options', 'deleted_by')) {
                $table->dropColumn('deleted_by');
            }
            if (Schema::hasColumn('options', 'updated_by')) {
                $table->dropColumn('updated_by');
            }
            if (Schema::hasColumn('options', 'created_by')) {
                $table->dropColumn('created_by');
            }
            if (Schema::hasColumn('options', 'description')) {
                $table->dropColumn('description');
            }
            if (Schema::hasColumn('options', 'slug')) {
                $table->dropColumn('slug');
            }
        });
    }
};
