<?php

namespace App\Exports;

use App\Models\List_Report;
use App\Models\List_Training;
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
        $reports = List_Report::with('report')
            ->whereYear('Time_Approved_Auditor', $this->year)
            ->whereMonth('Time_Approved_Auditor', $this->month)
            ->get()
            ->map(function ($item) {
                $item->audit_type = 'Jobdesc';
                return $item;
            });

        $trainings = List_Training::with('training')
            ->whereYear('Time_Approved_Auditor', $this->year)
            ->whereMonth('Time_Approved_Auditor', $this->month)
            ->get()
            ->map(function ($item) {
                $item->audit_type = 'Training';
                return $item;
            });

        return $reports->concat($trainings)
            ->sortBy(function ($item) {
                return ($item->Auditor_Name ?? 'Unknown Auditor').'_'.$item->Time_Approved_Auditor;
            })
            ->values();
    }

    public function headings(): array
    {
        return [
            'No',
            'Type',
            'Auditor',
            'Audit Date',
            'Audit Time',
            'Procedure',
            'Member',
        ];
    }

    public function map($item): array
    {
        $member = ($item->audit_type ?? 'Jobdesc') === 'Training'
            ? ($item->training->member->Name_Member ?? 'Unknown')
            : ($item->report->member->Name_Member ?? 'Unknown');

        return [
            ++$this->rowNumber,
            $item->audit_type ?? 'Jobdesc',
            $item->Auditor_Name ?? 'Unknown Auditor',
            $item->Time_Approved_Auditor ? Carbon::parse($item->Time_Approved_Auditor)->format('Y-m-d') : '-',
            $item->Time_Approved_Auditor ? Carbon::parse($item->Time_Approved_Auditor)->format('H:i:s') : '-',
            $item->Name_Procedure,
            $member,
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
                $lastColumn = 'G';
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
