<?php

use App\Models\Menu;
use App\Models\MenuPermission;
use App\Models\Permission;
use App\Models\MenuPermissionUsuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Permission::truncate();
        Menu::truncate();
        MenuPermission::truncate();
        MenuPermissionUsuario::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $show = Permission::create([
            'description' => 'Ver en el menú',
            'name' => 'show',
            'public_name' => 'Ver',
        ]);

        $receiveCalls = Permission::create([
            'description' => 'El funcionario puede recibir llamadas o atiente presencialmente',
            'name' => 'receive_calls',
            'public_name' => 'Recibir llamadas',
        ]);

        $showAllData = Permission::create([
            'description' => 'El funcionario puede ver todos los datos',
            'name' => 'show_all_data',
            'public_name' => 'Listar todos',
        ]);

        $approveProductCategories = Permission::create([
            'description' => 'Aprobar categorias producto',
            'name' => 'approve_product_categories',
            'public_name' => 'Aprobar categorías de productos',
        ]);

        $allCompanies = Permission::create([
            'description' => 'Ver todas las empresas',
            'name' => 'all_companies',
            'public_name' => 'Ver todas (empresas)',
        ]);

        $add = Permission::create([
            'description' => 'Crear nuevo registro',
            'name' => 'add',
            'public_name' => 'Crear',
        ]);

        $edit = Permission::create([
            'description' => 'Editar registro existente',
            'name' => 'edit',
            'public_name' => 'Editar',
        ]);

        $approve = Permission::create([
            'description' => 'Aprobar',
            'name' => 'approve',
            'public_name' => 'Aprobar',
        ]);

        $tablero = Menu::create([
            'name' => 'Tablero',
            'icon' => 'fas fa-columns',
            'link' => '/',
        ]);

        MenuPermission::create([
            'menu_id' => $tablero->id,
            'permission_id' => $show->id
        ]);

        /* INICIO AGENDAMIENTO */

        $agendamiento = Menu::create([
            'name' => 'Agendamiendo',
            'icon' => 'fas fa-calendar-alt',
        ]);

        MenuPermission::create([
            'menu_id' => $agendamiento->id,
            'permission_id' => $show->id
        ]);

        $abrirAgendas = Menu::create([
            'name' => 'Abir agendas',
            'parent_id' => $agendamiento->id,
            'link' => '/agendamiento/abrir-agendas'
        ]);

        MenuPermission::create([
            'menu_id' => $abrirAgendas->id,
            'permission_id' => $show->id
        ]);

        $asignacionCitas = Menu::create([
            'name' => 'Asignación de citas',
            'parent_id' => $agendamiento->id,
            'link' => '/agendamiento/asignacion-citas'
        ]);

        MenuPermission::create([
            'menu_id' => $asignacionCitas->id,
            'permission_id' => $show->id
        ]);

        MenuPermission::create([
            'menu_id' => $asignacionCitas->id,
            'permission_id' => $receiveCalls->id
        ]);

        $listaActas = Menu::create([
            'name' => 'Lista de actas',
            'parent_id' => $agendamiento->id,
            'link' => '/agendamiento/listaactaaplicacion'
        ]);

        MenuPermission::create([
            'menu_id' => $listaActas->id,
            'permission_id' => $show->id
        ]);

        $listaAgendas = Menu::create([
            'name' => 'Lista de agendas',
            'parent_id' => $agendamiento->id,
            'link' => '/agendamiento/agendas'
        ]);

        MenuPermission::create([
            'menu_id' => $listaAgendas->id,
            'permission_id' => $show->id
        ]);

        $listaEspera = Menu::create([
            'name' => 'Lista de espera',
            'parent_id' => $agendamiento->id,
            'link' => '/agendamiento/lista-espera'
        ]);

        MenuPermission::create([
            'menu_id' => $listaEspera->id,
            'permission_id' => $show->id
        ]);

        $listaTrabajo = Menu::create([
            'name' => 'Lista de trabajo',
            'parent_id' => $agendamiento->id,
            'link' => '/agendamiento/lista-trabajo'
        ]);

        MenuPermission::create([
            'menu_id' => $listaTrabajo->id,
            'permission_id' => $show->id
        ]);

        $llamadas = Menu::create([
            'name' => 'Llamadas',
            'parent_id' => $agendamiento->id,
            'link' => '/agendamiento/llamadas-por-paciente'
        ]);

        MenuPermission::create([
            'menu_id' => $llamadas->id,
            'permission_id' => $show->id
        ]);

        $recaudos = Menu::create([
            'name' => 'Recaudos',
            'parent_id' => $agendamiento->id,
            'link' => '/agendamiento/recaudos'
        ]);

        MenuPermission::create([
            'menu_id' => $recaudos->id,
            'permission_id' => $show->id
        ]);

        $remigrarCita = Menu::create([
            'name' => 'Remigrar cita',
            'parent_id' => $agendamiento->id,
            'link' => '/agendamiento/replay-migrate'
        ]);

        MenuPermission::create([
            'menu_id' => $remigrarCita->id,
            'permission_id' => $show->id
        ]);

        $reportes = Menu::create([
            'name' => 'Reportes',
            'parent_id' => $agendamiento->id,
            'link' => '/agendamiento/reportes'
        ]);

        MenuPermission::create([
            'menu_id' => $reportes->id,
            'permission_id' => $show->id
        ]);

        /* FIN AGENDAMIENTO */

        /* INICIO ASISTENCIAL */

        $asistencial = Menu::create([
            'name' => 'Asistencial',
            'icon' => 'fas fa-exclamation-circle',
        ]);

        MenuPermission::create([
            'menu_id' => $asistencial->id,
            'permission_id' => $show->id
        ]);

        $administracionHistorias = Menu::create([
            'name' => 'Administración de historias',
            'parent_id' => $asistencial->id,
            'link' => '/gestion-riesgo/administracion-historia-clinica'
        ]);

        MenuPermission::create([
            'menu_id' => $administracionHistorias->id,
            'permission_id' => $show->id
        ]);

        $caracterizacion = Menu::create([
            'name' => 'Caracterización',
            'parent_id' => $asistencial->id,
            'link' => '/gestion-riesgo/caracterizacion'
        ]);

        MenuPermission::create([
            'menu_id' => $caracterizacion->id,
            'permission_id' => $show->id
        ]);

        $historiasClinicas = Menu::create([
            'name' => 'Historias clínicas',
            'parent_id' => $asistencial->id,
            'link' => '/gestion-riesgo/historia-clinica'
        ]);

        MenuPermission::create([
            'menu_id' => $historiasClinicas->id,
            'permission_id' => $show->id
        ]);

        $kardexPorPatologia = Menu::create([
            'name' => 'Kardex por patología',
            'parent_id' => $asistencial->id,
            'link' => '/gestion-riesgo/kardex-patologia'
        ]);

        MenuPermission::create([
            'menu_id' => $kardexPorPatologia->id,
            'permission_id' => $show->id
        ]);

        $laboratorio = Menu::create([
            'name' => 'Laboratorio',
            'parent_id' => $asistencial->id,
            'link' => '/gestion-riesgo/laboratorio'
        ]);

        MenuPermission::create([
            'menu_id' => $laboratorio->id,
            'permission_id' => $show->id
        ]);

        $pacientes = Menu::create([
            'name' => 'Pacientes',
            'parent_id' => $asistencial->id,
            'link' => '/ajustes/informacion-base/pacientes' //!Cambiar
        ]);

        MenuPermission::create([
            'menu_id' => $pacientes->id,
            'permission_id' => $show->id
        ]);

        /* FIN ASISTENCIAL */

        /* INICIO CUENTAS MÉDICAS */

        $cuentasMedicas = Menu::create([
            'name' => 'Cuentas médicas',
            'icon' => 'fas fa-file-medical',
        ]);

        MenuPermission::create([
            'menu_id' => $cuentasMedicas->id,
            'permission_id' => $show->id
        ]);

        /* $auditorias = Menu::create([
            'name' => 'Auditorías',
            'parent_id' => $cuentasMedicas->id,
            'link' => '/cuentas-medicas/auditorias'
        ]);

        MenuPermission::create([
            'menu_id' => $auditorias->id,
            'permission_id' => $show->id
        ]); */

        /* $correspondencias = Menu::create([
            'name' => 'Correspondencias',
            'parent_id' => $cuentasMedicas->id,
            'link' => '/cuentas-medicas/correspondencias'
        ]);

        MenuPermission::create([
            'menu_id' => $correspondencias->id,
            'permission_id' => $show->id
        ]); */

        /* $direccionamientos = Menu::create([
            'name' => 'Direccionamientos',
            'parent_id' => $cuentasMedicas->id,
            'link' => '/cuentas-medicas/direccionamientos'
        ]);

        MenuPermission::create([
            'menu_id' => $direccionamientos->id,
            'permission_id' => $show->id
        ]); */

        $dispensaciones = Menu::create([
            'name' => 'Dispensaciones',
            'parent_id' => $cuentasMedicas->id,
            'link' => '/cuentas-medicas/dispensaciones'
        ]);

        MenuPermission::create([
            'menu_id' => $dispensaciones->id,
            'permission_id' => $show->id
        ]);

        /* $estadosDireccionamientos = Menu::create([
            'name' => 'Estados direccionamientos',
            'parent_id' => $cuentasMedicas->id,
            'link' => '/cuentas-medicas/estados-direccionamientos'
        ]);

        MenuPermission::create([
            'menu_id' => $estadosDireccionamientos->id,
            'permission_id' => $show->id
        ]); */

        $facturacion = Menu::create([
            'name' => 'Facturación',
            'parent_id' => $cuentasMedicas->id,
            'link' => '/cuentas-medicas/facturacion'
        ]);

        MenuPermission::create([
            'menu_id' => $facturacion->id,
            'permission_id' => $show->id
        ]);

        $facturasCapita = Menu::create([
            'name' => 'Facturas cápita',
            'parent_id' => $cuentasMedicas->id,
            'link' => '/cuentas-medicas/facturas-capita'
        ]);

        MenuPermission::create([
            'menu_id' => $facturasCapita->id,
            'permission_id' => $show->id
        ]);

        /* $facturaLaboratorio = Menu::create([
            'name' => 'Factura laboratorio',
            'parent_id' => $cuentasMedicas->id,
            'link' => '/cuentas-medicas/facturas-laboratorio'
        ]);

        MenuPermission::create([
            'menu_id' => $facturaLaboratorio->id,
            'permission_id' => $show->id
        ]); */

        $facturasMedicamentos = Menu::create([
            'name' => 'Facturas medicamentos',
            'parent_id' => $cuentasMedicas->id,
            'link' => '/cuentas-medicas/facturas-medicamentos'
        ]);

        MenuPermission::create([
            'menu_id' => $facturasMedicamentos->id,
            'permission_id' => $show->id
        ]);

        $facturasPGP = Menu::create([
            'name' => 'Facturas PGP',
            'parent_id' => $cuentasMedicas->id,
            'link' => '/cuentas-medicas/facturas-pgp'
        ]);

        MenuPermission::create([
            'menu_id' => $facturasPGP->id,
            'permission_id' => $show->id
        ]);

        $radicacion = Menu::create([
            'name' => 'Radicación',
            'parent_id' => $cuentasMedicas->id,
            'link' => '/cuentas-medicas/radicacion'
        ]);

        MenuPermission::create([
            'menu_id' => $radicacion->id,
            'permission_id' => $show->id
        ]);

        $reportesCuentasMedicas = Menu::create([
            'name' => 'Reportes',
            'parent_id' => $cuentasMedicas->id,
            'link' => '/cuentas-medicas/reportes'
        ]);

        MenuPermission::create([
            'menu_id' => $reportesCuentasMedicas->id,
            'permission_id' => $show->id
        ]);

        /* $reportesDireccionamientos = Menu::create([
            'name' => 'Reportes direccionamientos',
            'parent_id' => $cuentasMedicas->id,
            'link' => '/cuentas-medicas/reportes-direccionamientos'
        ]);

        MenuPermission::create([
            'menu_id' => $reportesDireccionamientos->id,
            'permission_id' => $show->id
        ]); */

        /* FIN CUENTAS MEDICAS */

        /* INICIO ABASTECIMIENTO */

        $abastecimiento = Menu::create([
            'name' => 'Abastecimiento',
            'icon' => 'fas fa-cart-arrow-down',
        ]);

        MenuPermission::create([
            'menu_id' => $abastecimiento->id,
            'permission_id' => $show->id
        ]);

        /* $administracionProductos = Menu::create([
            'name' => 'Administración de productos',
            'parent_id' => $abastecimiento->id,
            'link' => '/compras/administracion-productos'
        ]);

        MenuPermission::create([
            'menu_id' => $administracionProductos->id,
            'permission_id' => $show->id
        ]); */

        $ordenesCompra = Menu::create([
            'name' => 'Órdenes de compra',
            'parent_id' => $abastecimiento->id,
            'link' => '/compras/nacional'
        ]);

        MenuPermission::create([
            'menu_id' => $ordenesCompra->id,
            'permission_id' => $show->id
        ]);

        MenuPermission::create([
           'menu_id'=>$ordenesCompra->id,
           'permission_id'=>$approve->id
        ]);

        $reporteKardex = Menu::create([
            'name' => 'Reporte Kardex',
            'parent_id' => $abastecimiento->id,
            'link' => '/compras/reporte-kardex'
        ]);

        MenuPermission::create([
            'menu_id' => $reporteKardex->id,
            'permission_id' => $show->id
        ]);

        $reporteSISMED = Menu::create([
            'name' => 'Reporte SISMED',
            'parent_id' => $abastecimiento->id,
            'link' => '/compras/report-sismed'
        ]);

        MenuPermission::create([
            'menu_id' => $reporteSISMED->id,
            'permission_id' => $show->id
        ]);

        /* $rotativosCompras = Menu::create([
            'name' => 'Rotativos compras',
            'parent_id' => $abastecimiento->id,
            'link' => '/compras/rotativos-compras'
        ]);

        MenuPermission::create([
            'menu_id' => $rotativosCompras->id,
            'permission_id' => $show->id
        ]); */

        $solicitudesCompra = Menu::create([
            'name' => 'Solicitudes de compra',
            'parent_id' => $abastecimiento->id,
            'link' => '/compras/solicitud'
        ]);

        MenuPermission::create([
            'menu_id' => $solicitudesCompra->id,
            'permission_id' => $show->id
        ]);

        MenuPermission::create([
           'menu_id'=>$solicitudesCompra->id,
           'permission_id'=>$add->id
        ]);

        /* FIN ABASTECIMIENTO */

        /* INICIO DE ALMACÉN */

        $almacen = Menu::create([
            'name' => 'Almacén',
            'icon' => 'fas fa-warehouse',
        ]);

        MenuPermission::create([
            'menu_id' => $almacen->id,
            'permission_id' => $show->id
        ]);

        $remisiones = Menu::create([
            'name' => 'Remisiones',
            'parent_id' => $almacen->id,
            'link' => '/inventario/remisiones'
        ]);

        MenuPermission::create([
            'menu_id' => $remisiones->id,
            'permission_id' => $show->id
        ]);

        $actaRecepcion = Menu::create([
            'name' => 'Acta de recepción',
            'parent_id' => $almacen->id,
            'link' => '/inventario/acta-recepcion'
        ]);

        MenuPermission::create([
            'menu_id' => $actaRecepcion->id,
            'permission_id' => $show->id
        ]);

        $actaRecepcionAprobadas = Menu::create([
            'name' => 'Ubicar acta',
            'parent_id' => $almacen->id,
            'link' => '/inventario/ubicar-acta'
        ]);

        MenuPermission::create([
            'menu_id' => $actaRecepcionAprobadas->id,
            'permission_id' => $show->id
        ]);

        $actaRecepcionRemisiones = Menu::create([
            'name' => 'Recepción en punto',
            'parent_id' => $almacen->id,
            'link' => '/inventario/recepcion-punto'
        ]);

        MenuPermission::create([
            'menu_id' => $actaRecepcionRemisiones->id,
            'permission_id' => $show->id
        ]);

        $ajusteIndividual = Menu::create([
            'name' => 'Ajuste individual',
            'parent_id' => $almacen->id,
            'link' => '/inventario/ajuste-individual'
        ]);

        MenuPermission::create([
            'menu_id' => $ajusteIndividual->id,
            'permission_id' => $show->id
        ]);

        $alistamiento = Menu::create([
            'name' => 'Alistamiento',
            'parent_id' => $almacen->id,
            'link' => '/inventario/alistamiento'
        ]);

        MenuPermission::create([
            'menu_id' => $alistamiento->id,
            'permission_id' => $show->id
        ]);

        $inventarioFisico = Menu::create([
            'name' => 'Inventario físico',
            'parent_id' => $almacen->id,
            'link' => '/inventario/inventario-fisico'
        ]);

        MenuPermission::create([
            'menu_id' => $inventarioFisico->id,
            'permission_id' => $show->id
        ]);

        $catalogo = Menu::create([
            'name' => 'Catálogo',
            'parent_id' => $almacen->id,
            'link' => '/ajustes/informacion-base/catalogo' //! Cambiar
        ]);

        MenuPermission::create([
            'menu_id' => $catalogo->id,
            'permission_id' => $show->id
        ]);

        $devoluciones = Menu::create([
            'name' => 'Devoluciones',
            'parent_id' => $almacen->id,
            'link' => '/inventario/devoluciones'
        ]);

        MenuPermission::create([
            'menu_id' => $devoluciones->id,
            'permission_id' => $show->id
        ]);

        $inventario = Menu::create([
            'name' => 'Inventario',
            'parent_id' => $almacen->id,
            'link' => '/inventario/inventario'
        ]);

        MenuPermission::create([
            'menu_id' => $inventario->id,
            'permission_id' => $show->id
        ]);

        /* $inventarioFisicoPuntos = Menu::create([
            'name' => 'Inventario físico puntos',
            'parent_id' => $almacen->id,
            'link' => '/inventario/inventario-fisico-puntos'
        ]);

        MenuPermission::create([
            'menu_id' => $inventarioFisicoPuntos->id,
            'permission_id' => $show->id
        ]); */

        /* $inventarioVencer = Menu::create([
            'name' => 'Inventario por vencer',
            'parent_id' => $almacen->id,
            'link' => '/inventario/vencer'
        ]);

        MenuPermission::create([
            'menu_id' => $inventarioVencer->id,
            'permission_id' => $show->id
        ]); */

        $inventarioPuntos = Menu::create([
            'name' => 'Inventario puntos',
            'parent_id' => $almacen->id,
            'link' => '/inventario/inventario-puntos'
        ]);

        MenuPermission::create([
            'menu_id' => $inventarioPuntos->id,
            'permission_id' => $show->id
        ]);

        $vencimientos = Menu::create([
            'name' => 'Vencimientos',
            'parent_id' => $almacen->id,
            'link' => '/inventario/vencimientos'
        ]);

        MenuPermission::create([
            'menu_id' => $vencimientos->id,
            'permission_id' => $show->id
        ]);

        /* FIN ALMACEN */

        /* INICIO RRHH */

        $rrhh = Menu::create([
            'name' => 'RRHH',
            'icon' => 'fa fa-users',
        ]);

        MenuPermission::create([
            'menu_id' => $rrhh->id,
            'permission_id' => $show->id
        ]);

        /* INICIO RRHH -> DOTACIÓN */

        $dotacion = Menu::create([
            'name' => 'Dotación',
            'parent_id' => $rrhh->id,
        ]);

        MenuPermission::create([
            'menu_id' => $dotacion->id,
            'permission_id' => $show->id
        ]);

        $dotaciones = Menu::create([
            'name' => 'Dotaciones',
            'parent_id' => $dotacion->id,
            'link' => '/rrhh/dotacion/dotaciones'
        ]);

        MenuPermission::create([
            'menu_id' => $dotaciones->id,
            'permission_id' => $show->id
        ]);

        $inventarioDotacion = Menu::create([
            'name' => 'Inventario',
            'parent_id' => $dotacion->id,
            'link' => '/rrhh/dotacion/inventario'
        ]);

        MenuPermission::create([
            'menu_id' => $inventarioDotacion->id,
            'permission_id' => $show->id
        ]);

        /* FIN RRHH -> DOTACION */

        /* INICIO RRHH -> PROCESOS */

        $procesos = Menu::create([
            'name' => 'Procesos',
            'parent_id' => $rrhh->id,
        ]);

        MenuPermission::create([
            'menu_id' => $procesos->id,
            'permission_id' => $show->id
        ]);

        $disciplinarios = Menu::create([
            'name' => 'Disciplinarios',
            'parent_id' => $procesos->id,
            'link' => '/rrhh/procesos/disciplinarios'
        ]);

        MenuPermission::create([
            'menu_id' => $disciplinarios->id,
            'permission_id' => $show->id
        ]);

        $llamadosAtencion = Menu::create([
            'name' => 'Llamados de atención',
            'parent_id' => $procesos->id,
            'link' => '/rrhh/procesos/llamados-atencion'
        ]);

        MenuPermission::create([
            'menu_id' => $llamadosAtencion->id,
            'permission_id' => $show->id
        ]);

        $memorandos = Menu::create([
            'name' => 'Memorandos',
            'parent_id' => $procesos->id,
            'link' => '/rrhh/procesos/memorandos'
        ]);

        MenuPermission::create([
            'menu_id' => $memorandos->id,
            'permission_id' => $show->id
        ]);

        /* FIN RRHH -> PROCESOS */

        /* INICIO RRHH -> HORARIO */

        $horario = Menu::create([
            'name' => 'Horario',
            'parent_id' => $rrhh->id,
        ]);

        $asignacionTurnos = Menu::create([
            'name' => 'Asignación de turnos',
            'parent_id' => $horario->id,
            'link' => '/rrhh/turnos/asignacion'
        ]);

        MenuPermission::create([
            'menu_id' => $asignacionTurnos->id,
            'permission_id' => $show->id
        ]);

        $llegadasTarde = Menu::create([
            'name' => 'Llegadas tarde',
            'parent_id' => $horario->id,
            'link' => '/rrhh/llegadas-tarde'
        ]);

        MenuPermission::create([
            'menu_id' => $llegadasTarde->id,
            'permission_id' => $show->id
        ]);

        $reporteHorarios = Menu::create([
            'name' => 'Reporte de horarios',
            'parent_id' => $horario->id,
            'link' => '/rrhh/turnos/reporte'
        ]);

        MenuPermission::create([
            'menu_id' => $reporteHorarios->id,
            'permission_id' => $show->id
        ]);

        $validacionHorasExtra = Menu::create([
            'name' => 'Validación de horas extra',
            'parent_id' => $horario->id,
            'link' => '/rrhh/turnos/horas-extras'
        ]);

        MenuPermission::create([
            'menu_id' => $validacionHorasExtra->id,
            'permission_id' => $show->id
        ]);

        /* FIN RRHH -> HORARIO */

        $actividades = Menu::create([
            'name' => 'Actividades',
            'parent_id' => $rrhh->id,
            'link' => '/rrhh/actividades'
        ]);

        MenuPermission::create([
            'menu_id' => $actividades->id,
            'permission_id' => $show->id
        ]);

        $alertas = Menu::create([
            'name' => 'Alertas y comunicaciones',
            'parent_id' => $rrhh->id,
            'link' => '/rrhh/alertas-comun'
        ]);

        MenuPermission::create([
            'menu_id' => $alertas->id,
            'permission_id' => $show->id
        ]);

        $certificados = Menu::create([
            'name' => 'Certificados',
            'parent_id' => $rrhh->id,
            'link' => '/rrhh/certificados'
        ]);

        MenuPermission::create([
            'menu_id' => $certificados->id,
            'permission_id' => $show->id
        ]);

        $contratos = Menu::create([
            'name' => 'Contratos',
            'parent_id' => $rrhh->id,
            'link' => '/rrhh/contratos'
        ]);

        MenuPermission::create([
            'menu_id' => $contratos->id,
            'permission_id' => $show->id
        ]);

        $novedades = Menu::create([
            'name' => 'Novedades',
            'parent_id' => $rrhh->id,
            'link' => '/rrhh/novedades'
        ]);

        MenuPermission::create([
            'menu_id' => $novedades->id,
            'permission_id' => $show->id
        ]);

        $preliquidados = Menu::create([
            'name' => 'Preliquidados',
            'parent_id' => $rrhh->id,
            'link' => '/rrhh/liquidados'
        ]);

        MenuPermission::create([
            'menu_id' => $preliquidados->id,
            'permission_id' => $show->id
        ]);

        $vacantes = Menu::create([
            'name' => 'Vacantes',
            'parent_id' => $rrhh->id,
            'link' => '/rrhh/vacantes'
        ]);

        MenuPermission::create([
            'menu_id' => $vacantes->id,
            'permission_id' => $show->id
        ]);

        /* FIN RRHH */

        /* INICIO NÓMINA */

        $nomina = Menu::create([
            'name' => 'Nómina',
            'icon' => 'fas fa-wallet',
        ]);

        MenuPermission::create([
            'menu_id' => $nomina->id,
            'permission_id' => $show->id
        ]);

        $cesantias = Menu::create([
            'name' => 'Cesantías',
            'parent_id' => $nomina->id,
            'link' => '/nomina/cesantias'
        ]);

        MenuPermission::create([
            'menu_id' => $cesantias->id,
            'permission_id' => $show->id
        ]);

        $configuracionNomina = Menu::create([
            'name' => 'Configuración nómina',
            'parent_id' => $nomina->id,
            'link' => '/nomina/configuracion'
        ]);

        MenuPermission::create([
            'menu_id' => $configuracionNomina->id,
            'permission_id' => $show->id
        ]);

        $historialPagos = Menu::create([
            'name' => 'Historial de pagos',
            'parent_id' => $nomina->id,
            'link' => '/nomina/historial-pagos'
        ]);

        MenuPermission::create([
            'menu_id' => $historialPagos->id,
            'permission_id' => $show->id
        ]);

        $parametrosNomina = Menu::create([
            'name' => 'Parámetros nómina',
            'parent_id' => $nomina->id,
            'link' => '/ajustes/parametros/nomina' //! Cambiar
        ]);

        MenuPermission::create([
            'menu_id' => $parametrosNomina->id,
            'permission_id' => $show->id
        ]);

        $pagoNomina = Menu::create([
            'name' => 'Pago de nómina',
            'parent_id' => $nomina->id,
            'link' => '/nomina/nomina'
        ]);

        MenuPermission::create([
            'menu_id' => $pagoNomina->id,
            'permission_id' => $show->id
        ]);

        $prestamosLibranzas = Menu::create([
            'name' => 'Préstamos y libranzas',
            'parent_id' => $nomina->id,
            'link' => '/nomina/prestamos'
        ]);

        MenuPermission::create([
            'menu_id' => $prestamosLibranzas->id,
            'permission_id' => $show->id
        ]);

        $primas = Menu::create([
            'name' => 'Primas',
            'parent_id' => $nomina->id,
            'link' => '/nomina/primas'
        ]);

        MenuPermission::create([
            'menu_id' => $primas->id,
            'permission_id' => $show->id
        ]);

        $provisiones = Menu::create([
            'name' => 'Provisiones',
            'parent_id' => $nomina->id,
            'link' => '/nomina/provisiones'
        ]);

        MenuPermission::create([
            'menu_id' => $provisiones->id,
            'permission_id' => $show->id
        ]);

        $viaticos = Menu::create([
            'name' => 'Viáticos',
            'parent_id' => $nomina->id,
            'link' => '/nomina/viaticos'
        ]);

        MenuPermission::create([
            'menu_id' => $viaticos->id,
            'permission_id' => $show->id
        ]);

        /* FIN NÓMINA */

        /* INICIO CONTABILIDAD */

        $contabilidad = Menu::create([
            'name' => 'Contabilidad',
            'icon' => 'fas fa-calculator',
        ]);

        MenuPermission::create([
            'menu_id' => $contabilidad->id,
            'permission_id' => $show->id
        ]);

        /* INICIO CONTABILIDAD -> COMPROBANTES */

        $comprobantes = Menu::create([
            'name' => 'Comprobantes',
            'parent_id' => $contabilidad->id,
        ]);

        MenuPermission::create([
            'menu_id' => $comprobantes->id,
            'permission_id' => $show->id
        ]);

        $egresos = Menu::create([
            'name' => 'Egresos',
            'parent_id' => $comprobantes->id,
            'link' => '/contabilidad/comprobantes/egresos'
        ]);

        MenuPermission::create([
            'menu_id' => $egresos->id,
            'permission_id' => $show->id
        ]);

        $ingresos = Menu::create([
            'name' => 'Ingresos',
            'parent_id' => $comprobantes->id,
            'link' => '/contabilidad/comprobantes/ingresos'
        ]);

        MenuPermission::create([
            'menu_id' => $ingresos->id,
            'permission_id' => $show->id
        ]);

        $notasContables = Menu::create([
            'name' => 'Notas contables',
            'parent_id' => $comprobantes->id,
            'link' => '/contabilidad/comprobantes/notas-contables'
        ]);

        MenuPermission::create([
            'menu_id' => $notasContables->id,
            'permission_id' => $show->id
        ]);

        $notasCredito = Menu::create([
            'name' => 'Notas de crédito',
            'parent_id' => $comprobantes->id,
            'link' => '/contabilidad/comprobantes/notas-credito'
        ]);

        MenuPermission::create([
            'menu_id' => $notasCredito->id,
            'permission_id' => $show->id
        ]);

        $notasDebito = Menu::create([
            'name' => 'Notas de débito',
            'parent_id' => $comprobantes->id,
            'link' => '/contabilidad/comprobantes/notas-debito'
        ]);

        MenuPermission::create([
            'menu_id' => $notasDebito->id,
            'permission_id' => $show->id
        ]);

        /* FIN CONTABILIDAD -> COMPROBANTES */

        /* INICIO CONTABILIDAD -> INFORMES DIAN */

        $informesDian = Menu::create([
            'name' => 'Informes DIAN',
            'parent_id' => $contabilidad->id,
        ]);

        MenuPermission::create([
            'menu_id' => $informesDian->id,
            'permission_id' => $show->id
        ]);

        $agruparFormatosEspeciales = Menu::create([
            'name' => 'Agrupar formatos especiales',
            'parent_id' => $informesDian->id,
            'link' => '/contabilidad/informesdian/agruparmediosmagneticos'
        ]);

        MenuPermission::create([
            'menu_id' => $agruparFormatosEspeciales->id,
            'permission_id' => $show->id
        ]);

        $certificadosIngresoRetencion = Menu::create([
            'name' => 'Certificados de ingreso y retención',
            'parent_id' => $informesDian->id,
            'link' => '/contabilidad/informesdian/certificadoingresoyretencion'
        ]);

        MenuPermission::create([
            'menu_id' => $certificadosIngresoRetencion->id,
            'permission_id' => $show->id
        ]);

        $certificadosRetencion = Menu::create([
            'name' => 'Certificados de retención',
            'parent_id' => $informesDian->id,
            'link' => '/contabilidad/informesdian/certificadoretencion'
        ]);

        MenuPermission::create([
            'menu_id' => $certificadosRetencion->id,
            'permission_id' => $show->id
        ]);

        $mediosMagneticos = Menu::create([
            'name' => 'Medios magnéticos',
            'parent_id' => $informesDian->id,
            'link' => '/contabilidad/informesdian/mediosmagneticos'
        ]);

        MenuPermission::create([
            'menu_id' => $mediosMagneticos->id,
            'permission_id' => $show->id
        ]);

        $mediosMagneticosEspeciales = Menu::create([
            'name' => 'Medios magnéticos especiales',
            'parent_id' => $informesDian->id,
            'link' => '/contabilidad/informesdian/mediosmagneticosespeciales'
        ]);

        MenuPermission::create([
            'menu_id' => $mediosMagneticosEspeciales->id,
            'permission_id' => $show->id
        ]);

        $resumenRetenciones = Menu::create([
            'name' => 'Resumen de retenciones',
            'parent_id' => $informesDian->id,
            'link' => '/contabilidad/informesdian/resumenretenciones'
        ]);

        MenuPermission::create([
            'menu_id' => $resumenRetenciones->id,
            'permission_id' => $show->id
        ]);

        /* FIN CONTABILIDAD -> INFORMES DIAN */

        /* INCIO CONTABILIDAD -> BALANCES */

        $balances = Menu::create([
            'name' => 'Balances',
            'parent_id' => $contabilidad->id,
        ]);

        MenuPermission::create([
            'menu_id' => $balances->id,
            'permission_id' => $show->id
        ]);

        $balanceGeneral = Menu::create([
            'name' => 'Balance general',
            'parent_id' => $balances->id,
            'link' => '/contabilidad/balances/general'
        ]);

        MenuPermission::create([
            'menu_id' => $balanceGeneral->id,
            'permission_id' => $show->id
        ]);

        $movimientoGlobalizado = Menu::create([
            'name' => 'Movimiento globalizado',
            'parent_id' => $balances->id,
            'link' => '/contabilidad/balances/movimiento-globalizado'
        ]);

        MenuPermission::create([
            'menu_id' => $movimientoGlobalizado->id,
            'permission_id' => $show->id
        ]);

        $balancePruebas = Menu::create([
            'name' => 'Balance de pruebas',
            'parent_id' => $balances->id,
            'link' => '/contabilidad/balance-pruebas'
        ]);

        MenuPermission::create([
            'menu_id' => $balancePruebas->id,
            'permission_id' => $show->id
        ]);

        /* FIN CONTABILIDAD -> BALANCES */

        /* INICIO CONTABILIDAD -> ESTADOS */

        $estados = Menu::create([
            'name' => 'Estados',
            'parent_id' => $contabilidad->id,
        ]);

        MenuPermission::create([
            'menu_id' => $estados->id,
            'permission_id' => $show->id
        ]);

        $estadosResultantes = Menu::create([
            'name' => 'Estados resultantes',
            'parent_id' => $estados->id,
            'link' => '/contabilidad/estados/estados-resultantes'
        ]);

        MenuPermission::create([
            'menu_id' => $estadosResultantes->id,
            'permission_id' => $show->id
        ]);

        /* FIN CONTABILIDAD -> ESTADOS */

        $activosFijos = Menu::create([
            'name' => 'Activos fijos',
            'parent_id' => $contabilidad->id,
            'link' => '/contabilidad/activos-fijos'
        ]);

        MenuPermission::create([
            'menu_id' => $activosFijos->id,
            'permission_id' => $show->id
        ]);

        /* $cajas = Menu::create([
            'name' => 'Cajas',
            'parent_id' => $contabilidad->id,
            'link' => '/contabilidad/cajas'
        ]);

        MenuPermission::create([
            'menu_id' => $cajas->id,
            'permission_id' => $show->id
        ]); */

        $centroCosto = Menu::create([
            'name' => 'Centro de costo',
            'parent_id' => $contabilidad->id,
            'link' => '/contabilidad/centro-costos'
        ]);

        MenuPermission::create([
            'menu_id' => $centroCosto->id,
            'permission_id' => $show->id
        ]);

        $cierresContables = Menu::create([
            'name' => 'Cierres contables',
            'parent_id' => $contabilidad->id,
            'link' => '/contabilidad/cierres-contables'
        ]);

        MenuPermission::create([
            'menu_id' => $cierresContables->id,
            'permission_id' => $show->id
        ]);

        $depreciaciones = Menu::create([
            'name' => 'Depreciaciones',
            'parent_id' => $contabilidad->id,
            'link' => '/contabilidad/depreciaciones'
        ]);

        MenuPermission::create([
            'menu_id' => $depreciaciones->id,
            'permission_id' => $show->id
        ]);

        $planCuentas = Menu::create([
            'name' => 'Plan de cuentas',
            'parent_id' => $contabilidad->id,
            'link' => '/contabilidad/plan-cuentas'
        ]);

        MenuPermission::create([
            'menu_id' => $planCuentas->id,
            'permission_id' => $show->id
        ]);

        /* FIN CONTABILIDAD */

        /* INICIO AJUSTES */

        $ajustes = Menu::create([
            'name' => 'Ajustes',
            'icon' => 'fas fa-cog',
        ]);

        MenuPermission::create([
            'menu_id' => $ajustes->id,
            'permission_id' => $show->id
        ]);

        /* INICIO AJUSTES -> INFORMACIÓN BASE */

        $informacionBase = Menu::create([
            'name' => 'Información base',
            'parent_id' => $ajustes->id,
        ]);

        MenuPermission::create([
            'menu_id' => $informacionBase->id,
            'permission_id' => $show->id
        ]);

        $bodegas = Menu::create([
            'name' => 'Bodegas',
            'parent_id' => $informacionBase->id,
            'link' => '/ajustes/informacion-base/bodegas'
        ]);

        MenuPermission::create([
            'menu_id' => $bodegas->id,
            'permission_id' => $show->id
        ]);

        $contratosClientes = Menu::create([
            'name' => 'Contratos clientes',
            'parent_id' => $informacionBase->id,
            'link' => '/ajustes/informacion-base/contracts'
        ]);

        MenuPermission::create([
            'menu_id' => $contratosClientes->id,
            'permission_id' => $show->id
        ]);

        $cups = Menu::create([
            'name' => 'CUPS',
            'parent_id' => $informacionBase->id,
            'link' => '/ajustes/informacion-base/cups'
        ]);

        MenuPermission::create([
            'menu_id' => $cups->id,
            'permission_id' => $show->id
        ]);

        $empresas = Menu::create([
            'name' => 'Empresas',
            'parent_id' => $informacionBase->id,
            'link' => '/ajustes/informacion-base/empresas'
        ]);

        MenuPermission::create([
            'menu_id' => $empresas->id,
            'permission_id' => $show->id
        ]);
        MenuPermission::create([
            'menu_id' => $empresas->id,
            'permission_id' => $approveProductCategories->id
        ]);
        MenuPermission::create([
            'menu_id' => $empresas->id,
            'permission_id' => $allCompanies->id
        ]);

        $especialidades = Menu::create([
            'name' => 'Especialidades',
            'parent_id' => $informacionBase->id,
            'link' => '/ajustes/informacion-base/especialidades'
        ]);

        MenuPermission::create([
            'menu_id' => $especialidades->id,
            'permission_id' => $show->id
        ]);

        $funcionarios = Menu::create([
            'name' => 'Funcionarios',
            'parent_id' => $informacionBase->id,
            'link' => '/ajustes/informacion-base/funcionarios'
        ]);

        MenuPermission::create([
            'menu_id' => $funcionarios->id,
            'permission_id' => $show->id
        ]);
        MenuPermission::create([
            'menu_id' => $funcionarios->id,
            'permission_id' => $allCompanies->id
        ]);
        MenuPermission::create([
            'menu_id' => $funcionarios->id,
            'permission_id' => $add->id
        ]);
        MenuPermission::create([
            'menu_id' => $funcionarios->id,
            'permission_id' => $edit->id
        ]);

        $profesionales = Menu::create([
            'name' => 'Profesionales',
            'parent_id' => $informacionBase->id,
            'link' => '/ajustes/informacion-base/professionals'
        ]);

        MenuPermission::create([
            'menu_id' => $profesionales->id,
            'permission_id' => $show->id
        ]);

        $puntos = Menu::create([
            'name' => 'Puntos',
            'parent_id' => $informacionBase->id,
            'link' => '/ajustes/informacion-base/puntosdispensacion'
        ]);

        MenuPermission::create([
            'menu_id' => $puntos->id,
            'permission_id' => $show->id
        ]);

        $regimenesNiveles = Menu::create([
            'name' => 'Regímenes y niveles',
            'parent_id' => $informacionBase->id,
            'link' => '/ajustes/informacion-base/regimenes-niveles'
        ]);

        MenuPermission::create([
            'menu_id' => $regimenesNiveles->id,
            'permission_id' => $show->id
        ]);

        $responsables = Menu::create([
            'name' => 'Responsables',
            'parent_id' => $informacionBase->id,
            'link' => '/ajustes/informacion-base/responsables'
        ]);

        MenuPermission::create([
            'menu_id' => $responsables->id,
            'permission_id' => $show->id
        ]);

        $terceros = Menu::create([
            'name' => 'Terceros.',
            'parent_id' => $informacionBase->id,
            'link' => '/ajustes/informacion-base/terceros'
        ]);

        MenuPermission::create([
            'menu_id' => $terceros->id,
            'permission_id' => $show->id
        ]);

        $turnos = Menu::create([
            'name' => 'Turnos',
            'parent_id' => $informacionBase->id,
            'link' => '/ajustes/informacion-base/turnos'
        ]);

        MenuPermission::create([
            'menu_id' => $turnos->id,
            'permission_id' => $show->id
        ]);

        /* FIN AJUSTES -> INFORMACIÓN BASE */

        /* INICIO AJUSTES -> PARÁMETROS */

        $parametros = Menu::create([
            'name' => 'Parámetros',
            'parent_id' => $ajustes->id,
        ]);

        MenuPermission::create([
            'menu_id' => $parametros->id,
            'permission_id' => $show->id
        ]);

        $agendamiento = Menu::create([
            'name' => 'Agendamiento',
            'parent_id' => $parametros->id,
            'link' => '/ajustes/parametros/agendamiento'
        ]);

        MenuPermission::create([
            'menu_id' => $agendamiento->id,
            'permission_id' => $show->id
        ]);

        $contratosParametros = Menu::create([
            'name' => 'Contratos.',
            'parent_id' => $parametros->id,
            'link' => '/ajustes/tipos/contrato' //! TODO: cambiar por /ajustes/parametros/contratos
        ]);

        MenuPermission::create([
            'menu_id' => $contratosParametros->id,
            'permission_id' => $show->id
        ]);

        $tercerosParametros = Menu::create([
            'name' => 'Terceros',
            'parent_id' => $parametros->id,
            'link' => '/ajustes/parametros/terceros'
        ]);

        MenuPermission::create([
            'menu_id' => $tercerosParametros->id,
            'permission_id' => $show->id
        ]);

        $turneros = Menu::create([
            'name' => 'Turneros',
            'parent_id' => $parametros->id,
            'link' => '/ajustes/parametros/turneros'
        ]);

        MenuPermission::create([
            'menu_id' => $turneros->id,
            'permission_id' => $show->id
        ]);

        $unidadesMedida = Menu::create([
            'name' => 'Unidades de medida',
            'parent_id' => $parametros->id,
            'link' => '/ajustes/parametros/unidades-medidas'
        ]);

        MenuPermission::create([
            'menu_id' => $unidadesMedida->id,
            'permission_id' => $show->id
        ]);

        $vacantesParametros = Menu::create([
            'name' => 'Vacantes.',
            'parent_id' => $parametros->id,
            'link' => '/ajustes/parametros/vacantes'
        ]);

        MenuPermission::create([
            'menu_id' => $vacantesParametros->id,
            'permission_id' => $show->id
        ]);

        $viaticosParametros = Menu::create([
            'name' => 'Viáticos.',
            'parent_id' => $parametros->id,
            'link' => '/ajustes/parametros/viaticos'
        ]);

        MenuPermission::create([
            'menu_id' => $viaticosParametros->id,
            'permission_id' => $show->id
        ]);

        /* FIN AJUSTES -> PARÁMETROS */

        /* INICIO AJUSTES -> TIPOS */

        $tipos = Menu::create([
            'name' => 'Tipos',
            'parent_id' => $ajustes->id,
        ]);

        MenuPermission::create([
            'menu_id' => $tipos->id,
            'permission_id' => $show->id
        ]);

        $tiposEgreso = Menu::create([
            'name' => 'Tipos de egreso',
            'parent_id' => $tipos->id,
            'link' => '/ajustes/tipos/tipos-egreso'
        ]);

        MenuPermission::create([
            'menu_id' => $tiposEgreso->id,
            'permission_id' => $show->id
        ]);

        $tiposIngreso = Menu::create([
            'name' => 'Tipos de ingreso',
            'parent_id' => $tipos->id,
            'link' => '/ajustes/tipos/tipos-ingreso'
        ]);

        MenuPermission::create([
            'menu_id' => $tiposIngreso->id,
            'permission_id' => $show->id
        ]);

        $tiposNovedad = Menu::create([
            'name' => 'Tipos de novedad',
            'parent_id' => $tipos->id,
            'link' => '/ajustes/tipos/tipos-novedad'
        ]);

        MenuPermission::create([
            'menu_id' => $tiposNovedad->id,
            'permission_id' => $show->id
        ]);

        $tiposRetenciones = Menu::create([
            'name' => 'Tipos de retenciones',
            'parent_id' => $tipos->id,
            'link' => '/ajustes/tipos/tipos-retenciones'
        ]);

        MenuPermission::create([
            'menu_id' => $tiposRetenciones->id,
            'permission_id' => $show->id
        ]);

        /* $tiposRiesgo = Menu::create([
            'name' => 'Tipos de riesgo',
            'parent_id' => $tipos->id,
            'link' => '/ajustes/tipos/tipos-riesgo'
        ]);

        MenuPermission::create([
            'menu_id' => $tiposRiesgo->id,
            'permission_id' => $show->id
        ]); */

        $tiposServicio = Menu::create([
            'name' => 'Tipos de servicio',
            'parent_id' => $tipos->id,
            'link' => '/ajustes/tipos/tipos-servicio'
        ]);

        MenuPermission::create([
            'menu_id' => $tiposServicio->id,
            'permission_id' => $show->id
        ]);

        /* FIN AJUSTES -> TIPOS */

        /* INICIO AJUSTES -> CONFIGURACIÓN */

        $configuracion = Menu::create([
            'name' => 'Configuración',
            'parent_id' => $ajustes->id,
        ]);

        MenuPermission::create([
            'menu_id' => $configuracion->id,
            'permission_id' => $show->id
        ]);

        $ciudades = Menu::create([
            'name' => 'Ciudades',
            'parent_id' => $configuracion->id,
            'link' => '/ajustes/configuracion/ubicaciones'
        ]);

        MenuPermission::create([
            'menu_id' => $ciudades->id,
            'permission_id' => $show->id
        ]);

        $consecutivos = Menu::create([
            'name' => 'Consecutivos',
            'parent_id' => $configuracion->id,
            'link' => '/ajustes/configuracion/consecutivos'
        ]);

        MenuPermission::create([
            'menu_id' => $consecutivos->id,
            'permission_id' => $show->id
        ]);

        $formatoHistoria = Menu::create([
            'name' => 'Modelos de historia clínica',
            'parent_id' => $configuracion->id,
            'link' => '/ajustes/configuracion/modelos-historia-clinica'
        ]);

        MenuPermission::create([
            'menu_id' => $formatoHistoria->id,
            'permission_id' => $show->id
        ]);

        $configuracionContabilidad = Menu::create([
            'name' => 'Configuración contabilidad',
            'parent_id' => $configuracion->id,
            'link' => '/ajustes/configuracion/contabilidad'
        ]);

        MenuPermission::create([
            'menu_id' => $configuracionContabilidad->id,
            'permission_id' => $show->id
        ]);

        /* FIN AJUSTES -> CONFIGURACIÓN */

        /* FIN AJUSTES */
    }
}
