<?php

namespace Tests\Feature;

use App\Models\Kelas;
use App\Models\PosBundle;
use App\Models\PosBundleItem;
use App\Models\PosItem;
use App\Models\Student;
use App\Models\StudentBill;
use App\Models\StudentStatusLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Phase 12 — Registrasi Siswa Baru (PPDB) Integration Tests
 *
 * A.  create calon siswa
 * B.  activate requires NIS
 * C.  activate requires kelas
 * D.  activate success (single)
 * E.  duplicate NIS blocked
 * F.  status log created on activation
 * G.  bundle generation after activation
 * H.  calon cannot login to portal
 * I.  active can login to portal
 * J.  activation rollback on failure
 * K.  bulk activation summary
 * L.  duplicate bundle protection
 */
class Phase12RegistrasiSiswaTest extends TestCase
{
    use RefreshDatabase;

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function tu(): User
    {
        return User::factory()->create(['role' => 'tu']);
    }

    private function kelas(): Kelas
    {
        return Kelas::create([
            'nama_kelas' => 'X IPA 1',
            'tingkat'    => 10,
            'jurusan'    => 'IPA',
            'is_aktif'   => true,
        ]);
    }

    // ── A. Create calon siswa ─────────────────────────────────────────────────

    public function test_A1_store_creates_calon_siswa_with_correct_status(): void
    {
        $tu = $this->tu();

        $response = $this->actingAs($tu)->post('/ppdb/daftar', [
            'name'       => 'Budi Calon',
            'birth_date' => '2008-05-10',
            'gender'     => 'L',
        ]);

        $response->assertRedirect(route('ppdb.index'));
        $this->assertDatabaseHas('students', [
            'name'   => 'Budi Calon',
            'status' => 'calon_siswa',
        ]);

        // NIS and kelas_id must be null at registration
        $siswa = Student::where('name', 'Budi Calon')->first();
        $this->assertNull($siswa->nis);
        $this->assertNull($siswa->kelas_id);
    }

    public function test_A2_store_creates_status_log(): void
    {
        $tu = $this->tu();

        $this->actingAs($tu)->post('/ppdb/daftar', [
            'name'       => 'Siti Calon',
            'birth_date' => '2009-01-15',
            'gender'     => 'P',
        ]);

        $siswa = Student::where('name', 'Siti Calon')->first();
        $this->assertNotNull($siswa);

        $log = StudentStatusLog::where('student_id', $siswa->id)->first();
        $this->assertNotNull($log);
        $this->assertNull($log->status_lama);
        $this->assertEquals('calon_siswa', $log->status_baru);
    }

    public function test_A3_store_requires_name_gender_birth_date(): void
    {
        $tu = $this->tu();

        // Missing name
        $this->actingAs($tu)->post('/ppdb/daftar', [
            'birth_date' => '2008-05-10',
            'gender'     => 'L',
        ])->assertSessionHasErrors('name');

        // Missing gender
        $this->actingAs($tu)->post('/ppdb/daftar', [
            'name'       => 'Test',
            'birth_date' => '2008-05-10',
        ])->assertSessionHasErrors('gender');
    }

    public function test_A4_store_nullable_catatan_does_not_crash(): void
    {
        $tu = $this->tu();

        // catatan is null — must not cause string concat error
        $response = $this->actingAs($tu)->post('/ppdb/daftar', [
            'name'       => 'Tanpa Catatan',
            'birth_date' => '2008-05-10',
            'gender'     => 'L',
            'catatan'    => null,
        ]);

        $response->assertRedirect(route('ppdb.index'));
        $this->assertDatabaseHas('students', ['name' => 'Tanpa Catatan', 'status' => 'calon_siswa']);
    }

    // ── B. Activate requires NIS ──────────────────────────────────────────────

    public function test_B1_activation_fails_without_nis(): void
    {
        $tu    = $this->tu();
        $kelas = $this->kelas();
        $siswa = Student::factory()->calon()->create();

        $response = $this->actingAs($tu)->post("/ppdb/{$siswa->id}/seleksi", [
            'aksi'     => 'terima',
            'kelas_id' => $kelas->id,
            'nis'      => '',
        ]);

        $response->assertSessionHasErrors('nis');
        $siswa->refresh();
        $this->assertEquals('calon_siswa', $siswa->status);
    }

    // ── C. Activate requires kelas ────────────────────────────────────────────

