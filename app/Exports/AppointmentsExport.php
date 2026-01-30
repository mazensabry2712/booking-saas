<?php

namespace App\Exports;

use App\Models\Appointment;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AppointmentsExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $tenant;
    protected $period;
    protected $startDate;
    protected $endDate;

    public function __construct($tenant, $period = 'month', $startDate = null, $endDate = null)
    {
        $this->tenant = $tenant;
        $this->period = $period;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
     * Query for appointments
     */
    public function query()
    {
        $query = Appointment::query()
            ->where('tenant_id', $this->tenant->id)
            ->with(['customer', 'staff']);

        // Apply date filters
        if ($this->startDate && $this->endDate) {
            $query->whereBetween('appointment_date', [$this->startDate, $this->endDate]);
        } else {
            switch ($this->period) {
                case 'today':
                    $query->whereDate('appointment_date', now());
                    break;
                case 'week':
                    $query->whereBetween('appointment_date', [
                        now()->startOfWeek(),
                        now()->endOfWeek()
                    ]);
                    break;
                case 'month':
                    $query->whereMonth('appointment_date', now()->month)
                        ->whereYear('appointment_date', now()->year);
                    break;
            }
        }

        return $query->orderBy('appointment_date');
    }

    /**
     * Define column headings
     */
    public function headings(): array
    {
        return [
            'ID',
            'Customer Name',
            'Customer Email',
            'Staff Name',
            'Appointment Date',
            'Appointment Time',
            'Status',
            'Priority',
            'Notes',
            'Created At',
        ];
    }

    /**
     * Map data for each row
     */
    public function map($appointment): array
    {
        return [
            $appointment->id,
            $appointment->customer->name ?? 'N/A',
            $appointment->customer->email ?? 'N/A',
            $appointment->staff->name ?? 'N/A',
            $appointment->appointment_date->format('Y-m-d'),
            $appointment->appointment_date->format('H:i'),
            $appointment->status,
            $appointment->priority ?? 'Normal',
            $appointment->notes ?? '',
            $appointment->created_at->format('Y-m-d H:i'),
        ];
    }

    /**
     * Apply styles to the worksheet
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}
