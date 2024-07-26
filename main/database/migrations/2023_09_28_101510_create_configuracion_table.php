<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConfiguracionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Configuracion', function (Blueprint $table) {
            $table->bigIncrements('Id_Configuracion');
            $table->enum('PagoNomina', ['mensual', 'quincenal'])->nullable();
            $table->integer('Extras_Legales')->nullable();
            $table->integer('Festivos_Legales')->nullable();
            $table->integer('Llegadas_Tarde')->nullable();
            $table->integer('Salario_Base')->nullable();
            $table->integer('Subsidio_Transporte')->nullable();
            $table->integer('Maximo_Cotizacion')->nullable();
            $table->time('Hora_Inicio_Dia')->nullable();
            $table->time('Hora_Fin_Dia')->nullable();
            $table->string('Hora_Extra_Diurna', 5)->nullable();
            $table->string('Hora_Extra_Nocturna', 5)->nullable();
            $table->string('Hora_Extra_Domingo_Diurna', 5)->nullable();
            $table->string('Hora_Extra_Domingo_Nocturna', 5)->nullable();
            $table->string('Recargo_Dominical_Diurna', 5)->nullable();
            $table->string('Recargo_Dominical_Nocturna', 5)->nullable();
            $table->string('Recargo_Nocturno', 5)->nullable();
            $table->time('Hora_Inicio_Noche')->nullable();
            $table->time('Hora_Fin_Noche')->nullable();
            $table->longText('Festivos')->nullable();
            $table->longText('Libres')->nullable();
            $table->string('Nombre_Empresa', 200)->nullable();
            $table->string('NIT', 50)->nullable();
            $table->integer('Telefono')->nullable();
            $table->integer('Celular')->nullable();
            $table->string('Correo', 200)->nullable();
            $table->string('Direccion', 200)->nullable();
            $table->text('Condiciones_Comerciales')->nullable();
            $table->integer('Numero_Resolucion')->nullable();
            $table->date('Fecha_Resolucion')->nullable();
            $table->integer('Numero_Inicio_Facturacion')->nullable();
            $table->integer('Numero_Fin_Facturacion')->nullable();
            $table->integer('Consecutivo')->nullable();
            $table->string('Codigo_Formato_Cotizacion', 200)->nullable();
            $table->string('Codigo_Formato_Devolucion_Compras', 200)->nullable();
            $table->string('Codigo_Formato_Devolucion_Comprobante_Egreso', 200)->nullable();
            $table->string('Codigo_Formato_Devolucion_Comprobante_Ingreso', 200)->nullable();
            $table->string('Codigo_Formato_Devolucion_Nota_Credito', 200)->nullable();
            $table->string('Codigo_Formato_Devolucion_Nota_Debito', 200)->nullable();
            $table->string('Codigo_Formato_Devolucion_Remision', 200)->nullable();
            $table->string('Codigo_Formato_Devolucion_Ventas', 200)->nullable();
            $table->string('Codigo_Formato_Homologos', 200)->nullable();
            $table->string('Codigo_Formato_OC', 200)->nullable();
            $table->string('Codigo_Formato_Remision', 200)->nullable();
            $table->string('Codigo_Formato_Traslados', 200)->nullable();
            $table->integer('Comprobante_Egreso')->default(0);
            $table->integer('Comprobante_Ingreso')->default(0);
            $table->integer('Cotizacion')->default(0);
            $table->integer('Devolucion_Compras')->default(0);
            $table->integer('Devolucion_Remision')->default(0);
            $table->integer('Devolucion_Ventas')->default(0);
            $table->integer('Homologo')->default(0);
            $table->string('Nombre_Dian_Cotizacion', 200)->nullable();
            $table->string('Nombre_Dian_Devolucion_Compras', 200)->nullable();
            $table->string('Nombre_Dian_Devolucion_Comprobante_Egreso', 200)->nullable();
            $table->string('Nombre_Dian_Devolucion_Comprobante_Ingreso', 200)->nullable();
            $table->string('Nombre_Dian_Devolucion_Nota_Credito', 200)->nullable();
            $table->string('Nombre_Dian_Devolucion_Nota_Debito', 200)->nullable();
            $table->string('Nombre_Dian_Devolucion_Remision', 200)->nullable();
            $table->string('Nombre_Dian_Devolucion_Ventas', 200)->nullable();
            $table->string('Nombre_Dian_Homologos', 200)->nullable();
            $table->string('Nombre_Dian_OC', 200)->nullable();
            $table->string('Nombre_Dian_Remision', 200)->nullable();
            $table->string('Nombre_Dian_Traslados', 200)->nullable();
            $table->integer('Nota_Credito')->default(0);
            $table->integer('Nota_Debito')->default(0);
            $table->integer('Orden_Compra')->default(0);
            $table->string('Prefijo_Cotizacion', 200)->nullable();
            $table->string('Prefijo_Devolucion_Compras', 200)->nullable();
            $table->string('Prefijo_Devolucion_Comprobante_Egreso', 200)->nullable();
            $table->string('Prefijo_Devolucion_Comprobante_Ingreso', 200)->nullable();
            $table->string('Prefijo_Devolucion_Nota_Credito', 200)->nullable();
            $table->string('Prefijo_Devolucion_Nota_Debito', 200)->nullable();
            $table->string('Prefijo_Devolucion_Remision', 200)->nullable();
            $table->string('Prefijo_Devolucion_Ventas', 200)->nullable();
            $table->string('Prefijo_Homologo', 200)->nullable();
            $table->string('Prefijo_Orden_Compra', 200)->nullable();
            $table->string('Prefijo_Remision', 200)->nullable();
            $table->string('Prefijo_Traslados', 200)->nullable();
            $table->integer('Remision')->default(0);
            $table->integer('Traslado')->default(0);
            $table->string('Logo_Color', 200)->nullable();
            $table->string('Logo_Negro', 200)->nullable();
            $table->string('Logo_Blanco', 200)->nullable();
            $table->text('Resolucion');
            $table->text('Nota_1');
            $table->text('Nota_2');
            $table->string('Cuenta_Bancaria');
            $table->integer('Porcentaje_Rotativo');
            $table->integer('Dias_Capita');
            $table->integer('Dias_Otros_Servicios');
            $table->string('Prefijo_No_Conforme', 100)->default('NOC');
            $table->string('Orden_No_Conforme', 100);
            $table->string('Codigo_Formato_No_Conforme', 100);
            $table->integer('No_Conforme');
            $table->integer('Acta_Recepcion');
            $table->integer('Capita');
            $table->string('Nombre_Dian_Capita', 11);
            $table->string('Prefijo_Capita', 11);
            $table->string('Codigo_Capita', 11);
            $table->integer('Tolerancia_Peso_Global')->nullable();
            $table->integer('Tolerancia_Peso_Individual')->nullable();
            $table->string('Prefijo_Acta_Recepcion', 100)->default('AC');
            $table->string('Prefijo_Acta_Recepcion_Remision', 100)->nullable();
            $table->integer('Acta_Recepcion_Remision')->default(0);
            $table->string('Prefijo_Remision_Antigua', 100)->default('REMV');
            $table->integer('Remision_Antigua')->nullable();
            $table->string('Prefijo_Factura_Venta', 100)->nullable();
            $table->integer('Factura_Venta')->nullable();
            $table->string('Funcionarios_Autorizados_Inventario', 250);
            $table->string('Prefijo_Ajuste_Individual', 60);
            $table->integer('Ajuste_Individual');
            $table->integer('Cierre_Caja')->default(1);
            $table->string('Prefijo_Cierre_Caja', 100)->default('CCP');
            $table->integer('Rotativo')->default(105);
            $table->string('Prefijo_Nota_Credito', 100)->default('NC');
            $table->string('Prefijo_Comprobante_Ingreso', 20)->default('COI');
            $table->string('Prefijo_Comprobante_Egreso', 20)->default('COE');
            $table->string('Prefijo_Nota_Contable', 45)->default('N');
            $table->integer('Nota_Contable')->default(1);
            $table->integer('Meses_Vencimiento')->default(3);
            $table->string('Productos_Pendientes_Libres', 45)->default('No');
            $table->integer('Radicacion');
            $table->string('Prefijo_Radicacion', 60);
            $table->string('Representante_Legal', 100)->nullable();
            $table->integer('Identificacion_Representante')->nullable();
            $table->integer('Id_Departamento')->nullable();
            $table->integer('Id_Municipio')->nullable();
            $table->double('Valor_Unidad_Tributaria', 20, 2);
            $table->double('Base_Retencion_Compras_Reg_Comun', 10, 2);
            $table->double('Base_Retencion_Compras_Reg_Simpl', 10, 2);
            $table->double('Base_Retencion_Compras_Ica', 10, 2);
            $table->double('Base_Retencion_Iva_Reg_Comun', 10, 2);
            $table->integer('Cantidad_Formulada')->default(180);
            $table->enum('Ley_1607', ['si', 'no'])->default('No');
            $table->enum('Ley_1429', ['si', 'no'])->default('No');
            $table->enum('Ley_590', ['si', 'no'])->default('No');
            $table->integer('Salarios_Minimo_Cobro_Incapacidad')->default(3);
            $table->bigInteger('Orden_Compra_Internacional')->default(1);
            $table->string('Prefijo_Orden_Compra_Internacional', 3)->default('OCI');
            $table->integer('Salario_Auxilio_Transporte')->default(2);
            $table->integer('Valor_Uvt')->default(34270);
            $table->integer('Salarios_Minimos_Cobro_Seguridad_Social')->default(4);
            $table->integer('Nit_Sena')->nullable();
            $table->integer('ICBF')->nullable();
            $table->integer('Id_Arl')->nullable();
            $table->string('Prefijo_Acta_Recepcion_Internacional', 5)->default('ARCI');
            $table->bigInteger('Acta_Recepcion_Internacional');
            $table->string('Prefijo_No_Conforme_Internacional', 8)->default('NCI');
            $table->integer('No_Conforme_Internacional');
            $table->string('Prefijo_Parcial_Acta_Internacional', 20)->default('PAI');
            $table->bigInteger('Parcial_Acta_Internacional');
            $table->integer('Max_Item_Remision');
            $table->string('Prefijo_Gasto_Punto', 45)->default('GS');
            $table->integer('Gasto_Punto')->nullable();
            $table->integer('Max_Costo_Nopos')->default(500000);
            $table->string('Codigo_Sede', 100)->default('Prov001912');
            $table->string('Prefijo_Devolucion_Interna', 100)->default('DVI');
            $table->integer('Devolucion_Interna')->default(1);
            $table->bigInteger('Nota_Credito_Global');
            $table->string('Prefijo_Nota_Credito_Global')->nullable();
            $table->string('Prefijo_Rtica_Certificados', 100)->nullable()->comment("los certificados de retencion tienen un calculo diferente para los mismos. este los define");
            $table->string('Prefijo_Prestamo', 20)->nullable();
            $table->integer('Prestamo')->nullable();
            $table->integer('Responsable_Rh')->nullable();
            $table->integer('Responsable_Apro_Nomina_Contabilidad')->nullable();
            $table->integer('Responsable_Apro_Nomina')->nullable();
            $table->integer('Responsable_Apro_Revisor')->nullable();
            $table->integer('Balanza')->default(0);
            $table->integer('company_id')->nullable();
            $table->string('Prefijo_Nomina', 50)->nullable();
            $table->integer('Consecutivo_Nomina')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Configuracion');
    }
}
