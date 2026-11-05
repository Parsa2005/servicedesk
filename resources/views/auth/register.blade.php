@extends('layouts.app')

@section('title', 'ثبت نام')

@section('content')
<div class="row justify-content-center mt-5">
    <div class="col-md-5">
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white text-center">ثبت نام کاربر جدید</div>
            <div class="card-body">
                <form action="{{ route('register') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="name" class="form-label">نام و نام خانوادگی</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}">
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">ایمیل</label>
                        <input type="email" name="email" class="form-control text-start @error('email') is-invalid @enderror" value="{{ old('email') }}" dir="ltr">
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">رمز عبور</label>
                        <input type="password" name="password" class="form-control text-start @error('password') is-invalid @enderror" dir="ltr">
                        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">تکرار رمز عبور</label>
                        <input type="password" name="password_confirmation" class="form-control text-start" dir="ltr">
                    </div>
                    <button type="submit" class="btn btn-success w-100">ثبت نام</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection