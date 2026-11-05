<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'پنل مدیریت')</title>

    <!-- فراخوانی محلی لاراول بدون CDN -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 text-slate-800 antialiased min-h-screen">

<div class="flex flex-col md:flex-row min-h-screen">
    <!-- Sidebar -->
    <aside class="w-full md:w-64 bg-slate-900 text-white flex-shrink-0 p-4 border-l border-slate-800">
        <div class="text-center pb-4 mb-4 border-b border-slate-800">
            <h5 class="text-lg font-bold text-white">پنل مدیریت</h5>
        </div>
        
        <nav class="flex flex-col gap-1.5">
            <a href="{{ route('panel.menu') }}" class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm transition-colors {{ request()->routeIs('panel.menu') ? 'bg-blue-600 text-white font-medium shadow-sm' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                <span>📊</span> داشبورد اصلی
            </a>
            
            <a href="{{ route('panel.roles.index') }}" class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm transition-colors {{ request()->routeIs('panel.roles.*') ? 'bg-blue-600 text-white font-medium shadow-sm' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                <span>🔑</span> مدیریت نقش‌ها
            </a>
            
            <a href="{{ route('panel.users.index') }}" class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm transition-colors {{ request()->routeIs('panel.users.*') ? 'bg-blue-600 text-white font-medium shadow-sm' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                <span>👥</span> مدیریت کاربران
            </a>
            
            <hr class="border-slate-800 my-2">
            
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm text-sky-400 hover:bg-slate-800 transition-colors">
                <span>🏠</span> بازگشت به صفحه اصلی
            </a>
            
            <form action="{{ route('logout') }}" method="POST" class="mt-2">
                @csrf
                <button type="submit" class="w-full flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm text-red-400 hover:bg-red-500/10 hover:text-red-300 transition-colors text-right">
                    <span>🚪</span> خروج از حساب
                </button>
            </form>
        </nav>
    </aside>

    <!-- Main Content Area -->
    <main class="flex-1 p-4 md:p-8 overflow-y-auto">
        <!-- Top Header -->
        <header class="flex justify-between items-center mb-6 pb-4 border-b border-slate-200 bg-white p-4 rounded-2xl shadow-sm border border-slate-100">
            <h5 class="text-sm md:text-base font-medium text-slate-700">
                خوش آمدید، <strong class="text-slate-900 font-bold">{{ auth()->user()->name }}</strong>
            </h5>
            <span class="px-3 py-1 text-xs font-semibold rounded-full {{ auth()->user()->level === 'creator' ? 'bg-purple-100 text-purple-700 border border-purple-200' : 'bg-blue-100 text-blue-700 border border-blue-200' }}">
                {{ auth()->user()->level === 'creator' ? 'مالک سیستم (Creator)' : 'مدیر (Admin)' }}
            </span>
        </header>

        <!-- Page Content -->
        <div>
            @yield('content')
        </div>
    </main>
</div>

</body>
</html>