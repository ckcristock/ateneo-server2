<?php

// namespace Database\Seeders;

use App\Models\ReasonWithdrawal;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
class ReasonWithdrawalsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Reiniciar el contador de IDs
        ReasonWithdrawal::truncate();

        $data = [
            [
                'id' => 1,
                'name' => 'Compra de vivienda nueva o usada',
                'requirements' => "1. Formato de retiro de las cesantías.\n2. Promesa de compraventa",
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'Construcción o mejoramiento de vivienda',
                'requirements' => "1. Formato de retiro de las cesantías.\n2. Contrato civil de obra con el que se construirá o remodelará la vivienda.\n3. Tres cotizaciones.\n4. Certificado de libertad y tradición, no superior a 30 días de expedición.\n5. Fotocopia de la cédula del empelado.\n6. Certificado de extracto o certificado de saldos según el fondo que corresponda.",
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'name' => 'Pago o amortización de créditos hipotecarios',
                'requirements' => "1. Formato de retiro de las cesantías.\n2. Certificado bancario de la deuda o extracto bancario.\n3. Fotocopia de la cédula del empleado.\n4. Certificado de libertad y tradición no superior a 30 días de expedición.\n5. Certificado de extracto o certificado de saldos según del fondo de cesantías.",
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'name' => 'Financiar educación',
                'requirements' => "USOS: \n1. Para estudios superiores\n2. Para estudios de programas técnicos conducentes a certificados de aptitud ocupacional, debidamente acreditados, que impartan educación para el Trabajo y el Desarrollo Humano del empleado.\n3. Para pago de deudas al ICETEX.\n4. Para pago o compra de seguros educativos.\nBENEFICIARIOS: \n1. El trabajador.\n2. Cónyuge, compañero o compañera permanente.\n3. Hijos.\nREQUISITOS: \n1. Formato de retiro de las cesantías diligenciado.\n2. Copia del recibo de pago de la institución educativa o certificación de la institución educativa donde conste: nombre del estudiante, nombre de la carrera, valor de la matrícula o semestre a realizar.\n3. Resolución de aprobación donde indique que es una entidad de educación para el trabajo y el desarrollo humano.\n4. Estado de cuenta que emite el ICETEX o movimiento de cuenta en la cual se refleje el nombre del estudiante y valor de la deuda.",
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 5,
                'name' => 'Estudios en el exterior',
                'requirements' => "1. Solicitud de retiro de cesantías debidamente diligenciada.\n2. Documento de identificación del trabajador.\n3. La institución de educación superior en el exterior debe expedir una certificación en donde conste:\n3.a Admisión al programa educativo del beneficiario.\n3.b Área específica de estudio.\n3.c Recibo original pendiente de pago de la matrícula.\n3.d Certificación original emitida por el ente estatal que le corresponda, en el país sede de la institución de educación superior a la que se va a ingresar, en donde se acredite que la institución es técnica profesional, institución universitaria, escuela tecnológica o universidad.\n3.e Los documentos que estén en otro idioma deben tener traducción oficial.",
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 6,
                'name' => 'Compra de acciones del estado',
                'requirements' => "1. Formulario de solicitud de retiro de cesantías.\n2. Comprobante de adjudicación de acciones indicando el valor y los datos del vendedor o comisionista de bolsa según el caso.\n3. Documento de identificación original del consumidor financiero o apoderado.\n4. Copia del documento de identificación del trabajador.",
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 7,
                'name' => 'Terminación del contrato de trabajo',
                'requirements' => "1. Solicitud de retiro de las cesantías.\n2. Constancia o certificación de la terminación del contrato de trabajo.\n3. Documento de identidad del trabajador.",
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 8,
                'name' => 'Cambio a salario integral',
                'requirements' => "1. Solicitud de retiro de cesantías.\n2. Autorización del empleador para el retiro de las cesantías donde se certifique el cambio a salario integral.\n3. Copia del documento de identidad del trabajador.",
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 9,
                'name' => 'Fallecimiento',
                'requirements' => "1. Solicitud de retiro de cesantías.\n2. Documento de identificación del afiliado fallecido\n3. Documento de identificación de los beneficiarios que reclaman.\n4. Registro civil de defunción.\n5. Documentos que acrediten la calidad de beneficiario:\n5.a Cónyuges: registro civil de matrimonio.\n5.b Compañeros permanentes: dos declaraciones extrajuicio hechas ante notario, donde se especifique el tiempo y lugar de convivencia.\n5.c Padres: registro civil de nacimiento del afiliado.\n6. Carta del empleador notificando el fallecimiento del Afiliado y los nombres de sus beneficiarios.\n7. Dos edictos en original o copia según lo dispuesto por el Artículo 212 del CST.",
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Insertar los datos en la tabla ReasonWithdrawal
        ReasonWithdrawal::insert($data);
    }
}
