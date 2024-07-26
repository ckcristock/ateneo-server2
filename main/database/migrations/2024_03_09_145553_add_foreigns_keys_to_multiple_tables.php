<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('usuario', function (Blueprint $table) {
            $table->string('usuario')->nullable(false)->change();
            $table->foreign('person_id')->references('id')->on('people');
            $table->unsignedBigInteger('person_id')->nullable(false)->change();
            $table->string('password')->nullable(false)->change();
            $table->foreign('board_id')->references('id')->on('boards');
            $table->unsignedBigInteger('board_id')->nullable(true)->change();
        });

        Schema::table('work_contracts', function (Blueprint $table) {
            $table->foreign('position_id')->references('id')->on('positions');
            $table->unsignedBigInteger('position_id')->nullable(true)->change();
            $table->foreign('company_id')->references('id')->on('companies');
            $table->unsignedBigInteger('company_id')->nullable(false)->change();
            $table->foreign('person_id')->references('id')->on('people');
            $table->unsignedBigInteger('person_id')->nullable(false)->change();
            $table->foreign('fixed_turn_id')->references('id')->on('fixed_turns');
            $table->unsignedBigInteger('fixed_turn_id')->nullable(true)->change();
            $table->date('date_of_admission')->nullable(false)->change();
            $table->foreign('work_contract_type_id')->references('id')->on('work_contract_types');
            $table->unsignedBigInteger('work_contract_type_id')->nullable(false)->change();
            $table->dropColumn('rotating_turn_id');
            $table->foreign('contract_term_id')->references('id')->on('contract_terms');
            $table->unsignedBigInteger('contract_term_id')->nullable(true)->change();
        });

        Schema::table('fixed_turns', function (Blueprint $table) {
            $table->foreign('company_id')->references('id')->on('companies');
        });

        Schema::table('rotating_turns', function (Blueprint $table) {
            $table->foreign('company_id')->references('id')->on('companies');
        });

        Schema::table('contract_term_work_contract_type', function (Blueprint $table) {
            $table->foreign('contract_term_id')->references('id')->on('contract_terms');
            $table->unsignedBigInteger('contract_term_id')->nullable(false)->change();
            $table->foreign('work_contract_type_id')->references('id')->on('work_contract_types');
            $table->unsignedBigInteger('work_contract_type_id')->nullable(false)->change();
        });

        Schema::table('dotations', function (Blueprint $table) {
            $table->foreign('person_id')->references('id')->on('people');
            $table->unsignedBigInteger('person_id')->nullable(false)->change();
            $table->foreign('user_id')->references('id')->on('usuario');
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
            $table->foreign('company_id')->references('id')->on('companies');
            $table->unsignedBigInteger('company_id')->nullable(false)->change();
        });

        Schema::table('product_dotation_types', function (Blueprint $table) {
            $table->string('name')->nullable(false)->change();
            $table->foreign('company_id')->references('id')->on('companies');
            $table->unsignedBigInteger('company_id')->nullable(false)->change();
        });

        Schema::table('inventary_dotations', function (Blueprint $table) {
            $table->foreign('product_dotation_type_id')->references('id')->on('product_dotation_types');
            $table->unsignedBigInteger('product_dotation_type_id')->nullable(false)->change();
            $table->string('name')->nullable(false)->change();
            $table->foreign('company_id')->references('id')->on('companies');
            $table->unsignedBigInteger('company_id')->nullable(false)->change();
        });

        Schema::table('dotation_products', function (Blueprint $table) {
            $table->foreign('dotation_id')->references('id')->on('dotations');
            $table->unsignedBigInteger('dotation_id')->nullable(false)->change();
            $table->foreign('inventary_dotation_id')->references('id')->on('inventary_dotations');
            $table->unsignedBigInteger('inventary_dotation_id')->nullable(false)->change();
        });

        Schema::dropIfExists('type_of_memorandum');

        Schema::table('memorandum_types', function (Blueprint $table) {
            $table->string('name')->nullable(false)->change();
            $table->foreign('company_id')->references('id')->on('companies');
            $table->unsignedBigInteger('company_id')->nullable(false)->change();
        });

        Schema::table('attention_calls', function (Blueprint $table) {
            $table->foreign('person_id')->references('id')->on('people');
            $table->foreign('user_id')->references('id')->on('usuario');
        });

        Schema::table('memorandums', function (Blueprint $table) {
            $table->foreign('memorandum_type_id')->references('id')->on('memorandum_types');
            $table->unsignedBigInteger('memorandum_type_id')->nullable(false)->change();
            $table->unsignedBigInteger('person_id')->nullable(false)->change();
            $table->foreign('person_id')->references('id')->on('people');
            $table->unsignedBigInteger('approve_user_id')->nullable(false)->change();
            $table->foreign('approve_user_id')->references('id')->on('usuario');
        });

        Schema::table('person_involveds', function (Blueprint $table) {
            $table->unsignedBigInteger('disciplinary_process_id')->nullable(false)->change();
            $table->foreign('disciplinary_process_id')->references('id')->on('disciplinary_processes');
            $table->renameColumn('fileType', 'file_type');
            $table->unsignedBigInteger('person_id')->nullable(false)->change();
            $table->foreign('person_id')->references('id')->on('people');
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
            $table->foreign('user_id')->references('id')->on('usuario');
        });

        Schema::table('memorandum_involveds', function (Blueprint $table) {
            $table->unsignedBigInteger('memorandum_id')->nullable(false)->change();
            $table->foreign('memorandum_id')->references('id')->on('memorandums');
            $table->unsignedBigInteger('person_involved_id')->nullable(false)->change();
            $table->foreign('person_involved_id')->references('id')->on('person_involveds');
        });

        Schema::table('disciplinary_closure_reasons', function (Blueprint $table) {
            $table->foreign('company_id')->references('id')->on('companies');
        });

        Schema::table('disciplinary_processes', function (Blueprint $table) {
            $table->unsignedBigInteger('person_id')->nullable(false)->change();
            $table->foreign('person_id')->references('id')->on('people');
            $table->unsignedBigInteger('approve_user_id')->nullable(true)->change();
            $table->foreign('approve_user_id')->references('id')->on('usuario');
            $table->text('close_description')->nullable(true)->change();
            $table->unsignedBigInteger('memorandum_id')->nullable(true)->change();
            $table->foreign('memorandum_id')->references('id')->on('memorandums');
        });

        Schema::table('late_arrivals', function (Blueprint $table) {
            $table->unsignedBigInteger('person_id')->nullable(false)->change();
            $table->foreign('person_id')->references('id')->on('people');
            $table->text('justification')->nullable(true)->change();
        });

        Schema::table('accommodations', function (Blueprint $table) {
            $table->foreign('company_id')->references('id')->on('companies');
            $table->unsignedBigInteger('company_id')->nullable(false)->change();
        });

        Schema::table('accommodation_hotel', function (Blueprint $table) {
            $table->foreign('accommodation_id')->references('id')->on('accommodations');
            $table->foreign('hotel_id')->references('id')->on('hotels');
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->string('name')->nullable(false)->change();
            $table->string('tin')->nullable(false)->change();
            $table->unique('tin');
            $table->unsignedBigInteger('city')->nullable(false)->change();
            $table->foreign('city')->references('id')->on('municipalities');
            $table->date('creation_date')->nullable(false)->change();
            $table->dropColumn('email');
            $table->string('logo')->nullable(false)->change();
            $table->string('phone', 12)->nullable(false)->change();
            $table->string('email_contact')->nullable(false)->change();
            $table->unsignedBigInteger('document_type')->nullable(false)->change();
            $table->foreign('document_type')->references('id')->on('document_types');
            $table->string('payment_frequency')->nullable(false)->change();
            $table->string('account_type')->nullable(false)->change();
            $table->string('account_number')->nullable(false)->change();
            $table->unsignedBigInteger('bank_id')->nullable(false)->change();
            $table->foreign('bank_id')->references('id')->on('banks');
            $table->string('payment_method')->nullable(false)->change();
            $table->unsignedDouble('base_salary', 10, 2)->nullable(false)->change();
            $table->string('paid_operator')->nullable(false)->change();
            $table->boolean('law_1429')->nullable(false)->change();
            $table->boolean('law_590')->nullable(false)->change();
            $table->boolean('law_1607')->nullable(false)->change();
            $table->unsignedDouble('transportation_assistance', 10, 2)->nullable(false)->change();
            $table->unsignedBigInteger('arl_id')->nullable(false)->change();
            $table->foreign('arl_id')->references('id')->on('arl');
            $table->time('night_end_time')->nullable(false)->change();
            $table->time('night_start_time')->nullable(false)->change();
            $table->unsignedBigInteger('max_late_arrival')->nullable(false)->change();
            $table->unsignedBigInteger('max_holidays_legal')->nullable(false)->change();
            $table->unsignedBigInteger('max_extras_hours')->nullable(false)->change();
        });

        Schema::table('company_person', function (Blueprint $table) {
            $table->unsignedBigInteger('person_id')->nullable(false)->change();
            $table->foreign('person_id')->references('id')->on('people');
            $table->unsignedBigInteger('company_id')->nullable(false)->change();
            $table->foreign('company_id')->references('id')->on('companies');
        });

        Schema::table('restrictions', function (Blueprint $table) {
            $table->unsignedBigInteger('person_id')->nullable(false)->change();
            $table->foreign('person_id')->references('id')->on('people');
            $table->unsignedBigInteger('company_id')->nullable(false)->change();
            $table->foreign('company_id')->references('id')->on('companies');
        });

        Schema::table('company_restriction', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->nullable(false)->change();
            $table->foreign('company_id')->references('id')->on('companies');
            $table->unsignedBigInteger('restriction_id')->nullable(false)->change();
            $table->foreign('restriction_id')->references('id')->on('restrictions');
        });

        Schema::dropIfExists('type_documents');
        Schema::dropIfExists('Tipo_Documento');
        Schema::dropIfExists('Departamento');
        Schema::dropIfExists('Municipio');
        Schema::dropIfExists('Cliente');
        Schema::dropIfExists('Proveedor');
        Schema::dropIfExists('Contrato_Funcionario');
        Schema::dropIfExists('Funcionario');
        Schema::dropIfExists('Perfil_Funcionario');

        Schema::table('bonifications', function (Blueprint $table) {
            $table->unsignedBigInteger('countable_income_id')->nullable(false)->change();
            $table->foreign('countable_income_id')->references('id')->on('countable_income');
            $table->unsignedBigInteger('work_contract_id')->nullable(false)->change();
            $table->foreign('work_contract_id')->references('id')->on('work_contracts');
        });

        Schema::table('annotations', function (Blueprint $table) {
            $table->foreign('disciplinary_process_id')->references('id')->on('disciplinary_processes');
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
            $table->foreign('user_id')->references('id')->on('usuario');
        });

        Schema::table('menu_permission', function (Blueprint $table) {
            $table->unsignedBigInteger('menu_id')->nullable(false)->change();
            $table->foreign('menu_id')->references('id')->on('menus');
            $table->unsignedBigInteger('permission_id')->nullable(false)->change();
            $table->foreign('permission_id')->references('id')->on('permissions');
        });

        Schema::table('menu_permission_usuario', function (Blueprint $table) {
            $table->unsignedBigInteger('menu_permission_id')->nullable(false)->change();
            $table->foreign('menu_permission_id')->references('id')->on('menu_permission');
            $table->unsignedBigInteger('usuario_id')->nullable(false)->change();
            $table->foreign('usuario_id')->references('id')->on('usuario');
        });

        Schema::table('menus', function (Blueprint $table) {
            $table->foreign('parent_id')->references('id')->on('menus');
        });

        Schema::dropIfExists('permission_usuario');

        Schema::table('groups', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
        });

        Schema::table('groups', function (Blueprint $table) {
            $table->string('name')->nullable(false)->change();
            $table->unsignedBigInteger('company_id')->nullable(false)->change();
            $table->foreign('company_id')->references('id')->on('companies');
        });

        Schema::table('dependencies', function (Blueprint $table) {
            $table->string('name')->nullable(false)->change();
            $table->unsignedBigInteger('group_id')->nullable(false)->change();
            $table->foreign('group_id')->references('id')->on('groups');
        });

        Schema::table('positions', function (Blueprint $table) {
            $table->string('name')->nullable(false)->change();
            $table->unsignedBigInteger('dependency_id')->nullable(false)->change();
            $table->foreign('dependency_id')->references('id')->on('dependencies');
        });

        Schema::table('jobs', function (Blueprint $table) {
            $table->string('code')->nullable(false)->change();
            $table->unsignedBigInteger('company_id')->nullable(false)->change();
            $table->foreign('company_id')->references('id')->on('companies');
            $table->unsignedBigInteger('position_id')->nullable(false)->change();
            $table->foreign('position_id')->references('id')->on('positions');
            $table->unsignedBigInteger('municipality_id')->nullable(false)->change();
            $table->foreign('municipality_id')->references('id')->on('municipalities');
            $table->unsignedBigInteger('visa_type_id')->nullable()->change();
            $table->foreign('visa_type_id')->references('id')->on('visa_types');
            $table->unsignedBigInteger('salary_type_id')->nullable(false)->change();
            $table->foreign('salary_type_id')->references('id')->on('salary_types');
            $table->unsignedBigInteger('work_contract_type_id')->nullable(false)->change();
            $table->foreign('work_contract_type_id')->references('id')->on('work_contract_types');
            $table->unsignedBigInteger('document_type_id')->nullable(false)->change();
            $table->foreign('document_type_id')->references('id')->on('document_types');
            $table->unsignedBigInteger('driving_license')->nullable(true)->change();
            $table->foreign('driving_license')->references('id')->on('driving_licenses');
        });

        Schema::table('history_data_companies', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->nullable(false)->change();
            $table->foreign('company_id')->references('id')->on('companies');
            $table->unsignedBigInteger('person_id')->nullable(false)->change();
            $table->foreign('person_id')->references('id')->on('people');
        });

        Schema::table('applicants', function (Blueprint $table) {
            $table->unsignedBigInteger('job_id')->nullable(false)->change();
            $table->foreign('job_id')->references('id')->on('jobs');
            $table->string('name')->nullable(false)->change();
            $table->string('surname')->nullable(false)->change();
            $table->string('email')->nullable(false)->change();
            $table->string('phone')->nullable(false)->change();
            $table->string('city')->nullable(false)->change();
            $table->string('passport')->nullable(false)->change();
            $table->boolean('visa')->nullable(false)->change();
            $table->unsignedBigInteger('visaType_id')->nullable(true)->change();
            $table->foreign('visaType_id')->references('id')->on('visa_types');
            $table->string('education')->nullable(false)->change();
            $table->unsignedInteger('experience_year')->nullable(false)->change();
            $table->unsignedBigInteger('driving_license_id')->nullable(true)->change();
            $table->foreign('driving_license_id')->references('id')->on('driving_licenses');
        });

        Schema::dropIfExists('people_old');
        Schema::dropIfExists('Funcionario');
        Schema::dropIfExists('Perfil');
        Schema::dropIfExists('Funcionario_Punto');

        Schema::table('rotating_turn_hours', function (Blueprint $table) {
            $table->unsignedBigInteger('person_id')->nullable(false)->change();
            $table->foreign('person_id')->references('id')->on('people');
            $table->unsignedBigInteger('rotating_turn_id')->nullable(false)->change();
            $table->foreign('rotating_turn_id')->references('id')->on('rotating_turns');
        });

        Schema::table('rotating_turn_diaries', function (Blueprint $table) {
            $table->unsignedBigInteger('person_id')->nullable(false)->change();
            $table->foreign('person_id')->references('id')->on('people');
            $table->unsignedBigInteger('rotating_turn_id')->nullable(false)->change();
            $table->foreign('rotating_turn_id')->references('id')->on('rotating_turns');
        });

        Schema::table('fixed_turn_diaries', function (Blueprint $table) {
            $table->unsignedBigInteger('person_id')->nullable(false)->change();
            $table->foreign('person_id')->references('id')->on('people');
            $table->unsignedBigInteger('fixed_turn_id')->nullable(false)->change();
            $table->foreign('fixed_turn_id')->references('id')->on('fixed_turns');
        });

        Schema::table('fixed_turn_hours', function (Blueprint $table) {
            $table->unsignedBigInteger('fixed_turn_id')->nullable(false)->change();
            $table->foreign('fixed_turn_id')->references('id')->on('fixed_turns');
        });

        Schema::dropIfExists('horario_turno_rotativo');

        Schema::table('history_rotating_turn_hours', function (Blueprint $table) {
            $table->unsignedBigInteger('rotating_turn_hour_id')->nullable(false)->change();
            $table->foreign('rotating_turn_hour_id')->references('id')->on('rotating_turn_hours');
            $table->unsignedBigInteger('person_id')->nullable(false)->change();
            $table->foreign('person_id')->references('id')->on('people');
            $table->unsignedBigInteger('company_id')->nullable(false)->change();
            $table->foreign('company_id')->references('id')->on('companies');
        });

        Schema::dropIfExists('Alerta');

        Schema::table('alerts', function (Blueprint $table) {
            $table->unsignedBigInteger('person_id')->nullable(false)->change();
            $table->foreign('person_id')->references('id')->on('people');
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
            $table->foreign('user_id')->references('id')->on('usuario');
        });

        Schema::table('work_certificates', function (Blueprint $table) {
            $table->unsignedBigInteger('person_id')->nullable(false)->change();
            $table->foreign('person_id')->references('id')->on('people');
        });

        Schema::table('layoffs_certificates', function (Blueprint $table) {
            $table->unsignedBigInteger('reason_withdrawal')->nullable(false)->change();
            $table->foreign('reason_withdrawal')->references('id')->on('reason_withdrawals');
            $table->unsignedBigInteger('person_id')->nullable(false)->change();
            $table->foreign('person_id')->references('id')->on('people');
        });

        Schema::table('payroll_factors', function (Blueprint $table) {
            $table->unsignedBigInteger('person_id')->nullable(false)->change();
            $table->foreign('person_id')->references('id')->on('people');
            $table->unsignedBigInteger('disability_leave_id')->nullable(false)->change();
            $table->foreign('disability_leave_id')->references('id')->on('disability_leaves');
        });

        Schema::table('document_payroll_factors', function (Blueprint $table) {
            $table->unsignedBigInteger('payroll_factor_id')->nullable(false)->change();
            $table->foreign('payroll_factor_id')->references('id')->on('payroll_factors');
        });

        Schema::table('rrhh_activity_types', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->nullable(false)->change();
            $table->foreign('company_id')->references('id')->on('companies');
        });

        Schema::table('rrhh_activity_people', function (Blueprint $table) {
            $table->unsignedBigInteger('person_id')->nullable(false)->change();
            $table->foreign('person_id')->references('id')->on('people');
            $table->unsignedBigInteger('rrhh_activity_id')->nullable(false)->change();
            $table->foreign('rrhh_activity_id')->references('id')->on('rrhh_activities');
        });

        Schema::table('rrhh_activities', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
            $table->foreign('user_id')->references('id')->on('usuario');
            $table->unsignedBigInteger('rrhh_activity_type_id')->nullable(false)->change();
            $table->foreign('rrhh_activity_type_id')->references('id')->on('rrhh_activity_types');
            $table->unsignedBigInteger('company_id')->nullable(false)->change();
            $table->foreign('company_id')->references('id')->on('companies');
        });

        Schema::table('Producto_Orden_Compra_Nacional', function (Blueprint $table) {
            $table->dropColumn('Iva');
            $table->dropColumn('Costo');
            $table->unsignedBigInteger('impuesto_id')->nullable(false)->after('Id_Producto');
            $table->foreign('impuesto_id')->references('Id_Impuesto')->on('Impuesto');
            $table->double('Total', 50, 2)->nullable()->change();
            $table->double('Subtotal', 50, 2)->nullable()->after('Id_Producto');
            $table->double('Valor_Iva', 50, 2)->nullable()->after('Id_Producto');
            $table->timestamps();
            $table->unsignedBigInteger('Id_Producto')->nullable(false)->change();
            $table->foreign('Id_Producto')->references('Id_Producto')->on('Producto');
            $table->unsignedBigInteger('Id_Orden_Compra_Nacional')->nullable(false)->change();
            $table->foreign('Id_Orden_Compra_Nacional')->references('Id_Orden_Compra_Nacional')->on('Orden_Compra_Nacional');
        });

        Schema::table('Orden_Compra_Nacional', function (Blueprint $table) {
            $table->dropColumn('Fecha');
            $table->dropColumn('Id_Bodega');
            $table->dropColumn('Id_Punto_Dispensacion');
            $table->dropColumn('Fecha_Creacion_Compra');
            $table->unsignedBigInteger('Identificacion_Funcionario')->nullable(false)->change();
            $table->foreign('Identificacion_Funcionario')->references('id')->on('people');
            $table->unsignedBigInteger('Id_Bodega_Nuevo')->nullable(false)->change();
            $table->foreign('Id_Bodega_Nuevo')->references('Id_Bodega_Nuevo')->on('Bodega_Nuevo');
            $table->unsignedBigInteger('Id_Proveedor')->nullable(false)->change();
            $table->foreign('Id_Proveedor')->references('id')->on('third_parties');
        });

        Schema::table('Actividad_Orden_Compra', function (Blueprint $table) {
            $table->timestamps();
            $table->unsignedBigInteger('Id_Orden_Compra_Nacional')->nullable(false)->change();
            $table->foreign('Id_Orden_Compra_Nacional')->references('Id_Orden_Compra_Nacional')->on('Orden_Compra_Nacional');
            $table->unsignedBigInteger('Id_Acta_Recepcion_Compra')->nullable(true)->change();
            $table->unsignedBigInteger('Identificacion_Funcionario')->nullable(false)->change();
            $table->foreign('Identificacion_Funcionario')->references('id')->on('people');
        });

        Schema::table('account_plan_balances', function ($table) {
            $table->unsignedBigInteger('account_plan_id')->nullable(false)->change();
            $table->foreign('account_plan_id')->references('Id_Plan_Cuentas')->on('Plan_Cuentas');
        });

        Schema::table('Actividades_Dispensacion', function ($table) {
            $table->unsignedBigInteger('Id_Dispensacion')->nullable(false)->change();
            $table->foreign('Id_Dispensacion')->references('Id_Dispensacion')->on('Dispensacion');
            $table->unsignedBigInteger('Identificacion_Funcionario')->nullable(false)->change();
            $table->foreign('Identificacion_Funcionario')->references('id')->on('people');
        });

        Schema::table('Actividad_Ajuste_Individual', function ($table) {
            $table->unsignedBigInteger('Id_Ajuste_Individual')->nullable(false)->change();
            $table->foreign('Id_Ajuste_Individual')->references('Id_Ajuste_Individual')->on('Ajuste_Individual');
            $table->unsignedBigInteger('Identificacion_Funcionario')->nullable(false)->change();
            $table->foreign('Identificacion_Funcionario')->references('id')->on('people');
        });

        Schema::table('Actividad_Auditoria', function ($table) {
            $table->unsignedBigInteger('Id_Auditoria')->nullable(false)->change();
            $table->foreign('Id_Auditoria')->references('Id_Auditoria')->on('Auditoria');
            $table->unsignedBigInteger('Identificacion_Funcionario')->nullable(false)->change();
            $table->foreign('Identificacion_Funcionario')->references('id')->on('people');
        });

        Schema::table('Actividad_Producto', function ($table) {
            $table->unsignedBigInteger('Id_Producto')->nullable(false)->change();
            $table->foreign('Id_Producto')->references('Id_Producto')->on('Producto');
            $table->unsignedBigInteger('Person_Id')->nullable(false)->change();
            $table->foreign('Person_Id')->references('id')->on('people');
        });

        Schema::table('responsibles', function ($table) {
            $table->unsignedBigInteger('company_id')->nullable(false)->change();
            $table->foreign('company_id')->references('id')->on('companies');
            $table->unsignedBigInteger('person_id')->nullable(false)->change();
            $table->foreign('person_id')->references('id')->on('people');
        });

        Schema::dropIfExists('tabla_cruce009');
        Schema::dropIfExists('tabla_cruce10');
        Schema::dropIfExists('tabla_cruce6');
        Schema::dropIfExists('tabla_cruce7');
        Schema::dropIfExists('tabla_cruce8');
        Schema::dropIfExists('tabla_cruce9');

        Schema::table('liquidations', function (Blueprint $table) {
            $table->unsignedBigInteger('person_id')->nullable(false)->change();
            $table->foreign('person_id')->references('id')->on('people');
        });

        Schema::table('preliquidated_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('person_id')->nullable(false)->change();
            $table->foreign('person_id')->references('id')->on('people');
            $table->unsignedBigInteger('person_identifier')->nullable(false)->change();
            $table->foreign('person_identifier')->references('identifier')->on('people');
            $table->unsignedBigInteger('person_work_contract_id')->nullable(false)->change();
            $table->foreign('person_work_contract_id')->references('id')->on('work_contracts');
            $table->unsignedBigInteger('reponsible_id')->nullable(false)->change();
            $table->foreign('reponsible_id')->references('id')->on('people');
            $table->unsignedBigInteger('responsible_identifier')->nullable(false)->change();
            $table->foreign('responsible_identifier')->references('identifier')->on('people');
        });

        Schema::table('company_countable_liquidation', function (Blueprint $table) {
            $table->unsignedBigInteger('countable_liquidation_id')->nullable(true)->change();
            $table->foreign('countable_liquidation_id')->references('id')->on('countable_liquidations');
            $table->unsignedBigInteger('account_plan_id')->nullable(true)->change();
            $table->foreign('account_plan_id')->references('Id_Plan_Cuentas')->on('Plan_Cuentas');
            $table->unsignedBigInteger('company_id')->nullable(true)->change();
            $table->foreign('company_id')->references('id')->on('companies');
        });

        Schema::table('company_countable_salary', function (Blueprint $table) {
            $table->unsignedBigInteger('countable_salary_id')->nullable(true)->change();
            $table->foreign('countable_salary_id')->references('id')->on('countable_salaries');
            $table->unsignedBigInteger('account_plan_id')->nullable(true)->change();
            $table->foreign('account_plan_id')->references('Id_Plan_Cuentas')->on('Plan_Cuentas');
            $table->unsignedBigInteger('account_setoff')->nullable(true)->change();
            $table->foreign('account_setoff')->references('Id_Plan_Cuentas')->on('Plan_Cuentas');
            $table->unsignedBigInteger('company_id')->nullable(true)->change();
            $table->foreign('company_id')->references('id')->on('companies');
        });

        Schema::table('company_payment_configurations', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->nullable(false)->change();
            $table->foreign('company_id')->references('id')->on('companies');
        });

        Schema::table('company_payroll_manager', function (Blueprint $table) {
            $table->unsignedBigInteger('payroll_manager_id')->nullable(false)->change();
            $table->foreign('payroll_manager_id')->references('id')->on('payroll_managers');
            $table->unsignedBigInteger('person_id')->nullable(false)->change();
            $table->foreign('person_id')->references('id')->on('people');
            $table->unsignedBigInteger('company_id')->nullable(false)->change();
            $table->foreign('company_id')->references('id')->on('companies');
        });

        Schema::table('company_payroll_overtime', function (Blueprint $table) {
            $table->unsignedBigInteger('payroll_overtime_id')->nullable(true)->change();
            $table->foreign('payroll_overtime_id')->references('id')->on('payroll_overtimes');
            $table->unsignedBigInteger('account_plan_id')->nullable(true)->change();
            $table->foreign('account_plan_id')->references('Id_Plan_Cuentas')->on('Plan_Cuentas');
            $table->unsignedBigInteger('company_id')->nullable(false)->change();
            $table->foreign('company_id')->references('id')->on('companies');
        });

        Schema::table('company_payroll_parafiscal', function (Blueprint $table) {
            $table->unsignedBigInteger('payroll_parafiscal_id')->nullable(true)->change();
            $table->foreign('payroll_parafiscal_id')->references('id')->on('payroll_parafiscals');
            $table->unsignedBigInteger('account_plan_id')->nullable(true)->change();
            $table->foreign('account_plan_id')->references('Id_Plan_Cuentas')->on('Plan_Cuentas');
            $table->unsignedBigInteger('account_setoff')->nullable(true)->change();
            $table->foreign('account_setoff')->references('Id_Plan_Cuentas')->on('Plan_Cuentas');
            $table->unsignedBigInteger('company_id')->nullable(true)->change();
            $table->foreign('company_id')->references('id')->on('companies');
        });

        Schema::table('company_payroll_risks_arl', function (Blueprint $table) {
            $table->unsignedBigInteger('payroll_risks_arl_id')->nullable(true)->change();
            $table->foreign('payroll_risks_arl_id')->references('id')->on('payroll_risks_arls');
            $table->unsignedBigInteger('account_plan_id')->nullable(true)->change();
            $table->foreign('account_plan_id')->references('Id_Plan_Cuentas')->on('Plan_Cuentas');
            $table->unsignedBigInteger('account_setoff')->nullable(true)->change();
            $table->foreign('account_setoff')->references('Id_Plan_Cuentas')->on('Plan_Cuentas');
            $table->unsignedBigInteger('company_id')->nullable(true)->change();
            $table->foreign('company_id')->references('id')->on('companies');
        });

        Schema::table('company_payroll_social_security_company', function (Blueprint $table) {
            $table->unsignedBigInteger('payroll_social_security_company_id')->nullable(true)->change();
            $table->foreign(
                'payroll_social_security_company_id',
                'fk_payroll_social_security_company'
            )->references('id')->on('payroll_social_security_companies');
            $table->unsignedBigInteger('account_plan_id')->nullable(true)->change();
            $table->foreign('account_plan_id')->references('Id_Plan_Cuentas')->on('Plan_Cuentas');
            $table->unsignedBigInteger('account_setoff')->nullable(true)->change();
            $table->foreign('account_setoff')->references('Id_Plan_Cuentas')->on('Plan_Cuentas');
            $table->unsignedBigInteger('company_id')->nullable(true)->change();
            $table->foreign('company_id')->references('id')->on('companies');
        });

        Schema::table('company_payroll_social_security_person', function (Blueprint $table) {
            $table->unsignedBigInteger('payroll_social_security_person_id')->nullable(true)->change();
            $table->foreign(
                'payroll_social_security_person_id',
                'fk_payroll_social_security_person'
            )->references('id')->on('payroll_social_security_people');
            $table->unsignedBigInteger('account_plan_id')->nullable(true)->change();
            $table->foreign('account_plan_id')->references('Id_Plan_Cuentas')->on('Plan_Cuentas');
            $table->unsignedBigInteger('account_setoff')->nullable(true)->change();
            $table->foreign('account_setoff')->references('Id_Plan_Cuentas')->on('Plan_Cuentas');
            $table->unsignedBigInteger('company_id')->nullable(true)->change();
            $table->foreign('company_id')->references('id')->on('companies');
        });

        Schema::dropIfExists('cruce_augusto');
        Schema::dropIfExists('contractss');
        Schema::dropIfExists('Categoria');
        Schema::dropIfExists('Bodega');

        Schema::table('Categoria_Nueva', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->nullable(false)->change();
            $table->foreign('company_id')->references('id')->on('companies');
            $table->string('Nombre')->nullable(false)->change();
            $table->dropColumn('Departamento');
            $table->dropColumn('Municipio');
            $table->dropColumn('Direccion');
            $table->dropColumn('Telefono');
        });

        Schema::table('Subcategoria', function (Blueprint $table) {
            $table->unsignedBigInteger('Id_Categoria_Nueva')->nullable(false)->change();
            $table->foreign('Id_Categoria_Nueva')->references('Id_Categoria_Nueva')->on('Categoria_Nueva');
        });

        Schema::table('Bodega_Nuevo_Categoria_Nueva', function (Blueprint $table) {
            $table->unsignedBigInteger('Id_Categoria_Nueva')->nullable(false)->change();
            $table->foreign('Id_Categoria_Nueva')->references('Id_Categoria_Nueva')->on('Categoria_Nueva');
            $table->unsignedBigInteger('Id_Bodega_Nuevo')->nullable(false)->change();
            $table->foreign('Id_Bodega_Nuevo')->references('Id_Bodega_Nuevo')->on('Bodega_Nuevo');
        });

        Schema::table('category_variables', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id')->nullable(false)->change();
            $table->foreign('category_id')->references('Id_Categoria_Nueva')->on('Categoria_Nueva');
            $table->string('label')->nullable(false)->change();
        });

        Schema::table('subcategory_variables', function (Blueprint $table) {
            $table->unsignedBigInteger('subcategory_id')->nullable(false)->change();
            $table->foreign('subcategory_id')->references('Id_Subcategoria')->on('Subcategoria');
            $table->string('label')->nullable(false)->change();
        });

        Schema::table('ciiu_codes', function (Blueprint $table) {
            $table->unsignedBigInteger('value')->nullable(false)->change();
            $table->primary('value');
        });

        Schema::table('third_parties', function (Blueprint $table) {
            $table->unsignedBigInteger('document_type')->nullable(false)->change();
            $table->foreign('document_type')->references('id')->on('document_types');
            $table->string('nit')->nullable(false)->unique()->change();
            DB::statement("ALTER TABLE third_parties MODIFY COLUMN person_type ENUM('natural', 'juridico') NOT NULL");
            $table->string('dian_address')->nullable(false)->change();
            $table->string('address_one')->nullable(false)->change();
            $table->string('address_two')->nullable(false)->change();
            $table->string('address_three')->nullable(true)->change();
            $table->string('address_four')->nullable(false)->change();
            $table->string('cod_dian_address')->nullable(false)->change();
            $table->string('dian_address')->nullable(false)->change();
            $table->unsignedBigInteger('department_id')->nullable(false)->change();
            $table->foreign('department_id')->references('id')->on('departments');
            $table->unsignedBigInteger('municipality_id')->nullable(false)->change();
            $table->foreign('municipality_id')->references('id')->on('municipalities');
            $table->unsignedBigInteger('zone_id')->nullable(true)->change();
            $table->foreign('zone_id')->references('id')->on('zones');
            $table->string('email')->nullable(false)->unique()->change();
            $table->unsignedBigInteger('winning_list_id')->nullable(true)->change();
            $table->foreign('winning_list_id')->references('id')->on('winning_lists');
            $table->unsignedBigInteger('regime')->nullable(true)->change();
            $table->foreign('regime')->references('id')->on('regimen_types');
            $table->unsignedBigInteger('ciiu_code_id')->nullable(true)->change();
            $table->foreign('ciiu_code_id')->references('value')->on('ciiu_codes');
            $table->unsignedBigInteger('reteica_account_id')->nullable(true)->change();
            $table->foreign('reteica_account_id')->references('Id_Plan_Cuentas')->on('Plan_Cuentas');
            $table->unsignedBigInteger('retefuente_account_id')->nullable(true)->change();
            $table->foreign('retefuente_account_id')->references('Id_Plan_Cuentas')->on('Plan_Cuentas');
            $table->unsignedBigInteger('reteiva_account_id')->nullable(true)->change();
            $table->foreign('reteiva_account_id')->references('Id_Plan_Cuentas')->on('Plan_Cuentas');
            $table->double('assigned_space', 8, 2)->nullable(true)->change();
            $table->double('discount_prompt_payment', 8, 2)->nullable(true)->change();
            $table->unsignedInteger('discount_days')->nullable(true)->change();
            $table->string('rut')->nullable(true)->change();
            $table->unsignedBigInteger('fiscal_responsibility')->nullable(true)->change();
            $table->foreign('fiscal_responsibility')->references('id')->on('fiscal_responsibilities');
            $table->unsignedBigInteger('country_id')->nullable(false)->change();
            $table->foreign('country_id')->references('id')->on('countries');
            $table->dropColumn('location');
            $table->dropColumn('city_id');
            $table->unsignedBigInteger('company_id')->nullable(false)->change();
            $table->foreign('company_id')->references('id')->on('companies');
        });

        Schema::table('third_party_people', function (Blueprint $table) {
            $table->string('n_document')->nullable(true)->unique()->change();
            $table->string('email')->nullable(false)->unique()->change();
            $table->unsignedBigInteger('laboratory_id')->nullable(true)->change();
            $table->foreign('laboratory_id')->references('id')->on('laboratories');
            $table->unsignedBigInteger('third_party_id')->nullable(true)->change();
            $table->foreign('third_party_id')->references('id')->on('third_parties');
            $table->unsignedBigInteger('company_id')->nullable(false)->change();
            $table->foreign('company_id')->references('id')->on('companies');
        });

        Schema::table('third_party_fields', function (Blueprint $table) {
            $table->string('label')->nullable(false)->change();
            $table->string('name')->nullable(false)->change();
            $table->string('type')->nullable(false)->change();
            DB::statement("ALTER TABLE third_party_fields MODIFY COLUMN required ENUM('si', 'no') NOT NULL");
            $table->unsignedInteger('length')->nullable(false)->change();
            $table->unsignedBigInteger('company_id')->nullable(false)->change();
            $table->foreign('company_id')->references('id')->on('companies');
        });

        Schema::dropIfExists('Codigo_Ciiu');

        Schema::table('Comprobante_Consecutivo', function (Blueprint $table) {
            $table->string('Tipo')->nullable(false)->change();
            $table->string('Prefijo')->nullable(false)->change();
            $table->boolean('Anio')->nullable(false)->default(0)->change();
            $table->boolean('Mes')->nullable(false)->default(0)->change();
            $table->boolean('Dia')->nullable(false)->default(0)->change();
            $table->boolean('city')->nullable(false)->default(0)->change();
            $table->unsignedInteger('longitud')->nullable(false)->default(0)->change();
            $table->unsignedBigInteger('Consecutivo')->nullable(false)->default(0)->change();
            $table->unsignedBigInteger('company_id')->nullable(false)->change();
            $table->foreign('company_id')->references('id')->on('companies');
            $table->boolean('editable')->nullable(false)->default(1)->change();
        });

        Schema::dropIfExists('Configuracion');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usuario', function (Blueprint $table) {
            //
        });
    }
};
