<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('organization')->nullable()->after('email');
            $table->string('role_type')->nullable()->after('organization'); // legislator, activist, journalist, researcher, etc
            $table->text('bio')->nullable()->after('role_type');
            $table->string('avatar_url')->nullable()->after('bio');
            $table->json('notification_preferences')->nullable()->after('remember_token');
            $table->timestamp('last_activity_at')->nullable()->after('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'organization',
                'role_type',
                'bio',
                'avatar_url',
                'notification_preferences',
                'last_activity_at',
            ]);
        });
    }
};
