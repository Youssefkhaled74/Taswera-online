<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->string('token')->nullable()->after('is_active');
            $table->string('manager_email')->nullable()->after('token');
            $table->string('manager_password')->nullable()->after('manager_email');
            $table->string('admin_email')->nullable()->after('manager_password');
            $table->string('admin_password')->nullable()->after('admin_email');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn(['token', 'manager_email', 'manager_password', 'admin_email', 'admin_password']);
        });
    }
};
