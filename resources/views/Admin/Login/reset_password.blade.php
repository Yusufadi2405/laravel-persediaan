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
            <h4 class="fw-bold mt-4 text-black text-uppercase text-truncate">Password Baru</h4>
            <p class="text-muted">Gunakan password yang mudah diingat</p>
        </div>

        <form class="login100-form validate-form" method="POST" action="{{ url('admin/reset-password') }}">
            @csrf
            <input type="hidden" name="email" value="{{ $email }}">
            <div class="panel panel-primary">
                <div class="panel-body tabs-menu-body p-0 pt-5">
                    <div class="wrap-input100 validate-input input-group">
                        <a tabindex="-1" href="javascript:void(0)" class="input-group-text bg-white text-muted">
                            <i class="zmdi zmdi-lock text-muted ms-1"></i>
                        </a>
                        <input name="password" class="input100 border-start-0 form-control ms-0" type="password" placeholder="Password Baru" required>
                    </div>
                    
                    <div class="container-login100-form-btn">
                        <button type="submit" class="login100-form-btn btn btn-primary">Update Password</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection