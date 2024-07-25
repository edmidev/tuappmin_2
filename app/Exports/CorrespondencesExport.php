<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;

class CorrespondencesExport implements FromCollection, WithHeadings, WithColumnWidths
{
    use Exportable;
    /**
    * @return \Illuminate\Support\Collection
    */
    protected $conjunto;
    protected $request;

    function __construct($conjunto, $request) {
        $this->conjunto = $conjunto;
        $this->request = $request;
    }

    public function collection()
    {        
        return collect($this->request);      
    }

    public function headings(): array
    {
        if($this->conjunto->tipo == 'Apartamento'){
            return [
                'Bloque', 'Apartamento', 'Tipo', 'Servicio público', 'Residente', 'Foto',
                'Observación', 'Fecha de entrada', 'Fecha de entrega', 'Usuario ingreso', 'Usuario salida',
                'Estatus'
            ];
        }
        else{
            return [
                'Casa', 'Tipo', 'Servicio público', 'Residente', 'Foto',
                'Observación', 'Fecha de entrada', 'Fecha de entrega', 'Usuario ingreso', 'Usuario salida',
                'Estatus'
            ];
        }
    }

    public function columnWidths(): array
    {
        if($this->conjunto->tipo == 'Apartamento'){
            return [
                'A' => 10,
                'B' => 15,
                'C' => 20,
                'D' => 20,
                'E' => 35,
                'F' => 60,
                'G' => 45,
                'H' => 20,
                'I' => 20,
                'J' => 35,
                'K' => 35,
            ];
        }
        else{
            return [
                'A' => 10,
                'B' => 20,
                'C' => 20,
                'D' => 35,
                'E' => 60,
                'F' => 45,
                'G' => 20,
                'H' => 20,
                'I' => 35,
                'J' => 35,
            ];
        }
    }
}