<?php

declare(strict_types=1);

use App\Modules\Academic\Providers\AcademicServiceProvider;
use App\Modules\Accounts\Providers\AccountsServiceProvider;
use App\Modules\Administration\Providers\AdministrationServiceProvider;
use App\Modules\Admissions\Providers\AdmissionsServiceProvider;
use App\Modules\Assets\Providers\AssetsServiceProvider;
use App\Modules\Attendance\Providers\AttendanceServiceProvider;
use App\Modules\Authentication\Providers\AuthenticationServiceProvider;
use App\Modules\Cms\Providers\CmsServiceProvider;
use App\Modules\Communication\Providers\CommunicationServiceProvider;
use App\Modules\Examination\Providers\ExaminationServiceProvider;
use App\Modules\Finance\Providers\FinanceServiceProvider;
use App\Modules\Hostel\Providers\HostelServiceProvider;
use App\Modules\HumanResources\Providers\HumanResourcesServiceProvider;
use App\Modules\Inventory\Providers\InventoryServiceProvider;
use App\Modules\Library\Providers\LibraryServiceProvider;
use App\Modules\Parents\Providers\ParentsServiceProvider;
use App\Modules\Payroll\Providers\PayrollServiceProvider;
use App\Modules\Reports\Providers\ReportsServiceProvider;
use App\Modules\Staff\Providers\StaffServiceProvider;
use App\Modules\Students\Providers\StudentsServiceProvider;
use App\Modules\Timetable\Providers\TimetableServiceProvider;
use App\Modules\Transport\Providers\TransportServiceProvider;
use App\Modules\Website\Providers\WebsiteServiceProvider;
use App\Providers\AppServiceProvider;

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
    AppServiceProvider::class,

    // Configuration & master data (depended upon by most modules)
    AdministrationServiceProvider::class,

    // Identity
    AuthenticationServiceProvider::class,

    // Academic structure & people
    AcademicServiceProvider::class,
    StaffServiceProvider::class,
    HumanResourcesServiceProvider::class,
    PayrollServiceProvider::class,
    AdmissionsServiceProvider::class,
    StudentsServiceProvider::class,
    ParentsServiceProvider::class,

    // Daily operations & assessment
    TimetableServiceProvider::class,
    AttendanceServiceProvider::class,
    ExaminationServiceProvider::class,

    // Finance
    FinanceServiceProvider::class,
    AccountsServiceProvider::class,

    // Facilities
    LibraryServiceProvider::class,
    TransportServiceProvider::class,
    HostelServiceProvider::class,

    // Engagement, facilities & reporting
    CommunicationServiceProvider::class,
    CmsServiceProvider::class,
    WebsiteServiceProvider::class,
    AssetsServiceProvider::class,
    InventoryServiceProvider::class,
    ReportsServiceProvider::class,
];
