@extends('layouts.app')

@section('title', 'Admin Login — Travora')

@section('content')

<section class="auth-section">
    <div class="auth-bg">
        <img src="https://images.unsplash.com/photo-1488646953014-85cb44e25828?q=80&w=2000&auto=format&fit=crop" alt="Admin Login">
        <div class="auth-overlay"></div>
    </div>

    <div class="container position-relative" style="z-index: 2;">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-lg-5 col-md-7">
                
                <div class="auth-card glass-card rounded-4 p-4 p-md-5">
                    
                    <div class="text-center mb-4">
                        <a href="{{ route('home') }}" class="auth-logo">TRAVORA</a>
                        <p class="auth-subtitle">
                            <i class="fas fa-shield-alt text-accent me-2"></i>Admin Panel
                        </p>
                        <p class="text-white-50 small mb-0">Authorized personnel only</p>
                    </div>

                    @if($errors->any())
                        <div class="alert alert-danger mb-4">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <form action="{{ route('admin.login.submit') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label class="planner-label">Admin Email</label>
                            <div class="auth-input-wrap">
                                <i class="fas fa-envelope"></i>
                                <input type="email" name="email" class="form-control planner-input" 
                                       value="{{ old('email') }}" placeholder="admin@travora.com" required autofocus>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="planner-label">Password</label>
                            <div class="auth-input-wrap">
                                <i class="fas fa-lock"></i>
                                <input type="password" name="password" class="form-control planner-input" 
                                       placeholder="Enter admin password" required>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="form-check">
                                <input type="checkbox" name="remember" class="form-check-input" id="remember">
                                <label class="form-check-label text-white-50 small" for="remember">Remember me</label>
                            </div>
                        </div>

                        <button type="submit" class="btn travora-btn-primary w-100 magnetic-btn mb-4">
                            <i class="fas fa-sign-in-alt me-2"></i> Login to Admin Panel
                        </button>

                        <p class="text-center text-white-50 small mb-0">
                            Not an admin? 
                            <a href="{{ route('login') }}" class="text-accent text-decoration-none fw-semibold">User Login</a>
                        </p>

                    </form>

                    <!-- Demo Admin Credentials -->
                    <div class="demo-creds mt-4">
                        <p class="small mb-2 text-white-50"><i class="fas fa-info-circle me-1"></i> Demo Admin:</p>
                        <button type="button" class="demo-cred-btn" onclick="fillDemo('admin@travora.com', 'admin123')">
                            <i class="fas fa-user-shield"></i>
                            <span><strong>Admin:</strong> admin@travora.com / admin123</span>
                        </button>
                    </div>

                </div>

            </div>
        </div>
    </div>
</section>

@endsection

@push('scripts')
<script>
    function fillDemo(email, password) {
        document.querySelector('input[name="email"]').value = email;
        document.querySelector('input[name="password"]').value = password;
    }
</script>
@endpush