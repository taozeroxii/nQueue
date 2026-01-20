<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>nQueue - Menu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Prompt', sans-serif;
        }
    </style>
</head>

<body class="bg-slate-900 min-h-screen flex items-center justify-center p-4">

    <div class="max-w-4xl w-full">
        <div class="text-center mb-12">
            <h1 class="text-5xl font-bold text-white mb-2">nQueue System</h1>
            <p class="text-slate-400 text-xl">Select a module to continue</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

            <!-- Dashboard -->
            <a href="multipledisplay.php"
                class="group bg-slate-800 p-8 rounded-3xl border border-slate-700 hover:border-brand-500 hover:bg-slate-750 transition-all flex flex-col items-center text-center cursor-pointer shadow-lg hover:shadow-brand-500/20 hover:-translate-y-1">
                <div
                    class="w-20 h-20 bg-blue-500/20 rounded-full flex items-center justify-center mb-6 group-hover:scale-110 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-blue-400" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-white mb-2">Main Display</h2>
                <p class="text-slate-400">Dashboard for General Public (Waiting Hall)</p>
            </a>

            <!-- Room Display -->
            <a href="room.php"
                class="group bg-slate-800 p-8 rounded-3xl border border-slate-700 hover:border-emerald-500 hover:bg-slate-750 transition-all flex flex-col items-center text-center cursor-pointer shadow-lg hover:shadow-emerald-500/20 hover:-translate-y-1">
                <div
                    class="w-20 h-20 bg-emerald-500/20 rounded-full flex items-center justify-center mb-6 group-hover:scale-110 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-emerald-400" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-white mb-2">Room Display</h2>
                <p class="text-slate-400">Display for Individual Examination Room</p>
            </a>

            <!-- Management -->
            <a href="manage_queue.php"
                class="group bg-slate-800 p-8 rounded-3xl border border-slate-700 hover:border-orange-500 hover:bg-slate-750 transition-all flex flex-col items-center text-center cursor-pointer shadow-lg hover:shadow-orange-500/20 hover:-translate-y-1">
                <div
                    class="w-20 h-20 bg-orange-500/20 rounded-full flex items-center justify-center mb-6 group-hover:scale-110 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-orange-400" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-white mb-2">Queue Manager</h2>
                <p class="text-slate-400">Reorder, Delete, and Manage Lists</p>
            </a>

            <!-- Setup (Optional) -->
            <!-- Setup -->
            <div onclick="openSettings()"
                class="group bg-slate-800 p-8 rounded-3xl border border-slate-700 hover:border-purple-500 hover:bg-slate-750 transition-all flex flex-col items-center text-center cursor-pointer shadow-lg hover:shadow-purple-500/20 hover:-translate-y-1">
                <div
                    class="w-20 h-20 bg-purple-500/20 rounded-full flex items-center justify-center mb-6 group-hover:scale-110 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-purple-400" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-white mb-2">Settings</h2>
                <p class="text-slate-400">System Configuration</p>
            </div>

        </div>

        <footer class="text-center mt-12 text-slate-500">
            &copy; 2024 nQueue System
        </footer>
    </div>

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
            </div>

            <div class="flex justify-end gap-3 mt-8">
                <button onclick="closeSettings()"
                    class="px-4 py-2 text-slate-400 hover:text-white transition">Cancel</button>
                <button onclick="saveSettings()"
                    class="px-6 py-2 bg-purple-600 hover:bg-purple-500 text-white rounded-xl shadow-lg shadow-purple-500/20 transition-all font-semibold">Save
                    Changes</button>
            </div>
        </div>
    </div>

    <!-- Logic -->
    <script>
        // State
        let currentDeptFilter = localStorage.getItem('dept_filter') || '';
        let currentRoomFilter = localStorage.getItem('room_filter') || '';
        let ttsPrefix = localStorage.getItem('tts_prefix') || 'ขอเชิญหมายเลข';
        let ttsMiddle = localStorage.getItem('tts_middle') || 'ที่ห้องตรวจ';

        // Elements
        const modal = document.getElementById('settings-modal');
        const inputFilter = document.getElementById('input-dept-filter');
        const inputRoomFilter = document.getElementById('input-room-filter');
        const inputTtsPrefix = document.getElementById('input-tts-prefix');
        const inputTtsMiddle = document.getElementById('input-tts-middle');

        function openSettings() {
            modal.classList.remove('hidden');
            modal.classList.add('flex');

            // Load current values
            inputFilter.value = currentDeptFilter;
            inputTtsPrefix.value = ttsPrefix;
            inputTtsMiddle.value = ttsMiddle;

            // Populate Room Filter
            updateRoomFilterOptions(currentDeptFilter, currentRoomFilter);
        }

        function closeSettings() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function saveSettings() {
            // Save to LocalStorage
            currentDeptFilter = inputFilter.value;
            currentRoomFilter = inputRoomFilter.value;
            ttsPrefix = inputTtsPrefix.value || 'ขอเชิญหมายเลข';
            ttsMiddle = inputTtsMiddle.value || 'ที่ห้องตรวจ';

            localStorage.setItem('dept_filter', currentDeptFilter);
            localStorage.setItem('room_filter', currentRoomFilter);
            localStorage.setItem('tts_prefix', ttsPrefix);
            localStorage.setItem('tts_middle', ttsMiddle);

            closeSettings();
            alert("Settings Saved!");
        }

        async function onDeptFilterChange() {
            const dept = inputFilter.value;
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

        // Init
        async function loadInitData() {
            // Dept Options
            try {
                const res = await fetch('api/departments.php');
                const data = await res.json();
                if (data.success) {
                    const depts = data.data;
                    inputFilter.innerHTML = '<option value="">Show All</option>' +
                        depts.map(d => `<option value="${d}">${d}</option>`).join('');

                    if (currentDeptFilter) inputFilter.value = currentDeptFilter;
                }
            } catch (e) { }
        }
        loadInitData();
    </script>

    <!-- Script to add brand colors to config -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            500: '#3b82f6',
                        },
                        purple: {
                            400: '#c084fc',
                            500: '#a855f7',
                            600: '#9333ea',
                        }
                    }
                }
            }
        }
    </script>
</body>

</html>