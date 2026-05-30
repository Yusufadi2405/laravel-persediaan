@extends('Master.Layouts.app_login', ['title' => $title])

@section('content')
<div class="container-login100">
    <div class="wrap-login100 p-6">
        <div class="d-flex justify-content-center align-items-center">
            @if($web->web_logo == '' || $web->web_logo == 'default.png')
                <img src="{{url('/assets/default/web/default.png')}}" height="75px" alt="logo">
            @else
                <img src="{{url('/assets/default/web/' . $web->web_logo)}}" height="75px" alt="logo">
            @endif
        </div>
        <div class="text-center">
            <h4 class="fw-bold mt-4 text-black text-uppercase text-truncate">{{$web->web_nama}} <span class="text-gray">| LUPA PASSWORD</span></h4>
            <p class="text-muted">Masukkan email untuk menerima kode OTP</p>
        </div>

        <form class="login100-form validate-form" method="POST" action="{{ url('admin/forgot-password') }}">
            @csrf
            <div class="panel panel-primary">
                <div class="panel-body tabs-menu-body p-0 pt-5">
                    <div class="wrap-input100 validate-input input-group">
                        <a tabindex="-1" href="javascript:void(0)" class="input-group-text bg-white text-muted">
                            <i class="zmdi zmdi-email text-muted ms-1"></i>
                        </a>
                        <input name="email" class="input100 border-start-0 form-control ms-0" type="email" placeholder="Email Terdaftar" required autocomplete="off">
                    </div>
                    
                    <div class="container-login100-form-btn">
                        <button type="submit" class="login100-form-btn btn btn-primary">Kirim Kode OTP</button>
                    </div>

                    <div class="text-center pt-4">
                        <p class="mb-0"><a href="{{ url('admin/login') }}" class="text-primary ms-1">Kembali ke Login</a></p>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection