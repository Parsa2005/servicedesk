@extends('layouts.app')

@section('content')
<div class="container my-5" dir="rtl">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold mb-1">تنظیمات SLA شرکت: {{ $selectedCompany->name }}</h5>
                        <small class="text-muted">تعیین مهلت پاسخ‌گویی برای هر اولویت به صورت ساعت یا روز</small>
                    </div>
                </div>

                <div class="card-body p-4">
                    @if(session('success'))
                        <div class="alert alert-success rounded-3">{{ session('success') }}</div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger rounded-3">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- انتخاب شرکت فقط برای creator --}}
                    @if(auth()->user()->isCreator())
                        <form method="GET" action="{{ route('panel.slas.index') }}" class="mb-4 pb-3 border-bottom">
                            <label class="form-label fw-bold">انتخاب شرکت (ویژه مدیر کل / Creator):</label>
                            <select name="company_id" class="form-select" onchange="this.form.submit()">
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}" {{ $company->id === $selectedCompany->id ? 'selected' : '' }}>
                                        {{ $company->name }}
                                    </option>
                                @endforeach
                            </select>
                        </form>
                    @endif

                    <form method="POST" action="{{ route('panel.slas.update') }}">
                        @csrf
                        @method('PUT')

                        @if(auth()->user()->isCreator())
                            <input type="hidden" name="company_id" value="{{ $selectedCompany->id }}">
                        @endif

                        @php
                            $items = [
                                'critical' => ['label' => 'فوری', 'badge' => 'bg-danger'],
                                'high'     => ['label' => 'بالا', 'badge' => 'bg-warning text-dark'],
                                'normal'   => ['label' => 'عادی', 'badge' => 'bg-primary'],
                                'low'      => ['label' => 'کم', 'badge' => 'bg-secondary'],
                            ];
                        @endphp

                        @foreach($items as $key => $meta)
                            @php
                                $val = old("slas.$key.value", $slas[$key]['value']);
                                $unit = old("slas.$key.unit", $slas[$key]['unit']);
                            @endphp
                            <div class="row align-items-center mb-3 p-3 bg-light rounded-3 border">
                                <div class="col-md-4">
                                    <span class="badge {{ $meta['badge'] }} px-3 py-2 fs-6">{{ $meta['label'] }}</span>
                                </div>
                                <div class="col-md-4">
                                    <input type="number"
                                           step="1"
                                           min="1"
                                           name="slas[{{ $key }}][value]"
                                           value="{{ $val }}"
                                           class="form-control"
                                           placeholder="فقط عدد"
                                           required>
                                </div>
                                <div class="col-md-4">
                                    <select name="slas[{{ $key }}][unit]" class="form-select">
                                        <option value="hour" {{ $unit === 'hour' ? 'selected' : '' }}>ساعت (hour)</option>
                                        <option value="day" {{ $unit === 'day' ? 'selected' : '' }}>روز (day)</option>
                                    </select>
                                </div>
                            </div>
                        @endforeach

                        <div class="text-end mt-4">
                            <button type="submit" class="btn btn-primary px-4 rounded-pill fw-bold">
                                ذخیره تنظیمات SLA
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection