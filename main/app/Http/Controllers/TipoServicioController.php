<?php

namespace App\Http\Controllers;

use App\Http\Services\complex;
use App\Http\Services\consulta;
use App\Http\Services\HttpResponse;
use App\Http\Services\QueryBaseDatos;
use App\Http\Services\table;
use App\Models\TipoServicio;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class TipoServicioController extends Controller
{
    use ApiResponser;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->success(TipoServicio::get(['nombre AS text', 'Id_Tipo_Servicio AS value']));
    }

    public function listaTipoServicio()
    {
        $condicion = '';
        if (isset($_REQUEST['cod']) && $_REQUEST['cod'] != "") {
            $condicion .= "WHERE Codigo LIKE '%$_REQUEST[cod]%'";
        }
        if ($condicion != "") {
            if (isset($_REQUEST['tipo']) && $_REQUEST['tipo'] != "") {
                $condicion .= " AND Nombre LIKE '%$_REQUEST[tipo]%'";
            }
        } else {
            if (isset($_REQUEST['tipo']) && $_REQUEST['tipo'] != "") {
                $condicion .= "WHERE Nombre LIKE '%$_REQUEST[tipo]%'";
            }
        }
        $query = 'SELECT COUNT(*) AS Total FROM Tipo_Servicio ' . $condicion;
        $oCon = new consulta();
        $oCon->setQuery($query);
        $total = $oCon->getData();
        unset($oCon);
        ####### PAGINACIÓN ########
        $tamPag = 15;
        $numReg = $total["Total"];
        $paginas = ceil($numReg / $tamPag);
        $limit = "";
        $paginaAct = "";
        if (!isset($_REQUEST['pag']) || $_REQUEST['pag'] == '') {
            $paginaAct = 1;
            $limit = 0;
        } else {
            $paginaAct = $_REQUEST['pag'];
            $limit = ($paginaAct - 1) * $tamPag;
        }
        $query = 'SELECT TS.* FROM Tipo_Servicio TS ' . $condicion . ' ORDER BY Codigo DESC LIMIT ' . $limit . ',' . $tamPag;
        $oCon = new consulta();
        $oCon->setQuery($query);
        $oCon->setTipo('Multiple');
        $tipo_servicios['Servicios'] = $oCon->getData();
        unset($oCon);
        $tipo_servicios['numReg'] = $numReg;
        return response()->json($tipo_servicios);
    }

    public function saveTipoServicio(Request $request)
    {
        $http_response = new HttpResponse();
        $queryObj = new QueryBaseDatos();
        $repsonse = array();
        $modelo = (isset($_REQUEST['modelo']) ? $_REQUEST['modelo'] : '');
        $campos = (isset($_REQUEST['campos']) ? $_REQUEST['campos'] : '');
        $tiposoporte = (isset($_REQUEST['tiposoporte']) ? $_REQUEST['tiposoporte'] : '');
        $contratos = (isset($_REQUEST['contratos']) ? $_REQUEST['contratos'] : '');
        $modelo = json_decode($modelo, true);
        $campos = json_decode($campos, true);
        $tiposoporte = json_decode($tiposoporte, true);
        // $contratos = json_decode($contratos, true);
        $contratos = (array) json_decode($contratos, true);
        $fecha = date('Y-m-d H:i:s');
        $validateIdTipoServicio = $modelo['Id_Tipo_Servicio'] ?? false;
        if (!$validateIdTipoServicio) {
            $oItem = new complex("Tipo_Servicio", "Id_Tipo_Servicio");
        } else {
            $oItem = new complex("Tipo_Servicio", "Id_Tipo_Servicio", $modelo['Id_Tipo_Servicio']);
        }
        foreach ($modelo as $index => $value) {
            $oItem->$index = $value;
        }
        $oItem->save();
        $id_servicio = $oItem->getId();
        unset($oItem);
        if (is_array($tiposoporte)) {
            foreach ($tiposoporte as $tipo) {
                if ($tipo["Pre_Auditoria"] != "") {
                    $tipo['Pre_Auditoria'] = "Si";
                } else {
                    $tipo['Pre_Auditoria'] = "No";
                }
                if ($tipo["Auditoria"] != "") {
                    $tipo['Auditoria'] = "Si";
                } else {
                    $tipo['Auditoria'] = "No";
                }
                if ($tipo["Tipo_Soporte"] != "") {
                    $tipo["Id_Tipo_Servicio"] = $id_servicio;
                    $oItem = new complex('Tipo_Soporte', 'Id_Tipo_Soporte');
                    foreach ($tipo as $index => $value) {
                        $oItem->$index = $value;
                    }
                    $oItem->save();
                    unset($oItem);
                }
            }
        }
        //modificado roberth 04-08-2021
        $i = -1;
        foreach ($contratos as $index) {
            $i++;
            if ($id_servicio) {
                $oItem = new complex("Tipo_Servicio_Contrato", "Id_Tipo_Servicio_", $id_servicio);
            } else {
                $oItem = new complex("Tipo_Servicio_Contrato", "Id_Tipo_Servicio_Contrato");
            }
            $oItem->Id_Tipo_Servicio = $id_servicio;
            $oItem->Id_Contrato = $index['Id_Contrato'];
            $oItem->save();
            unset($oItem);
        }
        $i = -1;
        foreach ($campos as $item) {
            $i++;
            $val = '';
            $oItem = new complex("Campos_Tipo_Servicio", "Id_Campos_Tipo_Servicio");
            $tipo = '';
            if ($item['Tipo'] == 'text') {
                $tipo = ' VARCHAR(200)';
            } elseif ($item['Tipo'] == 'number') {
                if ($item['Longitud'] > 10) {
                    $tipo = ' BIGINT(20)';
                } else {
                    $tipo = ' INT(20)';
                }
            } elseif ($item['Tipo'] == 'date') {
                $tipo = ' DATE';
            }
            if ($item['Tipo_Campo'] == 'Cabecera') {

                $val = $this->AjustarNombreCampo($item['Nombre']);

                if ($item['Modulo'] == 'Dispensacion') {
                    $this->AgregarCampo($val, $tipo, "Dispensacion", $queryObj);
                } elseif ($item['Modulo'] == 'Auditoria') {
                    $this->AgregarCampo($val, $tipo, "Auditoria", $queryObj);
                } elseif ($item['Modulo'] == 'Ambos') {
                    $this->AgregarCampo($val, $tipo, "Dispensacion", $queryObj);
                    $this->AgregarCampo($val, $tipo, "Auditoria", $queryObj);
                }
            } elseif ($item['Tipo_Campo'] == 'Producto') {

                $val = $this->AjustarNombreCampo($item['Nombre']);
                $this->AgregarCampo($val, $tipo, "Producto_Dispensacion", $queryObj);
            }

            foreach ($item as $index => $value) {
                if ($index == 'Nombre') {
                    $oItem->$index = $val;
                } else {
                    if ($value != '' && $value != null) {
                        $oItem->$index = $value;
                    }

                }
            }
            $oItem->Id_Tipo_Servicio = $id_servicio;
            $oItem->save();
            unset($oItem);
        }

        $http_response->SetRespuesta(0, 'Operación exitosa', 'Se ha registrado el servicio correctamente');
        $repsonse = $http_response->GetRespuesta();

        return response()->json($repsonse);
    }

    function AgregarCampo($nombreCampo, $tipo, $tabla, $queryObj)
    {
        $query = "SELECT * FROM $tabla LIMIT 1";
        $queryObj->SetQuery($query);
        $data = $queryObj->ExecuteQuery('simple');
        $campos = $this->ArmarCadenaCamposTabla($data);
        $exist = $this->ValidarExistencia($campos, $nombreCampo);
        if (!$exist) {
            $modificar_tabla = new table($tabla);
            $modificar_tabla->addColumn($nombreCampo, $tipo);
            $modificar_tabla->save();
            unset($modificar_tabla);
        }
    }

    function AgregarCampoDispensacion($nombreCampo, $tipo, $queryObj)
    {
        $query = 'SELECT * FROM Dispensacion LIMIT 1';
        $queryObj->SetQuery($query);
        $data = $queryObj->ExecuteQuery('simple');

        $camposDispensacion = $this->ArmarCadenaCamposTabla($data);
        $exist = $this->ValidarExistencia($camposDispensacion, $nombreCampo);
        if (!$exist) {
            $modificar_tabla = new table("Dispensacion");
            $modificar_tabla->addColumn($nombreCampo, $tipo);
            $modificar_tabla->save();
            unset($modificar_tabla);
        }
    }

    function AgregarCampoAuditoria($nombreCampo, $tipo, $queryObj)
    {
        $query = 'SELECT * FROM Auditoria LIMIT 1';
        $queryObj->SetQuery($query);
        $data = $queryObj->ExecuteQuery('simple');

        $camposAuditoria = $this->ArmarCadenaCamposTabla($data);
        $exist = $this->ValidarExistencia($camposAuditoria, $nombreCampo);
        if (!$exist) {
            $modificar_tabla = new table("Auditoria");
            $modificar_tabla->addColumn($nombreCampo, $tipo);
            $modificar_tabla->save();
            unset($modificar_tabla);
        }
    }

    function AjustarNombreCampo($nombreCampo)
    {
        $palabras_campo = explode(" ", $nombreCampo);
        $nueva_palabra = '';

        foreach ($palabras_campo as $palabra) {
            $p = strtolower($palabra);
            $p = ucfirst($p);
            $nueva_palabra .= $p . '_';
        }

        return trim($nueva_palabra, "_");
    }

    function ArmarCadenaCamposTabla($tableData)
    {
        $cadena = '';
        foreach ($tableData as $key => $value) {
            $cadena .= $key . ",";
        }

        $camposTabla = explode(",", $cadena);

        return $camposTabla;
    }

    function ValidarExistencia($data, $key)
    {
        $pos = array_search($key, $data);
        return $pos;
    }

    function ValidarExistenciaUpdate($data, $key)
    {
        foreach ($data as $k => $value) {
            $data[$k] = strtolower($value);
        }
        $pos = array_search(strtolower($key), $data);
        return $pos;
    }

    function ActualizarCampo($oldName, $nombreCampo, $tipo, $tabla, $queryObj)
    {
        $query = "SELECT * FROM $tabla LIMIT 1";
        $queryObj->SetQuery($query);
        $data = $queryObj->ExecuteQuery('simple');

        $campos = $this->ArmarCadenaCamposTabla($data);


        $exist = $this->ValidarExistenciaUpdate($campos, $nombreCampo);

        if (!$exist) {
            $modificar_tabla = new table($tabla);
            $modificar_tabla->setColumn($oldName, $nombreCampo, $tipo);
            $modificar_tabla->save();
            unset($modificar_tabla);
        }
    }

    function ComparacionNombres($oldName, $newName)
    {
        return $oldName == $newName;
    }

    public function updateTipoServicio(Request $request)
    {
        $http_response = new HttpResponse();
        $queryObj = new QueryBaseDatos();
        $repsonse = array();
        $modelo = (isset($_REQUEST['modelo']) ? $_REQUEST['modelo'] : '');
        $campos = (isset($_REQUEST['campos']) ? $_REQUEST['campos'] : '');
        $id_tipo_servicio = (isset($_REQUEST['id_tipo_servicio']) ? $_REQUEST['id_tipo_servicio'] : '');
        $tiposoporte = (isset($_REQUEST['tiposoporte']) ? $_REQUEST['tiposoporte'] : '');
        $contratos = (isset($_REQUEST['contratos']) ? $_REQUEST['contratos'] : '');
        $modelo = json_decode($modelo, true);
        $campos = json_decode($campos, true);
        $tiposoporte = json_decode($tiposoporte, true);
        $contratos = (array) json_decode($contratos, true);
        $oItem = new complex('Tipo_Servicio', 'Id_Tipo_Servicio', $modelo['Id_Tipo_Servicio']);
        foreach ($modelo as $index => $value) {
            if ($value != '' && $value != null) {
                $oItem->$index = $value;
            }
        }
        $oItem->save();
        $id_servicio = $oItem->getId();
        unset($oItem);
        $i = -1;
        foreach ($contratos as $index) {
            $i++;
            if ($id_servicio) {
                $oItem = new complex("Tipo_Servicio_Contrato", "Id_Tipo_Servicio_Contrato", $index['Id_Contrato']);
            } else {
                $oItem = new complex("Tipo_Servicio_Contrato", "Id_Tipo_Servicio_Contrato");
            }
            $oItem->Id_Tipo_Servicio = $id_servicio;
            $oItem->Id_Contrato = $index['Id_Contrato'];
            $oItem->save();
            unset($oItem);
        }
        foreach ($tiposoporte as $tipo) {
            if ($tipo['Id_Tipo_Soporte']) {
                $oItem = new complex('Tipo_Soporte', 'Id_Tipo_Soporte', $tipo['Id_Tipo_Soporte']);
                foreach ($tipo as $index => $value) {
                    $oItem->$index = $value;
                }
                $oItem->save();
                unset($oItem);
            } else if ($tipo['Tipo_Soporte'] != '') {
                $oItem = new complex('Tipo_Soporte', 'Id_Tipo_Soporte');
                $tipo['Id_Tipo_Servicio'] = $modelo['Id_Tipo_Servicio'];
                foreach ($tipo as $index => $value) {
                    $oItem->$index = $value;
                }
                $oItem->save();
                unset($oItem);
            }
        }
        foreach ($campos as $item) {
            if ($item['Edicion'] != '0') {
                $val = '';
                $oItem = new complex("Campos_Tipo_Servicio", "Id_Campos_Tipo_Servicio", $item['Id_Campos_Tipo_Servicio']);
                unset($item['Id_Campos_Tipo_Servicio']);
                $tipo = '';
                if ($item['Tipo'] == 'text') {
                    $tipo = ' VARCHAR(200)';
                } elseif ($item['Tipo'] == 'number') {
                    $tipo = ' BIGINT(20)';
                } elseif ($item['Tipo'] == 'date') {
                    $tipo = ' DATE';
                }
                $comparacion = $this->ComparacionNombres($item['Nombre_Original'], $item['Nombre']);
                if (!$comparacion) {
                    $val = $this->AjustarNombreCampo($item['Nombre']);
                    if ($item['Tipo_Campo'] == 'Cabecera') {
                        if ($item['Modulo'] == 'Dispensacion') {
                            $this->ActualizarCampo($item['Nombre_Original'], $val, $tipo, "Dispensacion", $queryObj);
                        } elseif ($item['Modulo'] == 'Auditoria') {
                            $this->ActualizarCampo($item['Nombre_Original'], $val, $tipo, "Auditoria", $queryObj);
                        } elseif ($item['Modulo'] == 'Ambos') {
                            $this->ActualizarCampo($item['Nombre_Original'], $val, $tipo, "Dispensacion", $queryObj);
                            $this->ActualizarCampo($item['Nombre_Original'], $val, $tipo, "Auditoria", $queryObj);
                        }
                    } elseif ($item['Tipo_Campo'] == 'Producto') {
                        $this->ActualizarCampo($item['Nombre_Original'], $val, $tipo, "Producto_Dispensacion", $queryObj);
                    }
                }
                foreach ($item as $index => $value) {
                    if ($index == 'Nombre') {
                        if (!$comparacion) {
                            $oItem->$index = $val;
                        } else {
                            $oItem->$index = $item['Nombre_Original'];
                        }
                    } else {
                        if ($value != '' && $value != null) {
                            $oItem->$index = $value;
                        }

                    }
                }

                $oItem->Id_Tipo_Servicio = $id_servicio;
                $oItem->save();
                unset($oItem);
            } else if ($item['Edicion'] == '0') {
                unset($item['Id_Campos_Tipo_Servicio']);
                $val = '';
                $oItem = new complex("Campos_Tipo_Servicio", "Id_Campos_Tipo_Servicio");

                $tipo = '';
                if ($item['Tipo'] == 'text') {
                    $tipo = ' VARCHAR(200)';
                } elseif ($item['Tipo'] == 'number') {
                    $tipo = ' BIGINT(20)';
                } elseif ($item['Tipo'] == 'date') {
                    $tipo = ' DATE';
                }

                if ($item['Tipo_Campo'] == 'Cabecera') {

                    $val = $this->AjustarNombreCampo($item['Nombre']);

                    if ($item['Modulo'] == 'Dispensacion') {
                        $this->AgregarCampo($val, $tipo, "Dispensacion", $queryObj);
                    } elseif ($item['Modulo'] == 'Auditoria') {
                        $this->AgregarCampo($val, $tipo, "Auditoria", $queryObj);
                    } elseif ($item['Modulo'] == 'Ambos') {
                        $this->AgregarCampo($val, $tipo, "Dispensacion", $queryObj);
                        $this->AgregarCampo($val, $tipo, "Auditoria", $queryObj);
                    }
                } elseif ($item['Tipo_Campo'] == 'Producto') {

                    $val = $this->AjustarNombreCampo($item['Nombre']);
                    $this->AgregarCampo($val, $tipo, "Producto_Dispensacion", $queryObj);
                }

                foreach ($item as $index => $value) {
                    if ($index == 'Nombre') {
                        $oItem->$index = $val;
                    } else {
                        if ($value != '' && $value != null) {
                            $oItem->$index = $value;
                        }
                    }
                }

                $oItem->Id_Tipo_Servicio = $id_servicio;
                $oItem->save();
                unset($oItem);
            }
        }

        $http_response->SetRespuesta(0, 'Operacion exitosa', 'Se ha registrado el servicio exitosamente');
        $repsonse = $http_response->GetRespuesta();

        return response()->json($repsonse);
    }

    public function cambiarEstadoCampo()
    {
        $http_response = new HttpResponse();
        $queryObj = new QueryBaseDatos();
        $repsonse = array();

        $modelo = (isset($_REQUEST['modelo']) ? $_REQUEST['modelo'] : '');
        $tipo = (isset($_REQUEST['tipo']) ? $_REQUEST['tipo'] : '');

        $modelo = json_decode($modelo, true);

        if ($tipo == 'Estado') {
            $oItem = new complex("Campos_Tipo_Servicio", "Id_Campos_Tipo_Servicio", $modelo['Id_Campos_Tipo_Servicio']);
            $oItem->Estado = $modelo['Estado'];
            $oItem->save();
            unset($oItem);


            $http_response->SetRespuesta(0, 'Cambio exitoso', "Se ha $modelo[Estado]  el  campo exitosamente!");
            $repsonse = $http_response->GetRespuesta();
        } else {
            $oItem = new complex("Campos_Tipo_Servicio", "Id_Campos_Tipo_Servicio", $modelo['Id_Campos_Tipo_Servicio']);
            $oItem->Longitud = $modelo['Longitud'];
            $oItem->save();
            unset($oItem);


            $http_response->SetRespuesta(0, 'Cambio exitoso', "Se ha la longitud del  campo exitosamente!");
            $repsonse = $http_response->GetRespuesta();
        }


        return response()->json($repsonse);
    }

    public function detalleTipoServicio()
    {
        $id = (isset($_REQUEST['id']) ? $_REQUEST['id'] : '');

        $query = 'SELECT TP.*
            FROM Tipo_Servicio TP
            WHERE TP.Id_Tipo_Servicio =' . $id;

        $oCon = new consulta();
        $oCon->setQuery($query);
        $tipo = $oCon->getData();
        unset($oCon);

        $query = 'SELECT TS.*
            FROM Tipo_Soporte TS
            WHERE TS.Id_Tipo_Servicio =' . $id;

        $oCon = new consulta();
        $oCon->setQuery($query);
        $oCon->setTipo('Multiple');
        $soporte = $oCon->getData();
        unset($oCon);

        $query = 'SELECT 	Id_Campos_Tipo_Servicio,Nombre,Tipo,Id_Tipo_Servicio,Tipo_Campo,Longitud,Requerido,Modulo, Nombre AS Nombre_Original, 1 AS Edicion, Estado, Fecha_Formula
            FROM Campos_Tipo_Servicio
            WHERE Tipo_Campo="Producto" AND  Id_Tipo_Servicio =' . $id;

        $oCon = new consulta();
        $oCon->setQuery($query);
        $oCon->setTipo('Multiple');
        $Campo_producto = $oCon->getData();
        unset($oCon);

        $query = 'SELECT 	Id_Campos_Tipo_Servicio,Nombre,Tipo,Id_Tipo_Servicio,Tipo_Campo,Longitud, Requerido,Modulo, Nombre AS Nombre_Original, 1 AS Edicion,Estado
            FROM Campos_Tipo_Servicio
            WHERE Tipo_Campo="Cabecera" AND  Id_Tipo_Servicio =' . $id;

        $oCon = new consulta();
        $oCon->setQuery($query);
        $oCon->setTipo('Multiple');
        $Campo_cabecera = $oCon->getData();
        unset($oCon);
        foreach ($Campo_cabecera as $key => $value) {
            foreach ($value as $campo => $val) {
                if ($campo == 'Edicion') {
                    $Campo_cabecera[$key]->Edicion = intval($val);
                }
            }
        }

        foreach ($Campo_producto as $key => $value) {
            foreach ($value as $campo => $val) {
                if ($campo == 'Edicion') {
                    $Campo_producto[$key]->Edicion = intval($val);
                }
            }
            if ($value->Fecha_Formula == 'Si') {
                $Campo_producto[$key]->Display = 'true';
            } else {
                $Campo_producto[$key]->Display = 'false';
            }
        }

        $resultado['Tipo_Servicio'] = $tipo;
        $resultado['Soportes'] = $soporte;
        $resultado['Campos_Producto'] = $Campo_producto;
        $resultado['Campos_Cabecera'] = $Campo_cabecera;

        return response()->json($resultado);
    }

    public function listaContratoSelect()
    {

        $query = "SELECT C.*, C.Id_Contrato AS value, C.Nombre_Contrato AS label
            FROM Contrato C WHERE C.Estado = 'Activo'";

        $oCon = new consulta();
        $oCon->setTipo('Multiple');
        $oCon->setQuery($query);
        $contratos = $oCon->getData();
        unset($oCon);

        return response()->json($contratos);
    }

    public function servicios()
    {

        $query = 'SELECT Id_Servicio,Nombre
            FROM Servicio ';
        $oCon = new consulta();
        $oCon->setQuery($query);
        $oCon->setTipo('Multiple');
        $servicio = $oCon->getData();
        unset($oCon);
        return response()->json($servicio);
    }

}
