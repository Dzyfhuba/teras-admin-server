<?php

namespace App\Imports;

use App\Models\Student;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Events\BeforeSheet;

class StudentsImport implements ToModel, WithEvents, WithHeadingRow, WithMultipleSheets
{
    protected string $currentSheet = '';

    public function model(array $row): ?Model
    {

        return new Student([
            //
        ]);
    }

    public function sheets(): array
    {
        return [
            '1 Al Fajr' => $this,
            '1 Al Qomar' => $this,
            '1 Ad Dhuha' => $this,
            '2 Al Bayyinah' => $this,
            '2 An Nur' => $this,
            '2 Al Kautsar' => $this,
            '2 Al Lail' => $this,
            '3 An Naml' => $this,
            '3 An Nahl' => $this,
            '3 Al Ashr' => $this,
            '3 Al Fill' => $this,
            '3 Al Ankabut' => $this,
            '4 Al Qolam' => $this,
            '4 Al Buruj' => $this,
            '4 Al Anfal' => $this,
            '4 Al Mulk' => $this,
            '5 Al Hijr' => $this,
            '5 As Syams' => $this,
            '5 Al Ahqaf' => $this,
            '5 At Thur' => $this,
            '6 Ar Rahman' => $this,
            '6 Al Insan' => $this,
            '6 As Saff' => $this,
        ];
    }

    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function (BeforeSheet $event) {
                $this->currentSheet = $event->getSheet()->getDelegate()->getTitle();

                // Optional: If you need the 0-based sheet index instead:
                // $worksheet = $event->getSheet()->getDelegate();
                // $sheetIndex = $worksheet->getParent()->getIndex($worksheet);
            },
        ];
    }
}
