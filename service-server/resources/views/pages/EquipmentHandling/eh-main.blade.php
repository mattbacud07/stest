@extends('layout.master')

@push('plugin-styles')
  <link href="{{ asset('assets/plugins/datatables-net-bs5/dataTables.bootstrap5.css') }}" rel="stylesheet" />
@endpush

@section('content')
<nav class="page-breadcrumb d-flex justify-content-between">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="#">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Equipment Handling</li>
  </ol>
  {{-- <div>
    <a href="{{route('eh.addWorkOrder')}}" class="btn btn-primary btn-sm"><i data-feather='file-plus' class="text-sm" style="height: 17px;"></i> Submit Work Order</a>
  </div> --}}
</nav>

<div class="row">
  <div class="col-md-12 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        {{-- <h6 class="card-title">Data Table</h6> --}}
        <div class="table-responsive">
          <table id="dataTableExample" class="table table-bordered table-sm table-condensed" style="border: 1px solid #999;">
            <thead>
              <tr>
                <th>Requested by</th>
                <th>Institution</th>
                <th>Proposed Delivery Date</th>
                <th>Request Type</th>
                <th>Status</th>
                <th>Date</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($eh_data as $data)
                  <tr>
                    <td>{{$data->requested_by}}</td>
                    <td>{{$data->institution}}</td>
                    <td>{{$data->proposed_delivery}}</td>
                    <td>{{$data->requested_by}}</td>
                    <td>{{$data->status}}</td>
                    <td>{{$data->date}}</td>
                  </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('plugin-scripts')
  <script src="{{ asset('assets/plugins/datatables-net/jquery.dataTables.js') }}"></script>
  <script src="{{ asset('assets/plugins/datatables-net-bs5/dataTables.bootstrap5.js') }}"></script>
@endpush

@push('custom-scripts')
  <script src="{{ asset('assets/js/data-table.js') }}"></script>
@endpush