<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;

class ParkingsExport implements FromCollection, WithHeadings, WithColumnWidths
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
                'Número de parqueadero', 'Bloque', 'Apartamento', 'Propietario', 'Residente', 'Placas del vehículo', 'Tipo de vehículo',
                'Jornada', 'Fecha y hora de ingreso', 'Fecha y hora de salida', 'Usuario ingreso', 'Usuario salida', 'Valor'
            ];
        }
        else{
            return [
                'Número de parqueadero', 'Casa', 'Propietario', 'Residente', 'Placas del vehículo', 'Tipo de vehículo',
                'Jornada', 'Fecha y hora de ingreso', 'Fecha y hora de salida', 'Usuario ingreso', 'Usuario salida', 'Valor'
            ];
        }
    }

    public function columnWidths(): array
    {
        if($this->conjunto->tipo == 'Apartamento'){
            return [
                'A' => 20,
                'B' => 10,
                'C' => 15,
                'D' => 35,
                'E' => 35,
                'F' => 20,
                'G' => 15,
                'H' => 15,
                'I' => 20,
                'J' => 20,
                'K' => 35,
                'L' => 35,
                'M' => 20
            ];
        }
        else{
            return [
                'A' => 20,
                'B' => 10,
                'C' => 35,
                'D' => 35,
                'E' => 20,
                'F' => 15,
                'G' => 15,
                'H' => 20,
                'I' => 20,
                'J' => 35,
                'K' => 35,
                'L' => 20
            ];
        }
    }
}