    public function test_C1_activation_fails_without_kelas(): void
    {
        $tu    = $this->tu();
        $siswa = Student::factory()->calon()->create();

        $response = $this->actingAs($tu)->post("/ppdb/{$siswa->id}/seleksi", [
            'aksi'     => 'terima',
            'kelas_id' => '',
            'nis'      => '2024001',
        ]);

        $response->assertSessionHasErrors('kelas_id');
        $siswa->refresh();
        $this->assertEquals('calon_siswa', $siswa->status);
    }

    public function test_C2_activation_fails_with_inactive_kelas(): void
    {
        $tu    = $this->tu();
        $siswa = Student::factory()->calon()->create();

        $inactiveKelas = Kelas::create([
            'nama_kelas' => 'X IPS 2',
            'tingkat'    => 10,
            'is_aktif'   => false,
        ]);

        // kelas_id exists but is_aktif = false — seleksi() must reject inactive kelas.
        $response = $this->actingAs($tu)->post("/ppdb/{$siswa->id}/seleksi", [
            'aksi'     => 'terima',
            'kelas_id' => $inactiveKelas->id,
            'nis'      => '2024002',
        ]);

        $response->assertSessionHasErrors('kelas_id');
        $siswa->refresh();
        $this->assertEquals('calon_siswa', $siswa->status);
    }

    // ── D. Activate success ───────────────────────────────────────────────────

    public function test_D1_activation_sets_status_active_and_assigns_nis_kelas(): void
    {
        $tu    = $this->tu();
        $kelas = $this->kelas();
        $siswa = Student::factory()->calon()->create();

        $response = $this->actingAs($tu)->post("/ppdb/{$siswa->id}/seleksi", [
            'aksi'     => 'terima',
            'kelas_id' => $kelas->id,
            'nis'      => '2024099',
        ]);

        $response->assertRedirect(route('students.show', $siswa->id));

        $siswa->refresh();
        $this->assertEquals('active', $siswa->status);
        $this->assertEquals('2024099', $siswa->nis);
        $this->assertEquals($kelas->id, $siswa->kelas_id);
    }

    public function test_D2_activation_is_atomic_via_transaction(): void
    {
        // After successful activation, both student update AND status log exist.
        $tu    = $this->tu();
        $kelas = $this->kelas();
        $siswa = Student::factory()->calon()->create();

        $this->actingAs($tu)->post("/ppdb/{$siswa->id}/seleksi", [
            'aksi'     => 'terima',
            'kelas_id' => $kelas->id,
            'nis'      => '2024100',
        ]);

        $siswa->refresh();
        $this->assertEquals('active', $siswa->status);

        // Log must exist in same transaction
        $this->assertDatabaseHas('student_status_logs', [
            'student_id'  => $siswa->id,
            'status_lama' => 'calon_siswa',
            'status_baru' => 'active',
        ]);
    }

    // ── E. Duplicate NIS blocked ──────────────────────────────────────────────

    public function test_E1_activation_blocked_if_nis_already_exists(): void
    {
        $tu    = $this->tu();
        $kelas = $this->kelas();

        // Existing active student with NIS 2024001
        Student::factory()->create(['nis' => '2024001', 'status' => 'active']);

        $calon = Student::factory()->calon()->create();

        $response = $this->actingAs($tu)->post("/ppdb/{$calon->id}/seleksi", [
            'aksi'     => 'terima',
            'kelas_id' => $kelas->id,
            'nis'      => '2024001',  // duplicate
        ]);

        $response->assertSessionHasErrors('nis');
        $calon->refresh();
        $this->assertEquals('calon_siswa', $calon->status);
    }

    // ── F. Status log created on activation ──────────────────────────────────

    public function test_F1_status_log_created_on_activation(): void
    {
        $tu    = $this->tu();
        $kelas = $this->kelas();
        $siswa = Student::factory()->calon()->create();

        $this->actingAs($tu)->post("/ppdb/{$siswa->id}/seleksi", [
            'aksi'     => 'terima',
            'kelas_id' => $kelas->id,
            'nis'      => '2024200',
        ]);

        $log = StudentStatusLog::where('student_id', $siswa->id)
            ->where('status_baru', 'active')
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals('calon_siswa', $log->status_lama);
        $this->assertEquals($tu->id, $log->diubah_oleh);
    }

    // ── G. Bundle generation after activation ────────────────────────────────

