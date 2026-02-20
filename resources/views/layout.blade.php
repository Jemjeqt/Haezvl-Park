<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sistem Parkir') — Haezvl Park</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <div class="app-container">
        {{-- Sidebar --}}
        <aside class="sidebar">
            <div class="sidebar-brand">
                <div class="brand-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="1" y="3" width="15" height="13" rx="2"/>
                        <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/>
                        <circle cx="5.5" cy="18.5" r="2.5"/>
                        <circle cx="18.5" cy="18.5" r="2.5"/>
                    </svg>
                </div>
                <div class="brand-text">
                    <span class="brand-name">Haezvl Park</span>
                    <span class="brand-sub">Management System</span>
                </div>
            </div>

            <nav class="sidebar-nav">
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="7" height="7" rx="1"/>
                        <rect x="14" y="3" width="7" height="4" rx="1"/>
                        <rect x="14" y="10" width="7" height="11" rx="1"/>
                        <rect x="3" y="13" width="7" height="8" rx="1"/>
                    </svg>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('entry.index') }}" class="nav-link {{ request()->routeIs('entry.*') ? 'active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                        <polyline points="10 17 15 12 10 7"/>
                        <line x1="15" y1="12" x2="3" y2="12"/>
                    </svg>
                    <span>Entry Gate</span>
                </a>
                <a href="{{ route('exit.index') }}" class="nav-link {{ request()->routeIs('exit.*') ? 'active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                        <polyline points="16 17 21 12 16 7"/>
                        <line x1="21" y1="12" x2="9" y2="12"/>
                    </svg>
                    <span>Exit Gate</span>
                </a>
                <a href="{{ route('history.index') }}" class="nav-link {{ request()->routeIs('history.*') ? 'active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                        <line x1="16" y1="13" x2="8" y2="13"/>
                        <line x1="16" y1="17" x2="8" y2="17"/>
                    </svg>
                    <span>History</span>
                </a>
            </nav>

            <div class="sidebar-footer">
                <div class="clock" id="liveClock"></div>
            </div>
        </aside>

        {{-- Main content --}}
        <main class="main-content">
            <header class="main-header">
                <h1 class="page-title">@yield('page-title', 'Dashboard')</h1>
                <div class="header-badge">
                    <span class="pulse-dot"></span>
                    <span>Sistem Aktif</span>
                </div>
            </header>

            {{-- Flash messages --}}
            @if(session('success'))
                <div class="alert alert-success" id="flashAlert">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                        <polyline points="22 4 12 14.01 9 11.01"/>
                    </svg>
                    <span>{{ session('success') }}</span>
                    <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-error" id="flashAlert">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="15" y1="9" x2="9" y2="15"/>
                        <line x1="9" y1="9" x2="15" y2="15"/>
                    </svg>
                    <span>{{ session('error') }}</span>
                    <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-error">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="8" x2="12" y2="12"/>
                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    <div>
                        @foreach($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="content-area">
                @yield('content')
            </div>
        </main>
    </div>

    <script>
        // Live clock
        function updateClock() {
            const el = document.getElementById('liveClock');
            if (el) {
                const now = new Date();
                el.textContent = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            }
        }
        setInterval(updateClock, 1000);
        updateClock();

        // Auto-dismiss flash alerts
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(el => {
                el.style.animation = 'slideOut 0.3s ease forwards';
                setTimeout(() => el.remove(), 300);
            });
        }, 5000);
    </script>
    @stack('scripts')
</body>
</html>
