<?php

namespace App\Exports;

use App\Models\List_Report;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AuditExport implements FromCollection, ShouldAutoSize, WithEvents, WithHeadings, WithMapping, WithStyles
{
    protected $year;

    protected $month;

    private $rowNumber = 0;

    public function __construct($year, $month)
    {
        $this->year = $year;
        $this->month = $month;
    }

    public function collection()
    {
        return List_Report::with('report.member')
            ->whereYear('Time_Approved_Auditor', $this->year)
            ->whereMonth('Time_Approved_Auditor', $this->month)
            ->orderBy('Auditor_Name', 'asc')
            ->orderBy('Time_Approved_Auditor', 'asc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Auditor',
            'Audit Date',
            'Audit Time',
            'Procedure',
            'Member',
        ];
    }

    public function map($listReport): array
    {
        return [
            ++$this->rowNumber,
            $listReport->Auditor_Name ?? 'Unknown Auditor',
            $listReport->Time_Approved_Auditor ? Carbon::parse($listReport->Time_Approved_Auditor)->format('Y-m-d') : '-',
            $listReport->Time_Approved_Auditor ? Carbon::parse($listReport->Time_Approved_Auditor)->format('H:i:s') : '-',
            $listReport->Name_Procedure,
            $listReport->report->member->Name_Member ?? 'Unknown',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $lastColumn = 'F';
                $lastRow = $event->sheet->getHighestRow();
                $cellRange = 'A1:'.$lastColumn.$lastRow;

                // Auto filter
                $event->sheet->getDelegate()->setAutoFilter('A1:'.$lastColumn.'1');

                // Borders
                $event->sheet->getDelegate()->getStyle($cellRange)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        ],
                    ],
                ]);
            },
        ];
    }
}
