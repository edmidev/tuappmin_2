<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;

class CitofoniasExport implements FromCollection, WithHeadings, WithColumnWidths
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
                'Bloque', 'Apartamento', 'Residente', 'Motivo',
                'Fecha y hora de ingreso', 'Acceso entrada', 'Estatus'
            ];
        }
        else{
            return [
                'Casa', 'Residente', 'Motivo',
                'Fecha y hora de ingreso', 'Acceso entrada', 'Estatus'
            ];
        }
    }

    public function columnWidths(): array
    {
        if($this->conjunto->tipo == 'Apartamento'){
            return [
                'A' => 10,
                'B' => 15,
                'C' => 35,
                'D' => 60,
                'E' => 20,
                'F' => 35,
                'G' => 20,
            ];
        }
        else{
            return [
                'A' => 10,
                'B' => 35,
                'C' => 60,
                'D' => 20,
                'E' => 35,
                'F' => 20,
            ];
        }
    }
}