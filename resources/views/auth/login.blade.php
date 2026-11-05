@extends('layouts.app')

@section('title', 'ورود')

@section('content')
<div class="row justify-content-center mt-5">
    <div class="col-md-5">

        {{-- نمایش پیام موفقیت ثبت نام --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                {{ session('success') }}
            </div>
        @endif

        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white text-center">ورود به حساب کاربری</div>
            <div class="card-body">
                <form action="{{ route('login') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="email" class="form-label">ایمیل</label>
                        <input type="text" name="email" class="form-control text-start @error('email') is-invalid @enderror" value="{{ old('email') }}" dir="ltr">
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">رمز عبور</label>
                        <input type="password" name="password" class="form-control text-start @error('password') is-invalid @enderror" dir="ltr">
                        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <button type="submit" class="btn btn-primary w-100">ورود</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection