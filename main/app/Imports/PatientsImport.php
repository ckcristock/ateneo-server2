<?php

namespace App\Imports;

use App\Models\Patient;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PatientsImport implements ToModel, WithHeadingRow, SkipsOnError
{
    use Importable, SkipsErrors;

    protected $data;
    protected $duplicateIdentifiers;
    protected $rowData;

    public function __construct($data, &$duplicateIdentifiers)
    {
        $this->data = $data;
        $this->duplicateIdentifiers = &$duplicateIdentifiers;
    }

    public function model(array $row)
    {
        $this->validateHeaders($row);
        $this->rowData = $row;
        return new Patient([
            'type_document_id' => $row['id_tipo_de_documento'],
            'identifier' => $row['numero_de_documento'],
            'surname' => $row['primer_apellido'],
            'secondsurname' => $row['segundo_apellido'],
            'firstname' => $row['primer_nombre'],
            'middlename' => $row['segundo_nombre'],
            'date_of_birth' => $row['fecha_de_nacimiento'],
            'gener' => $row['genero'],
            'level_id' => $row['id_nivel'],
            'address' => $row['direccion'],
            'phone' => $row['telefono'],
            'department_id' => $this->data['department_id'],
            'municipality_id' => $this->data['municipality_id'],
            'eps_id' => $this->data['eps_id'],
            'regimen_id' => $this->data['regimen_id'],
            'state' => $this->data['state'],
        ]);
    }

    public function onError(\Throwable $e)
    {
        if ($e instanceof \Illuminate\Database\QueryException && $e->errorInfo[1] === 1062) {
            $duplicateIdentifier = $this->rowData['numero_de_documento'];
            $this->duplicateIdentifiers[] = $duplicateIdentifier;
            return null;
        }
        throw $e;
    }

    protected function validateHeaders(array $row)
    {
        $requiredHeaders = [
            'id_tipo_de_documento',
            'numero_de_documento',
            'primer_apellido',
            'segundo_apellido',
            'primer_nombre',
            'segundo_nombre',
            'fecha_de_nacimiento',
            'genero',
            'id_nivel',
            'direccion',
            'telefono',
        ];

        $missingHeaders = array_diff($requiredHeaders, array_keys($row));

        if (!empty($missingHeaders)) {
            $missingHeadersStr = implode(', ', $missingHeaders);
            $errorMessage = "Faltan las siguientes cabeceras en el archivo Excel: {$missingHeadersStr}";
            throw new \Exception($errorMessage);
        }
    }
}
