<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSyncJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sync_jobs', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key
            $table->unsignedBigInteger('branch_id'); // Branch identifier
            $table->string('employeeName'); // Employee name
            $table->decimal('pay_amount', 10, 2); // Payment amount with 2 decimal places
            $table->string('orderprefixcode'); // Order prefix code
            $table->string('status'); // Status of the job
            $table->string('shift_name'); // Shift name
            $table->string('orderphone'); // Phone number for the order
            $table->unsignedInteger('number_of_photos'); // Number of photos
            $table->timestamps(); // created_at and updated_at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sync_jobs');
    }
}