<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillItem extends Model
{
    // 👇 TAMBAHIN BARIS INI BIAR GAK ERROR
    protected $guarded = []; 
    
    // Atau kalau mau pake fillable (pilih salah satu), isinya harus lengkap:
    // protected $fillable = ['student_bill_id', 'pos_bundle_id', 'item_name', 'quantity', 'price', 'subtotal'];

    // Relasi: Item ini nempel ke Tagihan mana?
    public function bill()
    {
        return $this->belongsTo(StudentBill::class, 'student_bill_id');
    }
    
    // Relasi: (Opsional) Kalau item ini adalah barang POS
    public function product()
    {
        return $this->belongsTo(PosItem::class, 'pos_item_id');
    }
}