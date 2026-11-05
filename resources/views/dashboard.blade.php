@extends('layouts.app')

@section('title', 'داشبورد')

@section('content')
<div class="alert alert-success">
    شما با موفقیت وارد شدید! سطح کاربری شما: {{ auth()->user()->level }}
</div>
@endsection