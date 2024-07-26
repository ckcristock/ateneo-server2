<?php

namespace App\Http\Controllers;

use App\Http\Services\complex;
use App\Http\Services\consulta;
use App\Http\Services\QueryBaseDatos;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Services\DeleteAlerts;
use App\Http\Services\Mipres;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class DispensacionController extends Controller
{
    public function detalleDispensacion()
    {
        $id = (isset($_REQUEST['id']) ? $_REQUEST['id'] : '');
        $query = 'SELECT D.*, PD.numeroAutorizacion,
                         DATE_FORMAT(D.Fecha_Actual, "%d/%m/%Y") as Fecha_Dis,
                         CONCAT(F.first_name, " ",  F.first_surname) as Funcionario,
                         P.Nombre as Punto_Dispensacion,
                         P.Direccion as Direccion_Punto,
                         P.Telefono Telefono_Punto,
                         D.Codigo_Qr,
                         A.Id_Auditoria,
                         L.Nombre as Departamento,
                         CONCAT_WS(" ",patients.firstname,patients.middlename,patients.surname,  patients.secondsurname) as Nombre_Paciente , patients.eps_id, patients.address as Direccion_Paciente, R.name as Regimen_Paciente, patients.identifier, (SELECT CONCAT(S.Nombre," - ",T.Nombre) as Nombre FROM Tipo_Servicio T INNER JOIN Servicio S on T.Id_Servicio=S.Id_Servicio WHERE T.Id_Tipo_Servicio = D.Id_Tipo_Servicio) AS Servicio, IFNULL((SELECT Numero_Telefono FROM Paciente_Telefono WHERE Id_Paciente = D.Numero_Documento LIMIT 1), "No Registrado" ) AS Telefono_Paciente
                  FROM Dispensacion D
                  LEFT JOIN Positiva_Data PD ON PD.Id_Dispensacion = D.Id_Dispensacion
                  INNER JOIN people F on D.Identificacion_Funcionario=F.identifier
                  LEFT JOIN Auditoria A on A.Id_Dispensacion = D.Id_Dispensacion
                  INNER JOIN Punto_Dispensacion P on D.Id_Punto_Dispensacion=P.Id_Punto_Dispensacion
                  INNER JOIN Departamento L on P.Departamento = L.Id_Departamento
                  INNER JOIN patients on D.Numero_Documento = patients.identifier
                  INNER JOIN regimen_types R on patients.regimen_id = R.id
                  WHERE D.Id_Dispensacion =  ' . $id;
        //dd($query);
        $oCon = new consulta();
        $oCon->setQuery($query);
        $dis = $oCon->getData();
        unset($oCon);
        /**Listar los tipos de soporte  roberth 12-10-2021*/
        if (isset($dis["Id_Auditoria"])) {
            $query5 = 'SELECT S.*, T.Tipo_Soporte, "No" AS Cumple
                    FROM Soporte_Auditoria S
                    INNER JOIN Tipo_Soporte T ON T.Id_Tipo_Soporte =  S.Id_Tipo_Soporte
                    WHERE S.Archivo != "NULL" and S.Id_Auditoria =  ' . $dis["Id_Auditoria"] . '
                    ORDER BY Id_Soporte_Auditoria ASC';
            $oCon = new consulta();
            $oCon->setQuery($query5);
            $oCon->setTipo('Multiple');
            $soportes = $oCon->getData();
            unset($oCon);
        }
        $query = "SELECT * FROM Dispensacion_Reclamante WHERE Dispensacion_Id = '$id' ";
        $oCon = new consulta();
        $oCon->SetQuery($query);
        $customReclamante = $oCon->getData() ? $oCon->getData()['Reclamante_Id'] : null;
        unset($oCon);
        if ($customReclamante != null && $customReclamante != 'null') {
            $query = "SELECT Reclamante.* , DR.Parentesco FROM Reclamante   INNER JOIN Dispensacion_Reclamante AS DR ON Reclamante.Id_Reclamante = DR.Reclamante_Id
          WHERE Id_Reclamante = '$customReclamante'";
            $oCon = new consulta();
            $oCon->SetQuery($query);
            $customReclamante = $oCon->getData();
            unset($oCon);
        } else {
            $customReclamante = ['Id_Reclamante' => '', 'Nombre' => '', 'Parentesco' => ''];
        }
        /****************************************************************************************************************** */

        $query2 = 'SELECT PD.*, CONCAT_WS(" ",
        P.Presentacion,
        P.Concentracion,
        P.Principio_Activo,
        P.Cantidad,
        P.Unidad_Medida) as Nombre_Producto,
        CONCAT(P.Nombre_Comercial," - CUM: ",P.Codigo_Cum) as Nombre_Comercial,
        AD.Id_Actividades_Dispensacion AS Producto_Editado
        FROM Producto_Dispensacion as PD
        INNER JOIN Producto P
        on P.Id_Producto=PD.Id_Producto
        LEFT JOIN Actividades_Dispensacion AD ON AD.Detalle LIKE CONCAT("%", P.Codigo_Cum, "%") AND AD.Estado ="Edicion" AND AD.Id_Dispensacion = PD.Id_Dispensacion
        WHERE PD.Id_Dispensacion = ' . $id;
        $oCon = new consulta();
        $oCon->setQuery($query2);
        $oCon->setTipo('Multiple');
        $productos = $oCon->getData();
        unset($oCon);

        $query3 = 'SELECT AD.Identificacion_Funcionario, AD.Fecha, AD.Detalle, AD.Estado, CONCAT(F.first_name," ",F.first_surname) as Nombre, F.image
                    FROM Actividades_Dispensacion AD
                    INNER JOIN people F
                    ON AD.Identificacion_Funcionario=F.identifier
                    WHERE AD.Id_Dispensacion=  ' . $id;


        $oCon = new consulta();
        $oCon->setQuery($query3);
        $oCon->setTipo('Multiple');
        $acti = $oCon->getData();
        unset($oCon);

        $query4 = 'SELECT * FROM Auditoria
                    WHERE Id_Dispensacion=  ' . $id;

        $oCon = new consulta();
        $oCon->setQuery($query4);
        $auditoria = $oCon->getData();
        unset($oCon);

        if ($auditoria == NULL) {
            $auditoria['Id_Auditoria'] = '';
            $auditoria['Archivo'] = '';
        }
        $factura = null;
        if (isset($dis['Tipo']) && $dis['Tipo'] != 'Capita') {
            $query4 = 'SELECT F.image, CONCAT(F.first_name," ",F.first_surname) as Nombre,(SELECT F.Codigo FROM Factura F WHERE F.Id_Factura=D.Id_Factura ) as Detalle , D.Estado_Facturacion as Estado,D.Fecha_Facturado as Fecha FROM Dispensacion D
                    INNER JOIN people F ON D.Facturador_Asignado=F.identifier
                    WHERE D.Id_Dispensacion= ' . $id . ' AND D.Estado_Facturacion="Facturada"';

            $oCon = new consulta();
            $oCon->setQuery($query4);
            $factura = $oCon->getData();
            unset($oCon);
        } elseif (isset($dis['Tipo']) && $dis['Tipo'] == 'Capita') {
            $query4 = 'SELECT F.image, CONCAT(F.first_name," ",F.first_surname) as Nombre,(SELECT F.Codigo FROM Factura_Capita F WHERE F.Id_Factura_Capita=D.Id_Factura ) as Detalle , D.Estado_Facturacion as Estado,D.Fecha_Facturado as Fecha FROM Dispensacion D
                    INNER JOIN people F ON D.Facturador_Asignado=F.identifier
                    WHERE D.Id_Dispensacion= ' . $id . ' AND D.Estado_Facturacion="Facturada"';

            $oCon = new consulta();
            $oCon->setQuery($query4);
            $factura = $oCon->getData();
            unset($oCon);
        }


        $resultado["Datos"] = $dis;
        $resultado["Productos"] = $productos;
        $resultado["AcDispensacion"] = $acti;
        $resultado["Auditoria"] = $auditoria;
        $resultado["Factura"] = $factura;
        $resultado["Reclamante"] = $customReclamante;
        $resultado["Soportes"] = $soportes ?? [];


        return response()->json($resultado);
    }

    public function listaDispensaciones()
    {
        $hoy = date('Y-m-d');
        $ultimos_dos_meses = strtotime('-2 month', strtotime($hoy));
        $ultimos_dos_meses = date('Y-m-01', $ultimos_dos_meses);

        $condicion_fechas = '';

        $condicion = '';
        // exit;
        $orden = "ORDER BY Id_Dispensacion DESC";
        $condiciones = [];
        // $condiciones_dispensacion = [];

        if (isset($_REQUEST['orden']) && $_REQUEST['orden'] != "") {
            if ($_REQUEST['orden'] == "Fecha") {
                $orden = "ORDER BY Id_Dispensacion DESC";
            } elseif ($_REQUEST['orden'] == "Platino") {
                $orden = "ORDER BY Platino DESC, Id_Dispensacion DESC";
            } elseif ($_REQUEST['orden'] == "Tutela") {
                $orden = "ORDER BY Tiene_Tutela DESC, Id_Dispensacion DESC";
            }
        }

        if (isset($_REQUEST['fecha']) && $_REQUEST['fecha'] != "") {
            $fecha_inicio = trim(explode(' - ', $_REQUEST['fecha'])[0]);
            $fecha_fin = trim(explode(' - ', $_REQUEST['fecha'])[1]);

            $condicion .= " WHERE DATE_FORMAT(Fecha_Actual,'%Y-%m-%d') BETWEEN '$fecha_inicio' AND '$fecha_fin'";
            array_push($condiciones, "(DATE_FORMAT(D.Fecha_Actual,'%Y-%m-%d') BETWEEN '$fecha_inicio' AND '$fecha_fin')  ");
        } else {
            $fecha_fin = date('Y-m-d');
            $fecha_inicio = date('Y-m-d');
        }

        $condicion1 = " WHERE DATE_FORMAT(Fecha_Actual,'%Y-%m-%d') BETWEEN '$fecha_inicio' AND '$fecha_fin'";
        $condicion2 = " (DATE_FORMAT(Fecha_Actual,'%Y-%m-%d') BETWEEN '$fecha_inicio' AND '$fecha_fin') AND ";

        if ($condicion != '') {
            $condicion1 = '';
            $condicion2 = '';
        }
        if (isset($_REQUEST['cod']) && $_REQUEST['cod'] != "") {
            array_push($condiciones, "D.Codigo LIKE '%$_REQUEST[cod]%'");
            // array_push($condiciones_dispensacion, "D.Codigo LIKE '%$_REQUEST[cod]%'");

        }

        if (isset($_REQUEST['tipo']) && $_REQUEST['tipo']) {
            if ($_REQUEST['tipo'] != 'todos') {
                array_push($condiciones, "D.Id_Tipo_Servicio=$_REQUEST[tipo]");
                // array_push($condiciones_dispensacion, "D.Id_Tipo_Servicio=$_REQUEST[tipo]");
            }

        }

        if (isset($_REQUEST['pers']) && $_REQUEST['pers']) {
            $numero = (int) $_REQUEST['pers'];
            if ($numero !== 0) {
                array_push($condiciones, "D.Numero_Documento like '%$_REQUEST[pers]%'");
                // array_push($condiciones_dispensacion, "D.Numero_Documento like '%$_REQUEST[pers]%'");
            }

            array_push($condiciones, "(CONCAT(PC.firstname, ' ',  PC.surname) LIKE '%$_REQUEST[pers]%' or D.Numero_Documento LIKE '%$_REQUEST[pers]%')");
        }

        if (isset($_REQUEST['punto']) && $_REQUEST['punto']) {
            array_push($condiciones, "(P.Nombre LIKE '%$_REQUEST[punto]%' )");
        }

        if (isset($_REQUEST['dep']) && $_REQUEST['dep']) {
            array_push($condiciones, "L.Nombre LIKE '%$_REQUEST[dep]%'");

        }

        if (isset($_REQUEST['fact']) && $_REQUEST['fact']) {
            array_push($condiciones, " D.Estado_Facturacion='$_REQUEST[fact]'");
            // array_push($condiciones_dispensacion, " D.Estado_Facturacion='$_REQUEST[fact]'");
        }

        $auditoria = (isset($_REQUEST['auditoria']) ? $_REQUEST['auditoria'] : '');
        if ($auditoria) {
            if ($auditoria == "Sin Auditar") {
                array_push($condiciones, "D.Estado_Auditoria like '$auditoria'");
            } else {
                array_push($condiciones, "(A.Estado like '$auditoria' OR A1.Estado like '$auditoria')");
            }
        }


        if (isset($_REQUEST['est']) && $_REQUEST['est']) {
            array_push($condiciones, "D.Estado_Dispensacion='$_REQUEST[est]'");
            // array_push($condiciones_dispensacion, "D.Estado_Dispensacion='$_REQUEST[est]'");
        }

        if (isset($_REQUEST['funcionario']) && $_REQUEST['funcionario'] != "") {
            // array_push($condiciones_dispensacion, "D.Identificacion_Funcionario=$_REQUEST[funcionario]");
            array_push($condiciones, "D.Identificacion_Funcionario=$_REQUEST[funcionario]");
        }

        if (isset($_REQUEST['id_punto']) && $_REQUEST['id_punto'] != "") {
            array_push($condiciones, "(D.Id_Punto_Dispensacion=$_REQUEST[id_punto] OR D.Id_Propharmacy=$_REQUEST[id_punto] )");
        }

        if (isset($_REQUEST['eps']) && $_REQUEST['eps'] != "") {
            array_push($condiciones, "PC.Nit=$_REQUEST[eps]");
        }


        if (isset($_REQUEST['pend']) && $_REQUEST['pend'] != "") {
            // array_push($condiciones_dispensacion, $_REQUEST['pend'] == "Si" ? "D.Pendientes>0" : " D.Pendientes=0");
            array_push($condiciones, $_REQUEST['pend'] == "Si" ? "D.Pendientes>0" : " D.Pendientes=0");
        }

        if (count($_REQUEST) == 0) {
            $condicion_fechas .= " AND  DATE(D.Fecha_Actual) BETWEEN '$ultimos_dos_meses' AND '$hoy'";
        } elseif (count($_REQUEST) == 1) {
            $condicion_fechas .= " AND DATE(D.Fecha_Actual) BETWEEN '$ultimos_dos_meses' AND '$hoy'";
        }

        $condicion = count($condiciones) > 0 ? "WHERE " . implode(" AND ", $condiciones) : '';
        // $condicion_dis  = count($condiciones_dispensacion)>0? implode(" AND ", $condiciones_dispensacion)." AND ":'';

        if ($condicion != '') {
            $condicion1 = '';
            $condicion2 = '';
        }

        // $condicion_dis .= $condicion2;
// echo $condicion_dis; exit;
        $query = 'SELECT COUNT(*) AS Total
        FROM Dispensacion D
        LEFT JOIN Auditoria A ON A.Id_Dispensacion = D.Id_Dispensacion
        LEFT JOIN Auditoria A1 ON A1.Id_Auditoria = D.Id_Auditoria
        STRAIGHT_JOIN patients PC
        on D.Numero_Documento=PC.identifier
        STRAIGHT_JOIN Punto_Dispensacion P
        on D.Id_Punto_Dispensacion=P.Id_Punto_Dispensacion
        STRAIGHT_JOIN departments L
        ON P.Departamento = L.id
        ' . $condicion . $condicion1;
        $oCon = new consulta();

        $oCon->setQuery($query);
        $dispensaciones = $oCon->getData();
        // $dispensaciones = [];
        unset($oCon);

        ####### PAGINACIÓN ########
        $tamPag = $_REQUEST['pageSize'] ?? 20;
        $numReg = $dispensaciones["Total"];
        // $numReg = 350000;
        $paginas = ceil($numReg / $tamPag);
        $limit = "";
        $paginaAct = "";

        if (!isset($_REQUEST['page']) || $_REQUEST['page'] == '') {
            $paginaAct = 1;
            $limit = 0;
        } else {
            $paginaAct = $_REQUEST['page'];
            $limit = ($paginaAct - 1) * $tamPag;
        }

        $query = "SELECT
        D.Codigo,
        D.Fecha_Actual,
        D.Id_Punto_Dispensacion,
        D.Numero_Documento,
        DATE_FORMAT(D.Fecha_Actual, '%d/%m/%Y') AS Fecha_Dis,
        TS.Nombre AS Nombre_Tipo_Servicio,
        CONCAT_WS(' ',  PC.firstname,  PC.middlename,  PC.surname,  PC.secondsurname) AS Paciente,
        CONCAT(S.Nombre,' - ',TS.Nombre) AS Tipo,
        P.Nombre AS Punto_Dispensacion,
        P.Wacom,D.Acta_Entrega,
        L.name AS Departamento,
        D.Estado EstadoEntrega,
        D.Estado_Dispensacion AS Estado,
        D.Estado_Facturacion,
        D.Estado_Dispensacion,
        CONCAT_WS(' ', D.Estado_Auditoria, A.Estado, A1.Estado) AS Estado_Auditoria,
        D.Id_Factura,
        ifnull(A.Id_Auditoria, A1.Id_Auditoria) AS Id_Auditoria,
        D.Pendientes,
        D.Id_Dispensacion,
        IF(D.Estado_Dispensacion ='Anulada' OR PD.Estado ='Anulada', Null, PD.Tutela	) AS Tiene_Tutela,
        IF(D.Estado_Dispensacion ='Anulada' OR PD.Estado ='Anulada', NULL, PD.Tutela	) AS Tutela,
        IF(D.Estado_Dispensacion ='Anulada' OR PD.Estado ='Anulada', NULL, PD.Platino	) AS Platino

        FROM Dispensacion D
        LEFT JOIN (
            (SELECT PDA.id, D2.Id_Dispensacion AS Id_Dispensacion, trim(if(PDA.RLmarcaEmpleador!='', PDA.RLmarcaEmpleador, NULL)) AS Platino,  if(PDA.tieneTutela !='' || PDA.tieneTutela !='0', PDA.tieneTutela , NULL)AS Tutela, PDA.Estado,  D2.Codigo
				  FROM Positiva_Data PDA
				  inner JOIN Dispensacion D2 on PDA.id = D2.Id_Positiva_Data  )
                  Union all (
                      SELECT PDA.id, D2.Id_Dispensacion AS Id_Dispensacion, trim(if(PDA.RLmarcaEmpleador!='', PDA.RLmarcaEmpleador, NULL)) AS Platino,  if(PDA.tieneTutela !='' || PDA.tieneTutela !='0', PDA.tieneTutela , NULL)AS Tutela, PDA.Estado,  D2.Codigo
				  FROM Positiva_Data PDA
				  inner JOIN Dispensacion D2 on PDA.Id_Dispensacion = D2.Id_Dispensacion
                  )
        )PD ON PD.Id_Dispensacion = D.Id_Dispensacion
        LEFT JOIN Auditoria A ON A.Id_Dispensacion=D.Id_Dispensacion
		LEFT JOIN Auditoria A1 ON A1.Id_Auditoria = D.Id_Auditoria
        STRAIGHT_JOIN patients PC ON D.Numero_Documento=PC.identifier
        STRAIGHT_JOIN Punto_Dispensacion P ON D.Id_Punto_Dispensacion=P.Id_Punto_Dispensacion
        STRAIGHT_JOIN departments L ON P.Departamento = L.id
        LEFT JOIN Tipo_Servicio TS ON TS.Id_Tipo_Servicio = D.Id_Tipo_Servicio
        LEFT JOIN Servicio S ON TS.Id_Servicio=S.Id_Servicio
          $condicion $condicion1
          $orden LIMIT $limit , $tamPag";


        $oCon = new consulta();

        $oCon->setQuery($query);
        $oCon->setTipo('Multiple');
        $dispensaciones["dispensaciones"] = $oCon->getData();
        $dispensaciones["indicadores"] = [];
        unset($oCon);


        $dispensaciones["numReg"] = $numReg;

        $condicion2 = " AND  DATE_FORMAT(Fecha_Actual,'%Y-%m-%d') BETWEEN '$fecha_inicio' AND '$fecha_fin' ";
        $condicion3 = " AND  DATE_FORMAT(Fecha_Actual,'%Y-%m-%d') BETWEEN '$fecha_inicio' AND '$fecha_fin' ";
        $query = "SELECT
        (SELECT COUNT(*) FROM Dispensacion WHERE Estado_Dispensacion!='Anulada' $condicion3) AS Dis_Totales,
        (SELECT COUNT(*) FROM Dispensacion WHERE Tipo='Capita' $condicion2 AND Estado_Dispensacion!='Anulada') AS Dis_Capitadas,
        (SELECT COUNT(*) FROM Dispensacion WHERE Tipo='Evento' $condicion2 AND Estado_Dispensacion!='Anulada') AS Dis_Eventos,
        (SELECT COUNT(*) FROM Dispensacion WHERE Tipo='NoPos' $condicion2 AND Estado_Dispensacion!='Anulada') AS Dis_NoPos,
        (SELECT COUNT(*) FROM Dispensacion WHERE Pendientes > 0 $condicion2 AND Estado_Dispensacion!='Anulada') AS Dis_Pendientes,
        (SELECT COUNT(*) FROM Dispensacion WHERE Estado_Facturacion='Facturada' $condicion2) AS Dis_Facturadas";
        $oCon = new consulta();
        $oCon->setQuery($query);
        $dispensaciones["indicadores"] = $oCon->getData();
        unset($oCon);
        if (isset($_REQUEST['id_punto']) && $_REQUEST['id_punto'] != "") {
            $condicion2 .= " AND Id_Punto_Dispensacion=$_REQUEST[id_punto]  ";
        }

        /* if ($condicion2 != "" && $_REQUEST['id_punto'] != '' && isset($_REQUEST['id_punto'])) {
            $condicion3 = " AND Id_Punto_Dispensacion=$_REQUEST[id_punto] ";
        } */
        return response()->json($dispensaciones);
    }

    /* public function listaDispensaciones()
    {
        $hoy = date('Y-m-d');
        $ultimos_dos_meses = strtotime('-2 month', strtotime($hoy));
        $ultimos_dos_meses = date('Y-m-01', $ultimos_dos_meses);
        $condicion_fechas = '';
        $condicion = '';
        $condicion1 = '';
        if (isset($_REQUEST['fecha']) && $_REQUEST['fecha'] != "") {
            $fecha_inicio = trim(explode(' - ', $_REQUEST['fecha'])[0]);
            $fecha_fin = trim(explode(' - ', $_REQUEST['fecha'])[1]);
            $condicion .= " WHERE DATE_FORMAT(Fecha_Actual,'%Y-%m-%d') BETWEEN '$fecha_inicio' AND '$fecha_fin'";
        } else {
            $fecha_fin = date('Y-m-d');
            $fecha_inicio = date('Y-m-d');
        }
        $condicion1 .= " WHERE DATE_FORMAT(Fecha_Actual,'%Y-%m-%d') BETWEEN '$fecha_inicio' AND '$fecha_fin'";
        if (isset($_REQUEST['cod']) && $_REQUEST['cod'] != "") {
            if ($condicion != "") {
                $condicion .= " AND D.Codigo LIKE '%$_REQUEST[cod]%'";
            } else {
                $condicion .= " WHERE D.Codigo LIKE '%$_REQUEST[cod]%'";
            }
        }
        if ($condicion != "") {
            if (isset($_REQUEST['tipo']) && $_REQUEST['tipo']) {
                if ($_REQUEST['tipo'] != 'todos') {
                    $condicion .= " AND D.Id_Tipo_Servicio=$_REQUEST[tipo]";
                }
            }
        } else {
            if (isset($_REQUEST['tipo']) && $_REQUEST['tipo']) {
                if ($_REQUEST['tipo'] != 'todos') {
                    $condicion .= "WHERE D.Id_Tipo_Servicio=$_REQUEST[tipo]";
                }
            }
        }
        if ($condicion != "") {
            if (isset($_REQUEST['pers']) && $_REQUEST['pers']) {
                $condicion .= " AND (CONCAT(PC.firstname, ' ',  PC.surname) LIKE '%$_REQUEST[pers]%' OR D.Numero_Documento LIKE '%$_REQUEST[pers]%')";
            }
        } else {
            if (isset($_REQUEST['pers']) && $_REQUEST['pers']) {
                $condicion .= "WHERE (CONCAT(PC.firstname, ' ',  PC.surname) LIKE '%$_REQUEST[pers]%' OR D.Numero_Documento LIKE '%$_REQUEST[pers]%')";
            }
        }
        if ($condicion != "") {
            if (isset($_REQUEST['punto']) && $_REQUEST['punto']) {
                $condicion .= " AND P.Nombre LIKE '%$_REQUEST[punto]%'";
            }
        } else {
            if (isset($_REQUEST['punto']) && $_REQUEST['punto']) {
                $condicion .= "WHERE P.Nombre LIKE '%$_REQUEST[punto]%'";
            }
        }
        if ($condicion != "") {
            if (isset($_REQUEST['dep']) && $_REQUEST['dep']) {
                $condicion .= " AND L.Nombre LIKE '%$_REQUEST[dep]%'";
            }
        } else {
            if (isset($_REQUEST['dep']) && $_REQUEST['dep']) {
                $condicion .= "WHERE L.Nombre LIKE '%$_REQUEST[dep]%'";
            }
        }
        if ($condicion != "") {
            if (isset($_REQUEST['fact']) && $_REQUEST['fact']) {
                $condicion .= " AND D.Estado_Facturacion='$_REQUEST[fact]'";
            }
        } else {
            if (isset($_REQUEST['fact']) && $_REQUEST['fact']) {
                $condicion .= "WHERE D.Estado_Facturacion='$_REQUEST[fact]'";
            }
        }
        $auditoria = (isset($_REQUEST['auditoria']) ? $_REQUEST['auditoria'] : '');
        if ($auditoria)
            if ($condicion != "") {
                if ($auditoria == "Sin Auditar") {
                    $condicion .= " AND D.Estado_Auditoria like '$auditoria'";
                } else {
                    $condicion .= " AND A.Estado like '$auditoria'";
                }
            } else {
                if ($auditoria == "Sin Auditar") {
                    $condicion .= " WHERE D.Estado_Auditoria like '$auditoria'";
                } else {
                    $condicion .= " WHERE A.Estado like '$auditoria'";
                }
            }
        if ($condicion != "") {
            if (isset($_REQUEST['est']) && $_REQUEST['est']) {
                $condicion .= " AND D.Estado_Dispensacion='$_REQUEST[est]'";
            }
        } else {
            if (isset($_REQUEST['est']) && $_REQUEST['est']) {
                $condicion .= "WHERE D.Estado_Dispensacion='$_REQUEST[est]'";
            }
        }
        if (isset($_REQUEST['funcionario']) && $_REQUEST['funcionario'] != "") {
            if ($condicion != "") {
                $condicion .= " AND D.Identificacion_Funcionario=$_REQUEST[funcionario]";
            } else {
                $condicion .= " WHERE D.Identificacion_Funcionario=$_REQUEST[funcionario]";
            }
        }
        if (isset($_REQUEST['id_punto']) && $_REQUEST['id_punto'] != "") {
            if ($condicion != "") {
                $condicion .= " AND D.Id_Punto_Dispensacion=$_REQUEST[id_punto]";
            } else {
                $condicion .= "WHERE D.Id_Punto_Dispensacion=$_REQUEST[id_punto]";
            }
        }
        if (isset($_REQUEST['eps']) && $_REQUEST['eps'] != "") {
            if ($condicion != "") {
                $condicion .= " AND PC.Nit=$_REQUEST[eps]";
            } else {
                $condicion .= "WHERE PC.Nit=$_REQUEST[eps]";
            }
        }
        if (isset($_REQUEST['pend']) && $_REQUEST['pend'] != "") {
            if ($condicion != "") {
                $condicion .= $_REQUEST['pend'] == "Si" ? " AND D.Pendientes>0" : " AND D.Pendientes=0";
            } else {
                $condicion .= $_REQUEST['pend'] == "Si" ? " WHERE D.Pendientes>0" : " WHERE D.Pendientes=0";
            }
        }
        if (count($_REQUEST) == 0) {
            $condicion_fechas .= " AND  DATE(D.Fecha_Actual) BETWEEN '$ultimos_dos_meses' AND '$hoy'";
        } elseif (count($_REQUEST) == 1) {
            $condicion_fechas .= " AND DATE(D.Fecha_Actual) BETWEEN '$ultimos_dos_meses' AND '$hoy'";
        }
        if ($condicion != '') {
            $condicion1 = '';
        }
        $query = 'SELECT COUNT(*) AS Total
            FROM Dispensacion D
            Left JOIN Auditoria A ON A.Id_Dispensacion=D.Id_Dispensacion
            STRAIGHT_JOIN patients PC
            on D.Numero_Documento=PC.id
            STRAIGHT_JOIN Punto_Dispensacion P
            on D.Id_Punto_Dispensacion=P.Id_Punto_Dispensacion
            STRAIGHT_JOIN Departamento L
            ON P.Departamento = L.Id_Departamento
            ' . $condicion . $condicion1;


        $oCon = new consulta();
        $oCon->setQuery($query);
        $dispensaciones = $oCon->getData();
        unset($oCon);
        $tamPag = $_REQUEST['pageSize'] ?? 2;
        $numReg = $dispensaciones["Total"];
        $paginas = ceil($numReg / $tamPag);
        $limit = "";
        $paginaAct = "";
        if (!isset($_REQUEST['page']) || $_REQUEST['page'] == '') {
            $paginaAct = 1;
            $limit = 0;
        } else {
            $paginaAct = $_REQUEST['page'];
            $limit = ($paginaAct - 1) * $tamPag;
        }
        $query = 'SELECT
            D.Codigo,
            D.Fecha_Actual,
            D.Id_Punto_Dispensacion,
            (SELECT Nombre FROM Tipo_Servicio WHERE Id_Tipo_Servicio = D.Id_Tipo_Servicio) AS Nombre_Tipo_Servicio, D.Numero_Documento,
                DATE_FORMAT(D.Fecha_Actual, "%d/%m/%Y") AS Fecha_Dis,
            (SELECT CONCAT_WS(" ", firstname, surname) FROM patients
                WHERE identifier = D.Numero_Documento) AS Paciente,
            P.Nombre AS Punto_Dispensacion,
            P.Wacom,
            D.Acta_Entrega,
            L.Nombre AS Departamento,
            D.Estado AS EstadoEntrega,
            D.Estado_Dispensacion AS Estado,
            (SELECT CONCAT(S.Nombre," - ",T.Nombre)
                FROM Tipo_Servicio T
                INNER JOIN Servicio S ON T.Id_Servicio=S.Id_Servicio
                WHERE T.Id_Tipo_Servicio=D.Id_Tipo_Servicio ) as Tipo,
                D.Estado_Facturacion,
                D.Estado_Dispensacion,
                IFNULL (Concat( D.Estado_Auditoria, " ", A.Estado), D.Estado_Auditoria) as Estado_Auditoria,
                D.Id_Factura,
                A.Id_Auditoria,
                D.Pendientes,
                D.Id_Dispensacion,
                IF(D.Id_Tipo_Servicio!=7,
                    IFNULL((SELECT SUM(Subtotal) FROM Producto_Factura WHERE Id_Factura=D.Id_Factura),0),0 ) as Valor_Factura, D.Id_Factura
                    FROM Dispensacion D
                    Left JOIN Auditoria A ON A.Id_Dispensacion=D.Id_Dispensacion
                    STRAIGHT_JOIN patients PC on D.Numero_Documento=PC.identifier
                    STRAIGHT_JOIN Punto_Dispensacion P on D.Id_Punto_Dispensacion=P.Id_Punto_Dispensacion
                    STRAIGHT_JOIN Departamento L ON P.Departamento = L.Id_Departamento
                    ' . $condicion . $condicion1 . '
                    ORDER BY D.Id_Dispensacion DESC LIMIT ' . $limit . ',' . $tamPag;
        $oCon = new consulta();
        $oCon->setQuery($query);
        $oCon->setTipo('Multiple');
        $dispensaciones["dispensaciones"] = $oCon->getData();
        unset($oCon);
        $dispensaciones["numReg"] = $numReg;
        $condicion2 = " AND  DATE_FORMAT(Fecha_Actual,'%Y-%m-%d') BETWEEN '$fecha_inicio' AND '$fecha_fin' ";
        $condicion3 = " AND  DATE_FORMAT(Fecha_Actual,'%Y-%m-%d') BETWEEN '$fecha_inicio' AND '$fecha_fin' ";
        // if (isset($_REQUEST['id_punto']) && $_REQUEST['id_punto'] != "") {
        //    $condicion2 .= " AND Id_Punto_Dispensacion=$_REQUEST[id_punto]  ";
        // }
        //if ($condicion2 != "" && $_REQUEST['id_punto'] != '' && isset($_REQUEST['id_punto'])) {
        //   $condicion3 = " AND Id_Punto_Dispensacion=$_REQUEST[id_punto] ";
        // }
        $query = "SELECT
            (SELECT COUNT(*) FROM Dispensacion WHERE Estado_Dispensacion!='Anulada' $condicion3) AS Dis_Totales,
            (SELECT COUNT(*) FROM Dispensacion WHERE Tipo='Capita' $condicion2 AND Estado_Dispensacion!='Anulada') AS Dis_Capitadas,
            (SELECT COUNT(*) FROM Dispensacion WHERE Tipo='Evento' $condicion2 AND Estado_Dispensacion!='Anulada') AS Dis_Eventos,
            (SELECT COUNT(*) FROM Dispensacion WHERE Tipo='NoPos' $condicion2 AND Estado_Dispensacion!='Anulada') AS Dis_NoPos,
            (SELECT COUNT(*) FROM Dispensacion WHERE Pendientes > 0 $condicion2 AND Estado_Dispensacion!='Anulada') AS Dis_Pendientes,
            (SELECT COUNT(*) FROM Dispensacion WHERE Estado_Facturacion='Facturada' $condicion2) AS Dis_Facturadas";
        $oCon = new consulta();
        $oCon->setQuery($query);
        $dispensaciones["indicadores"] = $oCon->getData();
        unset($oCon);
        return response()->json($dispensaciones);
    } */

    public function getServicios()
    {
        $queryObj = new QueryBaseDatos();
        $punto = (isset($_REQUEST['id_punto']) ? $_REQUEST['id_punto'] : '');
        $condicion = $this->SetCondiciones($punto);
        $query = $this->GetQuery($condicion);
        $queryObj->SetQuery($query);
        $servicios = DB::select($query);
        return response()->json($servicios);
    }

    function SetCondiciones($punto)
    {
        $condicion = '';
        if ($punto != '') {
            $condicion = 'WHERE P.Id_Punto_Dispensacion =' . $punto;
        }
        return $condicion;
    }

    function GetQuery($condicion)
    {
        if ($condicion != '') {
            $query = "SELECT T.Id_Tipo_Servicio, CONCAT(S.Nombre,' - ',T.Nombre) as Nombre, T.Nombre AS Nombre_Tipo_Servicio
                FROM Tipo_Servicio_Punto_Dispensacion P
                INNER JOIN Tipo_Servicio T ON P.Id_Tipo_Servicio=T.Id_Tipo_Servicio
                INNER JOIN Servicio S on T.Id_Servicio=S.Id_Servicio $condicion ";
        } else {
            $query = "SELECT T.Id_Tipo_Servicio, CONCAT(S.Nombre,' - ',T.Nombre) as Nombre, T.Nombre AS Nombre_Tipo_Servicio FROM Tipo_Servicio T INNER JOIN Servicio S on T.Id_Servicio=S.Id_Servicio ";
        }
        return $query;
    }

    public function indicadores(Request $request)
    {
        $queryObj = new QueryBaseDatos();
        $colores = ['bg-c-blue', 'bg-info', 'bg-inverse', 'bg-c-pink', 'bg-c-lite-green', 'bg-default', 'bg-facebook'];
        $iconos_servicios = ['ti ti-tag', 'ti ti-receipt'];
        $condicion = $this->SetCondiciones2($request);
        $query = "SELECT S.Nombre AS Servicio, r.* FROM ( SELECT Id_Servicio, COUNT(Id_Servicio) AS Total_Servicio, COUNT(CASE WHEN Pendientes > 0 THEN 1 ELSE NULL END) AS Pendientes, COUNT(CASE WHEN Estado_Facturacion = 'Facturada' AND Id_Tipo_Servicio != 7 THEN 1 ELSE NULL END) AS Total_Facturadas FROM `Dispensacion` $condicion GROUP BY Id_Servicio ) r INNER JOIN Servicio S ON S.Id_Servicio = r.Id_Servicio";
        $indicadores = [];
        $respuesta = [];
        $queryObj->SetQuery($query);
        $resultado = $queryObj->ExecuteQuery('Multiple');
        $total = array_sum(array_column($resultado, 'Total_Servicio'));
        $pendientes = array_sum(array_column($resultado, 'Pendientes'));
        $facturadas = array_sum(array_column($resultado, 'Total_Facturadas'));
        $info_adicional = [
            "Titulo" => "Dispensaciones totales",
            "Total" => $total,
            "class" => "bg-info",
            "icono" => "bi bi-ticket"
        ];
        array_push($indicadores, $info_adicional);
        foreach ($resultado as $i => $value) {
            $info = [
                "Titulo" => "Dispensaciones" . strtolower($value->Servicio),
                "Total" => $value->Total_Servicio,
                "class" => $colores[$i],
                "icono" => $iconos_servicios[$i % 2]
            ];
            $indicadores[] = $info;
        }
        $info_adicional = [
            "Titulo" => "Dispensaciones pendientes",
            "Total" => $pendientes,
            "class" => "bg-warning",
            "icono" => "fas fa-hourglass-end"
        ];
        array_push($indicadores, $info_adicional);
        $info_adicional = [
            "Titulo" => "Dispensaciones facturadas",
            "Total" => $facturadas,
            "class" => "bg-success",
            "icono" => "bi bi-cash-stack"
        ];
        array_push($indicadores, $info_adicional);
        return response()->json($indicadores);
    }

    function SetCondiciones2($request)
    {
        $condicion = '';
        if ($request->fecha) {
            $fecha_inicio = trim(explode(' - ', $_REQUEST['fecha'])[0]);
            $fecha_fin = trim(explode(' - ', $_REQUEST['fecha'])[1]);
            $condicion .= " WHERE DATE(Fecha_Actual) BETWEEN '$fecha_inicio' AND '$fecha_fin'";
        } else {
            $condicion = " WHERE DATE(Fecha_Actual)=CURRENT_DATE()";
        }
        if ($request->id_punto) {
            $condicion .= " AND Id_Punto_Dispensacion=$_REQUEST[id_punto]";
        }
        return $condicion;
    }

    function fecha($str)
    {
        $parts = explode(" ", $str);
        $date = explode("-", $parts[0]);
        return $date[2] . "/" . $date[1] . "/" . $date[0];
    }

    public function dispensacionPdf(Request $request)
    {
        $id = (isset($_REQUEST['id']) ? $_REQUEST['id'] : '');
        $ruta = (isset($_REQUEST['Ruta']) ? $_REQUEST['Ruta'] : '');


        unset($oItem);
        $query = "SELECT D.*, PD.numeroAutorizacion, DATE_FORMAT(D.Fecha_Actual, '%d/%m/%Y') as Fecha_Dis, CONCAT(F.first_name, ' ',  F.first_surname) as Funcionario, P.Nombre as Punto_Dispensacion, P.Direccion as Direccion_Punto, P.Telefono Telefono_Punto, L.name as Departamento, CONCAT_WS(' ',Paciente.Primer_Nombre,Paciente.Segundo_Nombre,Paciente.Primer_Apellido, Paciente.Segundo_Apellido) as Nombre_Paciente , Paciente.EPS, Paciente.Direccion as Direccion_Paciente, R.Nombre as Regimen_Paciente, Paciente.Id_Paciente,  F.signature, F.identifier as Funcionario1,
            IFNULL((SELECT Numero_Telefono FROM Paciente_Telefono WHERE Id_Paciente = D.Numero_Documento LIMIT 1), 'No Registrado' ) AS Telefono_Paciente, (SELECT CONCAT(S.Nombre,' - ' ,T.Nombre) FROM Tipo_Servicio T INNER JOIN Servicio S ON T.Id_Servicio=S.Id_Servicio WHERE T.Id_Tipo_Servicio=D.Id_Tipo_Servicio ) as Tipo,
            (SELECT Numero_Prescripcion FROM Producto_Dispensacion WHERE Id_Dispensacion = D.Id_Dispensacion LIMIT 1) AS Numero_Prescripcion
            FROM Dispensacion D
            LEFT JOIN Positiva_Data PD ON PD.Id_Dispensacion = D.Id_Dispensacion
            INNER JOIN people F
            on D.Identificacion_Funcionario=F.identifier
            INNER JOIN Punto_Dispensacion P
            on D.Id_Punto_Dispensacion=P.Id_Punto_Dispensacion
            INNER JOIN departments L
            on P.Departamento=L.id
            INNER JOIN Paciente
            on D.Numero_Documento = Paciente.Id_Paciente
            INNER JOIN Regimen R
            on Paciente.Id_Regimen = R.Id_Regimen
            WHERE D.Id_Dispensacion=$id";
        $oCon = new consulta();
        $oCon->setQuery($query);
        $encabezado = $oCon->getData();
        unset($oCon);

        $marca_agua = 'backimg="' . $_SERVER["DOCUMENT_ROOT"] . '/IMAGENES/MARCA_DE_AGUA/acta-entrega.png" backimgw="80%"';
        if ($request->productos) {
            $productos = (isset($_REQUEST['productos']) ? $_REQUEST['productos'] : '');
            $productos = (array) json_decode(utf8_decode($productos), true);
            $productos = $this->getNombres($productos);
            $marca_agua = 'backimg="' . $_SERVER["DOCUMENT_ROOT"] . '/IMAGENES/MARCA_DE_AGUA/ACTA2.png" backimgw="80%"';
        } else {
            $query = "SELECT PD.*, IFNULL(CONCAT_WS(' ',
                Producto.Principio_Activo,
                Producto.Presentacion,
                Producto.Concentracion, '(',
                Producto.Nombre_Comercial,')',
                Producto.Cantidad,
                Producto.Unidad_Medida
                ), Producto.Nombre_Comercial) as Nombre_Producto, Producto.Codigo_Cum as Cum,
                Producto.Invima
            FROM Producto_Dispensacion as PD
            INNER JOIN Producto
            on Producto.Id_Producto=PD.Id_Producto
            WHERE PD.Id_Dispensacion =$id";

            $oCon = new consulta();
            $oCon->setQuery($query);
            $oCon->setTipo('Multiple');
            $productos = $oCon->getData();
            unset($oCon);
        }
        $query = "SELECT * FROM Dispensacion_Reclamante WHERE Dispensacion_Id = '$id' ";
        $oCon = new consulta();
        $oCon->SetQuery($query);
        $customReclamante = $oCon->getData() ? $oCon->getData()['Reclamante_Id'] : null;
        $contenido = '';
        unset($oCon);

        if ($customReclamante != null && $customReclamante != 'null') {
            $query = "SELECT Reclamante.* , DR.Parentesco FROM Reclamante   INNER JOIN Dispensacion_Reclamante AS DR
            ON Reclamante.Id_Reclamante = DR.Reclamante_Id
                WHERE Id_Reclamante = '$customReclamante'";
            $oCon = new consulta();
            $oCon->SetQuery($query);
            $customReclamante = $oCon->getData();
            unset($oCon);
        } else {
            $customReclamante = ['Id_Reclamante' => '', 'Nombre' => '', 'Parentesco' => ''];
        }

        $header = (object) [
            'Titulo' => 'Productos',
            'Codigo' => $encabezado->code ?? '',
            'Fecha' => $encabezado->created_at ?? '',
            'CodigoFormato' => $encabezado->format_code ?? '',
        ];



        $pdf = Pdf::loadView('pdf.dispensacion_pdf', [
            'encabezado' => $encabezado,
            'datosCabecera' => $header,
            'productos' => $productos,
            'customReclamante' => $customReclamante,
        ]);

        return $pdf->download("Dispensacion");

    }

    function getNombres(array $productos)
    {
        $i = 0;
        foreach ($productos as $producto) {
            array_push($producto['Id']);

            $query =
                "SELECT IFNULL(CONCAT_WS(' ',
        Producto.Principio_Activo,
        Producto.Presentacion,
        Producto.Concentracion, '(',
        Producto.Nombre_Comercial,')',
        Producto.Cantidad,
        Producto.Unidad_Medida
        ), Producto.Nombre_Comercial) As Nombre_Producto,
        Producto.Codigo_Cum as Cum,
        Producto.Invima
        FROM Producto Where Producto.Id_Producto = $producto[Id]";
            $oCon = new consulta();
            $oCon->setQuery($query);
            $prop = $oCon->getData();
            $productos[$i]['Nombre_Producto'] = $prop['Nombre_Producto'];
            $productos[$i]['Cum'] = $prop['Cum'];
            // $productos[$i]['Invima'] = $prop['Invima'];
            $i++;
        }
        return $productos;
    }

    public function productosPdf()
    {
        $tipo = (isset($_REQUEST['tipo']) ? $_REQUEST['tipo'] : '');
        $id = (isset($_REQUEST['id']) ? $_REQUEST['id'] : '');

        $query = "SELECT D.*, DATE_FORMAT(D.Fecha_Actual, '%d/%m/%Y') as Fecha_Dis, CONCAT(F.first_name, ' ',  F.first_surname) as Funcionario, P.Nombre as Punto_Dispensacion, P.Direccion as Direccion_Punto, P.Telefono Telefono_Punto, L.name as Departamento, CONCAT_WS(' ',Paciente.Primer_Nombre,Paciente.Segundo_Nombre,Paciente.Primer_Apellido, Paciente.Segundo_Apellido) as Nombre_Paciente , Paciente.EPS, Paciente.Direccion as Direccion_Paciente, R.Nombre as Regimen_Paciente, Paciente.Id_Paciente, Paciente.Tipo_Documento, (SELECT CONCAT(S.Nombre,'-' ,T.Nombre) FROM Tipo_Servicio T INNER JOIN Servicio S ON T.Id_Servicio=S.Id_Servicio WHERE T.Id_Tipo_Servicio=D.Id_Tipo_Servicio ) as Tipo
            FROM Dispensacion D
            INNER JOIN people F
            on D.Identificacion_Funcionario=F.identifier
            INNER JOIN Punto_Dispensacion P
            on D.Id_Punto_Dispensacion=P.Id_Punto_Dispensacion
            INNER JOIN departments L
            on P.Departamento=L.id
            INNER JOIN Paciente
            on D.Numero_Documento = Paciente.Id_Paciente
            INNER JOIN Regimen R
            on Paciente.Id_Regimen = R.Id_Regimen
            WHERE D.Id_Dispensacion=$id";
        $oCon = new consulta();
        $oCon->setQuery($query);
        $data = $oCon->getData();


        unset($oCon);
        if (isset($data['Tipo']) == 'Pos-Capita') {
            $cuota = " MODERADORA";
        } else {
            $cuota = " RECUPERACION";
        }
        ob_start(); // Se Inicializa el gestor de PDF
        $style = '<style>
            .page-content{
            width:55mm;;
            }
            </style>';


        $oItem = new complex('Paciente', "Id_Paciente", $data["Numero_Documento"] ?? '');
        $paciente = $oItem->getData();
        unset($oItem);

        $oItem = new complex('Regimen', "Id_Regimen", $paciente["Id_Regimen"] ?? '');
        $regimen = $oItem->getData();
        unset($oItem);

        $oItem = new complex("third_parties", "id",  $paciente["Nit"] ?? '');
        $cliente = $oItem->getData();
        unset($oItem);

        $query = 'SELECT PD.*, P.Nombre_Comercial
         FROM Producto_Dispensacion PD
         INNER JOIN Producto P
         ON PD.Id_Producto = P.Id_Producto
          WHERE PD.Id_Dispensacion =  ' . $id;

        $oCon = new consulta();
        $oCon->setQuery($query);
        $oCon->setTipo('Multiple');
        $productos = $oCon->getData();
        unset($oCon);

        $header = (object) [
            'Titulo' => 'Productos',
            'Codigo' => $data->code ?? '',
            'Fecha' => $data->created_at ?? '',
            'CodigoFormato' => $data->format_code ?? '',
        ];



        $pdf = Pdf::loadView('pdf.productospdf', [
            'data' => $data,
            'datosCabecera' => $header,
            'cliente' => $cliente,
            'productos' => $productos,


        ]);

        return $pdf->download("Productos");
    }

    public function descargaPdf()
    {
        $tipo = (isset($_REQUEST['tipo']) ? $_REQUEST['tipo'] : '');
        $id = (isset($_REQUEST['id']) ? $_REQUEST['id'] : '');

        $query = "SELECT D.*, DATE_FORMAT(D.Fecha_Actual, '%d/%m/%Y') as Fecha_Dis, CONCAT(F.first_name, ' ',  F.first_surname) as Funcionario, P.Nombre as Punto_Dispensacion, P.Direccion as Direccion_Punto, P.Telefono Telefono_Punto, L.name as Departamento, CONCAT_WS(' ',Paciente.Primer_Nombre,Paciente.Segundo_Nombre,Paciente.Primer_Apellido, Paciente.Segundo_Apellido) as Nombre_Paciente , Paciente.EPS, Paciente.Direccion as Direccion_Paciente, R.Nombre as Regimen_Paciente, Paciente.Id_Paciente,  Paciente.Tipo_Documento, (SELECT CONCAT(S.Nombre,'-' ,T.Nombre) FROM Tipo_Servicio T INNER JOIN Servicio S ON T.Id_Servicio=S.Id_Servicio WHERE T.Id_Tipo_Servicio=D.Id_Tipo_Servicio ) as Tipo
            FROM Dispensacion D
            INNER JOIN people F
            on D.Identificacion_Funcionario=F.identifier
            INNER JOIN Punto_Dispensacion P
            on D.Id_Punto_Dispensacion=P.Id_Punto_Dispensacion
            INNER JOIN departments L
            on P.Departamento=L.id
            INNER JOIN Paciente
            on D.Numero_Documento = Paciente.Id_Paciente
            INNER JOIN Regimen R
            on Paciente.Id_Regimen = R.Id_Regimen
            WHERE D.Id_Dispensacion=$id";

        $oCon = new consulta();
        $oCon->setQuery($query);
        $data = $oCon->getData();
        unset($oCon);

        if (isset($data['Tipo']) && $data['Tipo'] == 'Pos-Capita') {
            $cuota = " MODERADORA";
        } else {
            $cuota = " RECUPERACION";
        }

        ob_start(); // Se Inicializa el gestor de PDF
        $style = '<style>
            .page-content{
            width:55mm;;
            }
            </style>';
        $oItem = new complex('Paciente', "Id_Paciente", $data["Numero_Documento"] ?? '');
        $paciente = $oItem->getData();
        unset($oItem);
        $oItem = new complex('Paciente_Telefono', "Id_Paciente", $data["Numero_Documento"] ?? '');
        $telefono = $oItem->getData() ? $oItem->getData()['Numero_Telefono'] : '';
        unset($oItem);
        $oItem = new complex('Regimen', "Id_Regimen", $paciente["Id_Regimen"] ?? '');
        $regimen = $oItem->getData();
        unset($oItem);
        $oItem = new complex("third_parties", "id",  $paciente["Nit"] ?? '');
        $cliente = $oItem->getData();
        unset($oItem);
        $query = 'SELECT PD.*, P.Nombre_Comercial
         FROM Producto_Dispensacion PD
         INNER JOIN Producto P
         ON PD.Id_Producto = P.Id_Producto
          WHERE PD.Cantidad_Formulada > PD.Cantidad_Entregada AND PD.Id_Dispensacion =  ' . $id;

        $oCon = new consulta();
        $oCon->setQuery($query);
        $oCon->setTipo('Multiple');
        $productos = $oCon->getData();
        unset($oCon);




        $header = (object) [
            'Titulo' => 'Productos dispensacion',
            'Codigo' => $data->code ?? '',
            'Fecha' => $data->created_at ?? '',
            'CodigoFormato' => $data->format_code ?? '',

        ];



        $pdf = Pdf::loadView('pdf.descarga_pdf_dispensacion', [
            'data' => $data,
            'datosCabecera' => $header,
            'cliente' => $cliente,
            'productos' => $productos,
            'cuota' =>  $cuota,
            'telefono' => $telefono,
            'regimen' => $regimen,


        ]);

        return $pdf->download("Productos");
    }

    public function eliminarDispensacion(Request $request)
    {
        $serviceDelete = new DeleteAlerts();
        $datos = $request->all();
        $func = $request->funcionario;
        $oItem = new complex('Dispensacion', 'Id_Dispensacion', $datos['Id_Dispensacion']);
        $oItem->Estado_Dispensacion = "Anulada";
        $oItem->save();
        unset($oItem);
        $query = "SELECT Id_Inventario_Nuevo, Cantidad_Entregada FROM Producto_Dispensacion WHERE Id_Dispensacion=" . $datos['Id_Dispensacion'] . " AND Lote <> 'Pendiente'";
        $oCon = new consulta();
        $oCon->setQuery($query);
        $oCon->setTipo('Multiple');
        $productos = $oCon->getData();
        unset($oCon);
        $query = "SELECT id, Id_Dispensacion
          FROM Positiva_Data
          WHERE Id_Dispensacion=" . $datos['Id_Dispensacion'] . " ";
        $oCon = new consulta();
        $oCon->setQuery($query);
        $positiva = $oCon->getData();
        unset($oCon);
        if ($positiva) {
            $query = "UPDATE Positiva_Data SET Id_Dispensacion = NULL  WHERE id = " . $positiva["id"];
            $oCon = new consulta();
            $oCon->setQuery($query);
            $resultado = $oCon->createData();
            unset($oCon);
        }

        $disToDelete = $serviceDelete->search($datos['Id_Dispensacion']);
        $serviceDelete->delete($disToDelete);
        foreach ($productos as $prod) { // Ingresar nuevamente las cantidades al inventario.
            $oItem = new complex('Inventario_Nuevo', 'Id_Inventario_Nuevo', $prod['Id_Inventario_Nuevo']);
            $cantidad = number_format($prod['Cantidad_Entregada'], 0, "", "");
            $cantidad_final = $oItem->Cantidad + $cantidad;
            $oItem->Cantidad = number_format($cantidad_final, 0, "", "");
            $oItem->save();
            unset($oItem);
        }
        $ActividadDis["Identificacion_Funcionario"] = $func;
        $ActividadDis["Id_Dispensacion"] = $datos['Id_Dispensacion'];
        $ActividadDis['Fecha'] = date("Y-m-d H:i:s");
        $ActividadDis["Detalle"] = "Esta dispensacion fue anulada por el siguiente motivo: " . $datos['Motivo_Anulacion'];
        $ActividadDis["Estado"] = "Anulada";
        $oItem = new complex("Actividades_Dispensacion", "Id_Actividades_Dispensacion");
        foreach ($ActividadDis as $index => $value) {
            $oItem->$index = $value;
        }
        $oItem->save();
        unset($oItem);
        $query = "SELECT Id_Dispensacion_Mipres FROM Dispensacion WHERE Id_Dispensacion=" . $datos['Id_Dispensacion'];
        $oCon = new consulta();
        $oCon->setQuery($query);
        $Id_Dispensacion_Mipres = $oCon->getData();
        unset($oCon);
        if ($Id_Dispensacion_Mipres) {
            $mipres = new Mipres();
            $query = "SELECT * FROM Producto_Dispensacion_Mipres WHERE Id_Dispensacion_Mipres=" . $Id_Dispensacion_Mipres["Id_Dispensacion_Mipres"];
            $oCon = new consulta();
            $oCon->setQuery($query);
            $oCon->setTipo("Multiple");
            $lista = $oCon->getData();
            unset($oCon);
            foreach ($lista as $mipres_dis) {
                if ($mipres_dis["IdReporteEntrega"] != '' && $mipres_dis["IdReporteEntrega"] != '0') { //echo "entro a eliminar reporte entrega<br>";
                    $res1 = $mipres->AnularReporteEntrega($mipres_dis["IdReporteEntrega"]);
                }
                if ($mipres_dis["IdEntrega"] != '' && $mipres_dis["IdEntrega"] != '0') {  //echo "entro a eliminar id entrega<br>";
                    $res2 = $mipres->AnularEntrega($mipres_dis["IdEntrega"]);
                }
                if ($mipres_dis["IdProgramacion"] != '' && $mipres_dis["IdProgramacion"] != '0') {  //echo "entro a eliminar programacion<br>";
                    $res3 = $mipres->AnularProgramacion($mipres_dis["IdProgramacion"]);
                }
                $query = "UPDATE Producto_Dispensacion_Mipres SET IdReporteEntrega=0, IdEntrega=0, IdProgramacion=0  WHERE Id_Producto_Dispensacion_Mipres = " . $mipres_dis["Id_Producto_Dispensacion_Mipres"];
                $oCon = new consulta();
                $oCon->setQuery($query);
                $res = $oCon->createData();
                unset($oCon);
            }
            $query = "UPDATE Dispensacion_Mipres SET Estado = 'Pendiente'  WHERE Id_Dispensacion_Mipres = " . $Id_Dispensacion_Mipres["Id_Dispensacion_Mipres"];
            $oCon = new consulta();
            $oCon->setQuery($query);
            $resultado = $oCon->createData();
            unset($oCon);
        }
        $resu["Mensaje"] = "Anulado Correctamente";
        echo json_encode($resu);
    }
}
