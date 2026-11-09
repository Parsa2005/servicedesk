<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ServiceDesk - @yield('title')</title>
    <!-- Bootstrap CSS RTL -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <style>
        body { background-color: #f8f9fa; font-family: Tahoma, Arial, sans-serif; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="/">سامانه ServiceDesk</a>
            <div class="collapse navbar-collapse">
                <!-- منوی سمت راست (پیش‌فرض در RTL) -->
                <ul class="navbar-nav me-auto">
                    @auth
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('panel.menu') }}">پنل مدیریت</a>
                        </li>
                    @endauth
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('user.tickets.*') ? 'active' : '' }}" href="{{ route('user.tickets.index') }}">تیکت‌های پشتیبانی</a>
                    </li>
                </ul>

                <!-- منوی سمت چپ (اطلاعات کاربر، زنگوله اعلان‌ها و خروج) -->
                <ul class="navbar-nav ms-auto align-items-center">
                    @auth
                        <!-- 🔔 آیکون زنگوله اعلان‌ها -->
                        <li class="nav-item dropdown ms-3">
                            <a id="notificationDropdown" class="nav-link position-relative text-light fs-5 px-2" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                🔔
                                @if(auth()->user()->unreadNotifications->count() > 0)
                                    <span class="position-absolute top-25 start-0 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">
                                        {{ auth()->user()->unreadNotifications->count() }}
                                    </span>
                                @endif
                            </a>

                            <div class="dropdown-menu dropdown-menu-end py-0 shadow-lg border-0 rounded-4 overflow-hidden" style="width: 300px; max-height: 400px; overflow-y: auto;" aria-labelledby="notificationDropdown">
                                <div class="bg-light fw-bold text-dark py-2 px-3 border-bottom d-flex justify-content-between align-items-center small">
                                    <span>اعلان‌ها</span>
                                    @if(auth()->user()->unreadNotifications->count() > 0)
                                        <a href="{{ route('user.notifications.readAll') }}" class="text-decoration-none text-primary">خوانده شده همه</a>
                                    @endif
                                </div>

                                @forelse(auth()->user()->notifications()->take(10)->get() as $notification)
                                    <a href="{{ $notification->data['url'] ?? '#' }}" class="dropdown-item py-2 px-3 border-bottom text-wrap {{ $notification->unread() ? 'bg-body-tertiary fw-semibold' : '' }}" style="font-size: 0.85rem;">
                                        <div class="text-muted" style="font-size: 0.75rem; margin-bottom: 2px;">{{ $notification->created_at->diffForHumans() }}</div>
                                        <div class="text-dark lh-sm">{{ $notification->data['message'] ?? 'اعلان جدید' }}</div>
                                    </a>
                                @empty
                                    <div class="dropdown-item text-center py-3 text-muted small">هیچ اعلانی وجود ندارد</div>
                                @endforelse

                                <!-- 👈 لینک مشاهده همه اعلان‌ها در انتهای منو -->
                                <div class="text-center py-2 border-top bg-light">
                                    <a href="{{ route('user.notifications.index') }}" class="small text-decoration-none fw-bold text-primary">مشاهده همه اعلان‌ها</a>
                                </div>
                            </div>
                        </li>

                        <li class="nav-item">
                            <span class="nav-link text-light small">خوش آمدید، {{ auth()->user()->name }} ({{ auth()->user()->level }})</span>
                        </li>
                        <li class="nav-item">
                            <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-link nav-link text-danger small">خروج</button>
                            </form>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">ورود</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('register') }}">ثبت نام</a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        @yield('content')
    </div>

    <!-- اسکریپت جاوااسکریپت بوسترپ برای کار کردن منوی کشویی (Dropdown) زنگوله -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>