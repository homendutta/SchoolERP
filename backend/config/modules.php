<?php

/*
|--------------------------------------------------------------------------
| Asylinx Enterprise Architecture — Module Registry
|--------------------------------------------------------------------------
| Source of truth for the backend's layering and business-module decomposition.
| Mirrors the approved architecture and the Enterprise Domain Model.
|
| Two layers under app/:
|
|   Platform/   framework-wide INFRASTRUCTURE only (no business modules)
|     Core/         module system, routing kernel, bootstrap, middleware, exceptions
|     Foundation/   infrastructure services: audit, media, notifications,
|                   search, cache, tenancy
|     Shared/       reusable base building blocks (Controller, Request, Resource,
|                   Service, Repository, Policy, Event, Job, ApiResponse)
|
|   Modules/    BUSINESS modules — each a vertical slice following:
|     Controller -> Request -> Service -> Repository -> Model
|     with Config, Database/{Migrations,Seeders,Factories}, Events, Http/
|     {Controllers,Requests,Resources}, Jobs, Models, Policies, Providers,
|     Repositories, Routes/{api,web}, Services, Tests.
*/

return [

    'platform_namespace' => 'App\\Platform',
    'modules_namespace' => 'App\\Modules',
    'modules_path' => app_path('Modules'),

    /*
    | Active business modules (order reflects dependency direction).
    */
    'enabled' => [
        'Administration',
        'Authentication',
        'Academic',
        'Staff',
        'Admissions',
        'Students',
        'Parents',
        'Timetable',
        'Attendance',
        'Examination',
        'Finance',
        'Accounts',
        'Communication',
        'Website',
        'Assets',
        'Inventory',
        'Reports',
    ],

    /*
    | Standard internal structure every module follows.
    */
    'structure' => [
        'Config',
        'Database/Migrations',
        'Database/Seeders',
        'Database/Factories',
        'Events',
        'Http/Controllers',
        'Http/Requests',
        'Http/Resources',
        'Jobs',
        'Models',
        'Policies',
        'Providers',
        'Repositories',
        'Routes',
        'Services',
        'Tests',
    ],
];
