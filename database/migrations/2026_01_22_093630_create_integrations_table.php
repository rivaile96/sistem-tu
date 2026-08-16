<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Reconstructed historical migration — originally ran on production at batch 12.
 * File was lost from the repository; reconstructed from production schema evidence.
 *
 * Production evidence (SHOW FULL COLUMNS FROM integrations):
 *   id            | bigint unsigned | NOT NULL | PRI | auto_increment
 *   host          | varchar(255)    | NOT NULL |     | 127.0.0.1
 *   port          | varchar(255)    | NOT NULL |     | 3306
 *   database_name | varchar(255)    | NOT NULL |     |
 *   username      | varchar(255)    | NOT NULL |     | root
 *   password      | varchar(255)    | NULL     |     | NULL
 *   created_at    | timestamp       | NULL     |     |
 *   updated_at    | timestamp       | NULL     |     |
 *
 * Indexes: PRIMARY on id only.
 * No foreign keys.
 *
 * Used by IntegrationController to store external PPDB database credentials.
 * These settings were previously stored as key-value pairs in the settings table;
 * this migration introduced a dedicated typed table for connection config.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('integrations', function (Blueprint $table) {
            $table->id();
            $table->string('host')->default('127.0.0.1');
            $table->string('port')->default('3306');
            $table->string('database_name');
            $table->string('username')->default('root');
            $table->string('password')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integrations');
    }
};
