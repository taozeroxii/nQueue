<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modern Queue Display</title>
    <script src="assets/vendor/tailwind/tailwind.js"></script>
    <link href="assets/vendor/sarabun/sarabun.css" rel="stylesheet">
    <script src="assets/vendor/socket.io/socket.io.js"></script> <!-- Added Socket.IO here as it is used in the file -->
    <style>
        :root {
            --display-font: 'Sarabun', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            --ink: #0f172a;
            --brand-blue: #075985;
            --brand-cyan: #06b6d4;
            --brand-emerald: #10b981;
        }

        * {
            text-rendering: geometricPrecision;
        }

        body {
            font-family: var(--display-font);
            background:
                radial-gradient(circle at top left, rgba(14, 165, 233, 0.22), transparent 32rem),
                radial-gradient(circle at 80% 0%, rgba(16, 185, 129, 0.16), transparent 28rem),
                linear-gradient(135deg, #eef7ff 0%, #f8fafc 46%, #ecfeff 100%);
            color: var(--ink);
        }

        .glass-panel {
            background: rgba(255, 255, 255, 0.82);
            backdrop-filter: blur(22px);
            border: 1px solid rgba(255, 255, 255, 0.68);
            box-shadow: 0 24px 80px rgba(15, 23, 42, 0.12);
        }

        .display-header {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.9), rgba(240, 249, 255, 0.78));
            backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.72);
            box-shadow: 0 18px 55px rgba(2, 132, 199, 0.14);
        }

        .brand-orb {
            background: linear-gradient(135deg, #0ea5e9, #0369a1 58%, #10b981);
            box-shadow: 0 18px 35px rgba(14, 165, 233, 0.32);
        }

        .soft-card {
            background: rgba(255, 255, 255, 0.88);
            border: 1px solid rgba(226, 232, 240, 0.84);
            box-shadow: 0 22px 60px rgba(15, 23, 42, 0.1);
        }

        .called-card {
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.96), rgba(248, 250, 252, 0.94));
            border: 1px solid rgba(186, 230, 253, 0.86);
            box-shadow: 0 26px 75px rgba(2, 132, 199, 0.16);
        }

        .called-card-hot {
            background: linear-gradient(180deg, #fff7ed, #ffffff);
            border: 1px solid rgba(251, 191, 36, 0.9);
            box-shadow: 0 28px 80px rgba(245, 158, 11, 0.26);
        }

        .room-header {
            background: linear-gradient(135deg, #075985, #0284c7 62%, #06b6d4);
        }

        .room-header-hot {
            background: linear-gradient(135deg, #f59e0b, #facc15);
        }

        .queue-number-gradient {
            background: linear-gradient(135deg, #0f172a 8%, #0369a1 45%, #0891b2 92%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            text-shadow: 0 20px 60px rgba(14, 165, 233, 0.18);
        }

        .status-dock {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(24px);
            border: 1px solid rgba(226, 232, 240, 0.78);
            box-shadow: 0 -24px 70px rgba(15, 23, 42, 0.12);
        }

        .status-zone {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.92), rgba(248, 250, 252, 0.7));
            border: 1px solid rgba(226, 232, 240, 0.72);
        }

        .animate-pulse-slow {
            animation: pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        @keyframes slideIn {
            from {
                transform: translateY(20px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .queue-item {
            animation: slideIn 0.5s ease-out;
        }

        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }

        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            300: '#7dd3fc',
                            400: '#38bdf8',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e',
                        },
                        hospital: {
                            blue: '#0056b3',    /* Deep Blue like in the image */
                            light: '#e6f2ff',   /* Very light blue bg */
                            text: '#002e5d',    /* Dark Navy for text */
                            accent: '#007bff'
                        }
                    }
                }
            }
        }
    </script>
</head>

