<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel master kelas
        Schema::create('kelas', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kelas');          // "X IPA 1", "VII A", "4A"
            $table->unsignedTinyInteger('tingkat'); // 1-12 (SD=1-6, SMP=7-9, SMA=10-12)
            $table->string('jurusan')->nullable();  // "IPA", "IPS", "RPL", "TKJ" — null untuk SD/SMP
            $table->string('wali_kelas')->nullable();
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();

            $table->unique(['nama_kelas']); // nama kelas harus unik
        });

        // Tambah kolom kelas_id ke students (nullable dulu untuk backward compat)
        Schema::table('students', function (Blueprint $table) {
            if (!Schema::hasColumn('students', 'kelas_id')) {
                $table->foreignId('kelas_id')
                    ->nullable()
                    ->after('class_name')
                    ->constrained('kelas')
                    ->nullOnDelete();
            }
        });

        // Tambah jenjang ke school_settings
        $sudahAda = DB::table('school_settings')->where('key', 'jenjang')->exists();
        if (!$sudahAda) {
            DB::table('school_settings')->insert([
                'key'        => 'jenjang',
                'value'      => 'SMA',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (Schema::hasColumn('students', 'kelas_id')) {
                $table->dropForeign(['kelas_id']);
                $table->dropColumn('kelas_id');
            }
        });

        Schema::dropIfExists('kelas');

        DB::table('school_settings')->where('key', 'jenjang')->delete();
    }
};
