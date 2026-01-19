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

<body class="bg-gradient-to-br from-brand-900 via-slate-800 to-slate-900 min-h-screen text-white overflow-hidden">

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
                <div>
                    <label class="block text-slate-400 mb-2 text-sm">Filter by Department (Room Group)</label>
                    <select id="input-dept-filter"
                        class="w-full bg-slate-900 border border-slate-700 rounded-xl p-3 text-white focus:border-brand-500 focus:outline-none">
                        <option value="">Show All</option>
                    </select>
                </div>
                <div>
                    <label class="block text-slate-400 mb-2 text-sm">Subtitle</label>
                    <input type="text" id="input-dept-sub"
                        class="w-full bg-slate-900 border border-slate-700 rounded-xl p-3 text-white focus:border-brand-500 focus:outline-none placeholder-slate-600"
                        placeholder="e.g. Room 1-5">
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

        <!-- Left: Current Called (Focus) -->
        <section class="lg:col-span-9 flex flex-col gap-4 h-full overflow-hidden">
            <h2 class="text-xl md:text-2xl font-semibold flex items-center gap-2 text-brand-100 shrink-0">
                <span class="w-2 h-8 bg-brand-500 rounded-full block"></span>
                กำลังเรียก (Calling)
            </h2>

            <div id="current-called-container"
                class="grid grid-cols-[repeat(auto-fit,minmax(250px,1fr))] gap-4 overflow-hidden pr-2 pb-20 content-start">
                <!-- Dynamic Content Here -->
            </div>
        </section>

        <!-- Right: Waiting List -->
        <section
            class="lg:col-span-3 flex flex-col gap-4 bg-black/20 rounded-3xl p-4 border border-white/5 h-full overflow-hidden">
            <h2 class="text-xl md:text-2xl font-semibold text-brand-100 flex items-center gap-2 shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-brand-400" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                รอเรียก
            </h2>
            <div id="waiting-list" class="flex flex-col gap-2 overflow-hidden h-full pr-1 pb-20">
                <!-- Data -->
            </div>
        </section>
    </main>

    <script>
        // State
        let currentDeptFilter = localStorage.getItem('dept_filter');
        let allRooms = [];
        let allQueues = [];

        let calledPage = 0;
        let waitingPage = 0;
        const CALLED_PAGE_SIZE = 15; // Increased to 15
        const WAITING_PAGE_SIZE = 10;

        // Elements
        const deptNameEl = document.getElementById('dept-name');
        const deptSubEl = document.getElementById('dept-sub');
        const modal = document.getElementById('settings-modal');
        const inputName = document.getElementById('input-dept-name');
        const inputSub = document.getElementById('input-dept-sub');
        const inputFilter = document.getElementById('input-dept-filter');
        const deptOverlay = document.getElementById('dept-select-overlay');
        const deptListEl = document.getElementById('dept-selection-list');
        const calledContainer = document.getElementById('current-called-container');
        const waitingContainer = document.getElementById('waiting-list');

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
                    inputFilter.innerHTML = '<option value="">Show All</option>' +
                        depts.map(d => `<option value="${d}">${d}</option>`).join('');
                    if (currentDeptFilter !== null) inputFilter.value = currentDeptFilter;

                    deptListEl.innerHTML = depts.map(d => `
                        <button onclick="selectDept('${d}')" class="w-full py-4 px-6 bg-slate-800 hover:bg-brand-600 text-white rounded-xl text-xl font-bold transition border border-slate-700 hover:border-brand-500">
                            ${d}
                        </button>
                    `).join('');

                    if (currentDeptFilter === null) {
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
                        // If we want to restore default from settings, we'd need to re-fetch or store it.
                        // For now, assume "All Departments" if no filter.
                        // Or just don't touch it if we want to keep the "General OPD Queue" text from settings.
                        // But if we switch FROM a specific dept TO all, we should probably reset.
                        // Let's fetch settings again quickly or just set to "All Departments".
                        deptSubEl.innerText = "All Departments";
                    }
                }
            } catch (e) { allRooms = []; }

            fetchQueue();
        }

        async function fetchQueue() {
            try {
                let url = 'api/queue_data.php?limit=50'; // Fetch more so we can carousel
                if (currentDeptFilter) {
                    url += `&department=${encodeURIComponent(currentDeptFilter)}`;
                }

                const res = await fetch(url);
                const data = await res.json();

                if (data.success) {
                    allQueues = data.data;
                    processAndRender();

                    // Trigger TTS check (only for active calls)
                    const called = allQueues.filter(q => q.status === 'called');
                    if (called.length > 0) {
                        const maxIdItem = called.reduce((prev, current) => (prev.id > current.id) ? prev : current);
                        if (window.lastCalledId !== maxIdItem.id) {
                            window.lastCalledId = maxIdItem.id;
                            speakQueue(maxIdItem);
                        }
                    }
                }
            } catch (e) {
                console.error("Failed to fetch queue", e);
            }
        }

        function maskName(fullName) {
            if (!fullName) return '';
            const parts = fullName.trim().split(/\s+/);
            if (parts.length > 1) {
                return `${parts[0]} xxx`;
            }
            return fullName;
        }

        // Logic to Merge Rooms + Queues and Render Pages
        function processAndRender() {
            // 1. Prepare Room Cards (Merged)
            // Map ALL ROOMS. If a room has an active call, use it. Else Idle.
            const roomCards = allRooms.map(room => {
                const activeCall = allQueues.find(q => q.status === 'called' && String(q.room_number) === String(room.id));
                if (activeCall) {
                    // Check Blink
                    const lastTime = lastCallTimes[room.id] || 0;
                    const isBlinking = (Date.now() - lastTime) < 10000; // 10 seconds

                    // Styles based on state
                    const containerClass = isBlinking
                        ? "bg-yellow-400 border-yellow-200 shadow-yellow-500/50 animate-pulse text-slate-900"
                        : "bg-gradient-to-br from-emerald-500 to-teal-700 border-white/20 shadow-emerald-500/40 text-white";

                    const titleClass = isBlinking ? "text-slate-800" : "text-emerald-100";
                    const vnClass = isBlinking ? "text-black" : "text-white";
                    const nameBgClass = isBlinking ? "bg-black/10" : "bg-black/20";
                    const nameTextClass = isBlinking ? "text-slate-900" : "text-white";
                    const decorClass = isBlinking ? "bg-white/20" : "bg-white/10";

                    // Active Card
                    return `
                        <div class="relative overflow-hidden rounded-3xl ${containerClass} p-6 flex flex-col items-center justify-center text-center shadow-2xl border-4 transition-transform hover:scale-105">
                             <!-- Decorative Circle bg -->
                             <div class="absolute -top-10 -right-10 w-32 h-32 ${decorClass} rounded-full blur-2xl"></div>
                             
                             <span class="text-3xl font-bold uppercase tracking-wider mb-2 drop-shadow-sm ${titleClass}">ห้องตรวจ ${room.room_name}</span>
                             
                             <h3 class="text-8xl font-black tracking-tighter my-2 drop-shadow-lg leading-none ${vnClass}">
                                ${activeCall.oqueue || activeCall.vn}
                             </h3>
                             
                             <div class="mt-4 ${nameBgClass} rounded-full px-6 py-2 backdrop-blur-sm">
                                <p class="text-2xl font-medium truncate max-w-[200px] ${nameTextClass}">${maskName(activeCall.patient_name)}</p>
                             </div>
                        </div>
                    `;
                } else {
                    // Idle (Grey) - Compact Design
                    return `
                        <div class="bg-slate-800/40 p-4 rounded-2xl border-2 border-slate-700/50 flex flex-col items-center justify-center text-center opacity-60 grayscale hover:opacity-80 transition-opacity">
                            <span class="text-xl text-slate-400 font-semibold block truncate">ห้อง ${room.room_name}</span>
                            <h3 class="text-5xl font-bold text-slate-600 tracking-tight my-2">ว่าง</h3>
                            <p class="text-sm text-slate-600 truncate">รอเรียก...</p>
                        </div>
                    `;
                }
            });

            // 2. Prepare Waiting List
            const waitingItems = allQueues.filter(q => q.status === 'waiting').map(q => `
                <div class="bg-white/5 p-3 rounded-xl flex justify-between items-center border border-white/5">
                    <div>
                        <span class="text-xl font-bold text-white block">${q.oqueue || q.vn}</span>
                        <div class="text-xs text-white/50 truncate max-w-[120px]">${maskName(q.patient_name)}</div>
                    </div>
                    <span class="text-sm bg-brand-500/20 text-brand-300 px-2 py-1 rounded-lg">ห้อง ${q.room_number}</span>
                </div>
            `);

            // 3. Render Current Page
            renderPagination(roomCards, waitingItems);
        }

        // Scroll State
        let marqueeInterval;
        let lastWaitingData = ''; // To prevent re-rendering and jumpiness

        function renderPagination(roomCards, waitingItems) {
            // Called Section - Keep Pagination (Cards)
            const totalCalledPages = Math.ceil(roomCards.length / CALLED_PAGE_SIZE) || 1;
            if (calledPage >= totalCalledPages) calledPage = 0;
            const startC = calledPage * CALLED_PAGE_SIZE;
            const currentRooms = roomCards.slice(startC, startC + CALLED_PAGE_SIZE);

            // Always update Called Container to reflect real-time status changes (Idle -> Active)
            calledContainer.innerHTML = currentRooms.join('') || `
                <div class="col-span-full h-40 flex items-center justify-center text-white/20 text-xl font-bold border-2 border-dashed border-white/10 rounded-3xl">Loading Rooms...</div>
            `;

            // Waiting Section - MARQUEE SCROLL
            // Only update DOM if data CHANGED
            const currentDataStr = JSON.stringify(waitingItems);
            if (currentDataStr === lastWaitingData) {
                return; // Data hasn't changed, let it scroll!
            }
            lastWaitingData = currentDataStr;

            // Stop any existing marquee
            stopMarquee();

            // Render ALL items
            if (waitingItems.length === 0) {
                waitingContainer.innerHTML = '<div class="text-center text-white/30 py-4">ไม่มีคิวรอ</div>';
                return;
            }

            // Render content normally first to check height
            waitingContainer.innerHTML = waitingItems.join('');

            // Allow DOM to update then check height
            setTimeout(() => {
                if (waitingContainer.scrollHeight > waitingContainer.clientHeight) {
                    // Overflow: Enable Marquee
                    const content = waitingContainer.innerHTML;
                    // Create a wrapper for smooth movement
                    waitingContainer.innerHTML = `
                        <div class="marquee-content flex flex-col gap-2">
                            ${content}
                            <!-- Duplicate for loop -->
                            ${content}
                        </div>
                     `;
                    startMarquee();
                } else {
                    // No overflow, just leave as is
                }
            }, 100);
        }

        function startMarquee() {
            stopMarquee(); // clear old
            const el = waitingContainer.querySelector('.marquee-content');
            if (!el) return;

            let pos = 0;
            function step() {
                pos += 0.5; // Speed
                const singleHeight = el.scrollHeight / 2;

                if (pos >= singleHeight) {
                    pos = 0; // seamless reset
                }
                el.style.transform = `translateY(-${pos}px)`;
                marqueeInterval = requestAnimationFrame(step);
            }
            marqueeInterval = requestAnimationFrame(step);
        }

        function stopMarquee() {
            if (marqueeInterval) cancelAnimationFrame(marqueeInterval);
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
            // "ขอเชิญหมายเลข ... ที่ห้อง ... ค่ะ"
            const text = `ขอเชิญหมายเลข ${item.oqueue || item.vn} ที่ห้องตรวจ ${item.room_number}ครับ`;

            // Mark time for blinking effect
            lastCallTimes[item.room_number] = Date.now();

            // Force re-render to start blink immediately
            processAndRender();

            // Schedule stop blink after 11 seconds (buffer)
            setTimeout(() => {
                processAndRender();
            }, 11000);

            ttsQueue.push({ text: text, lang: 'th-TH' });
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

            const voices = window.speechSynthesis.getVoices();
            console.log("Available Voices:", voices.length); // DEBUG

            const thaiVoice = voices.find(v => v.lang.includes('th') && (v.name.includes('Google') || v.name.includes('Premwadee') || v.name.includes('Kanya'))) || voices.find(v => v.lang.includes('th'));

            if (thaiVoice) {
                console.log("Selected Voice:", thaiVoice.name); // DEBUG
                utterance.voice = thaiVoice;
            } else {
                console.warn("No Thai Voice Found!"); // DEBUG
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
        window.speechSynthesis.onvoiceschanged = () => {
            console.log("Voices Changed/Loaded: " + window.speechSynthesis.getVoices().length);
        };

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