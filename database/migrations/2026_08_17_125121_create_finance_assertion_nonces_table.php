<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Nonce table untuk replay protection pada finance assertion exchange.
 *
 * Setiap assertion JWT dari Kaffah memiliki jti (JWT ID) yang unik.
 * TU menyimpan jti yang sudah dipakai agar assertion tidak bisa di-replay.
 *
 * Cleanup: row dengan expires_at < now() bisa dihapus — tidak diperlukan lagi.
 * Jalankan: DELETE FROM finance_assertion_nonces WHERE expires_at < NOW()
 * via scheduled command (opsional, tidak blocking functionality).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_assertion_nonces', function (Blueprint $table) {
            $table->id();
            // jti dari assertion JWT — unique constraint mencegah duplicate
            $table->string('jti', 128)->unique();
            // NIS student pemilik assertion — untuk audit trail
            $table->string('nis', 20)->index();
            // Waktu assertion expire — nonce bisa dihapus setelah waktu ini
            $table->timestamp('expires_at')->index();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_assertion_nonces');
    }
};
