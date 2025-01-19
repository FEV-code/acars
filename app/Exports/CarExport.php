<?php

namespace App\Exports;

use AllowDynamicProperties;
use App\Models\Car;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;

#[AllowDynamicProperties] class CarExport implements FromQuery
{
    use Exportable;

    public function __construct ( string $sortBy, string $sortOrder, array $whereArr )
    {
        $this->sortBy = $sortBy;
        $this->sortOrder = $sortOrder;
        $this->whereArr = $whereArr;
    }

    public function query ()
    {
        return Car::query ()->select ( [ "id", "name", "number", "color", "vin", "brand", "model", "year" ] )
            ->where ( $this->whereArr )
            ->orderBy ( $this->sortBy, $this->sortOrder );
    }
}
