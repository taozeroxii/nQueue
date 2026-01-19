<?php
$room = $_GET['room'] ?? 1; // Default to room 1
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room
        <?php echo $room; ?> Queue
    </title>
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
    </style>
</head>

<body class="bg-zinc-900 min-h-screen text-white flex flex-col items-center justify-center p-4">

    <div class="w-full max-w-4xl flex-1 flex flex-col gap-6">
        <!-- Header -->
        <header class="text-center py-6">
            <h1 class="text-4xl md:text-5xl font-bold text-blue-400">ห้องตรวจที่
                <?php echo $room; ?>
            </h1>
            <p class="text-zinc-500 text-xl mt-2">Examination Room
                <?php echo $room; ?>
            </p>
        </header>

        <!-- Current Queue -->
        <main class="flex-1 flex flex-col items-center justify-center">

            <div id="current-queue"
                class="glass-panel w-full rounded-[3rem] p-12 text-center border-t-8 border-t-blue-500 min-h-[400px] flex flex-col items-center justify-center shadow-[0_0_100px_rgba(59,130,246,0.2)]">
                <span class="text-3xl text-zinc-400 font-medium mb-4 uppercase tracking-widest">Current Queue</span>
                <div id="q-number" class="text-[10rem] leading-none font-bold text-zinc-800 my-4 table-nums">...</div>
                <div id="q-name" class="text-4xl text-zinc-500 font-semibold">Waiting...</div>
            </div>

        </main>

        <!-- Next few list -->
        <footer class="h-48 grid grid-cols-3 gap-4">
            <!-- Javascript assumes 3 waiting slots -->
            <div
                class="bg-zinc-800 rounded-2xl p-4 border border-zinc-700 flex flex-col items-center justify-center opacity-60">
                <span class="text-zinc-500 text-sm">Next 1</span>
                <span id="next-1" class="text-4xl font-bold text-zinc-300">-</span>
            </div>
            <div
                class="bg-zinc-800 rounded-2xl p-4 border border-zinc-700 flex flex-col items-center justify-center opacity-40">
                <span class="text-zinc-500 text-sm">Next 2</span>
                <span id="next-2" class="text-3xl font-bold text-zinc-400">-</span>
            </div>
            <div
                class="bg-zinc-800 rounded-2xl p-4 border border-zinc-700 flex flex-col items-center justify-center opacity-20">
                <span class="text-zinc-500 text-sm">Next 3</span>
                <span id="next-3" class="text-2xl font-bold text-zinc-500">-</span>
            </div>
        </footer>

    </div>

    <!-- Setup Overlay -->
    <div id="setup-overlay" class="fixed inset-0 bg-slate-900 z-[60] hidden flex-col items-center justify-center">
        <div class="text-center max-w-md w-full p-6">
            <h1 class="text-4xl font-bold text-white mb-2">Display Setup</h1>
            <p class="text-slate-400 mb-8">Select Department & Room</p>

            <div class="flex flex-col gap-4">
                <select id="sel-dept" class="bg-gray-800 text-white p-4 rounded-xl text-lg border border-gray-700">
                    <option value="">Loading Deuts...</option>
                </select>
                <select id="sel-room" class="bg-gray-800 text-white p-4 rounded-xl text-lg border border-gray-700"
                    disabled>
                    <option value="">Select Dept First</option>
                </select>
                <button onclick="saveSetup()"
                    class="bg-blue-600 hover:bg-blue-500 text-white p-4 rounded-xl font-bold text-xl mt-4">START
                    DISPLAY</button>
            </div>
        </div>
    </div>

    <script>
        let roomId = localStorage.getItem('display_room_id');
        const overlay = document.getElementById('setup-overlay');
        const selDept = document.getElementById('sel-dept');
        const selRoom = document.getElementById('sel-room');

        // Init
        async function init() {
            if (!roomId) {
                overlay.classList.remove('hidden');
                overlay.classList.add('flex');
                loadDepts(); // Only load if needed
            } else {
                fetchRoomQueue();
                setInterval(fetchRoomQueue, 30000);
            }
        }

        async function loadDepts() {
            try {
                const r = await fetch('api/departments.php');
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
                const r = await fetch(`api/rooms.php?department=${dept}`);
                const d = await r.json();
                if (d.success) {
                    selRoom.innerHTML = d.data.map(r => `<option value="${r.id}">${r.room_name}</option>`).join('');
                    selRoom.disabled = false;
                }
            } catch (e) { }
        });

        function saveSetup() {
            const rid = selRoom.value;
            if (rid) {
                roomId = rid;
                localStorage.setItem('display_room_id', rid);
                overlay.classList.add('hidden');
                overlay.classList.remove('flex');

                // Update Title (Need reload or DOM update? Reload is easier for PHP echo replacements)
                // But we used PHP echo $room. Now we are fully JS client-side for title? 
                // Let's update DOM elements manually if we don't reload.
                // Reloading with ?room=ID is safer if we keep PHP echo, BUT user wants LocalStorage specific.
                // We should remove PHP echo usage and use JS to set titles.

                // Update UI titles
                document.querySelector('h1').innerText = `ห้องตรวจที่ ${selRoom.options[selRoom.selectedIndex].text}`;

                fetchRoomQueue();
                setInterval(fetchRoomQueue, 30000);
            }
        }

        async function fetchRoomQueue() {
            if (!roomId) return;
            try {
                const res = await fetch(`api/queue_data.php?room=${roomId}&limit=5`);
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
                return parts.length > 1 ? `${parts[0]} xxx` : name;
            };

            if (current) {
                qNum.innerText = current.oqueue || current.vn;
                qName.innerText = maskName(current.patient_name);
                // Add flash effect if it's new? (Store prev ID to check)
                container.classList.remove('border-t-zinc-500');
                container.classList.add('border-t-blue-500');
            } else {
                qNum.innerText = "-";
                qName.innerText = "No Active Queue";
                container.classList.add('border-t-zinc-500');
                container.classList.remove('border-t-blue-500');
            }

            // Waiting
            document.getElementById('next-1').innerText = waiting[0] ? (waiting[0].oqueue || waiting[0].vn) : '-';
            document.getElementById('next-2').innerText = waiting[1] ? (waiting[1].oqueue || waiting[1].vn) : '-';
            document.getElementById('next-3').innerText = waiting[2] ? (waiting[2].oqueue || waiting[2].vn) : '-';
        }

        // WebSocket
        function connectWS() {
            const socket = new WebSocket('ws://localhost:8765');
            socket.onopen = () => console.log('Room Connected');
            socket.onmessage = (e) => {
                console.log('Update', e.data);
                fetchRoomQueue();
            };
            socket.onclose = () => setTimeout(connectWS, 3000);
        }

        connectWS();
        init();
    </script>
</body>

</html>