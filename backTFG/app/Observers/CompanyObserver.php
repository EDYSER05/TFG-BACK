<?php

namespace App\Observers;

use App\Models\Company;
use App\Models\Holiday;

// Observer que se dispara automáticamente en eventos del modelo Company
class CompanyObserver
{
    // Al crear una empresa nueva, le asignamos todos los festivos existentes en la tabla holidays
    // Así no hay que hacerlo a mano cada vez que se da de alta una empresa
    public function created(Company $company): void
    {
        $nationalHolidayIds = Holiday::pluck('id');

        if ($nationalHolidayIds->isNotEmpty()) {
            $company->holidays()->attach($nationalHolidayIds);
        }
    }
}
