<?php

namespace App\Http\Services;

use App\Http\Services\consulta;
class CostoPromedio {
    public $existencia_actual; //ok
    private $existencia_nueva; //OK
    private $costo_actual;//OK
    private $valor_nueva_entrada;   //OK
    private $id_producto; //OK
    private $valor_inventario; // Valor total actual de mi producto (Costo Actual x Existencia Actual)
    private $costo_promedio;
    private $costo_ingresado;

    function __construct($id_producto,$nueva_cantidad,$nuevo_costo)
    {
        $this->costo_ingresado = $nuevo_costo;
        $this->id_producto = $id_producto;
        $this->existencia_nueva = (int)$nueva_cantidad;
        $this->valor_nueva_entrada = ((float)$nuevo_costo * (int)$nueva_cantidad);
        $this->existencia_actual = $this->buscarExistenciaActual();
        $this->costo_actual = $this->buscarCostoActual();

        $this->valor_inventario = $this->valorEnInventario();

        $this->costo_promedio = $this->calcularCostoPromedio();


    }
    private function calcularCostoPromedio(){

        $costo = ($this->valor_inventario + $this->valor_nueva_entrada) / ($this->existencia_actual + $this->existencia_nueva);
       return $costo;

    }

    function valorEnInventario(){
        return (float)$this->costo_actual * $this->existencia_actual;

    }
    private function buscarExistenciaActual(){
        $oCon=new consulta();
        $query='SELECT SUM( Cantidad-( Cantidad_Apartada+ Cantidad_Seleccionada)) AS Cantidad FROM Inventario_Nuevo WHERE Id_Producto = '.$this->id_producto.' AND Id_Estiba IS NOT NULL AND Id_Estiba <> 0';

        $oCon->setQuery($query);
        $cantidad=$oCon->getData();
        unset($oCon);
        return (int)$cantidad['Cantidad'];
    }

    private function buscarCostoActual(){
        $oCon=new consulta();
        $query="SELECT Costo_Promedio FROM Costo_Promedio WHERE Id_Producto = ".$this->id_producto;
        $oCon->setQuery($query);

        $costo_actual=$oCon->getData();


        unset($oCon);
        return $costo_actual['Costo_Promedio'];
    }

    function getCostoPromedio(){

        return $this->costo_promedio;
    }

    function actualizarCostoPromedio(){
        #actualiza el costo en la base de datos

        if($this->costo_actual == null){
           $oCon=new consulta();
            $query='INSERT INTO  Costo_Promedio (Id_Producto, Costo_Promedio) VALUES ('.$this->id_producto.', '. number_format($this->costo_ingresado, 2, '.', '') . ')';

            $oCon->setQuery($query);
            $oCon->createData();
            unset($oCon);
        }else{
            $costo = 0;
            if((float)$this->costo_actual >= 1){
                $costo = $this->costo_promedio;
            }else{
                 $costo = $this->costo_ingresado;
            }

            $oCon=new consulta();
            $query='UPDATE Costo_Promedio SET  Costo_Anterior = Costo_Promedio, Ultima_Actualizacion = NOW(), Costo_Promedio = '. number_format($costo, 2, '.', '') .'
             WHERE Id_Producto = '.$this->id_producto;

            $oCon->setQuery($query);
            $oCon->createData();
            unset($oCon);
        }

    }
}
/*
$costo = new Costo_Promedio(56227,2,500);
var_dump($costo->existencia_actual); */
