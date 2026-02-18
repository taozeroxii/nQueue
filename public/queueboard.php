<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>ดูคิวตรวจ</title>
    <script src="assets/vendor/tailwind/tailwind.js"></script>
    <link href="assets/vendor/css/prompt.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Prompt', sans-serif;
        }

        .fade-in {
            animation: fadeIn 0.4s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes pulse-ring {
            0% { box-shadow: 0 0 0 0 rgba(59,130,246,0.5); }
            70% { box-shadow: 0 0 0 10px rgba(59,130,246,0); }
            100% { box-shadow: 0 0 0 0 rgba(59,130,246,0); }
        }

        .pulse-ring {
            animation: pulse-ring 2s cubic-bezier(0.4,0,0.6,1) infinite;
        }

        .refresh-btn {
            transition: all 0.3s ease;
        }

        .refresh-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .refresh-btn:not(:disabled):active {
            transform: scale(0.92);
        }

        .spin {
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* Scrollbar hide */
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }

        /* Called card blink */
        @keyframes calledPulse {
            0%, 100% { border-color: #2563eb; background-color: rgba(37,99,235,0.04); }
            50% { border-color: #f59e0b; background-color: rgba(245,158,11,0.06); }
        }

        .called-active {
            animation: calledPulse 2s ease-in-out infinite;
        }

        /* Bottom status sections */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 700;
        }

        /* Safe area for notch phones */
        .safe-bottom {
            padding-bottom: max(16px, env(safe-area-inset-bottom));
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
                            200: '#bae6fd',
                            300: '#7dd3fc',
                            400: '#38bdf8',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                        },
                        hospital: {
                            blue: '#0050a0',
                            light: '#e6f0ff',
                            text: '#002e5d',
                            accent: '#007bff'
                        }
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-gradient-to-br from-blue-50 via-white to-blue-50 min-h-screen text-slate-800">

    <!-- Header -->
    <header class="bg-white/90 backdrop-blur-lg border-b border-blue-100 sticky top-0 z-50 px-4 py-3 safe-top">
        <div class="flex items-center justify-between max-w-lg mx-auto">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-hospital-blue to-blue-600 rounded-xl flex items-center justify-center shadow-lg shadow-blue-200">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <div>
                    <h1 id="header-title" class="text-base font-black text-hospital-text leading-tight">บอร์ดคิวตรวจ</h1>
                    <p id="header-dept" class="text-xs text-slate-400 font-semibold">กำลังโหลด...</p>
                </div>
            </div>
            <div class="text-right">
                <div id="clock" class="text-lg font-black text-hospital-text font-mono tracking-wide">--:--</div>
                <div id="date-display" class="text-[10px] text-slate-400 font-medium">...</div>
            </div>
        </div>
    </header>

    <!-- Department Selection Overlay -->
    <div id="dept-overlay" class="fixed inset-0 bg-white z-[60] hidden flex-col items-center justify-center px-6">
        <div class="w-full max-w-sm text-center">
            <div class="w-16 h-16 bg-gradient-to-br from-hospital-blue to-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-xl shadow-blue-200">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
            </div>
            <h1 class="text-2xl font-black text-hospital-text mb-1">เลือกแผนก</h1>
            <p class="text-sm text-slate-400 mb-8">กรุณาเลือกแผนกที่ต้องการดูคิว</p>
            <div id="dept-list" class="flex flex-col gap-3">
                <div class="text-slate-400 text-sm">กำลังโหลด...</div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main id="main-content" class="max-w-lg mx-auto px-4 py-4 pb-24 hidden">
        <!-- Status Summary Bar -->
        <div id="summary-bar" class="flex items-center gap-2 mb-4 overflow-x-auto scrollbar-hide">
            <!-- Dynamically filled -->
        </div>

        <!-- Room Cards -->
        <div id="room-list" class="flex flex-col gap-4">
            <div class="flex items-center justify-center h-40">
                <div class="text-slate-300 text-sm font-semibold">กำลังโหลดข้อมูลคิว...</div>
            </div>
        </div>

        <!-- Lab / X-Ray / Not Found Sections -->
        <div id="status-sections" class="mt-6 flex flex-col gap-3 hidden">
            <!-- Not Found -->
            <div id="notfound-section" class="bg-red-50 border border-red-200 rounded-2xl p-4 hidden">
                <div class="flex items-center gap-2 mb-3">
                    <span class="status-badge bg-red-100 text-red-700">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                        เรียกไม่พบ
                    </span>
                </div>
                <div id="notfound-items" class="flex flex-wrap gap-2">
                </div>
            </div>

            <!-- Lab -->
            <div id="lab-section" class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4 hidden">
                <div class="flex items-center gap-2 mb-3">
                    <span class="status-badge bg-emerald-100 text-emerald-700">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                        รอ Lab
                    </span>
                </div>
                <div id="lab-items" class="flex flex-wrap gap-2">
                </div>
            </div>

            <!-- X-Ray -->
            <div id="xray-section" class="bg-violet-50 border border-violet-200 rounded-2xl p-4 hidden">
                <div class="flex items-center gap-2 mb-3">
                    <span class="status-badge bg-violet-100 text-violet-700">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        รอ X-Ray
                    </span>
                </div>
                <div id="xray-items" class="flex flex-wrap gap-2">
                </div>
            </div>
        </div>
    </main>

    <!-- Floating Refresh Button -->
    <div class="fixed bottom-6 right-5 z-50 safe-bottom">
        <button id="refresh-btn" onclick="handleRefresh()" class="refresh-btn w-14 h-14 bg-gradient-to-br from-hospital-blue to-blue-600 text-white rounded-full shadow-xl shadow-blue-300/50 flex items-center justify-center active:shadow-lg relative overflow-hidden">
            <svg id="refresh-icon" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            <span id="refresh-countdown" class="absolute inset-0 flex items-center justify-center text-sm font-black hidden"></span>
        </button>
        <!-- Last updated label -->
        <div id="last-updated" class="text-center text-[10px] text-slate-400 font-semibold mt-1 opacity-0 transition-opacity duration-300">
            อัปเดตเมื่อ --:--
        </div>
    </div>

    <script>
        // === State ===
        const urlParams = new URLSearchParams(window.location.search);
        let currentDept = urlParams.get('dept') || '';
        let allRooms = [];
        let allQueues = [];
        let isLoading = false;

        // === Rate Limiting ===
        const COOLDOWN_SEC = 10;
        let cooldownTimer = null;
        let cooldownRemaining = 0;

        // === DOM Elements ===
        const headerTitle = document.getElementById('header-title');
        const headerDeptEl = document.getElementById('header-dept');
        const clockEl = document.getElementById('clock');
        const dateEl = document.getElementById('date-display');
        const deptOverlay = document.getElementById('dept-overlay');
        const deptListEl = document.getElementById('dept-list');
        const mainContent = document.getElementById('main-content');
        const roomListEl = document.getElementById('room-list');
        const summaryBar = document.getElementById('summary-bar');
        const statusSections = document.getElementById('status-sections');
        const refreshBtn = document.getElementById('refresh-btn');
        const refreshIcon = document.getElementById('refresh-icon');
        const refreshCountdown = document.getElementById('refresh-countdown');
        const lastUpdatedEl = document.getElementById('last-updated');

        // Status sections
        const notfoundSection = document.getElementById('notfound-section');
        const labSection = document.getElementById('lab-section');
        const xraySection = document.getElementById('xray-section');
        const notfoundItems = document.getElementById('notfound-items');
        const labItems = document.getElementById('lab-items');
        const xrayItems = document.getElementById('xray-items');

        // === Clock ===
        function updateTime() {
            const now = new Date();
            clockEl.innerText = now.toLocaleTimeString('th-TH', { hour: '2-digit', minute: '2-digit' });
            dateEl.innerText = now.toLocaleDateString('th-TH', { day: 'numeric', month: 'short', year: '2-digit' });
        }
        setInterval(updateTime, 1000);
        updateTime();

        // === Name Masking ===
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

        // === Init ===
        async function init() {
            if (currentDept) {
                showMainView();
                headerDeptEl.innerText = currentDept;
                await loadData();
            } else {
                showDeptSelection();
            }
        }

        async function showDeptSelection() {
            deptOverlay.classList.remove('hidden');
            deptOverlay.classList.add('flex');
            mainContent.classList.add('hidden');

            try {
                const res = await fetch('api/departments.php');
                const data = await res.json();
                if (data.success && data.data.length > 0) {
                    deptListEl.innerHTML = data.data.map(d => `
                        <button onclick="selectDept('${d}')"
                            class="w-full py-4 px-5 bg-white hover:bg-hospital-blue text-slate-700 hover:text-white rounded-2xl text-base font-bold transition-all duration-200 border border-slate-200 hover:border-hospital-blue shadow-sm hover:shadow-lg active:scale-[0.97]">
                            ${d}
                        </button>
                    `).join('');
                } else {
                    deptListEl.innerHTML = '<div class="text-slate-400 text-sm">ไม่พบข้อมูลแผนก</div>';
                }
            } catch (e) {
                deptListEl.innerHTML = '<div class="text-red-400 text-sm">โหลดข้อมูลไม่สำเร็จ</div>';
            }
        }

        function selectDept(dept) {
            currentDept = dept;
            // Update URL without reload
            const url = new URL(window.location);
            url.searchParams.set('dept', dept);
            window.history.replaceState({}, '', url);

            showMainView();
            headerDeptEl.innerText = dept;
            loadData();
        }

        function showMainView() {
            deptOverlay.classList.add('hidden');
            deptOverlay.classList.remove('flex');
            mainContent.classList.remove('hidden');
        }

        // === Data Loading ===
        async function loadData() {
            if (isLoading) return;
            isLoading = true;
            refreshIcon.classList.add('spin');

            try {
                // Load rooms for department
                let roomUrl = 'api/rooms.php';
                if (currentDept) roomUrl += `?department=${encodeURIComponent(currentDept)}`;
                const roomRes = await fetch(roomUrl);
                const roomData = await roomRes.json();
                if (roomData.success) allRooms = roomData.data;

                // Load queues
                let queueUrl = 'api/queue_data.php?limit=200';
                const queueRes = await fetch(queueUrl);
                const queueData = await queueRes.json();
                if (queueData.success) allQueues = queueData.data;

                render();
                updateLastUpdated();
            } catch (e) {
                console.error('Failed to load data', e);
                roomListEl.innerHTML = `
                    <div class="flex flex-col items-center justify-center h-40 text-center">
                        <svg class="w-10 h-10 text-red-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                        <span class="text-red-400 text-sm font-semibold">โหลดข้อมูลไม่สำเร็จ</span>
                        <span class="text-slate-400 text-xs mt-1">กรุณาลองกดรีเฟรชอีกครั้ง</span>
                    </div>
                `;
            } finally {
                isLoading = false;
                refreshIcon.classList.remove('spin');
            }
        }

        function updateLastUpdated() {
            const now = new Date();
            const t = now.toLocaleTimeString('th-TH', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            lastUpdatedEl.innerText = `อัปเดต ${t}`;
            lastUpdatedEl.classList.remove('opacity-0');
            lastUpdatedEl.classList.add('opacity-100');
        }

        // === Render ===
        function render() {
            // Filter queues to only rooms in current dept
            const validRoomIds = allRooms.map(r => String(r.id));
            const deptQueues = allQueues.filter(q => validRoomIds.includes(String(q.room_number)));

            // Count summary
            const waitingCount = deptQueues.filter(q => q.status === 'waiting').length;
            const calledCount = deptQueues.filter(q => q.status === 'called').length;
            const labCount = deptQueues.filter(q => q.status === 'lab').length;
            const xrayCount = deptQueues.filter(q => q.status === 'xray').length;
            const notfoundCount = deptQueues.filter(q => q.status === 'not_found').length;

            // Summary Bar
            summaryBar.innerHTML = `
                <div class="flex-shrink-0 bg-blue-100 text-blue-700 px-3 py-1.5 rounded-full text-xs font-bold flex items-center gap-1.5">
                    <span class="w-2 h-2 bg-blue-500 rounded-full"></span>
                    รอ ${waitingCount}
                </div>
                <div class="flex-shrink-0 bg-amber-100 text-amber-700 px-3 py-1.5 rounded-full text-xs font-bold flex items-center gap-1.5">
                    <span class="w-2 h-2 bg-amber-500 rounded-full animate-pulse"></span>
                    กำลังเรียก ${calledCount}
                </div>
                ${labCount > 0 ? `<div class="flex-shrink-0 bg-emerald-100 text-emerald-700 px-3 py-1.5 rounded-full text-xs font-bold">Lab ${labCount}</div>` : ''}
                ${xrayCount > 0 ? `<div class="flex-shrink-0 bg-violet-100 text-violet-700 px-3 py-1.5 rounded-full text-xs font-bold">X-Ray ${xrayCount}</div>` : ''}
                ${notfoundCount > 0 ? `<div class="flex-shrink-0 bg-red-100 text-red-700 px-3 py-1.5 rounded-full text-xs font-bold">ไม่พบ ${notfoundCount}</div>` : ''}
            `;

            // Room Cards
            const roomCards = allRooms.map(room => {
                const activeCall = deptQueues.find(q => q.status === 'called' && String(q.room_number) === String(room.id));
                const waitingList = deptQueues.filter(q => q.status === 'waiting' && String(q.room_number) === String(room.id));

                const calledHtml = activeCall ? `
                    <div class="called-active border-2 rounded-xl p-4 flex items-center gap-4">
                        <div class="flex-1 min-w-0">
                            <div class="text-xs text-blue-600 font-bold uppercase tracking-wider mb-1">กำลังเรียก</div>
                            <div class="text-4xl font-black text-hospital-text tracking-tight leading-none">${activeCall.oqueue || activeCall.vn}</div>
                            <div class="text-sm text-slate-500 font-medium mt-1 truncate">${maskName(activeCall.patient_name)}</div>
                        </div>
                        <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center pulse-ring shadow-lg shadow-blue-200">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/></svg>
                        </div>
                    </div>
                ` : `
                    <div class="border border-dashed border-slate-200 rounded-xl p-4 text-center">
                        <span class="text-slate-300 text-sm font-semibold">ยังไม่เรียกคิว</span>
                    </div>
                `;

                const waitingHtml = waitingList.length > 0 ? `
                    <div class="mt-3">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs text-slate-400 font-bold uppercase tracking-wider">คิวที่รอ</span>
                            <span class="bg-blue-100 text-blue-600 text-xs font-bold px-2 py-0.5 rounded-full">${waitingList.length}</span>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            ${waitingList.slice(0, 10).map(q => `
                                <div class="bg-slate-50 border border-slate-200 rounded-lg px-3 py-1.5 text-center">
                                    <span class="text-sm font-black text-slate-600">${q.oqueue || q.vn}</span>
                                </div>
                            `).join('')}
                            ${waitingList.length > 10 ? `<div class="bg-slate-50 border border-slate-200 rounded-lg px-3 py-1.5 text-center"><span class="text-xs font-bold text-slate-400">+${waitingList.length - 10}</span></div>` : ''}
                        </div>
                    </div>
                ` : '';

                return `
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden fade-in">
                        <div class="bg-gradient-to-r from-hospital-blue to-blue-600 px-4 py-2.5">
                            <h3 class="text-white font-bold text-sm tracking-wide">${room.room_name}</h3>
                        </div>
                        <div class="p-4">
                            ${calledHtml}
                            ${waitingHtml}
                        </div>
                    </div>
                `;
            });

            if (roomCards.length === 0) {
                roomListEl.innerHTML = `
                    <div class="flex flex-col items-center justify-center h-40 text-center">
                        <svg class="w-12 h-12 text-slate-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        <span class="text-slate-400 text-sm font-semibold">ไม่พบห้องตรวจในแผนกนี้</span>
                    </div>
                `;
            } else {
                roomListEl.innerHTML = roomCards.join('');
            }

            // Status Sections (Lab, X-Ray, Not Found)
            renderStatusSections(deptQueues);
        }

        function renderStatusSections(deptQueues) {
            const notfounds = deptQueues.filter(q => q.status === 'not_found');
            const labs = deptQueues.filter(q => q.status === 'lab');
            const xrays = deptQueues.filter(q => q.status === 'xray');

            const hasAny = notfounds.length > 0 || labs.length > 0 || xrays.length > 0;
            statusSections.classList.toggle('hidden', !hasAny);

            // Not Found
            if (notfounds.length > 0) {
                notfoundSection.classList.remove('hidden');
                notfoundItems.innerHTML = notfounds.map(q => `
                    <div class="bg-white border border-red-200 rounded-xl px-3 py-2 text-center shadow-sm">
                        <div class="text-base font-black text-red-700">${q.oqueue || q.vn}</div>
                        <div class="text-[10px] text-red-400 font-medium truncate max-w-[80px]">${maskName(q.patient_name)}</div>
                    </div>
                `).join('');
            } else {
                notfoundSection.classList.add('hidden');
            }

            // Lab
            if (labs.length > 0) {
                labSection.classList.remove('hidden');
                labItems.innerHTML = labs.map(q => `
                    <div class="bg-white border border-emerald-200 rounded-xl px-3 py-2 text-center shadow-sm">
                        <div class="text-base font-black text-emerald-700">${q.oqueue || q.vn}</div>
                        <div class="text-[10px] text-emerald-500 font-medium truncate max-w-[80px]">${maskName(q.patient_name)}</div>
                    </div>
                `).join('');
            } else {
                labSection.classList.add('hidden');
            }

            // X-Ray
            if (xrays.length > 0) {
                xraySection.classList.remove('hidden');
                xrayItems.innerHTML = xrays.map(q => `
                    <div class="bg-white border border-violet-200 rounded-xl px-3 py-2 text-center shadow-sm">
                        <div class="text-base font-black text-violet-700">${q.oqueue || q.vn}</div>
                        <div class="text-[10px] text-violet-500 font-medium truncate max-w-[80px]">${maskName(q.patient_name)}</div>
                    </div>
                `).join('');
            } else {
                xraySection.classList.add('hidden');
            }
        }

        // === Refresh with Rate Limiting ===
        function handleRefresh() {
            if (refreshBtn.disabled || isLoading) return;

            loadData();
            startCooldown();
        }

        function startCooldown() {
            refreshBtn.disabled = true;
            cooldownRemaining = COOLDOWN_SEC;

            refreshIcon.classList.add('hidden');
            refreshCountdown.classList.remove('hidden');
            refreshCountdown.innerText = cooldownRemaining;

            cooldownTimer = setInterval(() => {
                cooldownRemaining--;
                if (cooldownRemaining <= 0) {
                    clearInterval(cooldownTimer);
                    cooldownTimer = null;
                    refreshBtn.disabled = false;
                    refreshIcon.classList.remove('hidden');
                    refreshCountdown.classList.add('hidden');
                } else {
                    refreshCountdown.innerText = cooldownRemaining;
                }
            }, 1000);
        }

        // === Start ===
        init();
    </script>

</body>
</html>
