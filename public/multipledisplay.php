<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Main Queue Board</title>
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
                            500: '#0ea5e9',
                            600: '#0284c7',
                            900: '#0c4a6e',
                        }
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-slate-900 min-h-screen text-white overflow-hidden">

    <!-- Top Header -->
    <!-- Top Header -->
    <header
        class="p-6 flex justify-between items-center bg-slate-900/90 backdrop-blur-md border-b border-white/10 relative z-50">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-brand-500/20 rounded-full flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-brand-400" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
            </div>
            <div class="cursor-pointer group" onclick="openSettings()">
                <h1 id="dept-name" class="text-3xl font-bold text-white group-hover:text-brand-300 transition">
                    คิวตรวจโรคทั่วไป</h1>
                <p id="dept-sub" class="text-brand-200/60 text-sm group-hover:text-brand-300/60 transition">General OPD
                    Queue</p>
            </div>
        </div>
        <div class="text-right">
            <div id="clock" class="text-4xl font-mono font-bold tracking-widest text-brand-50">00:00</div>
            <div id="date" class="text-brand-100/60 text-lg">...</div>
        </div>
    </header>

    <!-- Settings Modal -->
    <div id="settings-modal"
        class="fixed inset-0 bg-black/80 hidden items-center justify-center z-[100] backdrop-blur-sm">
        <div
            class="bg-slate-800 p-8 rounded-3xl w-full max-w-md border border-white/10 shadow-2xl transform scale-100 transition-all">
            <h2 class="text-2xl font-bold text-white mb-6 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-brand-400" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Display Settings
            </h2>

            <div class="space-y-4">
                <div>
                    <label class="block text-slate-400 mb-2 text-sm">Department Name (Main Title)</label>
                    <input type="text" id="input-dept-name"
                        class="w-full bg-slate-900 border border-slate-700 rounded-xl p-3 text-white focus:border-brand-500 focus:outline-none placeholder-slate-600"
                        placeholder="e.g. แผนกอายุรกรรม">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-slate-400 mb-2 text-sm">TTS Prefix</label>
                        <input type="text" id="input-tts-prefix"
                            class="w-full bg-slate-900 border border-slate-700 rounded-xl p-3 text-white focus:border-brand-500 focus:outline-none placeholder-slate-600"
                            placeholder="default: ขอเชิญหมายเลข">
                    </div>
                    <div>
                        <label class="block text-slate-400 mb-2 text-sm">TTS Middle</label>
                        <input type="text" id="input-tts-middle"
                            class="w-full bg-slate-900 border border-slate-700 rounded-xl p-3 text-white focus:border-brand-500 focus:outline-none placeholder-slate-600"
                            placeholder="default: ที่ห้องตรวจ">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-slate-400 mb-2 text-sm">Filter by Department</label>
                        <select id="input-dept-filter" onchange="onDeptFilterChange()"
                            class="w-full bg-slate-900 border border-slate-700 rounded-xl p-3 text-white focus:border-brand-500 focus:outline-none">
                            <option value="">Show All</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-slate-400 mb-2 text-sm">Filter by Room</label>
                        <select id="input-room-filter"
                            class="w-full bg-slate-900 border border-slate-700 rounded-xl p-3 text-white focus:border-brand-500 focus:outline-none">
                            <option value="">Show All Rooms</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-slate-400 mb-2 text-sm">Subtitle</label>
                    <input type="text" id="input-dept-sub"
                        class="w-full bg-slate-900 border border-slate-700 rounded-xl p-3 text-white focus:border-brand-500 focus:outline-none placeholder-slate-600"
                        placeholder="e.g. Room 1-5">
                </div>

                <div>
                    <label class="block text-slate-400 mb-2 text-sm">Voice (Thai)</label>
                    <div class="flex gap-2">
                        <select id="input-voice"
                            class="flex-1 bg-slate-900 border border-slate-700 rounded-xl p-3 text-white focus:border-brand-500 focus:outline-none">
                            <option value="">Default</option>
                        </select>
                        <button onclick="testVoice()"
                            class="px-4 py-2 bg-slate-700 hover:bg-slate-600 rounded-xl">Test</button>
                    </div>
                </div>

                <div>
                    <label class="block text-slate-400 mb-2 text-sm">Call Repetitions</label>
                    <input type="number" id="input-tts-repeat" min="1" max="5"
                        class="w-full bg-slate-900 border border-slate-700 rounded-xl p-3 text-white focus:border-brand-500 focus:outline-none placeholder-slate-600"
                        placeholder="Default: 1">
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-8">
                <button onclick="closeSettings()"
                    class="px-4 py-2 text-slate-400 hover:text-white transition">Cancel</button>
                <button onclick="saveSettings()"
                    class="px-6 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-xl shadow-lg shadow-brand-500/20 transition-all font-semibold">Save
                    Changes</button>
            </div>
        </div>
    </div>

    <!-- Sound Enable Overlay (Autoplay Policy) -->
    <div id="sound-overlay"
        class="fixed inset-0 bg-slate-900/90 z-[70] flex flex-col items-center justify-center cursor-pointer"
        onclick="unlockAudio()">
        <div class="bg-brand-600 p-8 rounded-full animate-bounce mb-4 shadow-lg shadow-brand-500/50">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-white" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
            </svg>
        </div>
        <h1 class="text-3xl font-bold text-white mb-2">Click Anywhere to Enable Sound</h1>
        <p class="text-slate-400">Browser policy requires interaction to play audio</p>
    </div>

    <!-- Initial Department Selection Overlay -->
    <div id="dept-select-overlay" class="fixed inset-0 bg-slate-900 z-[60] hidden flex-col items-center justify-center">
        <div class="text-center max-w-md w-full p-6">
            <h1 class="text-4xl font-bold text-white mb-2">Welcome</h1>
            <p class="text-slate-400 mb-8">Please select a department to display</p>

            <div id="dept-selection-list" class="flex flex-col gap-3">
                <!-- Buttons injected here -->
            </div>

            <button onclick="selectDept('')" class="mt-6 text-slate-500 hover:text-white underline text-sm">Show All
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
            class="fixed bottom-0 left-0 right-0 h-32 bg-slate-900/95 border-t border-white/10 backdrop-blur-xl z-40 grid grid-cols-2 gap-px">

            <!-- Lab Section -->
            <div class="relative overflow-hidden group">
                <div class="absolute inset-0 bg-indigo-900/20 group-hover:bg-indigo-900/30 transition"></div>
                <div class="h-full flex items-center px-8 gap-6">
                    <div class="flex flex-col justify-center shrink-0">
                        <span class="text-indigo-400 font-bold text-sm tracking-widest uppercase">Laboratory</span>
                        <h3 class="text-3xl font-bold text-white">รอ Lab</h3>
                    </div>
                    <div id="lab-list"
                        class="flex items-center gap-4 overflow-x-auto p-4 scrollbar-hide w-full mask-linear-fade">
                        <!-- Dynamic Items -->
                        <div class="text-white/30 italic">No patients</div>
                    </div>
                </div>
            </div>

            <!-- X-Ray Section -->
            <div class="relative overflow-hidden group border-l border-white/10">
                <div class="absolute inset-0 bg-fuchsia-900/20 group-hover:bg-fuchsia-900/30 transition"></div>
                <div class="h-full flex items-center px-8 gap-6">
                    <div class="flex flex-col justify-center shrink-0">
                        <span class="text-fuchsia-400 font-bold text-sm tracking-widest uppercase">Radiology</span>
                        <h3 class="text-3xl font-bold text-white">รอ X-Ray</h3>
                    </div>
                    <div id="xray-list"
                        class="flex items-center gap-4 overflow-x-auto p-4 scrollbar-hide w-full mask-linear-fade">
                        <!-- Dynamic Items -->
                        <div class="text-white/30 italic">No patients</div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        // State
        let currentDeptFilter = localStorage.getItem('dept_filter') || ''; // Default empty if null
        let currentRoomFilter = localStorage.getItem('room_filter') || '';
        let ttsPrefix = localStorage.getItem('tts_prefix') || 'ขอเชิญหมายเลข';
        let ttsMiddle = localStorage.getItem('tts_middle') || 'ที่ห้องตรวจ';
        let ttsRepeat = parseInt(localStorage.getItem('tts_repeat')) || 1;

        let allRooms = [];
        let allQueues = [];
        let deptList = []; // Store depts for usage in modal

        let calledPage = 0;
        let waitingPage = 0;
        const CALLED_PAGE_SIZE = 15;
        const WAITING_PAGE_SIZE = 10;

        // Elements
        const deptNameEl = document.getElementById('dept-name');
        const deptSubEl = document.getElementById('dept-sub');
        const modal = document.getElementById('settings-modal');

        const inputName = document.getElementById('input-dept-name');
        const inputSub = document.getElementById('input-dept-sub');
        const inputFilter = document.getElementById('input-dept-filter');
        const inputRoomFilter = document.getElementById('input-room-filter');
        const inputTtsPrefix = document.getElementById('input-tts-prefix');
        const inputTtsMiddle = document.getElementById('input-tts-middle');
        const inputVoice = document.getElementById('input-voice');
        const inputTtsRepeat = document.getElementById('input-tts-repeat');

        const deptOverlay = document.getElementById('dept-select-overlay');
        const deptListEl = document.getElementById('dept-selection-list');

        // Combined Container
        const container = document.getElementById('room-grid');
        const labListEl = document.getElementById('lab-list');
        const xrayListEl = document.getElementById('xray-list');

        function openSettings() {
            modal.classList.remove('hidden');
            modal.classList.add('flex');

            // Load current values to inputs
            inputName.value = deptNameEl.innerText;
            inputSub.value = deptSubEl.innerText;
            inputFilter.value = currentDeptFilter;

            inputTtsPrefix.value = ttsPrefix;
            inputTtsMiddle.value = ttsMiddle;
            inputTtsRepeat.value = ttsRepeat;

            // Populate Room Filter based on current Dept
            updateRoomFilterOptions(currentDeptFilter, currentRoomFilter);

            // Force refresh voice list
            loadVoices();
        }

        function closeSettings() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        async function saveSettings() {
            // UI Text (Optional - handled by backend primarily, but we can override locally if needed)
            // But per code structure, loadInitData fetches api/settings.php. 
            // If we want "This Machine" settings to override, we should use localStorage for titles too?
            // The request says "Increase change dept and room at that page".

            // 1. Save to LocalStorage
            currentDeptFilter = inputFilter.value;
            currentRoomFilter = inputRoomFilter.value;
            ttsPrefix = inputTtsPrefix.value || 'ขอเชิญหมายเลข';
            ttsMiddle = inputTtsMiddle.value || 'ที่ห้องตรวจ';
            ttsRepeat = parseInt(inputTtsRepeat.value) || 1;
            const selectedVoice = inputVoice.value;

            localStorage.setItem('dept_filter', currentDeptFilter);
            localStorage.setItem('room_filter', currentRoomFilter);
            localStorage.setItem('tts_prefix', ttsPrefix);
            localStorage.setItem('tts_middle', ttsMiddle);
            localStorage.setItem('tts_voice', selectedVoice);
            localStorage.setItem('tts_repeat', ttsRepeat);

            // 2. Apply Titles (Client side override or just re-fetch if we had a backend save)
            // For now, let's trust the inputs for immediate feedback
            // Note: inputName and inputSub seem to be intended for Global Settings? 
            // The prompt says "Change dept and room at that page". 
            // Let's assume inputName/Sub are for local display override if desired, 
            // OR if they are intended to save back to server, we'd need an API.
            // Existing code loaded from api/settings.php.
            // I will NOT save name/sub to server as I don't see a PUT endpoint ready in the file list.
            // I will prioritize the "Text" and "Filter" logic requested.

            closeSettings();

            // Reload Data with new filters
            calledPage = 0;
            loadRoomsAndQueue();
        }

        async function onDeptFilterChange() {
            const dept = inputFilter.value;
            // Fetch rooms for this dept to populate room select
            await updateRoomFilterOptions(dept, '');
        }

        async function updateRoomFilterOptions(dept, selectedRoom) {
            try {
                let url = 'api/rooms.php';
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

        // Initial Load
        async function loadInitData() {
            // Global Settings
            try {
                const res = await fetch('api/settings.php');
                const data = await res.json();
                if (data.success && data.data) {
                    if (data.data.dept_name) deptNameEl.innerText = data.data.dept_name;
                    if (data.data.dept_sub) deptSubEl.innerText = data.data.dept_sub;
                }
            } catch (e) { }

            // Dept Options
            try {
                const res2 = await fetch('api/departments.php');
                const data2 = await res2.json();
                if (data2.success) {
                    const depts = data2.data;
                    deptList = depts; // Save for later
                    inputFilter.innerHTML = '<option value="">Show All</option>' +
                        depts.map(d => `<option value="${d}">${d}</option>`).join('');

                    // Restore saved filter
                    if (currentDeptFilter) inputFilter.value = currentDeptFilter;

                    deptListEl.innerHTML = depts.map(d => `
                        <button onclick="selectDept('${d}')" class="w-full py-4 px-6 bg-slate-800 hover:bg-brand-600 text-white rounded-xl text-xl font-bold transition border border-slate-700 hover:border-brand-500">
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

        // Time Updates
        function updateTime() {
            const now = new Date();
            document.getElementById('clock').innerText = now.toLocaleTimeString('th-TH', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            document.getElementById('date').innerText = now.toLocaleDateString('th-TH', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
        }
        setInterval(updateTime, 1000);
        updateTime();

        async function loadRoomsAndQueue() {
            // Fetch Rooms for this department
            try {
                let url = 'api/rooms.php';
                if (currentDeptFilter) url += `?department=${encodeURIComponent(currentDeptFilter)}`;
                const r = await fetch(url);
                const d = await r.json();
                if (d.success) {
                    allRooms = d.data;

                    // User Request: Header Subtitle should be Department Name + Room Description
                    if (currentDeptFilter) {
                        const uniqueDescriptions = [...new Set(
                            allRooms.map(r => r.description).filter(desc => desc && desc.trim().length > 0)
                        )];
                        const descText = uniqueDescriptions.join(' / ');

                        // Format: "DepartmentName - Description"
                        if (descText) {
                            deptSubEl.innerText = `${currentDeptFilter} ${descText}`;
                        } else {
                            deptSubEl.innerText = currentDeptFilter;
                        }
                    } else {
                        // Default / All
                        // deptSubEl.innerText = "All Departments";
                    }
                }
            } catch (e) { allRooms = []; }

            fetchQueue();
        }

        async function fetchQueue() {
            try {
                let url = 'api/queue_data.php?limit=50'; // Fetch more so we can carousel
                if (currentDeptFilter) {
                    // We need ALL queues for Lab/Xray too, not just filtered ones?
                    // But if this board is for Dept A, maybe we only show Lab/Xray for Dept A?
                    // Let's assume dashboard shows everything related to filtered Dept.
                    url += `&department=${encodeURIComponent(currentDeptFilter)}`;
                } else {
                    // If 'Show All', we just fetch all
                }

                const res = await fetch(url);
                const data = await res.json();

                if (data.success) {
                    allQueues = data.data;
                    processAndRender();

                    // Trigger TTS check (only for active calls)
                    // Trigger TTS check (only for active calls)
                    const called = allQueues.filter(q => q.status === 'called');
                    if (called.length > 0) {
                        // FIX: Use updated_at to find the *latest* call event, ensuring Recalls work.
                        // Fallback to 'created_at' or 'id' if updated_at is null (though DB default has it)
                        const latest = called.reduce((prev, current) => {
                            const prevTime = new Date(prev.updated_at || prev.created_at).getTime();
                            const currTime = new Date(current.updated_at || current.created_at).getTime();
                            return (prevTime > currTime) ? prev : current;
                        });

                        // We track by composite ID+Time to ensure we speak if same ID is Updated
                        // Actually, tracking "Last Spoken Timestamp" is safer?
                        // But let's check if the ID+Time changed from last known.

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

            // ฟังก์ชันย่อยสำหรับ Mask ข้อความ (เก็บ 2 ตัวหน้า ส่วนที่เหลือเปลี่ยนเป็น x)
            const maskText = (text) => {
                if (!text || text.length <= 2) return text; // ถ้าสั้นเกินไป ไม่ต้อง mask
                return text.substring(0, 2) + 'x'.repeat(text.length - 2);
            };

            // 1. แยกชื่อ และ นามสกุล ออกจากกันด้วยช่องว่าง
            const parts = fullName.split(' ');
            let firstName = parts[0];
            let lastName = parts.slice(1).join(' '); // นามสกุล (เผื่อมีเว้นวรรคหลายขยัก)

            // 2. จัดการส่วนชื่อจริง (ตรวจสอบคำนำหน้าชื่อที่มีจุด เช่น นาย. หรือ น.ส.)
            if (firstName.includes('.')) {
                const dotIndex = firstName.lastIndexOf('.'); // หาตำแหน่งจุดสุดท้าย
                const prefix = firstName.substring(0, dotIndex + 1); // เก็บคำนำหน้า (เช่น "นาย.")
                const realName = firstName.substring(dotIndex + 1);  // เก็บชื่อจริง (เช่น "กกกก")

                // ประกอบร่าง: คำนำหน้า + ชื่อที่ mask แล้ว
                firstName = prefix + maskText(realName);
            } else {
                // กรณีไม่มีคำนำหน้าแบบมีจุด
                firstName = maskText(firstName);
            }

            // 3. จัดการนามสกุล (ถ้ามี)
            if (lastName) {
                lastName = maskText(lastName);
                return `${firstName} ${lastName}`;
            }

            return firstName;
        }

        // Logic to Merge Rooms + Queues and Render Pages
        function processAndRender() {
            // 1. Prepare Room Cards (Merged)
            // Map ALL ROOMS. If a room has an active call, use it. Else Idle.
            const roomCards = allRooms.map(room => {
                const activeCall = allQueues.find(q => q.status === 'called' && String(q.room_number) === String(room.id));
                const waitingForThisRoom = allQueues.filter(q => q.status === 'waiting' && String(q.room_number) === String(room.id));
                const totalWaiting = waitingForThisRoom.length;
                const next5 = waitingForThisRoom.slice(0, 5);

                const waitingHtml = next5.length > 0 ? `
                    <div class="mt-4 w-full bg-slate-900/50 rounded-xl p-3 border border-white/5 backdrop-blur-sm">
                        <div class="flex justify-between items-center mb-2 px-1">
                             <div class="flex items-center gap-2">
                                <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                                <span class="text-xs font-bold text-slate-300 uppercase tracking-wider">รอเรียก (${totalWaiting})</span>
                             </div>
                        </div>
                        <div class="space-y-1.5">
                            ${next5.map(q => `
                                <div class="flex justify-between items-center text-sm bg-white/5 hover:bg-white/10 transition px-3 py-2 rounded-lg border border-white/5">
                                    <span class="font-mono font-bold text-white text-lg">${q.oqueue || q.vn}</span>
                                    <span class="text-slate-400 text-xs">${maskName(q.patient_name)}</span>
                                </div>
                            `).join('')}
                             ${totalWaiting > 5 ? `<div class="text-center text-xs text-slate-500 pt-2 font-medium">+${totalWaiting - 5} more</div>` : ''}
                        </div>
                    </div>
                ` : `
                    <div class="mt-4 w-full bg-slate-900/30 rounded-xl p-4 border border-white/5 text-center">
                         <span class="text-xs font-semibold text-slate-500">No Queue</span>
                    </div>
                `;

                // WRAPPER DIV for Card + Waiting List
                // We return the whole markup for this grid Item
                let cardContent = '';

                if (activeCall) {
                    // Active Card Logic
                    const lastTime = lastCallTimes[room.id] || 0;
                    const isBlinking = (Date.now() - lastTime) < 10000;
                    const containerClass = isBlinking
                        ? "bg-yellow-400 border-yellow-200 shadow-yellow-500/50 animate-pulse text-slate-900"
                        : "bg-gradient-to-br from-emerald-500 to-teal-700 border-white/20 shadow-emerald-500/40 text-white";

                    const titleClass = isBlinking ? "text-slate-800" : "text-emerald-100";
                    const vnClass = isBlinking ? "text-black" : "text-white";
                    const nameBgClass = isBlinking ? "bg-black/10" : "bg-black/20";
                    const nameTextClass = isBlinking ? "text-slate-900" : "text-white";

                    cardContent = `
                        <div class="relative overflow-hidden rounded-3xl ${containerClass} p-4 flex flex-col items-center justify-between text-center shadow-lg border-4 min-h-[300px]">
                             <div class="absolute -top-10 -right-10 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
                             
                             <div class="flex-1 flex flex-col justify-center items-center w-full z-10">
                                <span class="text-xl font-bold uppercase tracking-wider mb-2 opacity-90 ${titleClass}">ห้องตรวจ ${room.room_name}</span>
                                <h3 class="text-7xl font-black tracking-tighter my-2 leading-none ${vnClass}">${activeCall.oqueue || activeCall.vn}</h3>
                                <div class="mt-2 ${nameBgClass} rounded-full px-4 py-1.5 backdrop-blur-sm max-w-full">
                                    <p class="text-lg font-medium truncate ${nameTextClass}">${maskName(activeCall.patient_name)}</p>
                                </div>
                             </div>
                        </div>
                    `;
                } else {
                    // Idle Card Logic
                    if (currentRoomFilter && String(room.id) !== String(currentRoomFilter)) return '';

                    cardContent = `
                        <div class="bg-slate-800/60 p-4 rounded-3xl border-2 border-slate-700/50 flex flex-col items-center justify-center text-center opacity-75 min-h-[300px]">
                            <span class="text-lg text-slate-400 font-semibold block truncate">ห้อง ${room.room_name}</span>
                            <h3 class="text-5xl font-bold text-slate-600 tracking-tight my-4">ว่าง</h3>
                            <p class="text-sm text-slate-500 truncate">รอเรียก...</p>
                        </div>
                    `;
                }

                // Filtering Check
                if (currentRoomFilter && String(room.id) !== String(currentRoomFilter)) return '';

                // Combine Card + Separated Waiting List
                return `
                    <div class="flex flex-col gap-3">
                        ${cardContent}
                        ${waitingHtml}
                    </div>
                `;
            });

            // 3. Render Current Page
            renderPagination(roomCards);
        }

        // Scroll State
        let marqueeInterval;
        let lastWaitingData = ''; // To prevent re-rendering and jumpiness

        function renderPagination(roomCards) {
            // New Layout: We don't have a separate marquee list anymore.
            // But we still have pagination for rooms if many rooms?

            const totalCalledPages = Math.ceil(roomCards.length / CALLED_PAGE_SIZE) || 1;
            if (calledPage >= totalCalledPages) calledPage = 0;
            const startC = calledPage * CALLED_PAGE_SIZE;
            const currentRooms = roomCards.slice(startC, startC + CALLED_PAGE_SIZE);

            container.innerHTML = currentRooms.join('') || `
                <div class="col-span-full h-40 flex items-center justify-center text-white/20 text-xl font-bold border-2 border-dashed border-white/10 rounded-3xl">Loading Rooms...</div>
            `;

            // Render Lab/Xray
            renderLabXray();
        }

        function renderLabXray() {
            const labs = allQueues.filter(q => q.status === 'lab');
            const xrays = allQueues.filter(q => q.status === 'xray');

            const makeItem = (q, bg) => `
                <div class="flex flex-col items-center justify-center bg-white/10 px-6 py-3 rounded-2xl min-w-[140px] border border-white/10 shadow-lg animate-pulse-slow">
                     <span class="text-2xl font-black text-white">${q.oqueue || q.vn}</span>
                     <span class="text-xs text-white/60 truncate max-w-[120px]">${maskName(q.patient_name)}</span>
                </div>
            `;

            labListEl.innerHTML = labs.length ? labs.map(q => makeItem(q)).join('') : '<div class="text-white/20 italic pl-4">No patients</div>';
            xrayListEl.innerHTML = xrays.length ? xrays.map(q => makeItem(q)).join('') : '<div class="text-white/20 italic pl-4">No patients</div>';
        }


        function startMarquee() {
            // Deprecated
        }

        function stopMarquee() {
            // Deprecated
        }

        // Sound State
        let audioUnlocked = false;

        function unlockAudio() {
            if (audioUnlocked) return;

            // Create a short silence to unlock audio context
            const utterance = new SpeechSynthesisUtterance(" ");
            window.speechSynthesis.speak(utterance);
            audioUnlocked = true;

            // Hide overlay
            document.getElementById('sound-overlay').classList.add('hidden');
            console.log("Audio Context Unlocked");
        }

        // TTS Queue Logic
        const ttsQueue = [];
        let isSpeaking = false;
        let lastCallTimes = {}; // room_id -> timestamp (ms)

        function speakQueue(item) {
            console.log("Queueing TTS:", item); // DEBUG

            // Reload settings from localStorage to ensure we have the absolute latest values
            const currentPrefix = localStorage.getItem('tts_prefix') || 'ขอเชิญหมายเลข';
            const currentMiddle = localStorage.getItem('tts_middle') || 'ที่ห้องตรวจ';

            // "ขอเชิญหมายเลข ... ที่ห้อง ... ค่ะ"
            const text = `${currentPrefix} ${item.oqueue || item.vn} ${currentMiddle} ${item.room_number}`;

            // Mark time for blinking effect
            lastCallTimes[item.room_number] = Date.now();

            // Force re-render to start blink immediately
            processAndRender();

            // Schedule stop blink after 11 seconds (buffer)
            setTimeout(() => {
                processAndRender();
            }, 11000);

            // Repeat N times
            for (let i = 0; i < ttsRepeat; i++) {
                ttsQueue.push({ text: text, lang: 'th-TH' });
            }

            processTTSQueue();
        }

        // Page Cycling Interval
        setInterval(() => {
            const totalCalledPages = Math.ceil(allRooms.length / CALLED_PAGE_SIZE) || 1;
            if (totalCalledPages > 1) {
                calledPage++;
                if (calledPage >= totalCalledPages) calledPage = 0;
                processAndRender();
            }
        }, 10000);

        function processTTSQueue() {
            if (isSpeaking || ttsQueue.length === 0) return;

            const item = ttsQueue.shift();
            isSpeaking = true;

            console.log("Processing TTS Item:", item); // DEBUG

            const utterance = new SpeechSynthesisUtterance(item.text);
            utterance.lang = item.lang;
            utterance.rate = 1.0;
            utterance.pitch = 1.2;

            // Re-fetch voices to ensure we have the latest list (Chrome idiosyncrasy)
            const voices = window.speechSynthesis.getVoices();
            console.log("Available Voices during speak:", voices.length); // DEBUG

            let thaiVoice = voices.find(v => v.lang.includes('th') && (v.name.includes('Google') || v.name.includes('Premwadee') || v.name.includes('Kanya')));
            if (!thaiVoice) thaiVoice = voices.find(v => v.lang.includes('th'));

            if (thaiVoice) {
                // Default fallback
                utterance.voice = thaiVoice;
            }

            // Override with user selection if exists and valid
            const savedVoiceURI = localStorage.getItem('tts_voice');
            if (savedVoiceURI) {
                const specificVoice = voices.find(v => v.voiceURI === savedVoiceURI);
                if (specificVoice) {
                    utterance.voice = specificVoice;
                    console.log("Using Saved Voice:", specificVoice.name);
                } else {
                    console.warn("Saved voice not found in current list, using default");
                }
            }

            utterance.onend = function () {
                console.log("TTS Finished"); // DEBUG
                isSpeaking = false;
                setTimeout(processTTSQueue, 500);
            };

            utterance.onerror = function (e) {
                console.error("TTS Error:", e); // DEBUG
                isSpeaking = false;
                setTimeout(processTTSQueue, 500);
            };

            window.speechSynthesis.speak(utterance);
        }

        // Initialize voices
        let availableVoices = [];

        function loadVoices() {
            const all = window.speechSynthesis.getVoices();
            if (all.length > 0) {
                availableVoices = all;
                console.log("Voices Loaded via function: " + availableVoices.length);
                populateVoiceList();
            }
        }

        window.speechSynthesis.onvoiceschanged = loadVoices;

        // Try loading immediately as well (for browsers where voices are ready)
        loadVoices();

        // Polling fallback if voices are stuck
        let voiceInterval = setInterval(() => {
            if (availableVoices.length === 0) {
                loadVoices();
            } else {
                clearInterval(voiceInterval);
            }
        }, 1000);

        function populateVoiceList() {
            const thaiVoices = availableVoices.filter(v => v.lang.includes('th'));
            const saved = localStorage.getItem('tts_voice');

            inputVoice.innerHTML = '<option value="">Default (Auto)</option>' +
                thaiVoices.map(v => `<option value="${v.voiceURI}" ${v.voiceURI === saved ? 'selected' : ''}>${v.name}</option>`).join('');

            // Also add common English ones if no Thai found? No, user requested Thai.
        }

        function testVoice() {
            const uri = inputVoice.value;
            const text = "ทดสอบเสียงค่ะ 1 2 3";
            const ut = new SpeechSynthesisUtterance(text);
            ut.lang = 'th-TH';
            if (uri) {
                const v = availableVoices.find(x => x.voiceURI === uri);
                if (v) ut.voice = v;
            }
            window.speechSynthesis.speak(ut);
        }

        // WebSocket Connection
        function connectWS() {
            const socket = new WebSocket('ws://localhost:8765');
            socket.onopen = function () {
                console.log('Connected');
                document.body.style.borderTop = "4px solid #10b981";
            };
            socket.onmessage = function (event) {
                console.log("WS Data", event.data);
                try {
                    const payload = JSON.parse(event.data);
                    if (payload.event === 'recall' && payload.data) {
                        speakQueue(payload.data);
                    }
                    // Always refresh
                    fetchQueue();
                } catch (e) {
                    fetchQueue();
                }
            };
            socket.onclose = function () {
                document.body.style.borderTop = "4px solid #ef4444";
                setTimeout(connectWS, 3000);
            };
        }
        connectWS();

        // Fallback polling
        setInterval(fetchQueue, 30000);

        // Initial Fetch
        // fetchQueue called by overlay logic or load
    </script>
</body>

</html>