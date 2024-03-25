@extends('layout.master')

@push('plugin-styles')
    <link href="{{ asset('assets/plugins/select2/select2.min.css') }}" rel="stylesheet" />
    {{-- <link href="{{ asset('assets/plugins/jquery-tags-input/jquery.tagsinput.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/plugins/dropzone/dropzone.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/plugins/dropify/css/dropify.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/plugins/pickr/themes/classic.min.css') }}" rel="stylesheet" /> --}}
    <link href="{{ asset('assets/plugins/flatpickr/flatpickr.min.css') }}" rel="stylesheet" />
@endpush

@push('plugin-styles')
    <link href="{{ asset('assets/plugins/datatables-net-bs5/dataTables.bootstrap5.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/plugins/radioCheck/radioCheck.css') }}" rel="stylesheet" />
    {{-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css" integrity="sha512-5A8nwdMOWrSz20fDsjczgUidUBR8liPYU+WymTZP1lmY9G6Oc7HlZv156XqnsgNUzTyMefFTcsFH/tnJE/+xBg==" crossorigin="anonymous" referrerpolicy="no-referrer" /> --}}


@endpush

@section('content')
    {{-- Start of Form --}}
    <form action="{{ route('SubmitWorkOrder') }}" method="POST">
        @csrf
        <nav class="page-breadcrumb d-flex justify-content-between">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">&larr; <a href="#">Equipment Handling</a></li>
                <li class="breadcrumb-item active" aria-current="page">Request Work Order</li>
            </ol>
            <div class="">
                <button type="button" class="btn btn-secondary btn-sm"><i data-feather='x' class="text-sm"
                        style="height: 17px;"></i> Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm"><i data-feather='check' class="text-sm"
                        style="height: 17px;"></i> Save</button>
            </div>
        </nav>

        <div class="row">
            <div class="d-flex flex-column align-items-center">
                <div class="col-lg-10 grid-margin">
                    <div class="card">
                        <div class="card-body">
                            @foreach ($errors->all() as $error)
                                <p>{{$error}}</p>
                            @endforeach
                            <h5 class="mb-2">Recipient Details</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Institution</label>
                                        <select class="form-select" id="getInstitution" name="institution" data-width="100%"
                                            required value="{{ old('institution') }}">
                                            <option value="">Select Institution</option>
                                            @foreach ($institutions as $institution)
                                                <option value="{{ $institution->id }}" {{ old('institution') == $institution->id ? 'selected' : '' }}
                                                    data-InstitutionAddress="{{ $institution->address }}">
                                                    {{ $institution->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Address</label>
                                        <input id="address" class="form-control" name="address" type="text" required readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Requested by</label>
                                        <input id=""
                                            value="{{ user_details('first_name') }} {{ user_details('last_name') }}"
                                            class="form-control" name="requested_by" type="text" required readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Propose Delivery Date</label>
                                        <div class="input-group flatpickr-date-delivery-date"
                                            id="flatpickr-date-delivery-date">
                                            <input type="text" class="form-control" placeholder="Select date" name="proposed_delivery_date" id="propose_delivery_date" required>
                                            <span class="input-group-text input-group-addon" data-toggle><i
                                                    data-feather="calendar"></i></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- Style to fit inpput in table --}}
                    <style>
                        .equipmentPeripherals thead th {
                            text-align: left;
                        }

                        .equipmentPeripherals td {
                            position: relative;
                            /* padding: 2px 2px !important; */
                        }

                        .equipmentPeripherals td input {
                            position: absolute;
                            display: block;
                            top: 0;
                            left: 0;
                            margin: 0;
                            padding-left: 10px;
                            height: 100%;
                            width: 100%;
                            border: none;
                            /* padding: 10px; */
                            box-sizing: border-box;
                            text-align: left;
                        }

                        .equipmentPeripherals td input:focus {
                            outline: 1px solid #191970;
                        }

                        /* Chrome, Safari, Edge, Opera */
                        .equipmentPeripherals input::-webkit-outer-spin-button,
                        .equipmentPeripherals input::-webkit-inner-spin-button {
                            -webkit-appearance: none;
                            margin: 0;
                        }

                        /* Firefox */
                        .equipmentPeripherals input[type=number] {
                            -moz-appearance: textfield;
                        }
                    </style>

                    <div class="card mt-3">
                        <div class="card-body">
                            <h5 class="mb-3">Equipment & Peripherals</h5>
                            <div class="row">
                                <ul class="nav nav-tabs nav-tabs-line" id="lineTab" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active" id="home-line-tab" data-bs-toggle="tab" href="#equipment"
                                            role="tab" aria-controls="equipment" aria-selected="true">Equipment &
                                            Peripherals</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="profile-line-tab" data-bs-toggle="tab"
                                            href="#addditionalEquipment" role="tab"
                                            aria-controls="addditionalEquipment" aria-selected="false">Additional
                                            Peripherals</a>
                                    </li>
                                </ul>
                                <div class="tab-content mt-3" id="lineTabContent">
                                    <div class="tab-pane fade show active" id="equipment" role="tabpanel"
                                        aria-labelledby="home-line-tab">
                                        <div class="table-responsive">
                                            <table
                                                class="table table-bordered table-sm table-condense equipmentPeripherals">
                                                <thead>
                                                    <tr>
                                                        <th scope="col">Item Code</th>
                                                        <th scope="col">Item Description</th>
                                                        <th scope="col">Serial Number</th>
                                                        <th scope="col">Remarks</th>
                                                        <th scope="col"></th>
                                                    </tr>
                                                </thead>
                                                <tbody id="equipmentPeripherals">
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="col-md-2">
                                            <!-- Button trigger modal -->
                                            <button type="button" class="btn btn-primary btn-xs w- mt-3"
                                                data-bs-toggle="modal" data-bs-target="#addEquipment"
                                                data-getequipment="equipment">
                                                Add
                                            </button>
                                        </div>
                                    </div>

                                    <div class="tab-pane fade" id="addditionalEquipment" role="tabpanel"
                                        aria-labelledby="profile-line-tab">
                                        <div class="table-responsive">
                                            <table
                                                class="table table-sm table-bordered equipmentAdditional equipmentPeripherals">
                                                <thead>
                                                    <tr>
                                                        <th scope="col">Item Code</th>
                                                        <th scope="col">Item Description</th>
                                                        <th scope="col">Serial Number</th>
                                                        <th scope="col">Remarks</th>
                                                        <th scope="col"></th>
                                                    </tr>
                                                </thead>
                                                <tbody id="additionalEquipmentPeripherals">
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="col-md-2">
                                            <!-- Button trigger modal -->
                                            <button type="button" class="btn btn-primary btn-xs w- mt-3"
                                                data-bs-toggle="modal" data-bs-target="#addAdditionalEquipment"
                                                data-getequipment="additional">
                                                Add
                                            </button>
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <div class="row">
                                <!-- Modal -->
                                <div class="modal fade" id="addEquipment" tabindex="-1" role="dialog"
                                    aria-labelledby="modalTitleId" aria-hidden="true">
                                    <div class="modal-dialog modal-lg" role="document">
                                        <div class="modal-content">
                                            <div class="modal-body">
                                                <div class="table-responsive">
                                                    <p class="mb-2 text-warning">* Click to select</p>
                                                    <table class="table table-bordered table-sm table-condense"
                                                        id="dataTableEquipment" style="border: 1px solid #999999;">
                                                        <thead>
                                                            <tr>
                                                                <th scope="col">ID</th>
                                                                <th scope="col">Item Code</th>
                                                                <th scope="col">Description</th>
                                                                {{-- <th scope="col">Category</th> --}}
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach ($equipments as $equipment)
                                                                <tr>
                                                                    <td>{{ $equipment->id }}</td>
                                                                    <td>{{ $equipment->item_code }}</td>
                                                                    <td>{{ $equipment->description }}</td>
                                                                    {{-- <td>{{$equipment->category}}</td> wire:click='getEquipment' --}}
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <hr>

                                                {{-- <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                          Close
                                      </button> --}}

                                            </div>
                                        </div>
                                    </div>
                                </div>


                                <!-- Modal Add Additonal Equipment-->
                                <div class="modal fade" id="addAdditionalEquipment" tabindex="-1" role="dialog"
                                    aria-labelledby="modalTitleId" aria-hidden="true">
                                    <div class="modal-dialog modal-lg" role="document">
                                        <div class="modal-content">
                                            <div class="modal-body">
                                                <div class="table-responsive">
                                                    <p class="mb-2 text-warning">* Click to select</p>
                                                    <table class="table table-bordered table-sm table-condense"
                                                        id="dataTableAdditionalEquipment"
                                                        style="border: 1px solid #999999;">
                                                        <thead>
                                                            <tr>
                                                                <th scope="col">ID</th>
                                                                <th scope="col">Item Code</th>
                                                                <th scope="col">Description</th>
                                                                {{-- <th scope="col">Category</th> --}}
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach ($equipments as $equipment)
                                                                <tr>
                                                                    <td>{{ $equipment->id }}</td>
                                                                    <td>{{ $equipment->item_code }}</td>
                                                                    <td>{{ $equipment->description }}</td>
                                                                    {{-- <td>{{$equipment->category}}</td> wire:click='getEquipment' --}}
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <hr>

                                                {{-- <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                          Close
                                      </button> --}}

                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="card mt-3">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12 d-flex flex-row align-items-center pb-3"
                                    style="border-bottom: 1px dotted #191970;">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="request_type"
                                            id="internalRequest" checked />
                                        <label class="form-check-label" for="internalRequest"> Internal Request </label>
                                    </div>&nbsp;&nbsp;&nbsp;
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="request_type"
                                            id="externalRequest" />
                                        <label class="form-check-label" for="externalRequest">External Request</label>
                                    </div>
                                </div>
                                <div class="col-md-12 mt-3">
                                    <div class="col-md-6" id="display_internal_request">
                                        {{-- <div class="mb-3">
                                            <label for="" class="form-label">Internal Request</label>
                                            <select class="form-select form-select-md" name="" id=""
                                                required>
                                                <option selected>Select one</option>
                                                <option value="">For Corrective</option>
                                                <option value="">For Refurbishment</option>
                                                <option value="">For Quality Control</option>
                                                <option value="">Training Purposes</option>
                                                <option value="">For Disposal</option>
                                                <option value="">Others</option>
                                            </select>
                                        </div> --}}
                                        <div class="row">
                                            <!-- RADIO BUTTONS BLOCK -->
                                            <div class="col-xs-12 col-sm-12 col-md-12">
                                                <div class="frb-group">
                                                    <div class="frb frb-default">
                                                        <input type="radio" id="radio-button-0" name="internal_request"
                                                            value="6">
                                                        <label for="radio-button-0">
                                                            <span class="frb-title">For Corrective</span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="frb-group">
                                                    <div class="frb frb-default">
                                                        <input type="radio" id="radio-button-1" name="internal_request"
                                                            value="7">
                                                        <label for="radio-button-1">
                                                            <span class="frb-title">For Refurbishment</span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="frb-group">
                                                    <div class="frb frb-default">
                                                        <input type="radio" id="radio-button-3" name="internal_request"
                                                            value="8">
                                                        <label for="radio-button-3">
                                                            <span class="frb-title">For Quality Control</span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="frb-group">
                                                    <div class="frb frb-default">
                                                        <input type="radio" id="radio-button-4" name="internal_request"
                                                            value="9">
                                                        <label for="radio-button-4">
                                                            <span class="frb-title">Training Purposes</span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="frb-group">
                                                    <div class="frb frb-default">
                                                        <input type="radio" id="radio-button-5" name="internal_request"
                                                            value="10">
                                                        <label for="radio-button-5">
                                                            <span class="frb-title">For Disposal</span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="frb-group">
                                                    <div class="frb frb-default">
                                                        <input type="radio" id="radio-button-6" name="internal_request"
                                                            value="11">
                                                        <label for="radio-button-6">
                                                            <span class="frb-title">Others</span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 d-none" id="display_external_request">
                                        {{-- <div class="mb-3">
                                            <label for="" class="form-label">Extenal Request</label>
                                            <select class="form-select form-select-md" name="" id=""
                                                required>
                                                <option selected>Select one</option>
                                                <option value="">For Demonstration</option>
                                                <option value="">Reagent Tie-up</option>
                                                <option value="">Purchased</option>
                                                <option value="">Shipment/Delivery</option>
                                                <option value="">Service Unit</option>
                                                <option value="">Others</option>
                                            </select>
                                        </div> --}}
                                        <div class="row">
                                            <!-- RADIO BUTTONS BLOCK -->
                                            <div class="col-xs-12 col-sm-12 col-md-12">
                                                <div class="frb-group">
                                                    <div class="frb frb-default">
                                                        <input type="radio" id="radio-button-7" name="external_request"
                                                            value="1">
                                                        <label for="radio-button-7">
                                                            <span class="frb-title">For Demonstration</span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="frb-group">
                                                    <div class="frb frb-default">
                                                        <input type="radio" id="radio-button-8" name="external_request"
                                                            value="2">
                                                        <label for="radio-button-8">
                                                            <span class="frb-title">Reagent Tie-up</span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="frb-group">
                                                    <div class="frb frb-default">
                                                        <input type="radio" id="radio-button-9" name="external_request"
                                                            value="3">
                                                        <label for="radio-button-9">
                                                            <span class="frb-title">Purchased</span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="frb-group">
                                                    <div class="frb frb-default">
                                                        <input type="radio" id="radio-button-10" name="external_request"
                                                            value="4">
                                                        <label for="radio-button-10">
                                                            <span class="frb-title">Shipment/Delivery</span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="frb-group">
                                                    <div class="frb frb-default">
                                                        <input type="radio" id="radio-button-11" name="external_request"
                                                            value="5">
                                                        <label for="radio-button-11">
                                                            <span class="frb-title">Service Unit</span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="frb-group">
                                                    <div class="frb frb-default">
                                                        <input type="radio" id="radio-button-12" name="external_request"
                                                            value="11">
                                                        <label for="radio-button-12">
                                                            <span class="frb-title">Others</span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- OTHER REQUEST DETAILS --}}
                    <div class="card mt-3">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <h5 class="mb-3">Other Request Details</h5>
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" name="other_request_details" type="checkbox" value="" />
                                        <label class="form-check-label" for=""> Request for Occular </label>
                                    </div>
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" name="other_request_details" type="checkbox" value="" id="" />
                                        <label class="form-check-label" for=""> Bypass Internal Servicing
                                            Procedures </label>
                                    </div>
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" name="other_request_details" type="checkbox" value="" id="" />
                                        <label class="form-check-label" for=""> Ship & Deliver direct to
                                            customerimmediately</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <h5 class="mt-3">Request Approval Status</h5>
                    </div>
                    <div class="card mt-3">
                        <div class="row">
                            <div class="col-md-12 col-lg-12">
                                <div id="tracking-pre"></div>
                                <div id="tracking">
                                    <div class="tracking-list">
                                        <div class="tracking-item">
                                            <div class="tracking-icon status-current blinker"><!--blinker-->
                                                <svg class="svg-inline--fa fa-circle fa-w-16" aria-hidden="true"
                                                    data-prefix="fas" data-icon="circle" role="img"
                                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"
                                                    data-fa-i2svg="">
                                                    <path fill="currentColor"
                                                        d="M256 8C119 8 8 119 8 256s111 248 248 248 248-111 248-248S393 8 256 8z">
                                                    </path>
                                                </svg>
                                            </div>
                                            <div class="tracking-date"><i data-feather="user"></i></div>
                                            <div class="tracking-content">APM/NSM/SM<span>12/25/2023, 10:00am -
                                                    Pending</span></div>
                                        </div>
                                        <div class="tracking-item-pending"> <!--tracking-item-->
                                            <div class="tracking-icon status-intransit">
                                                <svg class="svg-inline--fa fa-circle fa-w-16" aria-hidden="true"
                                                    data-prefix="fas" data-icon="circle" role="img"
                                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"
                                                    data-fa-i2svg="">
                                                    <path fill="currentColor"
                                                        d="M256 8C119 8 8 119 8 256s111 248 248 248 248-111 248-248S393 8 256 8z">
                                                    </path>
                                                </svg>
                                            </div>
                                            <div class="tracking-date"><i data-feather="user"></i></div>
                                            <div class="tracking-content">IT Personnel<span>12/20/2023, 10:00am -
                                                    Pending</span></div>
                                        </div>
                                        <div class="tracking-item-pending">
                                            <div class="tracking-icon status-transit">
                                                <svg class="svg-inline--fa fa-circle fa-w-16" aria-hidden="true"
                                                    data-prefix="fas" data-icon="circle" role="img"
                                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"
                                                    data-fa-i2svg="">
                                                    <path fill="currentColor"
                                                        d="M256 8C119 8 8 119 8 256s111 248 248 248 248-111 248-248S393 8 256 8z">
                                                    </path>
                                                </svg>
                                            </div>
                                            <div class="tracking-date"><i data-feather="user"></i></div>
                                            <div class="tracking-content">Warehouse & Inventory Management
                                                (WIM)<span>12/28/2023, 10:30am - Pending</span></div>
                                        </div>
                                        <div class="tracking-item-pending">
                                            <div class="tracking-icon status-intransit">
                                                <svg class="svg-inline--fa fa-circle fa-w-16" aria-hidden="true"
                                                    data-prefix="fas" data-icon="circle" role="img"
                                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"
                                                    data-fa-i2svg="">
                                                    <path fill="currentColor"
                                                        d="M256 8C119 8 8 119 8 256s111 248 248 248 248-111 248-248S393 8 256 8z">
                                                    </path>
                                                </svg>
                                            </div>
                                            <div class="tracking-date"><i data-feather="user"></i></div>
                                            <div class="tracking-content">Service Department Team Leader<span>1/30/2024,
                                                    12:00pm - Pending</span></div>
                                        </div>
                                        <div class="tracking-item-pending">
                                            <div class="tracking-icon status-intransit">
                                                <svg class="svg-inline--fa fa-circle fa-w-16" aria-hidden="true"
                                                    data-prefix="fas" data-icon="circle" role="img"
                                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"
                                                    data-fa-i2svg="">
                                                    <path fill="currentColor"
                                                        d="M256 8C119 8 8 119 8 256s111 248 248 248 248-111 248-248S393 8 256 8z">
                                                    </path>
                                                </svg>
                                            </div>
                                            <div class="tracking-date"><i data-feather="user"></i></div>
                                            <div class="tracking-content">Service Department Head / Service
                                                Engineer<span>2/25/2024, 02:00pm - Pending</span></div>
                                        </div>
                                        <div class="tracking-item-pending">
                                            <div class="tracking-icon status-intransit">
                                                <svg class="svg-inline--fa fa-circle fa-w-16" aria-hidden="true"
                                                    data-prefix="fas" data-icon="circle" role="img"
                                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"
                                                    data-fa-i2svg="">
                                                    <path fill="currentColor"
                                                        d="M256 8C119 8 8 119 8 256s111 248 248 248 248-111 248-248S393 8 256 8z">
                                                    </path>
                                                </svg>
                                            </div>
                                            <div class="tracking-date"><i data-feather="user"></i></div>
                                            <div class="tracking-content">Billing & Invoicing Staff / WIM
                                                Personnel<span>3/05/2024, 03:00pm - Pending</span></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                </div>
            </div>

        </div>
        
    </form>
    {{-- End  of Form --}}
