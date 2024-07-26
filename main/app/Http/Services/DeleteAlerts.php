<?php

namespace App\Http\Services;

use App\Http\Services\consulta;

class DeleteAlerts
{
    public static function search(string $id)
    {
        try {
            $query = "SELECT * FROM Dispensacion WHERE Id_Dispensacion = '$id' ";
            $oCon = new consulta();
            $oCon->setQuery($query);
            $oCon->setTipo('Simple');
            $result = $oCon->getData();
            unset($oCon);
            return  $result[0]->Codigo;
        } catch (\Throwable $th) {
            echo $th->getMessage();
        }
    }
    public static function delete(string $id)
    {
        try {
            $query = "DELETE FROM Alerta WHERE Modulo = '$id'";
            $oCon = new consulta();
            $oCon->setQuery($query);
            $oCon->createData();
            unset($oCon);

            return;
        } catch (\Throwable $th) {
            echo $th->getMessage();
        }
    }
}