<body class="min-h-screen text-slate-900 overflow-hidden">

    <!-- Top Header -->
    <!-- Top Header -->
    <header
        class="display-header m-4 mb-0 rounded-[2rem] p-5 px-8 flex justify-between items-center relative z-50">
        <div class="flex items-center gap-6">
            <div
                class="brand-orb w-16 h-16 rounded-3xl flex items-center justify-center p-3 border border-white/50">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-full w-full text-white" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
            </div>
            <div class="cursor-pointer group" onclick="openSettings()">
                <div class="flex items-center gap-3">
                <h1 id="dept-name"
                    class="text-4xl 2xl:text-5xl font-extrabold text-sky-950 group-hover:text-sky-700 transition tracking-tight leading-tight">
                    โรงพยาบาลบุรีรัมย์</h1>
                    <span id="ws-status-badge"
                        class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1.5 text-sm font-extrabold text-slate-500 ring-1 ring-slate-200 shadow-sm"
                        title="WebSocket: connecting">
                        <span id="ws-status-dot"
                            class="inline-flex h-4 w-4 rounded-full bg-slate-300 ring-4 ring-slate-200 transition shadow-sm"></span>
                        <span id="ws-status-text">ws:connecting</span>
                    </span>
                </div>
                <p id="dept-sub"
                    class="text-slate-500 font-semibold text-xl 2xl:text-2xl group-hover:text-sky-700 transition mt-1">
                    คิวตรวจโรคทั่วไป</p>
            </div>
        </div>
        <div class="text-right">
            <div id="clock" class="text-5xl 2xl:text-6xl font-black tracking-tight text-sky-950 tabular-nums">00:00</div>
            <div id="date" class="text-slate-500 font-semibold text-lg mt-1">...</div>
        </div>
    </header>

    <!-- Settings Modal -->
    <div id="settings-modal"
        class="fixed inset-0 bg-slate-900/50 hidden items-center justify-center z-[100] backdrop-blur-sm">
        <div
            class="bg-white p-8 rounded-3xl w-full max-w-md border border-slate-200 shadow-2xl transform scale-100 transition-all">
            <h2 class="text-2xl font-black text-hospital-blue mb-6 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-hospital-accent" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Display Settings
            </h2>

            <div class="space-y-4">
                <div>
                    <label class="block text-slate-500 mb-2 text-sm font-semibold">Department Name (Main Title)</label>
                    <input type="text" id="input-dept-name"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-slate-800 focus:border-hospital-blue focus:ring-2 focus:ring-blue-100 focus:outline-none placeholder-slate-400"
                        placeholder="e.g. แผนกอายุรกรรม">
                </div>

                <!-- NOTE: TTS Prefix/Middle removed/disabled for File-mode as it uses fixed files -->
                <div class="bg-blue-50 p-3 rounded-xl border border-blue-100">
                    <p class="text-xs text-blue-600 text-center font-medium">Note: This display uses File-based Audio
                        (Prompt4)</p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-slate-500 mb-2 text-sm font-semibold">Filter by Department</label>
                        <select id="input-dept-filter" onchange="onDeptFilterChange()"
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-slate-800 focus:border-hospital-blue focus:ring-2 focus:ring-blue-100 focus:outline-none">
                            <option value="">Show All</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-slate-500 mb-2 text-sm font-semibold">Filter by Room (Select
                            Multiple)</label>
                        <div class="relative">
                            <button id="room-filter-btn" onclick="toggleRoomFilter()"
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-left text-slate-800 focus:border-hospital-blue focus:ring-2 focus:ring-blue-100 focus:outline-none flex justify-between items-center">
                                <span id="room-filter-label">Select Rooms</span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-400" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div id="room-filter-menu"
                                class="hidden absolute top-full left-0 right-0 mt-1 bg-white border border-slate-200 rounded-xl shadow-xl z-50 max-h-60 overflow-y-auto p-2 grid grid-cols-1 gap-1">
                                <!-- Checkboxes injected here -->
                                <div class="text-slate-400 text-sm p-2">Select a department first</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-slate-500 mb-2 text-sm font-semibold">Subtitle</label>
                    <input type="text" id="input-dept-sub"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-slate-800 focus:border-hospital-blue focus:ring-2 focus:ring-blue-100 focus:outline-none placeholder-slate-400"
                        placeholder="e.g. Room 1-5">
                </div>

                <div>
                    <label class="block text-slate-500 mb-2 text-sm font-semibold">Call Repetitions</label>
                    <input type="number" id="input-tts-repeat" min="1" max="5"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-slate-800 focus:border-hospital-blue focus:ring-2 focus:ring-blue-100 focus:outline-none placeholder-slate-400"
                        placeholder="Default: 1">
                </div>

                <div class="border-t border-slate-100 pt-4 mt-4">
                    <h3 class="text-slate-800 font-bold mb-3">Connection Settings</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-slate-500 mb-2 text-sm font-semibold">API Base URL</label>
                            <input type="text" id="input-api-base"
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-slate-800 focus:border-hospital-blue focus:ring-2 focus:ring-blue-100 focus:outline-none placeholder-slate-400"
                                placeholder="e.g. http://localhost/nQueue/public/">
                            <p class="text-xs text-slate-400 mt-1">Leave empty for relative path (default)</p>
                        </div>
                        <div>
                            <label class="block text-slate-500 mb-2 text-sm font-semibold">WebSocket URL</label>
                            <input type="text" id="input-ws-url"
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-slate-800 focus:border-hospital-blue focus:ring-2 focus:ring-blue-100 focus:outline-none placeholder-slate-400"
                                placeholder="e.g. ws://localhost:8765">
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-8">
                <button onclick="closeSettings()"
                    class="px-4 py-2 text-slate-500 hover:text-slate-800 transition font-medium">Cancel</button>
                <button onclick="saveSettings()"
                    class="px-6 py-2 bg-hospital-blue hover:bg-blue-700 text-white rounded-xl shadow-lg shadow-blue-500/30 transition-all font-semibold">Save
                    Changes</button>
            </div>
        </div>
    </div>

    <!-- Sound Enable Overlay (Autoplay Policy) -->
    <div id="sound-overlay"
        class="fixed inset-0 bg-white/90 z-[70] flex flex-col items-center justify-center cursor-pointer backdrop-blur-sm"
        onclick="unlockAudio()">
        <div class="bg-hospital-blue p-8 rounded-full animate-bounce mb-4 shadow-xl shadow-blue-500/30">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-white" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
            </svg>
        </div>
        <h1 class="text-3xl font-black text-slate-800 mb-2">Click Anywhere to Enable Sound</h1>
        <p class="text-slate-500">Browser policy requires interaction to play audio</p>
    </div>

    <!-- Kiosk Auto-Init Script -->
    <script>
        // Check URL param or default kiosk behavior
        const urlParams = new URLSearchParams(window.location.search);

        function tryAutoUnlock() {
            if (audioUnlocked) return;
            console.log("Attempting Auto-Unlock Audio...");

            // Try to speak a silent char/play silent audio to trigger unlock
            // With File Audio, we should try to play an empty buffer or a silent file if we had one
            // Or just a tiny part of a file known to exist
            const a = new Audio("Prompt4/Prompt4_Number.mp3");
            a.volume = 0;
            a.play().then(() => {
                a.pause();
                audioUnlocked = true;
                const overlay = document.getElementById('sound-overlay');
                if (overlay) overlay.classList.add('hidden');
                console.log("Auto-Unlock Successful!");
            }).catch(e => console.log("Auto-Unlock failed (user interaction might be needed)"));
        }

        window.addEventListener('load', () => {
            tryAutoUnlock();
            // Retry once after a short delay just in case
            setTimeout(tryAutoUnlock, 1000);

            // Attempt Fullscreen
            if (urlParams.has('kiosk')) {
                document.documentElement.requestFullscreen().catch(e => {
                    console.log("Auto-FS failed", e);
                });
            }
        });

        // Additional listener for first click to ensure FS if failed
        document.addEventListener('click', () => {
            if (urlParams.has('kiosk') && !document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(() => { });
            }
        }, { once: true });
    </script>

    <!-- Initial Department Selection Overlay -->
    <div id="dept-select-overlay" class="fixed inset-0 bg-slate-50 z-[60] hidden flex-col items-center justify-center">
        <div class="text-center max-w-md w-full p-6">
            <h1 class="text-4xl font-black text-hospital-text mb-2">Welcome</h1>
            <p class="text-slate-500 mb-8 font-medium">Please select a department to display</p>
            <div id="dept-selection-list" class="flex flex-col gap-3">
                <!-- Buttons injected here -->
            </div>
            <button onclick="selectDept('')"
                class="mt-6 text-slate-400 hover:text-hospital-blue underline text-sm transition font-semibold">Show All
                Departments</button>
        </div>
    </div>

    <!-- Main Content -->
    <main class="px-4 lg:px-8 pt-5 pb-40 h-[calc(100vh-112px)] grid grid-cols-1 lg:grid-cols-12 gap-8 overflow-hidden">
        <!-- Main Content Grid -->
        <div id="current-called-container" class="lg:col-span-12 h-full overflow-hidden pb-40">
            <!-- Combined Room Card + Waiting List Grid -->
            <div id="room-grid" class="grid grid-cols-[repeat(auto-fit,minmax(320px,1fr))] gap-6 p-2">
                <!-- Dynamic Content -->
            </div>
        </div>

        <!-- Bottom: Lab & X-Ray Status -->
        <div
            class="status-dock fixed bottom-4 left-4 right-4 h-36 rounded-[2rem] z-40 grid grid-cols-3 gap-3 p-3">
            <!-- Not Found Section -->
            <div class="status-zone relative overflow-hidden group rounded-[1.5rem]">
                <div class="absolute inset-0 bg-gradient-to-br from-red-50/80 to-white group-hover:from-red-100/80 transition"></div>
                <div class="h-full flex items-center px-6 gap-4 relative z-10">
                    <div class="flex flex-col justify-center shrink-0 border-r-2 border-red-200 pr-4">
                        <span class="text-red-500 font-bold text-sm tracking-widest uppercase">Not Found</span>
                        <h3 class="text-2xl font-black text-red-700">เรียกไม่พบ</h3>
                    </div>
                    <div id="notfound-list"
                        class="flex items-center gap-3 overflow-x-auto p-3 scrollbar-hide w-full mask-linear-fade">
                        <div class="text-slate-400 italic">No patients</div>
                    </div>
                </div>
            </div>
            <!-- Lab Section -->
            <div class="status-zone relative overflow-hidden group rounded-[1.5rem]">
                <div class="absolute inset-0 bg-gradient-to-br from-sky-50/80 to-white group-hover:from-sky-100/80 transition"></div>
                <div class="h-full flex items-center px-6 gap-4 relative z-10">
                    <div class="flex flex-col justify-center shrink-0 border-r-2 border-hospital-blue/10 pr-4">
                        <span class="text-hospital-blue font-bold text-sm tracking-widest uppercase">Laboratory</span>
                        <h3 class="text-2xl font-black text-hospital-text">รอ Lab</h3>
                    </div>
                    <div id="lab-list"
                        class="flex items-center gap-3 overflow-x-auto p-3 scrollbar-hide w-full mask-linear-fade">
                        <!-- Dynamic Items -->
                        <div class="text-slate-400 italic">No patients</div>
                    </div>
                </div>
            </div>
            <!-- X-Ray Section -->
            <div class="status-zone relative overflow-hidden group rounded-[1.5rem]">
                <div class="absolute inset-0 bg-gradient-to-br from-violet-50/80 to-white group-hover:from-violet-100/80 transition"></div>
                <div class="h-full flex items-center px-6 gap-4 relative z-10">
                    <div class="flex flex-col justify-center shrink-0 border-r-2 border-hospital-blue/10 pr-4">
                        <span class="text-hospital-blue font-bold text-sm tracking-widest uppercase">Radiology</span>
                        <h3 class="text-2xl font-black text-hospital-text">รอ X-Ray</h3>
                    </div>
                    <div id="xray-list"
                        class="flex items-center gap-3 overflow-x-auto p-3 scrollbar-hide w-full mask-linear-fade">
                        <!-- Dynamic Items -->
                        <div class="text-slate-400 italic">No patients</div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Style fix applied directly to HTML
    </script>


    <script>
        // State
        let currentDeptFilter = localStorage.getItem('dept_filter') || '';

        let currentRoomFilter = [];
        try {
            const stored = localStorage.getItem('room_filter');
            if (stored) {
                // Support both old single value and new array
                if (stored.startsWith('[')) {
                    currentRoomFilter = JSON.parse(stored);
                } else {
                    currentRoomFilter = [stored];
                }
            }
        } catch (e) { currentRoomFilter = []; }
        let ttsRepeat = parseInt(localStorage.getItem('tts_repeat')) || 1;

        // Connection Settings: keep this display local/same-host to avoid delays when offline.
        const defaultWsUrl = `${window.location.protocol === 'https:' ? 'wss' : 'ws'}://${window.location.hostname || 'localhost'}:8765`;

        const isPrivateNetworkHost = (host) => {
            return host === 'localhost'
                || host === '127.0.0.1'
                || host === '::1'
                || host.endsWith('.local')
                || /^10\./.test(host)
                || /^192\.168\./.test(host)
                || /^172\.(1[6-9]|2\d|3[0-1])\./.test(host);
        };

        const isLocalUrl = (urlValue, allowedProtocols) => {
            if (!urlValue) return true;
            try {
                const parsed = new URL(urlValue, window.location.href);
                const host = parsed.hostname;
                return allowedProtocols.includes(parsed.protocol)
                    && (
                        host === window.location.hostname
                        || isPrivateNetworkHost(host)
                    );
            } catch (e) {
                return false;
            }
        };

        const normalizeLocalApiBase = (value) => {
            const trimmed = (value || '').trim();
            if (!trimmed) return '';
            if (isLocalUrl(trimmed, ['http:', 'https:'])) return trimmed;
            console.warn('External API Base URL ignored. Using local relative API paths instead:', trimmed);
            localStorage.setItem('api_base', '');
            return '';
        };

        const normalizeLocalWsUrl = (value) => {
            const trimmed = (value || '').trim();
            if (!trimmed) return defaultWsUrl;
            if (isLocalUrl(trimmed, ['ws:', 'wss:'])) return trimmed;
            console.warn('External WebSocket URL ignored. Using local WebSocket instead:', trimmed);
            localStorage.setItem('ws_url', defaultWsUrl);
            return defaultWsUrl;
        };

        let apiBase = normalizeLocalApiBase(localStorage.getItem('api_base')); // Default empty = relative
        let wsUrl = normalizeLocalWsUrl(localStorage.getItem('ws_url'));

        // Helper to construct API URL
        const getApiUrl = (endpoint) => {
            if (!apiBase) return endpoint;
            // Remove trailing slash from base if present, remove leading slash from endpoint if present
            const cleanBase = apiBase.replace(/\/+$/, '');
            const cleanEndpoint = endpoint.replace(/^\/+/, '');
            return `${cleanBase}/${cleanEndpoint}`;
        };

        let allRooms = [];
        let allQueues = [];
        let deptList = [];
        let queueAudioArmed = false;

        let calledPage = 0;
        const CALLED_PAGE_SIZE = 15;

        // Elements
        const deptNameEl = document.getElementById('dept-name');
        const deptSubEl = document.getElementById('dept-sub');
        const modal = document.getElementById('settings-modal');

        const inputName = document.getElementById('input-dept-name');
        const inputSub = document.getElementById('input-dept-sub');
        const inputFilter = document.getElementById('input-dept-filter');
        const roomFilterMenu = document.getElementById('room-filter-menu');
        const roomFilterBtn = document.getElementById('room-filter-btn');
        const roomFilterLabel = document.getElementById('room-filter-label');
        const inputTtsRepeat = document.getElementById('input-tts-repeat');
        const inputApiBase = document.getElementById('input-api-base');
        const inputWsUrl = document.getElementById('input-ws-url');
        const wsStatusBadge = document.getElementById('ws-status-badge');
        const wsStatusDot = document.getElementById('ws-status-dot');
        const wsStatusText = document.getElementById('ws-status-text');

        const deptOverlay = document.getElementById('dept-select-overlay');
        const deptListEl = document.getElementById('dept-selection-list');

        const container = document.getElementById('room-grid');
        const labListEl = document.getElementById('lab-list');
        const xrayListEl = document.getElementById('xray-list');
        const notfoundListEl = document.getElementById('notfound-list');

        function openSettings() {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            inputName.value = deptNameEl.innerText;
            inputSub.value = deptSubEl.innerText;
            inputFilter.value = currentDeptFilter;
            inputTtsRepeat.value = ttsRepeat;
            inputApiBase.value = apiBase;
            inputWsUrl.value = wsUrl;
            updateRoomFilterOptions(currentDeptFilter, currentRoomFilter);
        }

        function closeSettings() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        async function saveSettings() {
            currentDeptFilter = inputFilter.value;

            // Collect checked rooms
            const checked = [];
            document.querySelectorAll('input[name="room_select"]:checked').forEach(cb => {
                checked.push(cb.value);
            });
            currentRoomFilter = checked;

            ttsRepeat = parseInt(inputTtsRepeat.value) || 1;

            localStorage.setItem('dept_filter', currentDeptFilter);
            localStorage.setItem('room_filter', JSON.stringify(currentRoomFilter));
            localStorage.setItem('tts_repeat', ttsRepeat);

            updateRoomFilterLabel();

            // Save Connection Settings
            const newApiBase = normalizeLocalApiBase(inputApiBase.value);
            const newWsUrl = normalizeLocalWsUrl(inputWsUrl.value);
            const wsChanged = newWsUrl !== wsUrl;

            apiBase = newApiBase;
            wsUrl = newWsUrl;

            localStorage.setItem('api_base', apiBase);
            localStorage.setItem('ws_url', wsUrl);

            closeSettings();
            calledPage = 0;
            loadRoomsAndQueue();

            // Reconnect WS if changed
            if (wsChanged) {
                if (window.wsSocket) {
                    window.wsSocket.close();
                }
                setTimeout(connectWS, 500);
            }
        }

        async function onDeptFilterChange() {
            const dept = inputFilter.value;
            await updateRoomFilterOptions(dept, []);
        }

        async function updateRoomFilterOptions(dept, selectedRooms) {
            try {
                let url = getApiUrl('api/rooms.php');
                if (dept) url += `?department=${encodeURIComponent(dept)}`;
                const r = await fetch(url);
                const d = await r.json();
                if (d.success) {
                    const rooms = d.data;

                    if (rooms.length === 0) {
                        roomFilterMenu.innerHTML = '<div class="text-slate-400 text-sm p-2">No rooms found</div>';
                        updateRoomFilterLabel();
                        return;
                    }

                    roomFilterMenu.innerHTML = rooms.map(r => {
                        const isChecked = selectedRooms.includes(String(r.id)) || selectedRooms.includes(r.id);
                        return `
                            <label class="flex items-center space-x-2 p-2 rounded hover:bg-blue-50 cursor-pointer">
                                <input type="checkbox" name="room_select" value="${escapeHtml(r.id)}" ${isChecked ? 'checked' : ''} class="w-4 h-4 text-hospital-blue rounded focus:ring-blue-500" onchange="updateRoomFilterLabel()">
                                <span class="text-sm font-medium text-slate-700 truncate" title="${escapeHtml(r.room_name)}">${escapeHtml(r.room_name)}</span>
                            </label>
                        `;
                    }).join('');
                    updateRoomFilterLabel();
                }
            } catch (e) {
                console.error(e);
                roomFilterMenu.innerHTML = '<div class="text-red-400 text-sm p-2">Error loading rooms</div>';
            }
        }

        function toggleRoomFilter() {
            roomFilterMenu.classList.toggle('hidden');
        }

        function updateRoomFilterLabel() {
            const checked = document.querySelectorAll('input[name="room_select"]:checked');
            if (checked.length === 0) {
                roomFilterLabel.innerText = "Select Rooms (All)";
                roomFilterLabel.className = "text-slate-400 italic";
            } else {
                roomFilterLabel.innerText = `${checked.length} Room(s) Selected`;
                roomFilterLabel.className = "text-hospital-blue font-bold";
            }
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function (event) {
            const isClickInside = roomFilterBtn.contains(event.target) || roomFilterMenu.contains(event.target);
            if (!isClickInside && !roomFilterMenu.classList.contains('hidden')) {
                roomFilterMenu.classList.add('hidden');
            }
        });

        async function loadInitData() {
            try {
                const res = await fetch(getApiUrl('api/settings.php'));
                const data = await res.json();
                if (data.success && data.data) {
                    if (data.data.dept_name) deptNameEl.innerText = data.data.dept_name;
                    if (data.data.dept_sub) deptSubEl.innerText = data.data.dept_sub;
                }
            } catch (e) { }

            try {
                const res2 = await fetch(getApiUrl('api/departments.php'));
                const data2 = await res2.json();
                if (data2.success) {
                    const depts = data2.data;
                    deptList = depts;
                    inputFilter.innerHTML = '<option value="">Show All</option>' +
                        depts.map(d => `<option value="${d}">${d}</option>`).join('');

                    if (currentDeptFilter) inputFilter.value = currentDeptFilter;

                    deptListEl.innerHTML = depts.map(d => `
                        <button onclick="selectDept('${d}')" class="w-full py-4 px-6 bg-white hover:bg-hospital-blue text-slate-700 hover:text-white rounded-xl text-xl font-bold transition border border-slate-200 hover:border-hospital-accent shadow-md hover:shadow-xl hover:-translate-y-1">
                            ${d}
                        </button>
                    `).join('');

                    if (!currentDeptFilter) {
                        deptOverlay.classList.remove('hidden');
                        deptOverlay.classList.add('flex');
                    } else {
                        loadRoomsAndQueue();
                    }
                }
            } catch (e) { }
        }
        loadInitData();

        function selectDept(dept) {
            currentDeptFilter = dept;
            localStorage.setItem('dept_filter', dept);
            deptOverlay.classList.add('hidden');
            deptOverlay.classList.remove('flex');
            loadRoomsAndQueue();
        }

        function updateTime() {
            const now = new Date();
            document.getElementById('clock').innerText = now.toLocaleTimeString('th-TH', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            document.getElementById('date').innerText = now.toLocaleDateString('th-TH', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
        }
        setInterval(updateTime, 1000);
        updateTime();

        async function loadRoomsAndQueue() {
            try {
                let url = getApiUrl('api/rooms.php');
                if (currentDeptFilter) url += `?department=${encodeURIComponent(currentDeptFilter)}`;
                const r = await fetch(url);
                const d = await r.json();
                if (d.success) {
                    allRooms = d.data;
                    if (currentDeptFilter) {
                        const uniqueDescriptions = [...new Set(
                            allRooms.map(r => r.description).filter(desc => desc && desc.trim().length > 0)
                        )];
                        const descText = uniqueDescriptions.join(' / ');
                        if (descText) {
                            deptSubEl.innerText = `${currentDeptFilter} ${descText}`;
                        } else {
                            deptSubEl.innerText = currentDeptFilter;
                        }
                    }
                }
            } catch (e) { allRooms = []; }

            fetchQueue();
        }

        async function fetchQueue() {
            try {
                let url = getApiUrl('api/queue_data.php?limit=50');
                if (currentDeptFilter) {
                    url += `&department=${encodeURIComponent(currentDeptFilter)}`;
                }

                const res = await fetch(url);
                const data = await res.json();

                if (data.success) {
                    allQueues = data.data;
                    processAndRender();

                    const called = allQueues.filter(q => q.status === 'called');
                    if (called.length > 0) {
                        const latest = called.reduce((prev, current) => {
                            const prevTime = new Date(prev.updated_at || prev.created_at).getTime();
                            const currTime = new Date(current.updated_at || current.created_at).getTime();
                            return (prevTime > currTime) ? prev : current;
                        });

                        const latestTime = new Date(latest.updated_at || latest.created_at).getTime();
                        const uniqueKey = `${latest.id}_${latestTime}`;

                        if (!queueAudioArmed) {
                            window.lastCalledKey = uniqueKey;
                            queueAudioArmed = true;
                            return;
                        }

                        if (window.lastCalledKey !== uniqueKey) {
                            window.lastCalledKey = uniqueKey;
                            speakQueue(latest);
                        }
                    } else if (!queueAudioArmed) {
                        queueAudioArmed = true;
                    }
                }
            } catch (e) {
                console.error("Failed to fetch queue", e);
            }
        }

        function maskName(fullName) {
            if (!fullName) return '';
            const maskText = (text) => {
                if (!text || text.length <= 2) return text;
                return text.substring(0, 2) + 'x'.repeat(text.length - 2);
            };
            const parts = fullName.split(' ');
            let firstName = parts[0];
            let lastName = parts.slice(1).join(' ');
            if (firstName.includes('.')) {
                const dotIndex = firstName.lastIndexOf('.');
                const prefix = firstName.substring(0, dotIndex + 1);
                const realName = firstName.substring(dotIndex + 1);
                firstName = prefix + maskText(realName);
            } else {
                firstName = maskText(firstName);
            }
            if (lastName) {
                lastName = maskText(lastName);
                return `${firstName} ${lastName}`;
            }
            return firstName;
        }

        function escapeHtml(value) {
            return String(value ?? '').replace(/[&<>"']/g, (char) => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[char]));
        }

        function processAndRender() {
            const roomCards = allRooms.map(room => {
                // Filter Logic Update: Check if room.id is in currentRoomFilter list (if list is not empty)
                if (currentRoomFilter && currentRoomFilter.length > 0) {
                    if (!currentRoomFilter.includes(String(room.id))) return '';
                }

                const activeCall = allQueues.find(q => q.status === 'called' && String(q.room_number) === String(room.id));
                const waitingForThisRoom = allQueues.filter(q => q.status === 'waiting' && String(q.room_number) === String(room.id));
                const totalWaiting = waitingForThisRoom.length;
                const next5 = waitingForThisRoom.slice(0, 5);

                const waitingHtml = next5.length > 0 ? `
                    <div class="mt-4 w-full soft-card rounded-[1.5rem] overflow-hidden">
                        <div class="bg-sky-50/80 px-5 py-3 border-b border-sky-100 flex justify-between items-center">
                             <div class="flex items-center gap-2">
                                <span class="text-xl font-extrabold text-sky-800 uppercase tracking-wide">คิวที่รอเรียก</span>
                             </div>
                             <span class="bg-sky-600 text-white text-sm font-extrabold px-3 py-1 rounded-full shadow-sm">${totalWaiting}</span>
                        </div>
                        <div class="grid grid-cols-5 gap-2 p-3">
                            ${next5.map(q => `
                                <div class="flex flex-col items-center justify-center py-3 px-1 rounded-2xl bg-slate-50 border border-slate-100 group hover:bg-sky-50 hover:border-sky-200 transition">
                                    <span class="font-black text-slate-700 text-4xl tracking-tight group-hover:text-sky-700 transition">${escapeHtml(q.oqueue || q.vn)}</span>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                ` : `
                    <div class="mt-4 w-full bg-white/60 border border-dashed border-sky-200 rounded-[1.5rem] p-5 text-center">
                         <span class="text-base font-bold text-slate-400">ไม่มีคิวที่รอเรียก</span>
                    </div>
                `;

                let cardContent = '';
                if (activeCall) {
                    const lastTime = lastCallTimes[room.id] || 0;
                    const isBlinking = (Date.now() - lastTime) < 10000;

                    // Card Container Styles
                    const containerClass = isBlinking
                        ? "called-card-hot ring-4 ring-amber-200 scale-[1.02]"
                        : "called-card hover:-translate-y-1";

                    // Header Styles
                    const headerClass = isBlinking ? "room-header-hot text-slate-950" : "room-header text-white";

                    // Number Styles
                    const numClass = isBlinking ? "text-amber-950 scale-110" : "queue-number-gradient";

                    cardContent = `
                        <div class="relative overflow-hidden rounded-[2rem] ${containerClass} flex flex-col items-center justify-between text-center transition-all duration-300 ease-out min-h-[400px]">
                             <div class="absolute -right-14 -top-14 h-36 w-36 rounded-full bg-sky-200/30 blur-2xl"></div>
                             <!-- Header Room Name -->
                             <div class="w-full ${headerClass} py-4 px-5 transition-colors duration-300">
                                <div class="flex items-center justify-between gap-4">
                                    <span class="text-sm font-bold uppercase tracking-[0.24em] opacity-80">Room</span>
                                    <h2 class="text-5xl font-black tracking-tight mt-1 truncate"> ห้อง ${escapeHtml(room.room_name)}</h2>
                                </div>
                             </div>

                             <!-- Main Calling Number -->
                             <div class="flex-1 flex flex-col justify-center items-center w-full px-5 py-8 relative z-10 bg-white/80">
                                <div class="text-xs font-extrabold uppercase tracking-[0.32em] text-sky-600 mb-2">Now Calling</div>
                                <h3 class="text-[12rem] 2xl:text-[14rem] leading-none font-black tracking-tighter ${numClass} transition-all duration-300 scale-110 origin-center">${escapeHtml(activeCall.oqueue || activeCall.vn)}</h3>
                                
                                <div class="mt-7 bg-white rounded-full px-8 py-3 border border-slate-200 max-w-full shadow-sm">
                                    <p class="text-4xl font-extrabold text-slate-700 truncate">${escapeHtml(maskName(activeCall.patient_name))}</p>
                                </div>
                             </div>
                        </div>
                    `;
                } else {
                    cardContent = `
                        <div class="soft-card p-0 rounded-[2rem] flex flex-col items-center justify-center text-center opacity-90 min-h-[400px] hover:opacity-100 hover:-translate-y-1 transition-all">
                             <div class="w-full bg-white/70 py-4 px-5 border-b border-slate-100 rounded-t-[2rem]">
                                <span class="text-4xl text-slate-600 font-extrabold block truncate">ห้อง ${escapeHtml(room.room_name)}</span>
                             </div>
                             <div class="flex-1 flex flex-col justify-center items-center">
                                <div class="h-24 w-24 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center mb-5">
                                    <span class="text-4xl text-slate-300 font-black">–</span>
                                </div>
                                <h3 class="text-6xl font-black text-slate-300 tracking-tight my-2">ว่าง</h3>
                                <p class="text-xl font-semibold text-slate-400">รอเรียกคิว...</p>
                             </div>
                        </div>
                    `;
                }

                return `
                    <div class="flex flex-col gap-4">
                        ${cardContent}
                        ${waitingHtml}
                    </div>
                `;
            }).filter(Boolean); // Filter out empty strings
            renderPagination(roomCards);
        }

        function renderPagination(roomCards) {
            const totalCalledPages = Math.ceil(roomCards.length / CALLED_PAGE_SIZE) || 1;
            if (calledPage >= totalCalledPages) calledPage = 0;
            const startC = calledPage * CALLED_PAGE_SIZE;
            const currentRooms = roomCards.slice(startC, startC + CALLED_PAGE_SIZE);

            container.innerHTML = currentRooms.join('') || `
                <div class="col-span-full h-40 flex items-center justify-center text-slate-400 text-xl font-bold border-2 border-dashed border-slate-300 rounded-3xl">Loading Rooms...</div>
            `;
            renderLabXray();
        }

        function renderLabXray() {
            // Filter: Only show Lab/Xray/NotFound for rooms in the current department list
            const validRoomIds = allRooms.map(r => String(r.id));

            const notfounds = allQueues.filter(q => q.status === 'not_found' && validRoomIds.includes(String(q.room_number)));
            const labs = allQueues.filter(q => q.status === 'lab' && validRoomIds.includes(String(q.room_number)));
            const xrays = allQueues.filter(q => q.status === 'xray' && validRoomIds.includes(String(q.room_number)));

            const makeItem = (q, bg) => `
                <div class="flex flex-col items-center justify-center bg-white/90 px-6 py-3 rounded-2xl min-w-[140px] border border-sky-100 shadow-sm animate-pulse-slow">
                        <span class="text-2xl font-black text-sky-900">${escapeHtml(q.oqueue || q.vn)}</span>
                        <span class="text-xs font-semibold text-slate-500 truncate max-w-[120px]">${escapeHtml(maskName(q.patient_name))}</span>
                </div>
            `;

            const makeNotFoundItem = (q) => `
                <div class="flex flex-col items-center justify-center bg-red-50/90 px-6 py-3 rounded-2xl min-w-[140px] border border-red-200 shadow-sm animate-pulse-slow">
                        <span class="text-2xl font-black text-red-700">${escapeHtml(q.oqueue || q.vn)}</span>
                        <span class="text-xs font-semibold text-red-500 truncate max-w-[120px]">${escapeHtml(maskName(q.patient_name))}</span>
                </div>
            `;

            notfoundListEl.innerHTML = notfounds.length ? notfounds.map(q => makeNotFoundItem(q)).join('') : '<div class="text-slate-400 italic pl-4">No patients</div>';
            labListEl.innerHTML = labs.length ? labs.map(q => makeItem(q)).join('') : '<div class="text-slate-400 italic pl-4">No patients</div>';
            xrayListEl.innerHTML = xrays.length ? xrays.map(q => makeItem(q)).join('') : '<div class="text-slate-400 italic pl-4">No patients</div>';
        }

        let audioUnlocked = false;
        function unlockAudio() {
            if (audioUnlocked) return;
            // Play a silent buffer or short file to unlock
            // With File Audio, we should try to play an empty buffer or a silent file if we had one
            // Or just a tiny part of Prompt4_Number
            const a = new Audio("Prompt4/Prompt4_Number.mp3");
            a.volume = 0;
            a.play().then(() => {
                a.pause();
                audioUnlocked = true;
                document.getElementById('sound-overlay').classList.add('hidden');
                console.log("Audio Context Unlocked");
            }).catch(e => console.error("Unlock failed", e));
        }

        // --- NEW TTS LOGIC (File Based) ---
        const ttsQueue = [];
        let isSpeaking = false;
        let lastCallTimes = {};

        function speakQueue(item) {
            console.log("Queueing File Audio:", item);

            // Blink Effect
            lastCallTimes[item.room_number] = Date.now();
            processAndRender();
            setTimeout(() => { processAndRender(); }, 11000);

            // Construct File List
            // 1. Prompt4_Number (prefix)
            // 2. Prompt4_{int}.mp3 (number)
            // 3. Prompt4_Sir (suffix)

            // Extract Number
            const numStr = item.oqueue || item.vn;
            const num = parseInt(numStr);

            if (isNaN(num)) {
                console.warn("Invalid Queue Number for File Audio:", numStr);
                return;
            }

            // Get Sequence
            const numberFiles = getThaiNumberFiles(num);

            // Get Sequence for Room
            // LOOKUP FROM ALLROOMS using ID to get custom room_number
            const roomObj = allRooms.find(r => String(r.id) === String(item.room_number));
            let actualRoomNum = parseInt(item.room_number); // Default to ID

            if (roomObj && roomObj.room_number) {
                const parsed = parseInt(roomObj.room_number);
                if (!isNaN(parsed)) actualRoomNum = parsed;
            }

            const roomFiles = (!isNaN(actualRoomNum)) ? getThaiNumberFiles(actualRoomNum) : [];

            const files = [
                'Prompt4/Prompt4_Number.mp3',
                ...numberFiles,
                'Prompt4/station_old.mp3',
                ...roomFiles,
                'Prompt4/Prompt4_Sir.mp3'
            ];

            // Repeat N times
            for (let i = 0; i < ttsRepeat; i++) {
                ttsQueue.push(files);
            }

            processTTSQueue();
        }

        async function processTTSQueue() {
            if (isSpeaking || ttsQueue.length === 0) return;
            const fileSet = ttsQueue.shift();
            isSpeaking = true;
            try {
                await playAudioSequence(fileSet);
            } catch (e) {
                console.error("Audio Sequence Failed", e);
            }
            isSpeaking = false;
            setTimeout(processTTSQueue, 500);
        }

        function playAudioSequence(files) {
            return new Promise(async (resolve, reject) => {
                for (const file of files) {
                    try {
                        await playSingleFile(file);
                    } catch (e) {
                        console.warn(`Failed to play ${file}`, e);
                        // Convert missing file error into a "continue" so we play the rest? 
                        // Or break? Let's continue to attempt suffix.
                    }
                }
                resolve();
            });
        }

        function playSingleFile(url) {
            return new Promise((resolve, reject) => {
                const audio = new Audio(url);
                audio.volume = 1.0;        // เสียงดังสุด
                audio.playbackRate = 1.25;  // เร็วขึ้น 0.5 เท่า
                audio.onended = resolve;
                audio.onerror = () => {
                    // reject(`Error loading ${url}`);
                    // Resolve anyway to prevent hanging
                    console.error(`Error loading ${url}`);
                    resolve();
                };
                audio.play().catch(e => {
                    console.error("Play error", e);
                    resolve();
                });
            });
        }

        function getThaiNumberFiles(num) {
            const files = [];

            // Thousands
            if (num >= 1000) {
                const thousands = Math.floor(num / 1000);
                num %= 1000;

                files.push(`Prompt4/Prompt4_${thousands}.mp3`);
                files.push('Prompt4/backupold/Prompt4_1000.wav');
            }

            // Hundreds
            if (num >= 100) {
                const hundreds = Math.floor(num / 100);
                num %= 100;

                files.push(`Prompt4/Prompt4_${hundreds}.mp3`);
                files.push('Prompt4/Prompt4_100.mp3');
            }

            // Tens & Ones
            if (num >= 10) {
                const tens = Math.floor(num / 10);
                const ones = num % 10;

                if (tens === 1) {
                    // 10–19 (Sip ...)
                    files.push('Prompt4/Prompt4_10.mp3');

                    if (ones === 1) {
                        files.push('Prompt4/Prompt4_11-1.mp3'); // สิบเอ็ด (Sip Et)
                    } else if (ones > 1) {
                        files.push(`Prompt4/Prompt4_${ones}.mp3`);
                    }
                    return files;
                }

                if (tens === 2) {
                    // 20–29 (Yi Sip ...)
                    files.push('Prompt4/Prompt4_20_[cut_0sec].mp3'); // Yi Sip

                    if (ones === 1) {
                        files.push('Prompt4/Prompt4_11-1.mp3'); // Yi Sip Et
                    } else if (ones > 1) {
                        files.push(`Prompt4/Prompt4_${ones}.mp3`);
                    }
                    return files;
                }

                // 30-90 (Sam Sip, Si Sip, ...)
                files.push(`Prompt4/Prompt4_${tens}.mp3`); // digit (3, 4, 5...)
                files.push('Prompt4/Prompt4_10.mp3'); // Sip

                if (ones === 1) {
                    files.push('Prompt4/Prompt4_11-1.mp3'); // Et
                } else if (ones > 1) {
                    files.push(`Prompt4/Prompt4_${ones}.mp3`);
                }
                return files;
            }

            // Ones only (1-9)
            if (num > 0) {
                // ถ้าเลขหลักหน่วยเป็น 1 และมีหลักพัน/ร้อยนำหน้า ต้องใช้ "เอ็ด" (11-1.mp3)
                // เช่น 1001 = หนึ่งพันเอ็ด, 201 = สองร้อยเอ็ด
                if (num === 1 && files.length > 0) {
                    files.push('Prompt4/Prompt4_11-1.mp3');
                } else {
                    files.push(`Prompt4/Prompt4_${num}.mp3`);
                }
            }

            return files;
        }

        setInterval(() => {
            const totalCalledPages = Math.ceil(allRooms.length / CALLED_PAGE_SIZE) || 1;
            if (totalCalledPages > 1) {
                calledPage++;
                if (calledPage >= totalCalledPages) calledPage = 0;
                processAndRender();
            }
        }, 10000);

        // WS Init
        let wsReconnectTimer = null;
        let wsReconnectAttempts = 0;
        let wsDisconnectedSince = null;
        const WS_RELOAD_AFTER_MS = 3 * 60 * 1000;

        function updateWSStatus(status) {
            if (!wsStatusDot || !wsStatusText || !wsStatusBadge) return;

            const statusConfig = {
                connected: {
                    dotClassName: 'inline-flex h-4 w-4 rounded-full bg-emerald-500 ring-4 ring-emerald-200 transition shadow-sm animate-pulse',
                    badgeClassName: 'inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1.5 text-sm font-extrabold text-emerald-700 ring-1 ring-emerald-200 shadow-sm',
                    text: 'ws:connected',
                    title: 'WebSocket: connected'
                },
                connecting: {
                    dotClassName: 'inline-flex h-4 w-4 rounded-full bg-yellow-400 ring-4 ring-yellow-100 transition shadow-sm animate-pulse',
                    badgeClassName: 'inline-flex items-center gap-2 rounded-full bg-yellow-50 px-3 py-1.5 text-sm font-extrabold text-yellow-700 ring-1 ring-yellow-200 shadow-sm',
                    text: 'ws:connecting',
                    title: 'WebSocket: connecting'
                },
                disconnected: {
                    dotClassName: 'inline-flex h-4 w-4 rounded-full bg-red-500 ring-4 ring-red-200 transition shadow-sm',
                    badgeClassName: 'inline-flex items-center gap-2 rounded-full bg-red-50 px-3 py-1.5 text-sm font-extrabold text-red-700 ring-1 ring-red-200 shadow-sm',
                    text: 'ws:disconnected',
                    title: 'WebSocket: disconnected'
                }
            };

            const config = statusConfig[status] || statusConfig.disconnected;
            wsStatusDot.className = config.dotClassName;
            wsStatusBadge.className = config.badgeClassName;
            wsStatusText.innerText = config.text;
            wsStatusBadge.title = `${config.title} (${wsUrl})`;
        }

        function scheduleWSReconnect() {
            if (wsReconnectTimer) return;

            if (!wsDisconnectedSince) {
                wsDisconnectedSince = Date.now();
            }

            wsReconnectAttempts++;
            const delay = Math.min(3000 + (wsReconnectAttempts - 1) * 1000, 15000);

            wsReconnectTimer = setTimeout(() => {
                wsReconnectTimer = null;
                connectWS();
            }, delay);
        }

        function connectWS() {
            if (window.wsSocket) {
                if (window.wsSocket.readyState === WebSocket.OPEN || window.wsSocket.readyState === WebSocket.CONNECTING) return;
            }
            console.log("Connecting to WS:", wsUrl);
            updateWSStatus('connecting');
            const socket = new WebSocket(wsUrl);
            window.wsSocket = socket;

            socket.onopen = function () {
                console.log('Connected');
                if (wsReconnectTimer) {
                    clearTimeout(wsReconnectTimer);
                    wsReconnectTimer = null;
                }
                wsReconnectAttempts = 0;
                wsDisconnectedSince = null;
                document.body.style.borderTop = "4px solid #10b981";
                updateWSStatus('connected');
                fetchQueue();
            };
            socket.onmessage = function (event) {
                try {
                    const payload = JSON.parse(event.data);
                    if (payload.event === 'recall' && payload.data) {
                        speakQueue(payload.data);
                    }
                    fetchQueue();
                } catch (e) { fetchQueue(); }
            };
            socket.onclose = function () {
                document.body.style.borderTop = "4px solid #ef4444";
                updateWSStatus('disconnected');
                scheduleWSReconnect();
            };
            socket.onerror = function () {
                document.body.style.borderTop = "4px solid #ef4444";
                updateWSStatus('disconnected');
                try {
                    socket.close();
                } catch (e) { }
                scheduleWSReconnect();
            };
        }
        updateWSStatus('connecting');
        connectWS();
        setInterval(() => {
            const socket = window.wsSocket;
            const isDisconnected = !socket || socket.readyState === WebSocket.CLOSED || socket.readyState === WebSocket.CLOSING;

            if (isDisconnected) {
                if (!wsDisconnectedSince) {
                    wsDisconnectedSince = Date.now();
                }

                scheduleWSReconnect();

                if (Date.now() - wsDisconnectedSince >= WS_RELOAD_AFTER_MS) {
                    window.location.reload();
                }
            }
        }, 10000);
        setInterval(fetchQueue, 30000);
    </script>
</body>

</html>