@endsection

@push('plugin-scripts')
    <script src="{{ asset('assets/plugins/datatables-net/jquery.dataTables.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-net-bs5/dataTables.bootstrap5.js') }}"></script>
@endpush

@push('plugin-scripts')
    {{-- <script src="{{ asset('assets/plugins/jquery-validation/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/bootstrap-maxlength/bootstrap-maxlength.min.js') }}"></script> --}}
    {{-- <script src="{{ asset('assets/plugins/inputmask/jquery.inputmask.min.js') }}"></script>
  <script src="{{ asset('assets/plugins/typeahead-js/typeahead.bundle.min.js') }}"></script>
  <script src="{{ asset('assets/plugins/jquery-tags-input/jquery.tagsinput.min.js') }}"></script>
  <script src="{{ asset('assets/plugins/dropzone/dropzone.min.js') }}"></script>
  <script src="{{ asset('assets/plugins/dropify/js/dropify.min.js') }}"></script>
  <script src="{{ asset('assets/plugins/pickr/pickr.min.js') }}"></script>
  <script src="{{ asset('assets/plugins/moment/moment.min.js') }}"></script> --}}

    <script src="{{ asset('assets/plugins/select2/select2.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/flatpickr/flatpickr.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/feather-icons/feather.min.js') }}"></script>
    
    @notifyJs
@endpush

@push('custom-scripts')
    {{-- <script src="{{ asset('assets/js/form-validation.js') }}"></script>
  <script src="{{ asset('assets/js/bootstrap-maxlength.js') }}"></script>
  <script src="{{ asset('assets/js/inputmask.js') }}"></script>
  <script src="{{ asset('assets/js/typeahead.js') }}"></script>
  <script src="{{ asset('assets/js/tags-input.js') }}"></script>
  <script src="{{ asset('assets/js/dropzone.js') }}"></script>
  <script src="{{ asset('assets/js/dropify.js') }}"></script>
  <script src="{{ asset('assets/js/pickr.js') }}"></script> --}}
    <script src="{{ asset('assets/js/flatpickr.js') }}"></script>
    <script src="{{ asset('assets/js/select2.js') }}"></script>
    <script src="{{ asset('js/general.js') }}"></script>
    <script src="{{ asset('js/EquipmentHandling/WorkOrder.js') }}"></script>
@endpush
