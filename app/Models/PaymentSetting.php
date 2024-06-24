<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use PhpOffice\PhpSpreadsheet\Calculation\DateTimeExcel\Current;

class PaymentSetting extends Model
{
    use HasFactory;
    function currency(){
        return $this->belongsTo(Currency::class);
    }
}
