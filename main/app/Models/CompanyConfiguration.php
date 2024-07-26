<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyConfiguration extends Model
{
    use HasFactory;

    protected $fillable = ['company_id', 'max_memos_per_employee', 'attention_expiry_days', 'max_item_remision'];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
