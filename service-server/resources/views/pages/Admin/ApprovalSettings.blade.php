@extends('layout.master')

@push('plugin-styles')
    <link href="{{ asset('assets/plugins/datatables-net-bs5/dataTables.bootstrap5.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/plugins/select2/select2.min.css') }}" rel="stylesheet" />
@endpush

@section('content')
    <nav class="page-breadcrumb d-flex justify-content-between">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Approval Configuration</li>
        </ol>
        {{-- <div>
    <a href="{{route('eh.addWorkOrder')}}" class="btn btn-primary btn-sm"><i data-feather='file-plus' class="text-sm" style="height: 17px;"></i> Submit Work Order</a>
  </div> --}}
    </nav>

    <div class="row">
        <div class="col-md-12 mb-3">
            <form action="{{ route('AssignApprover') }}" method="POST">
                @csrf
                <div class="card" style="border: 1px dashed #d3d3d3;">
                    <div class="card-body">
                        <h5 class="mb-4">Assign Approver</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Approver</label>
                                    <select class="form-select" name="user_id" id="assignedApprover" data-width="100%"
                                        required>
                                        <option value="">Select Approver</option>
                                        @foreach ($users as $user)
                                            <option value="{{ $user->id }}">
                                                {{ $user->first_name }} {{ $user->last_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Approver Designation</label>
                                    <select name="approver_level" id="" class="form-select" required>
                                        <option value=""></option>
                                        <option value="1">IT DEPARTMENT</option>
                                        <option value="2">APM/NSM/SM</option>
                                        <option value="3">WAREHOUSE & INVENTORY MANAGEMENT</option>
                                        <option value="4">SERVICE DEPARTMENT TEAM LEADER</option>
                                        <option value="5">SERVICE DEPARTMENT HEAD / SERVICE ENGINEER</option>
                                        <option value="6">BILLING & INVOICING STAFF / WIM PERSONNEL</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 d-flex justify-content-end mt-3">
                                <div class="mb-3">
                                    <button type="submit" class="btn btn-md btn-primary">Submit Approver</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

        </div>
    </div>

    <div class="row">
        <h5 class="mb-3 mt-5">Approver Details</h5>
        <div class="col-md-12">
            <div class="row gx-1 gy-4">
                @php
                    $hasApprovers = false;
                @endphp
                {{-- IT DEPARTMENT APPROVERS --}}
                <div class="col-lg-5 col-xl-4 grid-margin grid-margin-xl-0 stretch-card">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-baseline mb-2">
                                <h6 class="card-title mb-0">IT Department</h6>
                                <div class="dropdown mb-3">
                                </div>
                            </div>
                            @foreach ($approvers as $approver)
                                @if ($approver->approval_level === 1)
                                    @php
                                        $hasApprovers = true;
                                    @endphp
                                    <div class="d-flex flex-column">
                                        <div href="javascript:;" class="d-flex align-items-center border-bottom pb-3 pt-3">
                                            <div class="me-3">
                                                <img src="{{ asset('assets/images/userIcon.png') }}"
                                                    class="rounded-circle wd-35" alt="user">
                                            </div>
                                            <div class="w-100">
                                                <div class="d-flex justify-content-between">
                                                    <h6 class="fw-normal text-body mb-1">{{ $approver->first_name }}
                                                        {{ $approver->last_name }}</h6>
                                                    {{-- <p class="text-muted fs-5 tx-10"> --}}
                                                        <form action="{{ route('DeleteApprover', ['id' => $approver->id]) }}"
                                                            method="post">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-outline"><i data-feather="trash" class="text-danger small-feather"></i></button>
                                                        </form>
                                                    {{-- </p> --}}
                                                </div>
                                                <p class="text-muted tx-13">{{ $approver->position_name }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                            @if (!$hasApprovers)
                                <p class="text-muted">No records found</p>
                            @endif
                        </div>
                    </div>
                </div>

                @php
                    $hasApprovers = false;
                @endphp
                {{-- APM/NPM/AM APPROVERS --}}
                <div class="col-lg-5 col-xl-4 grid-margin grid-margin-xl-0 stretch-card">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-baseline mb-2">
                                <h6 class="card-title mb-0">APM/NPM/AM</h6>
                                <div class="dropdown mb-3">
                                </div>
                            </div>
                            @foreach ($approvers as $approver)
                                @if ($approver->approval_level === 2)
                                    @php
                                        $hasApprovers = true;
                                    @endphp
                                    <div class="d-flex flex-column">
                                        <a href="javascript:;" class="d-flex align-items-center border-bottom pb-3 pt-3">
                                            <div class="me-3">
                                                <img src="{{ asset('assets/images/userIcon.png') }}"
                                                    class="rounded-circle wd-35" alt="user">
                                            </div>
                                            <div class="w-100">
                                                <div class="d-flex justify-content-between">
                                                    <h6 class="fw-normal text-body mb-1">{{ $approver->first_name }}
                                                        {{ $approver->last_name }}</h6>
                                                    {{-- <p class="text-muted fs-5 tx-10"> --}}
                                                        <form action="{{ route('DeleteApprover', ['id' => $approver->id]) }}"
                                                            method="post">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-outline"><i data-feather="trash" class="text-danger small-feather"></i></button>
                                                        </form>
                                                    {{-- </p> --}}
                                                </div>
                                                <p class="text-muted tx-13">{{ $approver->position_name }}</p>
                                            </div>
                                        </a>
                                    </div>
                                @endif
                            @endforeach
                            @if (!$hasApprovers)
                                <p class="text-muted">No records found</p>
                            @endif
                        </div>
                    </div>
                </div>

                @php
                    $hasApprovers = false;
                @endphp
                {{-- WAREHOUSE & INVENTORY MANAGMENT - WIM --}}
                <div class="col-lg-5 col-xl-4 grid-margin grid-margin-xl-0 stretch-card">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-baseline mb-2">
                                <h6 class="card-title mb-0">Warehouse & Inventory Managment (WIM)</h6>
                                <div class="dropdown mb-3">
                                </div>
                            </div>
                            @foreach ($approvers as $approver)
                                @if ($approver->approval_level === 3)
                                    @php
                                        $hasApprovers = true;
                                    @endphp
                                    <div class="d-flex flex-column">
                                        <a href="javascript:;" class="d-flex align-items-center border-bottom pb-3 pt-3">
                                            <div class="me-3">
                                                <img src="{{ asset('assets/images/userIcon.png') }}"
                                                    class="rounded-circle wd-35" alt="user">
                                            </div>
                                            <div class="w-100">
                                                <div class="d-flex justify-content-between">
                                                    <h6 class="fw-normal text-body mb-1">{{ $approver->first_name }}
                                                        {{ $approver->last_name }}</h6>
                                                    {{-- <p class="text-muted fs-5 tx-10"> --}}
                                                        <form action="{{ route('DeleteApprover', ['id' => $approver->id]) }}"
                                                            method="post">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-outline"><i data-feather="trash" class="text-danger small-feather"></i></button>
                                                        </form>
                                                    {{-- </p> --}}
                                                </div>
                                                <p class="text-muted tx-13">{{ $approver->position_name }}</p>
                                            </div>
                                        </a>
                                    </div>
                                @endif
                            @endforeach
                            @if (!$hasApprovers)
                                <p class="text-muted">No records found</p>
                            @endif
                        </div>
                    </div>
                </div>

                @php
                    $hasApprovers = false;
                @endphp
                {{-- SERVICE DEPARTMENT TEAM LEADER --}}
                <div class="col-lg-5 col-xl-4 grid-margin grid-margin-xl-0 stretch-card">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-baseline mb-2">
                                <h6 class="card-title mb-0">Service Department Team Leader</h6>
                                <div class="dropdown mb-3">
                                </div>
                            </div>
                            @foreach ($approvers as $approver)
                                @if ($approver->approval_level === 4)
                                    @php
                                        $hasApprovers = true;
                                    @endphp
                                    <div class="d-flex flex-column">
                                        <a href="javascript:;" class="d-flex align-items-center border-bottom pb-3 pt-3">
                                            <div class="me-3">
                                                <img src="{{ asset('assets/images/userIcon.png') }}"
                                                    class="rounded-circle wd-35" alt="user">
                                            </div>
                                            <div class="w-100">
                                                <div class="d-flex justify-content-between">
                                                    <h6 class="fw-normal text-body mb-1">{{ $approver->first_name }}
                                                        {{ $approver->last_name }}</h6>
                                                    {{-- <p class="text-muted fs-5 tx-10"> --}}
                                                        <form action="{{ route('DeleteApprover', ['id' => $approver->id]) }}"
                                                            method="post">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-outline"><i data-feather="trash" class="text-danger small-feather"></i></button>
                                                        </form>
                                                    {{-- </p> --}}
                                                </div>
                                                <p class="text-muted tx-13">{{ $approver->position_name }}</p>
                                            </div>
                                        </a>
                                    </div>
                                @endif
                            @endforeach
                            @if (!$hasApprovers)
                                <p class="text-muted">No records found</p>
                            @endif
                        </div>
                    </div>
                </div>

                @php
                    $hasApprovers = false;
                @endphp
                {{-- SERVICE DEPARTMENT HEAD / SERVICE ENGINEER --}}
                <div class="col-lg-5 col-xl-4 grid-margin grid-margin-xl-0 stretch-card">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-baseline mb-2">
                                <h6 class="card-title mb-0">Service Department Head / Service Engineer</h6>
                                <div class="dropdown mb-3">
                                </div>
                            </div>
                            @foreach ($approvers as $approver)
                                @if ($approver->approval_level === 5)
                                    @php
                                        $hasApprovers = true;
                                    @endphp
                                    <div class="d-flex flex-column">
                                        <a href="javascript:;" class="d-flex align-items-center border-bottom pb-3 pt-3">
                                            <div class="me-3">
                                                <img src="{{ asset('assets/images/userIcon.png') }}"
                                                    class="rounded-circle wd-35" alt="user">
                                            </div>
                                            <div class="w-100">
                                                <div class="d-flex justify-content-between">
                                                    <h6 class="fw-normal text-body mb-1">{{ $approver->first_name }}
                                                        {{ $approver->last_name }}</h6>
                                                    {{-- <p class="text-muted fs-5 tx-10"> --}}
                                                        <form action="{{ route('DeleteApprover', ['id' => $approver->id]) }}"
                                                            method="post">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-outline"><i data-feather="trash" class="text-danger small-feather"></i></button>
                                                        </form>
                                                    {{-- </p> --}}
                                                </div>
                                                <p class="text-muted tx-13">{{ $approver->position_name }}</p>
                                            </div>
                                        </a>
                                    </div>
                                @endif
                            @endforeach
                            @if (!$hasApprovers)
                                <p class="text-muted">No records found</p>
                            @endif

                        </div>
                    </div>
                </div>

                @php
                    $hasApprovers = false;
                @endphp
                {{-- Billing & Invoicing Staff/WIM Personnel --}}
                <div class="col-lg-5 col-xl-4 grid-margin grid-margin-xl-0 stretch-card">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-baseline mb-2">
                                <h6 class="card-title mb-0">Billing & Invoicing Staff / WIM Personnel</h6>
                                <div class="dropdown mb-3">
                                </div>
                            </div>
                            @foreach ($approvers as $approver)
                                @if ($approver->approval_level === 6)
                                    @php
                                        $hasApprovers = true;
                                    @endphp
                                    <div class="d-flex flex-column">
                                        <a href="javascript:;" class="d-flex align-items-center border-bottom pb-3 pt-3">
                                            <div class="me-3">
                                                <img src="{{ asset('assets/images/userIcon.png') }}"
                                                    class="rounded-circle wd-35" alt="user">
                                            </div>
                                            <div class="w-100">
                                                <div class="d-flex justify-content-between">
                                                    <h6 class="fw-normal text-body mb-1">{{ $approver->first_name }}
                                                        {{ $approver->last_name }}</h6>
                                                    {{-- <p class="text-muted fs-5 tx-10"> --}}
                                                        <form action="{{ route('DeleteApprover', ['id' => $approver->id]) }}"
                                                            method="post">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-outline"><i data-feather="trash" class="text-danger small-feather"></i></button>
                                                        </form>
                                                    {{-- </p> --}}
                                                </div>
                                                <p class="text-muted tx-13">{{ $approver->position_name }}</p>
                                            </div>
                                        </a>
                                    </div>
                                @endif
                            @endforeach
                            @if (!$hasApprovers)
                                <p class="text-muted">No records found</p>
                            @endif
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
@endsection

@push('plugin-scripts')
    <script src="{{ asset('assets/plugins/datatables-net/jquery.dataTables.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-net-bs5/dataTables.bootstrap5.js') }}"></script>
    <script src="{{ asset('assets/plugins/select2/select2.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/feather-icons/feather.min.js') }}"></script>
@endpush

@push('custom-scripts')
    <script src="{{ asset('assets/js/data-table.js') }}"></script>
    <script src="{{ asset('js/ApprovalSettings/select2.js') }}"></script>
@endpush
