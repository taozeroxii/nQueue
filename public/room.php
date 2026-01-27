<?php
$room = $_GET['room'] ?? 1; // Default fallback
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Queue Display</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Prompt', sans-serif;
        }

        .animate-pulse-slow {
            animation: pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
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

<body class="bg-slate-900 min-h-screen text-white flex flex-col items-center justify-center p-4 overflow-hidden"
    onclick="unlockAudio()">

    <!-- Sound Unlock Overlay -->
    <div id="sound-overlay"
        class="fixed inset-0 bg-black/80 z-[100] flex flex-col items-center justify-center cursor-pointer">
        <div class="bg-white/10 p-8 rounded-full mb-4 animate-bounce">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-white" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
            </svg>
        </div>
        <p class="text-2xl font-bold animate-pulse">Click anywhere to enable Sound</p>
    </div>

    <div class="w-full max-w-5xl flex-1 flex flex-col gap-6 h-full relative">

        <!-- Top Right Settings Button -->
        <div class="absolute top-0 right-0 z-50">
            <button onclick="openSettings()"
                class="bg-white/10 hover:bg-white/20 p-2 rounded-full text-white/50 hover:text-white transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </button>
        </div>

        <!-- Header -->
        <header class="text-center py-4 flex flex-col items-center">
            <h1 id="header-title" class="text-3xl md:text-4xl font-bold text-white mb-1">Loading...</h1>
            <p id="header-sub" class="text-brand-200/60 text-lg">Examination Room</p>
        </header>

        <!-- Current Queue -->
        <main class="flex-1 flex flex-col items-center justify-center min-h-0">
            <div id="current-queue"
                class="w-full rounded-[3rem] p-8 text-center flex flex-col items-center justify-center relative overflow-hidden bg-indigo-900/20 border border-white/20 shadow-lg shadow-indigo-900/40 min-h-[500px]">

                <div class="absolute -top-10 -right-10 w-40 h-40 bg-white/10 rounded-full blur-2xl pointer-events-none">
                </div>

                <span
                    class="text-3xl text-indigo-200 font-bold mb-4 uppercase tracking-widest opacity-90">หมายเลขที่กำลังรับบริการ</span>

                <div id="q-number"
                    class="text-[14rem] leading-none font-black text-white my-4 tracking-tighter table-nums">...</div>

                <div class="mt-4 bg-black/20 rounded-full px-8 py-3 backdrop-blur-sm max-w-full">
                    <div id="q-name" class="text-4xl text-white font-medium truncate">Waiting...</div>
                </div>
            </div>
        </main>

        <!-- Next few list -->
        <footer class="h-auto py-6 grid grid-cols-3 gap-6">
            <div
                class="bg-slate-900/50 rounded-2xl p-4 border border-white/10 flex flex-col items-center justify-center">
                <span class="text-slate-400 text-sm font-medium uppercase tracking-wider mb-1">Next 1</span>
                <span id="next-1" class="text-4xl font-bold text-white">-</span>
            </div>
            <div
                class="bg-slate-900/50 rounded-2xl p-4 border border-white/10 flex flex-col items-center justify-center opacity-80">
                <span class="text-slate-400 text-sm font-medium uppercase tracking-wider mb-1">Next 2</span>
                <span id="next-2" class="text-4xl font-bold text-slate-300">-</span>
            </div>
            <div
                class="bg-slate-900/50 rounded-2xl p-4 border border-white/10 flex flex-col items-center justify-center opacity-60">
                <span class="text-slate-400 text-sm font-medium uppercase tracking-wider mb-1">Next 3</span>
                <span id="next-3" class="text-4xl font-bold text-slate-500">-</span>
            </div>
        </footer>

    </div>

    <!-- Settings Modal -->
    <div id="settings-modal"
        class="fixed inset-0 bg-slate-900/95 z-[60] hidden flex-col items-center justify-center backdrop-blur-md">
        <div
            class="text-center max-w-md w-full p-8 bg-slate-800 rounded-3xl border border-white/10 shadow-2xl overflow-y-auto max-h-[90vh]">
            <h1 class="text-3xl font-bold text-white mb-6">Display Settings</h1>

            <div class="flex flex-col gap-4 text-left">

                <!-- Department & Room Selection -->
                <div>
                    <label class="block text-slate-400 mb-2 text-sm">Select Department</label>
                    <select id="sel-dept"
                        class="w-full bg-slate-900 text-white p-3 rounded-xl border border-slate-700 focus:border-brand-500 focus:outline-none">
                        <option value="">Loading Depts...</option>
                    </select>
                </div>

                <div>
                    <label class="block text-slate-400 mb-2 text-sm">Select Room</label>
                    <select id="sel-room"
                        class="w-full bg-slate-900 text-white p-3 rounded-xl border border-slate-700 focus:border-brand-500 focus:outline-none"
                        disabled>
                        <option value="">Select Dept First</option>
                    </select>
                </div>

                <!-- Sound Config -->
                <div>
                    <label class="block text-slate-400 mb-2 text-sm">Sound Play Count (times)</label>
                    <input type="number" id="input-tts-repeat"
                        class="w-full bg-slate-900 border border-slate-700 rounded-lg p-2 text-white text-sm focus:border-brand-500 focus:outline-none"
                        min="0" max="10" value="1">
                    <p class="text-xs text-slate-500 mt-1">Number of times to announce the queue</p>
                </div>

                <!-- API & WS Config -->
                <div class="border-t border-slate-700 pt-4 mt-2">
                    <h3 class="text-white font-semibold mb-3 text-sm">Advanced Connection</h3>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-slate-400 mb-1 text-xs">API Base URL</label>
                            <input type="text" id="input-api-base"
                                class="w-full bg-slate-900 border border-slate-700 rounded-lg p-2 text-white text-sm focus:border-brand-500 focus:outline-none"
                                placeholder="e.g. http://localhost/nQueue/public/">
                        </div>
                        <div>
                            <label class="block text-slate-400 mb-1 text-xs">WebSocket URL</label>
                            <input type="text" id="input-ws-url"
                                class="w-full bg-slate-900 border border-slate-700 rounded-lg p-2 text-white text-sm focus:border-brand-500 focus:outline-none"
                                placeholder="e.g. ws://localhost:8765">
                        </div>
                    </div>
                </div>

                <div class="flex gap-3 mt-6">
                    <button onclick="closeSettings()"
                        class="flex-1 py-3 text-slate-400 hover:text-white transition">Cancel</button>
                    <button onclick="saveSettings()"
                        class="flex-1 py-3 bg-brand-600 hover:bg-brand-500 text-white rounded-xl font-bold shadow-lg shadow-brand-500/20 transition-all">SAVE
                        & START</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // State
        let roomId = localStorage.getItem('display_room_id');
        let selectedRoomName = localStorage.getItem('display_room_name');

        let apiBase = localStorage.getItem('api_base') || '';
        let wsUrl = localStorage.getItem('ws_url') || 'ws://localhost:8765';
        let ttsRepeat = parseInt(localStorage.getItem('tts_repeat')) || 1;

        // Elements
        const modal = document.getElementById('settings-modal');
        const selDept = document.getElementById('sel-dept');
        const selRoom = document.getElementById('sel-room');
        const inputApiBase = document.getElementById('input-api-base');
        const inputWsUrl = document.getElementById('input-ws-url');
        const inputTtsRepeat = document.getElementById('input-tts-repeat');
        const soundOverlay = document.getElementById('sound-overlay');

        // Helper: API URL
        const getApiUrl = (endpoint) => {
            if (!apiBase) return endpoint;
            const cleanBase = apiBase.replace(/\/+$/, '');
            const cleanEndpoint = endpoint.replace(/^\/+/, '');
            return `${cleanBase}/${cleanEndpoint}`;
        };

        // Init
        async function init() {
            if (!roomId) {
                openSettings();
            } else {
                updateHeader();
                fetchRoomQueue();
                setInterval(fetchRoomQueue, 60000);
                connectWS();
            }
        }

        function updateHeader() {
            if (selectedRoomName) {
                document.getElementById('header-title').innerText = `ห้องตรวจที่ ${selectedRoomName}`;
                document.getElementById('header-sub').innerText = `Examination Room ${selectedRoomName}`;
            } else {
                document.getElementById('header-title').innerText = `Room ${roomId}`;
            }
        }

        async function openSettings() {
            modal.classList.remove('hidden');
            modal.classList.add('flex');

            // Populate Inputs
            inputApiBase.value = apiBase;
            inputWsUrl.value = wsUrl;
            inputTtsRepeat.value = ttsRepeat;

            await loadDepts();
        }

        function closeSettings() {
            if (!roomId) return;
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            // Stop props to body onclick
            event?.stopPropagation();
        }

        async function loadDepts() {
            try {
                const r = await fetch(getApiUrl('api/departments.php'));
                const d = await r.json();
                if (d.success) {
                    selDept.innerHTML = '<option value="">-- Select Department --</option>' +
                        d.data.map(n => `<option value="${n}">${n}</option>`).join('');
                }
            } catch (e) { }
        }

        selDept.addEventListener('change', async () => {
            const dept = selDept.value;
            if (!dept) { selRoom.disabled = true; return; }

            try {
                const r = await fetch(getApiUrl(`api/rooms.php?department=${dept}`));
                const d = await r.json();
                if (d.success) {
                    selRoom.innerHTML = d.data.map(r => `<option value="${r.id}">${r.room_name}</option>`).join('');
                    selRoom.disabled = false;
                }
            } catch (e) { }
        });

        function saveSettings() {
            const rid = selRoom.value;
            const rname = selRoom.options[selRoom.selectedIndex]?.text;

            if (rid) {
                roomId = rid;
                selectedRoomName = rname;
                localStorage.setItem('display_room_id', rid);
                localStorage.setItem('display_room_name', rname);
            }

            if (!roomId) {
                alert("Please select a room first!");
                return;
            }

            apiBase = inputApiBase.value.trim();
            const newWsUrl = inputWsUrl.value.trim();
            const wsChanged = newWsUrl !== wsUrl;
            wsUrl = newWsUrl || 'ws://localhost:8765';

            ttsRepeat = parseInt(inputTtsRepeat.value) || 1;

            localStorage.setItem('api_base', apiBase);
            localStorage.setItem('ws_url', wsUrl);
            localStorage.setItem('tts_repeat', ttsRepeat);

            closeSettings();
            updateHeader();
            fetchRoomQueue();

            if (wsChanged) {
                if (window.wsSocket) window.wsSocket.close();
                setTimeout(connectWS, 500);
            }
        }

        let latestQueues = [];
        let lastCallTime = 0;

        async function fetchRoomQueue() {
            if (!roomId) return;
            try {
                const res = await fetch(getApiUrl(`api/queue_data.php?room=${roomId}&limit=50&_=${Date.now()}`));
                const data = await res.json();
                if (data.success) {
                    console.log("Queue Data:", data.data);
                    latestQueues = data.data;
                    updateDisplay();
                }
            } catch (e) { console.error("Fetch Error", e); }
        }

        function updateDisplay() {
            const queues = latestQueues;
            // Fix: Find the LATEST called queue based on timestamp
            const calledQueues = queues.filter(q => q.status === 'called');
            let current = null;

            if (calledQueues.length > 0) {
                current = calledQueues.reduce((prev, curr) => {
                    const prevTime = new Date(prev.updated_at || prev.created_at).getTime();
                    const currTime = new Date(curr.updated_at || curr.created_at).getTime();
                    return (prevTime > currTime) ? prev : curr;
                });
            }

            const waiting = queues.filter(q => q.status === 'waiting');

            const qNum = document.getElementById('q-number');
            const qName = document.getElementById('q-name');
            const container = document.getElementById('current-queue');

            // Masking Helper
            const maskName = (name) => {
                if (!name) return '';
                const parts = name.trim().split(/\s+/);
                const maskText = (text) => {
                    if (!text || text.length <= 2) return text;
                    return text.substring(0, 2) + 'x'.repeat(text.length - 2);
                };

                if (parts.length > 0) {
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
                    if (lastName) return `${firstName} ${maskText(lastName)}`;
                    return firstName;
                }
                return name;
            };

            if (current) {
                const newNum = current.oqueue || current.vn;
                const updateTime = current.updated_at || current.created_at || Date.now();
                const uniqueKey = `${current.id}_${updateTime}`;

                if (window.lastFlashKey !== uniqueKey) {
                    window.lastFlashKey = uniqueKey;

                    // New Call Detected
                    lastCallTime = Date.now();
                    speakQueue(current);

                    // Re-render in 11s to stop blinking
                    setTimeout(updateDisplay, 11000);
                }

                // Determine Visual State based on Time
                const isBlinking = (Date.now() - lastCallTime) < 11000;

                if (isBlinking) {
                    // Yellow / Blinking State
                    container.classList.remove('bg-indigo-900/20', 'border-white/20');
                    container.classList.add('bg-yellow-400', 'text-slate-900', 'border-yellow-200', 'animate-pulse', 'shadow-yellow-500/50');

                    qNum.classList.remove('text-white');
                    qNum.classList.add('text-slate-900');

                    qName.classList.remove('text-white');
                    qName.classList.add('text-slate-800');

                    const titleEl = document.querySelector('#current-queue span');
                    if (titleEl) {
                        titleEl.classList.remove('text-indigo-200');
                        titleEl.classList.add('text-slate-800');
                    }
                } else {
                    // Normal State
                    container.classList.add('bg-indigo-900/20', 'border-white/20');
                    container.classList.remove('bg-yellow-400', 'text-slate-900', 'border-yellow-200', 'animate-pulse', 'shadow-yellow-500/50');

                    qNum.classList.add('text-white');
                    qNum.classList.remove('text-slate-900');

                    qName.classList.add('text-white');
                    qName.classList.remove('text-slate-800');

                    const titleEl = document.querySelector('#current-queue span');
                    if (titleEl) {
                        titleEl.classList.add('text-indigo-200');
                        titleEl.classList.remove('text-slate-800');
                    }
                }

                qNum.innerText = newNum;
                qName.innerText = maskName(current.patient_name);
            } else {
                // No Active Queue
                container.classList.add('bg-indigo-900/20', 'border-white/20');
                container.classList.remove('bg-yellow-400', 'text-slate-900', 'border-yellow-200', 'animate-pulse', 'shadow-yellow-500/50');

                qNum.classList.add('text-white');
                qNum.classList.remove('text-slate-900');

                qName.classList.add('text-white');
                qName.classList.remove('text-slate-800');

                const titleEl = document.querySelector('#current-queue span');
                if (titleEl) {
                    titleEl.classList.add('text-indigo-200');
                    titleEl.classList.remove('text-slate-800');
                }

                qNum.innerText = "-";
                qName.innerText = "No Active Queue";
            }

            // Waiting
            document.getElementById('next-1').innerText = waiting[0] ? (waiting[0].oqueue || waiting[0].vn) : '-';
            document.getElementById('next-2').innerText = waiting[1] ? (waiting[1].oqueue || waiting[1].vn) : '-';
            document.getElementById('next-3').innerText = waiting[2] ? (waiting[2].oqueue || waiting[2].vn) : '-';
        }

        // --- AUDIO Logic ---
        let audioUnlocked = false;
        function unlockAudio() {
            if (audioUnlocked) return;
            const a = new Audio("Prompt4/Prompt4_Number.wav");
            a.volume = 0;
            a.play().then(() => {
                a.pause();
                audioUnlocked = true;
                soundOverlay.classList.add('hidden');
                console.log("Audio Unlocked");
            }).catch(e => console.error("Unlock failed", e));
        }

        let ttsQueue = [];
        let isSpeaking = false;

        function speakQueue(item) {
            console.log("Speaking:", item.oqueue || item.vn);
            const numStr = item.oqueue || item.vn;
            const num = parseInt(numStr);
            if (isNaN(num)) return;

            const numberFiles = getThaiNumberFiles(num);
            const roomNum = parseInt(item.room_number);
            const roomFiles = (!isNaN(roomNum)) ? getThaiNumberFiles(roomNum) : [];

            const files = [
                'Prompt4/Prompt4_Number.wav',
                ...numberFiles,
                'Prompt4/Prompt4_Service.wav',
                ...roomFiles,
                'Prompt4/Prompt4_Sir.wav'
            ];

            // Repeat
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
            } catch (e) { console.error("Audio Failed", e); }
            isSpeaking = false;
            setTimeout(processTTSQueue, 500);
        }

        function playAudioSequence(files) {
            return new Promise(async (resolve, reject) => {
                for (const file of files) {
                    try { await playSingleFile(file); } catch (e) { }
                }
                resolve();
            });
        }

        function playSingleFile(url) {
            return new Promise((resolve, reject) => {
                const audio = new Audio(url);
                audio.onended = resolve;
                audio.onerror = () => { console.error("File error", url); resolve(); };
                audio.play().catch(resolve);
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
                    files.push('Prompt4/Prompt4_10.wav');
                    if (ones === 1) files.push('Prompt4/Prompt4_11-1.wav');
                    else if (ones > 1) files.push(`Prompt4/Prompt4_${ones}.wav`);
                    return files;
                }
                if (tens === 2) {
                    files.push('Prompt4/Prompt4_20.wav');
                    if (ones === 1) files.push('Prompt4/Prompt4_11-1.wav');
                    else if (ones > 1) files.push(`Prompt4/Prompt4_${ones}.wav`);
                    return files;
                }
                files.push(`Prompt4/Prompt4_${tens}.wav`);
                files.push('Prompt4/Prompt4_10.wav');
                if (ones === 1) files.push('Prompt4/Prompt4_11-1.wav');
                else if (ones > 1) files.push(`Prompt4/Prompt4_${ones}.wav`);
                return files;
            }
            if (num > 0) files.push(`Prompt4/Prompt4_${num}.wav`);
            return files;
        }

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
                    fetchRoomQueue();
                } catch (e) { fetchRoomQueue(); }
            };
            socket.onclose = function () {
                document.body.style.borderTop = "4px solid #ef4444";
                setTimeout(connectWS, 3000);
            };
        }
        connectWS();
        init();
    </script>
</body>

</html>