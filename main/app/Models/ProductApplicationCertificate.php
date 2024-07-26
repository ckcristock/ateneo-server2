<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductApplicationCertificate extends Model
{
    protected $table = 'product_application_certificate';

    protected $fillable = [
        "application_certificate_id",
        "product_id",
        "amount",
        "lote",
        "file1",
        "file2"
    ];
}
