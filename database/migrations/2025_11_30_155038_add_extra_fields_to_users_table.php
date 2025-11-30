<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Campos extra
            if (!Schema::hasColumn('users', 'last_name')) {
                $table->string('last_name', 100)->nullable();
            }
            if (!Schema::hasColumn('users', 'address')) {
                $table->string('address', 255)->nullable();
            }
            if (!Schema::hasColumn('users', 'dni')) {
                $table->string('dni', 20)->unique()->nullable();
            }
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone', 15)->nullable();
            }
            if (!Schema::hasColumn('users', 'image')) {
                $table->string('image', 255)->nullable();
            }
            if (!Schema::hasColumn('users', 'status')) {
                $table->boolean('status')->default(true);
            }
            if (!Schema::hasColumn('users', 'last_login')) {
                $table->dateTime('last_login')->nullable();
            }
            if (!Schema::hasColumn('users', 'slug')) {
                $table->string('slug')->unique()->index();
            }

            // Auditoría
            if (!Schema::hasColumn('users', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable();
            }
            if (!Schema::hasColumn('users', 'updated_by')) {
                $table->unsignedBigInteger('updated_by')->nullable();
            }
            if (!Schema::hasColumn('users', 'deleted_by')) {
                $table->unsignedBigInteger('deleted_by')->nullable();
            }

            // Seguridad
            if (!Schema::hasColumn('users', 'last_password_update')) {
                $table->dateTime('last_password_update')->nullable();
            }
            if (!Schema::hasColumn('users', 'failed_attempts')) {
                $table->integer('failed_attempts')->default(0);
            }
            if (!Schema::hasColumn('users', 'blocked_until')) {
                $table->dateTime('blocked_until')->nullable();
            }

            // Foreign keys
            if (!Schema::hasColumn('users', 'created_by')) {
                $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('users', 'updated_by')) {
                $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('users', 'deleted_by')) {
                $table->foreign('deleted_by')->references('id')->on('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = [
                'last_name','address','dni','phone','image','status','last_login','slug',
                'created_by','updated_by','deleted_by',
                'last_password_update','failed_attempts','blocked_until'
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }

            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropForeign(['deleted_by']);
        });
    }
};