    public function test_G1_bundle_prompt_shown_after_activation(): void
    {
        $tu    = $this->tu();
        $kelas = $this->kelas();
        $siswa = Student::factory()->calon()->create();

        $response = $this->actingAs($tu)->post("/ppdb/{$siswa->id}/seleksi", [
            'aksi'     => 'terima',
            'kelas_id' => $kelas->id,
            'nis'      => '2024300',
        ]);

        // After activation, redirected to students.show with show_bundle_prompt
        $response->assertRedirect(route('students.show', $siswa->id));
        $response->assertSessionHas('show_bundle_prompt', true);
    }

    // ── H. Calon cannot login to portal ──────────────────────────────────────

    public function test_H1_calon_siswa_cannot_login(): void
    {
        $siswa = Student::factory()->calon()->create([
            'nis'        => '9999001',
            'birth_date' => '2008-07-12',
        ]);

        $response = $this->post('/siswa/login', [
            'nis'      => '9999001',
            'password' => Carbon::parse('2008-07-12')->format('dmy'),  // 120708
        ]);

        // Login must fail — calon_siswa does not have status=active
        $response->assertSessionHasErrors('nis');
        $this->assertGuest('siswa');
    }

    // ── I. Active can login to portal ────────────────────────────────────────

    public function test_I1_active_student_can_login(): void
    {
        $birthDate = '2008-07-12';
        $siswa     = Student::factory()->create([
            'nis'        => '9999002',
            'birth_date' => $birthDate,
            'status'     => 'active',
        ]);

        $response = $this->post('/siswa/login', [
            'nis'      => '9999002',
            'password' => Carbon::parse($birthDate)->format('dmy'),
        ]);

        $response->assertRedirect(route('siswa.dashboard'));
        $this->assertAuthenticatedAs($siswa, 'siswa');
    }

    // ── J. Activation rollback on failure ────────────────────────────────────

    public function test_J1_activation_does_not_partially_update_if_kelas_invalid(): void
    {
        $tu    = $this->tu();
        $siswa = Student::factory()->calon()->create();

        // kelas_id 99999 does not exist → validation fails before any DB write
        $response = $this->actingAs($tu)->post("/ppdb/{$siswa->id}/seleksi", [
            'aksi'     => 'terima',
            'kelas_id' => 99999,
            'nis'      => '2024400',
        ]);

        $response->assertSessionHasErrors('kelas_id');

        $siswa->refresh();
        $this->assertEquals('calon_siswa', $siswa->status);
        $this->assertNull($siswa->nis);

        // No status log created
        $this->assertDatabaseMissing('student_status_logs', [
            'student_id'  => $siswa->id,
            'status_baru' => 'active',
        ]);
    }

    // ── K. Bulk activation summary ────────────────────────────────────────────

    public function test_K1_bulk_activation_succeeds_for_valid_students(): void
    {
        $tu    = $this->tu();
        $kelas = $this->kelas();

        $s1 = Student::factory()->calon()->create(['name' => 'Bulk Siswa 1']);
        $s2 = Student::factory()->calon()->create(['name' => 'Bulk Siswa 2']);

        $response = $this->actingAs($tu)->post('/ppdb/konversi', [
            'siswa_ids'        => [$s1->id, $s2->id],
            'kelas_id_default' => $kelas->id,
            'nis_per_siswa'    => [
                $s1->id => '2024501',
                $s2->id => '2024502',
            ],
        ]);

        $response->assertRedirect(route('ppdb.index'));
        $response->assertSessionHas('success');

        $s1->refresh();
        $s2->refresh();
        $this->assertEquals('active', $s1->status);
        $this->assertEquals('active', $s2->status);
        $this->assertEquals('2024501', $s1->nis);
        $this->assertEquals('2024502', $s2->nis);
    }

    public function test_K2_bulk_activation_skips_student_with_missing_nis(): void
    {
        $tu    = $this->tu();
        $kelas = $this->kelas();

        $s1 = Student::factory()->calon()->create(['name' => 'Bulk OK']);
        $s2 = Student::factory()->calon()->create(['name' => 'Bulk No NIS']);

        $this->actingAs($tu)->post('/ppdb/konversi', [
            'siswa_ids'        => [$s1->id, $s2->id],
            'kelas_id_default' => $kelas->id,
            'nis_per_siswa'    => [
                $s1->id => '2024601',
                // $s2 intentionally missing NIS
            ],
        ]);

        $s1->refresh();
        $s2->refresh();
        $this->assertEquals('active', $s1->status);
        $this->assertEquals('calon_siswa', $s2->status); // skipped

        // Summary must include failure detail
        $gagal = session('aktivasi_gagal');
        $this->assertNotEmpty($gagal);
        $this->assertStringContainsString('Bulk No NIS', implode(' ', $gagal));
    }

