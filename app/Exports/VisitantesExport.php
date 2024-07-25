<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;

class VisitantesExport implements FromCollection, WithHeadings, WithColumnWidths
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
                'Bloque', 'Apartamento', 'Residente', 'Tipo', 'Foto', 'Visitante', 'Número de documento',
                'Fecha de nacimiento', 'RH', 'Sexo', 'Fecha y hora de ingreso', 'Fecha y hora de salida',
                'Portero entrada', 'Portero salida', 'Estatus'
            ];
        }
        else{
            return [
                'Casa', 'Residente', 'Tipo', 'Foto', 'Visitante', 'Número de documento',
                'Fecha de nacimiento', 'RH', 'Sexo', 'Fecha y hora de ingreso', 'Fecha y hora de salida',
                'Portero entrada', 'Portero salida', 'Estatus'
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
                'D' => 20,
                'E' => 60,
                'F' => 35,
                'G' => 20,
                'H' => 20,
                'I' => 5,
                'J' => 10,
                'K' => 20,
                'L' => 20,
                'M' => 35, 
                'N' => 35,
                'O' => 20,
            ];
        }
        else{
            return [
                'A' => 10,
                'B' => 35,
                'C' => 20,
                'D' => 60, 
                'E' => 35,
                'F' => 20,
                'G' => 20,
                'H' => 5,
                'I' => 10,
                'J' => 20,
                'K' => 20,
                'L' => 35, 
                'M' => 35,
                'N' => 20,
            ];
        }
    }
}