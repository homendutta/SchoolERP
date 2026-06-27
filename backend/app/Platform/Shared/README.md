# Platform / Shared

Reusable **base building blocks** for the module layering. Every business module composes these; they
contain no business logic.

```
Shared/
├── Http/
│   ├── Controllers/BaseController.php     # thin orchestration base
│   ├── Requests/BaseRequest.php           # validation + authorization base
│   ├── Resources/BaseResource.php         # response transformer base
│   └── Responses/ApiResponse.php          # standard success/error envelope
├── Services/{ServiceInterface, BaseService}.php       # business-rule layer base (+ transaction helper)
├── Repositories/{RepositoryInterface, BaseRepository}.php  # data-access layer base
├── Policies/BasePolicy.php                # RBAC policy base
├── Events/DomainEvent.php                 # domain event base
└── Jobs/BaseJob.php                       # queued job base
```

- Enforces the mandatory layering `Controller -> Request -> Service -> Repository -> Model`.
- Namespace: `App\Platform\Shared\*`.

> Relocated from the previous `app/Shared` during the enterprise refactor; existing code preserved,
> only re-namespaced. No business logic added.
