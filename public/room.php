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

<body class="bg-slate-900 min-h-screen text-white flex flex-col items-center justify-center p-4 overflow-hidden">

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

                <span class="text-3xl text-indigo-200 font-bold mb-4 uppercase tracking-widest opacity-90">Current
                    Queue</span>

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
        <div class="text-center max-w-md w-full p-8 bg-slate-800 rounded-3xl border border-white/10 shadow-2xl">
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

        // Elements
        const modal = document.getElementById('settings-modal');
        const selDept = document.getElementById('sel-dept');
        const selRoom = document.getElementById('sel-room');
        const inputApiBase = document.getElementById('input-api-base');
        const inputWsUrl = document.getElementById('input-ws-url');

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
                setInterval(fetchRoomQueue, 5000);
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

            await loadDepts();
        }

        function closeSettings() {
            // Cannot close if no room selected (first load)
            if (!roomId) return;
            modal.classList.add('hidden');
            modal.classList.remove('flex');
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

            // Only require selection if we don't have one yet or user explicitly selected something
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

            // Save Connection Config
            const newApiBase = inputApiBase.value.trim();
            const newWsUrl = inputWsUrl.value.trim();
            const wsChanged = newWsUrl !== wsUrl;

            apiBase = newApiBase;
            wsUrl = newWsUrl || 'ws://localhost:8765';

            localStorage.setItem('api_base', apiBase);
            localStorage.setItem('ws_url', wsUrl);

            closeSettings();
            updateHeader();
            fetchRoomQueue();

            if (wsChanged) {
                if (window.wsSocket) window.wsSocket.close();
                setTimeout(connectWS, 500);
            }
        }

        let flashTimeout;

        async function fetchRoomQueue() {
            if (!roomId) return;
            try {
                const res = await fetch(getApiUrl(`api/queue_data.php?room=${roomId}&limit=5`));
                const data = await res.json();
                if (data.success) {
                    updateDisplay(data.data);
                }
            } catch (e) { console.error(e); }
        }

        function updateDisplay(queues) {
            const current = queues.find(q => q.status === 'called');
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

                if (qNum.innerText !== newNum) {
                    // Flash effect
                    container.classList.remove('bg-indigo-900/20', 'border-white/20');
                    container.classList.add('bg-yellow-400', 'text-slate-900', 'border-yellow-200');
                    qNum.classList.remove('text-white');
                    qNum.classList.add('text-slate-900');
                    qName.classList.remove('text-white');
                    qName.classList.add('text-slate-800');
                    document.querySelector('.text-indigo-200').classList.remove('text-indigo-200');

                    clearTimeout(flashTimeout);
                    flashTimeout = setTimeout(() => {
                        container.classList.add('bg-indigo-900/20', 'border-white/20');
                        container.classList.remove('bg-yellow-400', 'text-slate-900', 'border-yellow-200');
                        qNum.classList.add('text-white');
                        qNum.classList.remove('text-slate-900');
                        qName.classList.add('text-white');
                        qName.classList.remove('text-slate-800');
                        container.querySelector('span').classList.add('text-indigo-200');
                    }, 5000);
                }

                qNum.innerText = newNum;
                qName.innerText = maskName(current.patient_name);
            } else {
                qNum.innerText = "-";
                qName.innerText = "No Active Queue";
            }

            // Waiting
            document.getElementById('next-1').innerText = waiting[0] ? (waiting[0].oqueue || waiting[0].vn) : '-';
            document.getElementById('next-2').innerText = waiting[1] ? (waiting[1].oqueue || waiting[1].vn) : '-';
            document.getElementById('next-3').innerText = waiting[2] ? (waiting[2].oqueue || waiting[2].vn) : '-';
        }

        // WebSocket
        function connectWS() {
            if (window.wsSocket) {
                // If exists and same url, no op (or close/reopen?)
                // Just close and reopen to be safe if called from Settings
            }

            console.log("Connecting WS to", wsUrl);
            const socket = new WebSocket(wsUrl);
            window.wsSocket = socket;

            socket.onopen = () => console.log('Room WS Connected');
            socket.onmessage = (e) => {
                console.log('Update', e.data);
                fetchRoomQueue();
            };
            socket.onclose = () => {
                console.log("WS Closed, retrying...");
                setTimeout(connectWS, 3000);
            };
            socket.onerror = (e) => console.log("WS Error", e);
        }

        init();
    </script>
</body>

</html>