<?php
/**
 * Manual import logic test — run with: php scripts/test_import_logic.php
 * Tests: BOM strip, delimiter auto-detect, case-insensitive kelas, dup NIS/NISN, wrong kelas.
 */
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use League\Csv\Reader;
use App\Models\Kelas;
use App\Models\Student;

function runImport(string $path, string $label): void
{
    echo PHP_EOL . "=== $label ===" . PHP_EOL;

    $raw = file_get_contents($path);
    if (str_starts_with($raw, "\xEF\xBB\xBF")) {
        echo '[BOM stripped]' . PHP_EOL;
        $raw = substr($raw, 3);
        file_put_contents($path, $raw);
    }

    $firstLine = strtok($raw, "\n");
    $delimiter = substr_count($firstLine, ';') > substr_count($firstLine, ',') ? ';' : ',';
    echo "Delimiter: '$delimiter'" . PHP_EOL;

    $csv = Reader::createFromPath($path, 'r');
    $csv->setDelimiter($delimiter);
    $csv->setHeaderOffset(0);

    $headers  = $csv->getHeader();
    $required = ['nis', 'name', 'nama_kelas'];
    $missing  = array_diff($required, $headers);
    if ($missing) {
        echo 'HEADER ERROR: missing ' . implode(', ', $missing) . PHP_EOL;
        echo 'Found headers: ' . implode(', ', $headers) . PHP_EOL;
        return;
    }

    $kelasRaw = Kelas::aktif()->pluck('id', 'nama_kelas')->toArray();
    $kelasMap = [];
    foreach ($kelasRaw as $nama => $id) {
        $kelasMap[mb_strtolower(trim($nama))] = ['id' => $id, 'original' => $nama];
    }
    $availableKelas = implode(', ', array_column($kelasMap, 'original'));

    $inserted = 0;
    $skipped  = 0;
    $errors   = [];

    foreach ($csv->getRecords() as $offset => $row) {
        $row       = array_map('trim', $row);
        $lineNo    = $offset + 2;
        $rowErrors = [];

        if (empty($row['nis']))        $rowErrors[] = 'kolom nis kosong';
        if (empty($row['name']))       $rowErrors[] = 'kolom name kosong';
        if (empty($row['nama_kelas'])) $rowErrors[] = 'kolom nama_kelas kosong';

        $kelasId = null;
        if (!empty($row['nama_kelas'])) {
            $key = mb_strtolower(trim($row['nama_kelas']));
            if (isset($kelasMap[$key])) {
                $kelasId = $kelasMap[$key]['id'];
            } else {
                $rowErrors[] = "kelas '{$row['nama_kelas']}' tidak ditemukan (tersedia: $availableKelas)";
            }
        }

        if (!empty($row['nis']) && Student::where('nis', $row['nis'])->exists())
            $rowErrors[] = "NIS '{$row['nis']}' sudah terdaftar";

        if (!empty($row['nisn']) && Student::where('nisn', $row['nisn'])->exists())
            $rowErrors[] = "NISN '{$row['nisn']}' sudah terdaftar";

        if ($rowErrors) {
            $errors[] = "Baris $lineNo ({$row['name']}): " . implode('; ', $rowErrors);
            $skipped++;
        } else {
            $inserted++;
        }
    }

    echo "Result: $inserted inserted, $skipped skipped" . PHP_EOL;
    foreach ($errors as $e) echo "  ✗ $e" . PHP_EOL;
    if (!$errors) echo "  ✓ No errors" . PHP_EOL;
}

// ── Setup test CSV files ──────────────────────────────────────────────────────

// Test 1: Normal CSV — 3 valid rows, one uses lowercase kelas name
$tmp1 = storage_path('app/test_normal.csv');
$f = fopen($tmp1, 'w');
fputcsv($f, ['nis','nisn','name','gender','nama_kelas','birth_place','birth_date','address','agama','tahun_masuk','parent_phone']);
fputcsv($f, ['2024001','1111111111','Budi Santoso','L','X - Umum','Jakarta','2008-01-01','Jl. A','Islam','2024','081111']);
fputcsv($f, ['2024002','2222222222','Siti Rahayu','P','x - umum','Bandung','2008-02-02','Jl. B','Islam','2024','082222']); // lowercase kelas
fputcsv($f, ['2024003','','Andi Pratama','L','XII-RPL','Surabaya','2008-03-03','Jl. C','Islam','2024','083333']); // no nisn
fclose($f);

// Test 2: BOM CSV — simulates Excel save-as CSV
$tmp2 = storage_path('app/test_bom.csv');
file_put_contents($tmp2,
    "\xEF\xBB\xBF" . "nis,nisn,name,gender,nama_kelas\n" .
    "2024099,9999999999,BOM Test,L,X - Umum\n"
);

// Test 3: Semicolon delimiter — Excel regional (Europe/Indonesia some setups)
$tmp3 = storage_path('app/test_semicolon.csv');
file_put_contents($tmp3, "nis;nisn;name;gender;nama_kelas\n2024088;8888888888;Semi Test;L;XII-RPL\n");

// Test 4: All error types
// Seed an existing student to test duplicates
Student::firstOrCreate(
    ['nis' => '9000001'],
    ['name' => 'Existing Student', 'status' => 'active', 'nisn' => '9999999991']
);
$tmp4 = storage_path('app/test_errors.csv');
$f = fopen($tmp4, 'w');
fputcsv($f, ['nis','nisn','name','gender','nama_kelas']);
fputcsv($f, ['9000001','3333333333','Duplikat NIS','L','X - Umum']);   // dup NIS
fputcsv($f, ['9000099','9999999991','Duplikat NISN','L','X - Umum']);  // dup NISN
fputcsv($f, ['9000098','7777777777','Kelas Salah','L','X - SALAH']);   // wrong kelas
fputcsv($f, ['','','Empty Fields','L','X - Umum']);                    // empty nis+name
fclose($f);

// ── Run all tests ─────────────────────────────────────────────────────────────
runImport($tmp1, 'Test 1: Normal CSV (3 rows, lowercase kelas variant)');
runImport($tmp2, 'Test 2: BOM CSV (Excel save-as)');
runImport($tmp3, 'Test 3: Semicolon delimiter');
runImport($tmp4, 'Test 4: All error types (dup NIS, dup NISN, wrong kelas, empty fields)');

// Cleanup
foreach ([$tmp1, $tmp2, $tmp3, $tmp4] as $f) @unlink($f);
// Remove seeded test student
Student::where('nis', '9000001')->delete();

echo PHP_EOL . "Done." . PHP_EOL;
