<?php

namespace App\Imports;

use App\Models\DocumentoContable;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;

class DocumentoContableImport implements ToModel
{
    /**
     * @param array $row
     *
     * @return \App\Models\DocumentoContable
     */
    public function model(array $row)
    {
        return new DocumentoContable([
            'name' => $row[0],
            'email' => $row[1],
            'password' => Hash::make($row[2]),
        ]);
    }
}
