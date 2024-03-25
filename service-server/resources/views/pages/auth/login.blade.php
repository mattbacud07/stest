@extends('layout.master2')

@section('content')
<div class="page-content d-flex align-items-center justify-content-center">
  {{-- <div class="row w-100"> --}}
    <div class="col-md-4">
        <div class="card border-0 shadow-none">
            {{-- <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Login</h4>
            </div> --}}
            <style>
                .form-control{
                    box-shadow: 0 0 0 !important;
                }
            </style>

            <div class="card-body row">
                <form action="{{route('dashboardLogin')}}" method="POST">
                    @csrf
                    <div class="mb-3 text-center">
                        <img src="{{asset('assets/images/SBSI-Logo.png')}}" class="img-fluid rounded mb-3" style="width: 100px;" alt="" srcset="">
                        <h4>Hello, Welcome Back!</h4>
                    </div>
                    @if(session()->has('error'))
                        <span class="text-danger">{{session()->get('error')}}</span>
                    @endif
                    <div class="mb-3">
                        <label for="username" class="form-label">Email</label>
                        <input type="text" class="form-control form-control-md" id="username" name="email" value="{{old('email')}}">
                        @error('email')
                            <span class="text-danger">{{$message}}</span>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password">
                        @error('password')
                        <span class="text-danger">{{$message}}</span>
                        @enderror
                    </div>
                    {{-- <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="remember">
                        <label class="form-check-label" for="remember">Remember me</label>
                    </div> --}}
                    <button type="submit" class="btn btn-primary form-control border-0" style="background: #191970;">Login</button>
                </form>
            </div>
        </div>
    </div>
  {{-- </div> --}}

</div>
@endsection