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
            <h4 class="fw-bold mt-4 text-black text-uppercase text-truncate">Verifikasi OTP</h4>
            <p class="text-muted">Kode dikirim ke: <strong>{{ $email }}</strong></p>
        </div>

        <form class="login100-form validate-form" method="POST" action="{{ url('admin/verify-otp') }}">
            @csrf
            <input type="hidden" name="email" value="{{ $email }}">
            <div class="panel panel-primary">
                <div class="panel-body tabs-menu-body p-0 pt-5">
                    <div class="wrap-input100 validate-input input-group">
                        <a tabindex="-1" href="javascript:void(0)" class="input-group-text bg-white text-muted">
                            <i class="zmdi zmdi-key text-muted ms-1"></i>
                        </a>
                        <input name="otp" class="input100 border-start-0 form-control ms-0" type="text" placeholder="Masukkan 6 Digit OTP" maxlength="6" required>
                    </div>
                    
                    <div class="container-login100-form-btn">
                        <button type="submit" class="login100-form-btn btn btn-primary">Verifikasi</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection