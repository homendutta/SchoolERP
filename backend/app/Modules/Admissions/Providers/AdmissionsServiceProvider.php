<?php

declare(strict_types=1);

namespace App\Modules\Admissions\Providers;

use App\Platform\Core\Providers\ModuleServiceProvider;

/**
 * Admissions module.
 *
 * Owns the applicant pipeline up to enrollment:
 *   - Admission Enquiry
 *   - Registration
 *   - Admission Workflow (register -> confirm -> enroll, with reject/cancel)
 *   - Enrollment (hands the new learner over to the Students module)
 *
 * Depends on Academic (allotted class), Administration (numbering), and
 * collaborates with Students and Finance on enrollment.
 *
 * Refactor stage: structure and wiring point only — no business bindings yet.
 */
class AdmissionsServiceProvider extends ModuleServiceProvider
{
    protected string $moduleName = 'Admissions';
}
