<?php

// namespace Database\Seeders;

use App\Models\TypeCondition;
use App\Models\VariableType;
use Illuminate\Database\Seeder;

class VariableTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $textVariableType = VariableType::create([
            'name' => 'Texto',
            'value' => 'text',
            'primary' => true,
        ]);

        $textAreaVariableType = VariableType::create([
            'name' => 'Área de texto',
            'value' => 'textarea',
            'primary' => true,
        ]);

        $numberVariableType = VariableType::create([
            'name' => 'Numérico',
            'value' => 'number',
            'primary' => true,
        ]);

        $lengthCondition = TypeCondition::create([
            'name' => 'Longitud',
            'value' => 'length',
            'type' => 'number',
        ]);

        $decimalLengthCondition = TypeCondition::create([
            'name' => 'Cantidad de decimales',
            'value' => 'length',
            'type' => 'number',
        ]);

        $textAreaVariableType->conditions()->attach($lengthCondition);
        $textVariableType->conditions()->attach($lengthCondition);
        $numberVariableType->conditions()->attach($decimalLengthCondition);

        $FechaVariableType = VariableType::create([
            'name' => 'Fecha',
            'value' => 'date',
            'primary' => true,
        ]);

        $minDateCondition = TypeCondition::create([
            'name' => 'fecha mínima',
            'value' => 'min-date',
            'type' => 'date',
        ]);

        $maxDateCondition = TypeCondition::create([
            'name' => 'fecha máxima',
            'value' => 'max-date',
            'type' => 'date',
        ]);

        $FechaVariableType->conditions()->attach($minDateCondition);
        $FechaVariableType->conditions()->attach($maxDateCondition);

        $RangeVariableType = VariableType::create([
            'name' => 'Rango de fecha',
            'value' => 'range',
            'primary' => true,
        ]);

        $maxRangeCondition = TypeCondition::create([
            'name' => 'Rango máximo de días',
            'value' => 'maxRange',
            'type' => 'number',
        ]);

        $minRangeCondition = TypeCondition::create([
            'name' => 'Rango minimo de días',
            'value' => 'minRange',
            'type' => 'number',
        ]);

        $RangeVariableType->conditions()->attach($minDateCondition);
        $RangeVariableType->conditions()->attach($maxDateCondition);
        $RangeVariableType->conditions()->attach($maxRangeCondition);
        $RangeVariableType->conditions()->attach($minRangeCondition);

        $timeVariableType = VariableType::create([
            'name' => 'Hora',
            'value' => 'time',
            'primary' => true,
        ]);

        $minTimeCondition = TypeCondition::create([
            'name' => 'Tiempo mínimo',
            'value' => 'min-time',
            'type' => 'string',
        ]);

        $maxTimeCondition = TypeCondition::create([
            'name' => 'tiempo máximo',
            'value' => 'max-time',
            'type' => 'string',
        ]);

        $timeVariableType->conditions()->attach($minTimeCondition);
        $timeVariableType->conditions()->attach($maxTimeCondition);

        $variableTypes = [
            [
                'name' => 'Booleano',
                'value' => 'boolean',
                'primary' => true,
            ],
            [
                'name' => 'Lista existente',
                'value' => 'exist-list',
                'primary' => false,
            ],
            [
                'name' => 'Lista personalizada',
                'value' => 'custom-list',
                'primary' => false,
            ],
        ];

        // Insertar los datos en la base de datos de forma masiva
        VariableType::insert($variableTypes);
    }
}
