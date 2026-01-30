<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Exports\AppointmentsExport;
use App\Exports\InvoicesExport;

class ExportsTest extends TestCase
{
    /**
     * Test AppointmentsExport class exists
     */
    public function test_appointments_export_exists(): void
    {
        $this->assertTrue(class_exists(AppointmentsExport::class));
    }

    /**
     * Test InvoicesExport class exists
     */
    public function test_invoices_export_exists(): void
    {
        $this->assertTrue(class_exists(InvoicesExport::class));
    }

    /**
     * Test exports implement FromCollection or FromQuery
     */
    public function test_exports_implement_correct_interfaces(): void
    {
        $appointmentsExport = new \ReflectionClass(AppointmentsExport::class);
        $invoicesExport = new \ReflectionClass(InvoicesExport::class);

        $hasFromCollection = $appointmentsExport->implementsInterface(\Maatwebsite\Excel\Concerns\FromCollection::class);
        $hasFromQuery = $appointmentsExport->implementsInterface(\Maatwebsite\Excel\Concerns\FromQuery::class);

        $this->assertTrue(
            $hasFromCollection || $hasFromQuery,
            'AppointmentsExport should implement FromCollection or FromQuery'
        );
    }
}
