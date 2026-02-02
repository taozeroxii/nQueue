<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Main Queue Board (Prompt4)</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Prompt', sans-serif;
        }

        .glass-panel {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
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

<body class="bg-blue-50/50 min-h-screen text-slate-800 overflow-hidden">

    <!-- Top Header -->
    <!-- Top Header -->
    <header
        class="p-4 px-8 flex justify-between items-center bg-white shadow-md border-b-4 border-hospital-blue relative z-50">
        <div class="flex items-center gap-6">
            <div
                class="w-16 h-16 bg-hospital-blue/10 rounded-full flex items-center justify-center p-3 shadow-sm border border-hospital-blue/20">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-full w-full text-hospital-blue" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
            </div>
            <div class="cursor-pointer group" onclick="openSettings()">
                <h1 id="dept-name"
                    class="text-4xl font-black text-hospital-blue group-hover:text-hospital-accent transition tracking-tight">
                    โรงพยาบาลบุรีรัมย์</h1>
                <p id="dept-sub"
                    class="text-slate-500 font-semibold text-xl group-hover:text-hospital-blue transition mt-1">
                    คิวตรวจโรคทั่วไป</p>
            </div>
        </div>
        <div class="text-right">
            <div id="clock" class="text-5xl font-black tracking-widest text-hospital-text font-mono">00:00</div>
            <div id="date" class="text-slate-500 font-medium text-lg mt-1">...</div>
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
                        <label class="block text-slate-500 mb-2 text-sm font-semibold">Filter by Room</label>
                        <select id="input-room-filter"
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-slate-800 focus:border-hospital-blue focus:ring-2 focus:ring-blue-100 focus:outline-none">
                            <option value="">Show All Rooms</option>
                        </select>
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
            const a = new Audio("Prompt4/Prompt4_Number.wav");
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
    <main class="p-4 lg:p-8 h-[calc(100vh-140px)] grid grid-cols-1 lg:grid-cols-12 gap-8 overflow-hidden">
        <!-- Main Content Grid -->
        <div id="current-called-container" class="lg:col-span-12 h-full overflow-hidden pb-40">
            <!-- Combined Room Card + Waiting List Grid -->
            <div id="room-grid" class="grid grid-cols-[repeat(auto-fit,minmax(300px,1fr))] gap-6 p-4">
                <!-- Dynamic Content -->
            </div>
        </div>

        <!-- Bottom: Lab & X-Ray Status -->
        <div
            class="fixed bottom-0 left-0 right-0 h-32 bg-white border-t border-slate-200 shadow-[0_-5px_20px_-5px_rgba(0,0,0,0.1)] z-40 grid grid-cols-2 gap-px">
            <!-- Lab Section -->
            <div class="relative overflow-hidden group border-r border-slate-200">
                <div class="absolute inset-0 bg-white group-hover:bg-blue-50 transition"></div>
                <div class="h-full flex items-center px-8 gap-6 relative z-10">
                    <div class="flex flex-col justify-center shrink-0 border-r-2 border-hospital-blue/10 pr-6">
                        <span class="text-hospital-blue font-bold text-sm tracking-widest uppercase">Laboratory</span>
                        <h3 class="text-3xl font-black text-hospital-text">รอ Lab</h3>
                    </div>
                    <div id="lab-list"
                        class="flex items-center gap-4 overflow-x-auto p-4 scrollbar-hide w-full mask-linear-fade">
                        <!-- Dynamic Items -->
                        <div class="text-slate-400 italic">No patients</div>
                    </div>
                </div>
            </div>
            <!-- X-Ray Section -->
            <div class="relative overflow-hidden group">
                <div class="absolute inset-0 bg-white group-hover:bg-blue-50 transition"></div>
                <div class="h-full flex items-center px-8 gap-6 relative z-10">
                    <div class="flex flex-col justify-center shrink-0 border-r-2 border-hospital-blue/10 pr-6">
                        <span class="text-hospital-blue font-bold text-sm tracking-widest uppercase">Radiology</span>
                        <h3 class="text-3xl font-black text-hospital-text">รอ X-Ray</h3>
                    </div>
                    <div id="xray-list"
                        class="flex items-center gap-4 overflow-x-auto p-4 scrollbar-hide w-full mask-linear-fade">
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
        let currentRoomFilter = localStorage.getItem('room_filter') || '';
        let ttsRepeat = parseInt(localStorage.getItem('tts_repeat')) || 1;

        // Connection Settings
        let apiBase = localStorage.getItem('api_base') || ''; // Default empty = relative
        let wsUrl = localStorage.getItem('ws_url') || 'ws://localhost:8765';

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

        let calledPage = 0;
        const CALLED_PAGE_SIZE = 15;

        // Elements
        const deptNameEl = document.getElementById('dept-name');
        const deptSubEl = document.getElementById('dept-sub');
        const modal = document.getElementById('settings-modal');

        const inputName = document.getElementById('input-dept-name');
        const inputSub = document.getElementById('input-dept-sub');
        const inputFilter = document.getElementById('input-dept-filter');
        const inputRoomFilter = document.getElementById('input-room-filter');
        const inputTtsRepeat = document.getElementById('input-tts-repeat');
        const inputApiBase = document.getElementById('input-api-base');
        const inputWsUrl = document.getElementById('input-ws-url');

        const deptOverlay = document.getElementById('dept-select-overlay');
        const deptListEl = document.getElementById('dept-selection-list');

        const container = document.getElementById('room-grid');
        const labListEl = document.getElementById('lab-list');
        const xrayListEl = document.getElementById('xray-list');

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
            currentRoomFilter = inputRoomFilter.value;
            ttsRepeat = parseInt(inputTtsRepeat.value) || 1;

            localStorage.setItem('dept_filter', currentDeptFilter);
            localStorage.setItem('room_filter', currentRoomFilter);
            localStorage.setItem('tts_repeat', ttsRepeat);

            // Save Connection Settings
            const newApiBase = inputApiBase.value.trim();
            const newWsUrl = inputWsUrl.value.trim();
            const wsChanged = newWsUrl !== wsUrl;

            apiBase = newApiBase;
            wsUrl = newWsUrl || 'ws://localhost:8765'; // Default if empty

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
            await updateRoomFilterOptions(dept, '');
        }

        async function updateRoomFilterOptions(dept, selectedRoom) {
            try {
                let url = getApiUrl('api/rooms.php');
                if (dept) url += `?department=${encodeURIComponent(dept)}`;
                const r = await fetch(url);
                const d = await r.json();
                if (d.success) {
                    const rooms = d.data;
                    inputRoomFilter.innerHTML = '<option value="">Show All Rooms</option>' +
                        rooms.map(r => `<option value="${r.id}">${r.room_name}</option>`).join('');
                    if (selectedRoom) inputRoomFilter.value = selectedRoom;
                }
            } catch (e) { console.error(e); }
        }

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

                        if (window.lastCalledKey !== uniqueKey) {
                            window.lastCalledKey = uniqueKey;
                            speakQueue(latest);
                        }
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

        function processAndRender() {
            const roomCards = allRooms.map(room => {
                const activeCall = allQueues.find(q => q.status === 'called' && String(q.room_number) === String(room.id));
                const waitingForThisRoom = allQueues.filter(q => q.status === 'waiting' && String(q.room_number) === String(room.id));
                const totalWaiting = waitingForThisRoom.length;
                const next5 = waitingForThisRoom.slice(0, 5);

                const waitingHtml = next5.length > 0 ? `
                    <div class="mt-4 w-full bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                        <div class="bg-hospital-light px-4 py-2 border-b border-blue-100 flex justify-between items-center">
                             <div class="flex items-center gap-2">
                                <span class="text-sm font-bold text-hospital-blue uppercase tracking-wider">คิวที่รอเรียก</span>
                             </div>
                             <span class="bg-blue-500 text-white text-xs font-bold px-2 py-0.5 rounded-full">${totalWaiting}</span>
                        </div>
                        <div class="grid grid-cols-5 divide-x divide-slate-100">
                            ${next5.map(q => `
                                <div class="flex flex-col items-center justify-center py-4 px-1 group hover:bg-blue-50 transition">
                                    <span class="font-black text-slate-700 text-4xl tracking-tighter group-hover:text-hospital-blue transition">${q.oqueue || q.vn}</span>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                ` : `
                    <div class="mt-4 w-full bg-slate-50 border-2 border-dashed border-slate-200 rounded-xl p-4 text-center">
                         <span class="text-xs font-semibold text-slate-400">ไม่มีคิวรอ</span>
                    </div>
                `;

                let cardContent = '';
                if (activeCall) {
                    const lastTime = lastCallTimes[room.id] || 0;
                    const isBlinking = (Date.now() - lastTime) < 10000;

                    // Card Container Styles
                    const containerClass = isBlinking
                        ? "bg-yellow-50 border-yellow-400 ring-4 ring-yellow-200 shadow-xl scale-[1.02]"
                        : "bg-white border-slate-200 shadow-lg hover:shadow-xl hover:border-blue-300";

                    // Header Styles
                    const headerClass = isBlinking ? "bg-yellow-400 text-slate-900" : "bg-hospital-blue text-white";

                    // Number Styles
                    const numClass = isBlinking ? "text-slate-900 scale-110" : "text-hospital-text";

                    cardContent = `
                        <div class="relative overflow-hidden rounded-2xl ${containerClass} flex flex-col items-center justify-between text-center transition-all duration-300 ease-out min-h-[400px] border">
                             <!-- Header Room Name -->
                             <div class="w-full ${headerClass} py-4 px-2 transition-colors duration-300">
                                <div class="flex flex-col items-center">
                                    <h2 class="text-4xl font-black tracking-tight mt-1">ห้องตรวจ ${room.room_name}</h2>
                                </div>
                             </div>

                             <!-- Main Calling Number -->
                             <div class="flex-1 flex flex-col justify-center items-center w-full px-4 py-8 relative z-10 bg-white">
                                <h3 class="text-[10rem] leading-none font-black tracking-tighter ${numClass} transition-all duration-300 font-mono">${activeCall.oqueue || activeCall.vn}</h3>
                                
                                <div class="mt-8 bg-slate-100 rounded-full px-8 py-3 border border-slate-200 max-w-full">
                                    <p class="text-4xl font-bold text-slate-700 truncate">${maskName(activeCall.patient_name)}</p>
                                </div>
                             </div>
                        </div>
                    `;
                } else {
                    if (currentRoomFilter && String(room.id) !== String(currentRoomFilter)) return '';
                    cardContent = `
                        <div class="bg-white p-0 rounded-2xl border border-slate-200 shadow-sm flex flex-col items-center justify-center text-center opacity-80 min-h-[400px] hover:opacity-100 transition-opacity">
                             <div class="w-full bg-slate-100 py-4 border-b border-slate-200">
                                <span class="text-2xl text-slate-500 font-bold block truncate">ห้อง ${room.room_name}</span>
                             </div>
                             <div class="flex-1 flex flex-col justify-center items-center">
                                <h3 class="text-6xl font-black text-slate-300 tracking-tight my-4">ว่าง</h3>
                                <p class="text-lg text-slate-400">รอเรียกคิว...</p>
                             </div>
                        </div>
                    `;
                }

                if (currentRoomFilter && String(room.id) !== String(currentRoomFilter)) return '';
                return `
                    <div class="flex flex-col gap-4">
                        ${cardContent}
                        ${waitingHtml}
                    </div>
                `;
            });
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
            const labs = allQueues.filter(q => q.status === 'lab');
            const xrays = allQueues.filter(q => q.status === 'xray');

            const makeItem = (q, bg) => `
                <div class="flex flex-col items-center justify-center bg-white px-6 py-3 rounded-2xl min-w-[140px] border border-slate-200 shadow-lg animate-pulse-slow">
                     <span class="text-2xl font-black text-slate-800">${q.oqueue || q.vn}</span>
                     <span class="text-xs text-slate-500 truncate max-w-[120px]">${maskName(q.patient_name)}</span>
                </div>
            `;
            labListEl.innerHTML = labs.length ? labs.map(q => makeItem(q)).join('') : '<div class="text-slate-400 italic pl-4">No patients</div>';
            xrayListEl.innerHTML = xrays.length ? xrays.map(q => makeItem(q)).join('') : '<div class="text-slate-400 italic pl-4">No patients</div>';
        }

        let audioUnlocked = false;
        function unlockAudio() {
            if (audioUnlocked) return;
            // Play a silent buffer or short file to unlock
            // With File Audio, we should try to play an empty buffer or a silent file if we had one
            // Or just a tiny part of Prompt4_Number
            const a = new Audio("Prompt4/Prompt4_Number.wav");
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
            // 2. Prompt4_{int}.wav (number)
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
            const roomNum = parseInt(item.room_number);
            const roomFiles = (!isNaN(roomNum)) ? getThaiNumberFiles(roomNum) : [];

            const files = [
                'Prompt4/Prompt4_Number.wav',
                ...numberFiles,
                'Prompt4/station_old.wav',
                ...roomFiles,
                'Prompt4/Prompt4_Sir.wav'
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

                files.push(`Prompt4/Prompt4_${thousands}.wav`);
                files.push('Prompt4/Prompt4_1000.wav');
            }

            // Hundreds
            if (num >= 100) {
                const hundreds = Math.floor(num / 100);
                num %= 100;

                files.push(`Prompt4/Prompt4_${hundreds}.wav`);
                files.push('Prompt4/Prompt4_100.wav');
            }

            // Tens & Ones
            if (num >= 10) {
                const tens = Math.floor(num / 10);
                const ones = num % 10;

                if (tens === 1) {
                    // 10–19 (Sip ...)
                    files.push('Prompt4/Prompt4_10.wav');

                    if (ones === 1) {
                        files.push('Prompt4/Prompt4_11-1.wav'); // สิบเอ็ด (Sip Et)
                    } else if (ones > 1) {
                        files.push(`Prompt4/Prompt4_${ones}.wav`);
                    }
                    return files;
                }

                if (tens === 2) {
                    // 20–29 (Yi Sip ...)
                    files.push('Prompt4/Prompt4_20.wav'); // Yi Sip

                    if (ones === 1) {
                        files.push('Prompt4/Prompt4_11-1.wav'); // Yi Sip Et
                    } else if (ones > 1) {
                        files.push(`Prompt4/Prompt4_${ones}.wav`);
                    }
                    return files;
                }

                // 30-90 (Sam Sip, Si Sip, ...)
                files.push(`Prompt4/Prompt4_${tens}.wav`); // digit (3, 4, 5...)
                files.push('Prompt4/Prompt4_10.wav'); // Sip

                if (ones === 1) {
                    files.push('Prompt4/Prompt4_11-1.wav'); // Et
                } else if (ones > 1) {
                    files.push(`Prompt4/Prompt4_${ones}.wav`);
                }
                return files;
            }

            // Ones only (1-9)
            if (num > 0) {
                files.push(`Prompt4/Prompt4_${num}.wav`);
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
        function connectWS() {
            if (window.wsSocket) {
                if (window.wsSocket.readyState === WebSocket.OPEN || window.wsSocket.readyState === WebSocket.CONNECTING) return;
            }
            console.log("Connecting to WS:", wsUrl);
            const socket = new WebSocket(wsUrl);
            window.wsSocket = socket;

            socket.onopen = function () {
                console.log('Connected');
                document.body.style.borderTop = "4px solid #10b981";
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
                setTimeout(connectWS, 3000);
            };
        }
        connectWS();
        setInterval(fetchQueue, 30000);
    </script>
</body>

</html>