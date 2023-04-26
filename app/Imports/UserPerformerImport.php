<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\Importable;

class UserPerformerImport implements ToCollection
{
    use Importable;

    public function collection(Collection $rows)
    {
        dd($rows);
        // foreach ($rows as $row)
        // {
        //     User::create([
        //         'name' => $row[0],
        //     ]);
        // }
    }
}
