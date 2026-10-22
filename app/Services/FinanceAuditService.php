<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

/**
 * FinanceAuditService — Catat audit log untuk aksi finance configuration.
 *
 * Reuses tabel audit_logs yang sudah ada (dibuat di migration 2026_01_06).
 * Kolom target_type, target_id, old_value, new_value, ip_address
 * ditambahkan di migration 2026_08_24_000003.
 *
 * JANGAN log: password, token, kredensial payment gateway.
 * LOG: siapa, aksi apa, model apa, ID berapa, sebelum/sesudah.
 */
class FinanceAuditService
{
    /**
     * Catat aksi CREATE.
     */
    public static function logCreate(Model $model, ?Request $request = null): void
    {
        static::write('CREATE', $model, null, $model->toArray(), $request);
    }

    /**
     * Catat aksi UPDATE.
     *
     * @param array $oldData Snapshot sebelum perubahan (ambil SEBELUM update)
     */
    public static function logUpdate(Model $model, array $oldData, ?Request $request = null): void
    {
        static::write('UPDATE', $model, $oldData, $model->fresh()->toArray(), $request);
    }

    /**
     * Catat aksi DELETE (soft atau hard).
     */
    public static function logDelete(Model $model, ?Request $request = null): void
    {
        static::write('DELETE', $model, $model->toArray(), null, $request);
    }

    /**
     * Catat aksi TOGGLE (aktif/nonaktif).
     */
    public static function logToggle(Model $model, string $field, bool $newValue, ?Request $request = null): void
    {
        static::write(
            $newValue ? 'ACTIVATE' : 'DEACTIVATE',
            $model,
            [$field => !$newValue],
            [$field => $newValue],
            $request
        );
    }

    /**
     * Catat aksi bebas (raw) — untuk SPP generation, preview, dll.
     */
    public static function logRaw(
        string   $action,
        string   $module,
        string   $targetType,
        string   $description,
        ?Request $request = null,
    ): void {
        try {
            \Illuminate\Support\Facades\DB::table('audit_logs')->insert([
                'user_id'     => Auth::id(),
                'action'      => $action,
                'module'      => $module,
                'target_type' => $targetType,
                'target_id'   => null,
                'description' => $description,
                'old_value'   => null,
                'new_value'   => null,
                'ip_address'  => $request?->ip(),
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('FinanceAuditService::logRaw gagal', [
                'action' => $action,
                'error'  => $e->getMessage(),
            ]);
        }
    }

    /**
     * Tulis satu baris audit log.
     *
     * Jika tabel audit_logs tidak ada (contoh: test environment tanpa migration),
     * fallback ke Laravel Log::info — tidak throw exception.
     */
    private static function write(
        string  $action,
        Model   $model,
        ?array  $oldValue,
        ?array  $newValue,
        ?Request $request
    ): void {
        try {
            // Sanitasi: hapus kolom sensitif dari snapshot
            $sensitiveKeys = ['password', 'token', 'secret', 'snap_token', 'midtrans_order_id'];
            $oldValue = $oldValue
                ? array_diff_key($oldValue, array_flip($sensitiveKeys))
                : null;
            $newValue = $newValue
                ? array_diff_key($newValue, array_flip($sensitiveKeys))
                : null;

            \Illuminate\Support\Facades\DB::table('audit_logs')->insert([
                'user_id'     => Auth::id(),
                'action'      => $action,
                'module'      => 'FINANCE_CONFIG',
                'target_type' => class_basename($model),
                'target_id'   => $model->getKey(),
                'old_value'   => $oldValue ? json_encode($oldValue) : null,
                'new_value'   => $newValue ? json_encode($newValue) : null,
                'description' => class_basename($model) . ' #' . $model->getKey() . ' — ' . $action,
                'ip_address'  => $request?->ip(),
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        } catch (\Throwable $e) {
            // Audit failure tidak boleh menghentikan operasi utama
            Log::warning('FinanceAuditService: gagal catat audit log', [
                'action'  => $action,
                'model'   => class_basename($model),
                'id'      => $model->getKey(),
                'error'   => $e->getMessage(),
            ]);
        }
    }
}
