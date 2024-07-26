<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CompanyResource extends JsonResource
{

    public function toArray($request)
    {
        return [
            'value' => $this->id,
            'text' => $this->name,
            'tipo' => ($this->type) ? 'Compañias propias' : 'Compañias de terceros',
            'estado' => ($this->state) ? 'Activo' : 'Inactivo',
            'categoria' => ($this->category)
        ];
    }
}
