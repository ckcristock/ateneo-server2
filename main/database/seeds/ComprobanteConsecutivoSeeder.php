<?php

// namespace Database\Seeders;

use App\Models\Company;
use App\Models\ComprobanteConsecutivo;
use Illuminate\Database\Seeder;

class ComprobanteConsecutivoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtén todos los registros de la tabla company
        $companies = Company::all();

        $comprobantesConsecutivos = [
            [
                'tipo' => 'Nómina global',
                'prefijo' => 'NOM',
                'format_code' => 'V1',
                'table_name' => 'payroll_payments',
                'editable' => 1,
                'Anio' => 0,
                'Mes' => 0,
                'Dia' => 0,
                'city' => 0,
                'longitud' => 0,
                'Consecutivo' => 0,
            ],
            [
                'tipo' => 'Nómina personal',
                'prefijo' => 'NE',
                'format_code' => 'V1',
                'table_name' => 'person_payroll_payments',
                'editable' => 0,
                'Anio' => 0,
                'Mes' => 0,
                'Dia' => 0,
                'city' => 0,
                'longitud' => 0,
                'Consecutivo' => 0,
            ],
            [
                'tipo' => 'Activo',
                'prefijo' => 'ACT',
                'format_code' => 'V1',
                'table_name' => 'Activo_Fijo',
                'editable' => 1,
                'Anio' => 0,
                'Mes' => 0,
                'Dia' => 0,
                'city' => 0,
                'longitud' => 0,
                'Consecutivo' => 0,
            ],
            [
                'tipo' => 'Nota',
                'prefijo' => 'NOT',
                'format_code' => 'V1',
                'table_name' => 'Documento_Contable',
                'editable' => 1,
                'Anio' => 0,
                'Mes' => 0,
                'Dia' => 0,
                'city' => 0,
                'longitud' => 0,
                'Consecutivo' => 0,
            ],
            [
                'tipo' => 'Ingreso',
                'prefijo' => 'ING',
                'format_code' => 'V1',
                'table_name' => 'Comprobante_Ingreso',
                'editable' => 1,
                'Anio' => 0,
                'Mes' => 0,
                'Dia' => 0,
                'city' => 0,
                'longitud' => 0,
                'Consecutivo' => 0,
            ],
            [
                'tipo' => 'Egreso',
                'prefijo' => 'EGR',
                'format_code' => 'V1',
                'table_name' => null,
                'editable' => 1,
                'Anio' => 0,
                'Mes' => 0,
                'Dia' => 0,
                'city' => 0,
                'longitud' => 0,
                'Consecutivo' => 0,
            ],
            [
                'tipo' => 'Cierre_Anio',
                'prefijo' => 'CIE',
                'format_code' => 'V1',
                'table_name' => null,
                'editable' => 1,
                'Anio' => 0,
                'Mes' => 0,
                'Dia' => 0,
                'city' => 0,
                'longitud' => 0,
                'Consecutivo' => 0,
            ],
            [
                'tipo' => 'Orden de compra',
                'prefijo' => 'OC',
                'format_code' => 'V1',
                'table_name' => 'Orden_Compra_Nacional',
                'editable' => 1,
                'Anio' => 0,
                'Mes' => 0,
                'Dia' => 0,
                'city' => 0,
                'longitud' => 0,
                'Consecutivo' => 0,
            ],
            [
                'tipo' => 'Solicitud de compra',
                'prefijo' => 'SOL',
                'format_code' => 'V1',
                'table_name' => 'purchase_requests',
                'editable' => 1,
                'Anio' => 0,
                'Mes' => 0,
                'Dia' => 0,
                'city' => 0,
                'longitud' => 0,
                'Consecutivo' => 0,
            ],
            [
                'tipo' => 'Cotización solicitud de compra',
                'prefijo' => 'COS',
                'format_code' => 'V1',
                'table_name' => 'quotation_purchase_requests',
                'editable' => 1,
                'Anio' => 0,
                'Mes' => 0,
                'Dia' => 0,
                'city' => 0,
                'longitud' => 0,
                'Consecutivo' => 0,
            ],
            [
                'tipo' => 'Proceso disciplinario',
                'prefijo' => 'PRO',
                'format_code' => 'V1',
                'table_name' => 'disciplinary_processes',
                'editable' => 1,
                'Anio' => 0,
                'Mes' => 0,
                'Dia' => 0,
                'city' => 0,
                'longitud' => 0,
                'Consecutivo' => 0,
            ],
            [
                'tipo' => 'Acta de recepción',
                'prefijo' => 'ACT',
                'format_code' => 'V1',
                'table_name' => 'acta_recepcion',
                'editable' => 1,
                'Anio' => 0,
                'Mes' => 0,
                'Dia' => 0,
                'city' => 0,
                'longitud' => 0,
                'Consecutivo' => 0,
            ],
            [
                'tipo' => 'Entrega dotación',
                'prefijo' => 'DOT',
                'format_code' => 'V1',
                'table_name' => 'dotations',
                'editable' => 1,
                'Anio' => 0,
                'Mes' => 0,
                'Dia' => 0,
                'city' => 0,
                'longitud' => 0,
                'Consecutivo' => 0,
            ],
            [
                'tipo' => 'Inventario físico',
                'prefijo' => 'INVF',
                'format_code' => 'V1',
                'table_name' => 'Doc_Inventario_Fisico',
                'editable' => 1,
                'Anio' => 0,
                'Mes' => 0,
                'Dia' => 0,
                'city' => 0,
                'longitud' => 0,
                'Consecutivo' => 0,
            ],
            [
                'tipo' => 'Ajuste individual',
                'prefijo' => 'AI',
                'format_code' => 'V1',
                'table_name' => 'Ajuste_Individual',
                'editable' => 1,
                'Anio' => 0,
                'Mes' => 0,
                'Dia' => 0,
                'city' => 0,
                'longitud' => 0,
                'Consecutivo' => 0,
            ],
        ];

        // Inserta los datos de comprobantes_consecutivos para cada registro de company
        foreach ($companies as $company) {
            foreach ($comprobantesConsecutivos as $comprobante) {
                $comprobanteConsecutivo = new ComprobanteConsecutivo($comprobante);
                $comprobanteConsecutivo->company_id = $company->id;
                $comprobanteConsecutivo->save();
            }
        }
    }
}
