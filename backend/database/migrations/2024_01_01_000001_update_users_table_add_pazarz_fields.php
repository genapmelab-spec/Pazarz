<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->string('role_id')->default('customer')->after('phone');
            $table->string('status')->default('active')->after('role_id');
            $table->string('avatar_url')->nullable()->after('status');
            $table->softDeletes()->after('updated_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'role_id', 'status', 'avatar_url', 'deleted_at']);
        });
    }
};