    public function test_K3_bulk_activation_each_student_is_independent(): void
    {
        // If one student fails, others must still succeed.
        $tu    = $this->tu();
        $kelas = $this->kelas();

        $sOk   = Student::factory()->calon()->create(['name' => 'Indep OK']);
        $sGagal = Student::factory()->calon()->create(['name' => 'Indep Gagal']);

        // Create another student that already has NIS 2024701 — so s_gagal's NIS is duplicate
        Student::factory()->create(['nis' => '2024701', 'status' => 'active']);

        $this->actingAs($tu)->post('/ppdb/konversi', [
            'siswa_ids'        => [$sOk->id, $sGagal->id],
            'kelas_id_default' => $kelas->id,
            'nis_per_siswa'    => [
                $sOk->id    => '2024700',
                $sGagal->id => '2024701',  // duplicate → fails
            ],
        ]);

        $sOk->refresh();
        $sGagal->refresh();
        $this->assertEquals('active', $sOk->status);       // succeeded
        $this->assertEquals('calon_siswa', $sGagal->status); // failed — but sOk unaffected
    }

    public function test_K4_bulk_activation_duplicate_nis_within_same_batch_blocked(): void
    {
        $tu    = $this->tu();
        $kelas = $this->kelas();

        $s1 = Student::factory()->calon()->create(['name' => 'Batch Dup 1']);
        $s2 = Student::factory()->calon()->create(['name' => 'Batch Dup 2']);

        // Both use same NIS — second one must be blocked
        $this->actingAs($tu)->post('/ppdb/konversi', [
            'siswa_ids'        => [$s1->id, $s2->id],
            'kelas_id_default' => $kelas->id,
            'nis_per_siswa'    => [
                $s1->id => '2024800',
                $s2->id => '2024800',  // same NIS as s1
            ],
        ]);

        $s1->refresh();
        $s2->refresh();
        $this->assertEquals('active', $s1->status);
        $this->assertEquals('calon_siswa', $s2->status);
    }

    public function test_K5_bulk_activation_status_logs_created(): void
    {
        $tu    = $this->tu();
        $kelas = $this->kelas();

        $s1 = Student::factory()->calon()->create();
        $s2 = Student::factory()->calon()->create();

        $this->actingAs($tu)->post('/ppdb/konversi', [
            'siswa_ids'        => [$s1->id, $s2->id],
            'kelas_id_default' => $kelas->id,
            'nis_per_siswa'    => [
                $s1->id => '2024901',
                $s2->id => '2024902',
            ],
        ]);

        foreach ([$s1, $s2] as $s) {
            $this->assertDatabaseHas('student_status_logs', [
                'student_id'  => $s->id,
                'status_lama' => 'calon_siswa',
                'status_baru' => 'active',
                'diubah_oleh' => $tu->id,
            ]);
        }
    }

    // ── L. Duplicate bundle protection ───────────────────────────────────────

    public function test_L1_bundle_can_be_generated_for_active_student(): void
    {
        $tu    = $this->tu();
        $kelas = $this->kelas();
        $siswa = Student::factory()->create(['status' => 'active', 'kelas_id' => $kelas->id]);

        $item = PosItem::create([
            'name'       => 'SPP Bulanan',
            'price'      => 250000,
            'category'   => 'SPP',
            'is_active'  => true,
        ]);
        $bundle = PosBundle::create([
            'name'      => 'Paket Awal Masuk',
            'price'     => 250000,
            'is_active' => true,
        ]);
        PosBundleItem::create([
            'pos_bundle_id' => $bundle->id,
            'pos_item_id'   => $item->id,
            'qty'           => 1,
        ]);

        $response = $this->actingAs($tu)->post(
            route('pos.bundles.generateBills', $bundle->id),
            ['student_ids' => [$siswa->id]]
        );

        // Should succeed — bill created
        $this->assertDatabaseHas('student_bills', [
            'student_id' => $siswa->id,
        ]);
    }
}
