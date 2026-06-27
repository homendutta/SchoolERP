<?php

/*
|--------------------------------------------------------------------------
| Registered Service Providers
|--------------------------------------------------------------------------
| The application provider plus one provider per active business module. Each
| module provider is a self-contained vertical slice that registers that
| module's bindings, policies, events, and routes. Adding a module = adding its
| provider here. No core change required.
|
| Framework-wide infrastructure lives in app/Platform (Core, Foundation,
| Shared) and is wired by the framework / AppServiceProvider, not listed as a
| business module. Future modules (Library, Transport, Hostel, Payroll,
| Visitor, Alumni) are intentionally NOT registered yet.
|
| Order reflects dependency direction.
*/

return [
    App\Providers\AppServiceProvider::class,

    // Configuration & master data (depended upon by most modules)
    App\Modules\Administration\Providers\AdministrationServiceProvider::class,

    // Identity
    App\Modules\Authentication\Providers\AuthenticationServiceProvider::class,

    // Academic structure & people
    App\Modules\Academic\Providers\AcademicServiceProvider::class,
    App\Modules\Staff\Providers\StaffServiceProvider::class,
    App\Modules\Admissions\Providers\AdmissionsServiceProvider::class,
    App\Modules\Students\Providers\StudentsServiceProvider::class,
    App\Modules\Parents\Providers\ParentsServiceProvider::class,

    // Daily operations & assessment
    App\Modules\Timetable\Providers\TimetableServiceProvider::class,
    App\Modules\Attendance\Providers\AttendanceServiceProvider::class,
    App\Modules\Examination\Providers\ExaminationServiceProvider::class,

    // Finance
    App\Modules\Finance\Providers\FinanceServiceProvider::class,
    App\Modules\Accounts\Providers\AccountsServiceProvider::class,

    // Engagement, facilities & reporting
    App\Modules\Communication\Providers\CommunicationServiceProvider::class,
    App\Modules\Website\Providers\WebsiteServiceProvider::class,
    App\Modules\Assets\Providers\AssetsServiceProvider::class,
    App\Modules\Inventory\Providers\InventoryServiceProvider::class,
    App\Modules\Reports\Providers\ReportsServiceProvider::class,
];
