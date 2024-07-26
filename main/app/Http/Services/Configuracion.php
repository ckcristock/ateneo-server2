<?php

namespace App\Http\Services;

use App\Http\Services\consulta;
use App\Http\Services\complex;
use App\Http\Services\QueryBaseDatos;


class Configuracion
{
    private $company_id;
    private $Id_Configuracion;

    public function __construct($company_id)
    {
        $this->company_id = $company_id;
        # code...
    }

    function prefijoConsecutivo($index)
    {

        $query = 'SELECT * FROM Configuracion WHERE Id_Configuracion = ' . $this->company_id;
        $oCon = new consulta();
        $oCon->setQuery($query);
        $nc = $oCon->getData();

        /*
    $oItem = new complex('Configuracion','Id_Configuracion',1);
    $nc = $oItem->getData();
    unset($oItem);*/
        unset($oCon);
        $this->Id_Configuracion = $nc['Id_Configuracion'];

        $prefijo = $nc["Prefijo_" . $index];


        return $prefijo;
    }

    function guardarConsecutivoConfig($index, $consecutivo)
    {

        $oItem = new complex('Configuracion', 'Id_Configuracion', $this->Id_Configuracion);
        $nc = $oItem->getData();
        $oItem->$index = $consecutivo += 1;
        /* echo $index;
     echo $consecutivo;exit;*/
        $oItem->save();

        unset($oItem);
    }

    function getConsecutivo($mod, $tipo_consecutivo)
    {

        sleep(strval(rand(2, 8)));
        # $query = "SELECT  MAX(Codigo)  AS Codigo FROM $mod ";
        $query = "SELECT MAX(N.Codigo) AS Codigo
         FROM ( SELECT Codigo FROM $mod ORDER BY Id_$mod DESC LIMIT 2
         )N ";


        $oCon = new consulta();
        $oCon->setQuery($query);
        $res = $oCon->getData();
        unset($oCon);
        $prefijo = $this->prefijoConsecutivo($tipo_consecutivo);


        $NumeroCodigo = substr($res['Codigo'], strlen($prefijo));
        //var_dump($NumeroCodigo);exit;
        $NumeroCodigo += 1;

        $cod = $prefijo . $NumeroCodigo;

        $query = "SELECT Id_$mod AS ID FROM $mod WHERE Codigo = '$cod'";
        $oCon = new consulta();
        $oCon->setQuery($query);
        $res2 = $oCon->getData();
        unset($oCon);


        if ($res2 && $res2["ID"]) {
            sleep(strval(rand(0, 3)));
            $this->getConsecutivo($mod, $tipo_consecutivo);
        }

        $this->guardarConsecutivoConfig($tipo_consecutivo, $NumeroCodigo);

        return $cod;
    }

    function Consecutivo($index)
    {
        $oItem = new complex('Configuracion', 'Id_Configuracion', 1);
        $nc = $oItem->getData();
        $consecutivo = number_format((int) $oItem->$index, 0, "", "");
        $oItem->$index = $consecutivo + 1;
        $oItem->save();
        $num_cotizacion = $nc[$index];
        unset($oItem);

        $cod = $nc["Prefijo_" . $index] . sprintf("%05d", $num_cotizacion);

        return $cod;
    }
}
