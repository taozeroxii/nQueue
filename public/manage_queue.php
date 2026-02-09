<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Queues</title>
    <script src="assets/vendor/tailwind/tailwind.js"></script>
    <link href="assets/vendor/css/prompt.css" rel="stylesheet">
    <script src="assets/vendor/sweetalert2/sweetalert2.js"></script>
    <style>
        body {
            font-family: 'Prompt', sans-serif;
        }
    </style>
</head>

<body class="bg-gray-100 min-h-screen p-8">

    <div class="max-w-4xl mx-auto bg-white rounded-xl shadow-lg p-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-6 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
            </svg>
            Queue Management
        </h1>

        <div class="flex flex-col md:flex-row gap-4 mb-6">
            <select id="dept-filter" class="border border-gray-300 rounded-lg px-4 py-2 w-full md:w-1/3"
                onchange="onDeptChange()">
                <option value="">เลือกแผนก...</option>
                <!-- Injected via JS -->
            </select>
            <select id="room-filter" class="border border-gray-300 rounded-lg px-4 py-2 w-full md:w-1/3"
                onchange="fetchList()" disabled>
                <option value="">เลือกห้อง...</option>
                <!-- Injected via JS -->
            </select>
            <button onclick="fetchList()"
                class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">Refresh</button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                        <th class="py-3 px-6 text-center">Priority</th>
                        <th class="py-3 px-6 text-center">Queue No</th>
                        <th class="py-3 px-6 text-left">Patient Name</th>
                        <th class="py-3 px-6 text-center">Room</th>
                        <th class="py-3 px-6 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="queue-table-body" class="text-gray-600 text-sm font-light">
                    <!-- Rows -->
                </tbody>
            </table>
        </div>
    </div>

    <script>
        const deptSelect = document.getElementById('dept-filter');
        const roomSelect = document.getElementById('room-filter');
        const tbody = document.getElementById('queue-table-body');

        // Initial Load - Fetch Departments
        async function init() {
            try {
                const res = await fetch('api/departments.php');
                const data = await res.json();
                if (data.success) {
                    data.data.forEach(d => {
                        const opt = document.createElement('option');
                        opt.value = d;
                        opt.text = d;
                        deptSelect.appendChild(opt);
                    });
                }
            } catch (e) { console.error(e); }
        }
        init();

        // When Department changes -> Load Rooms
        async function onDeptChange() {
            const dept = deptSelect.value;
            roomSelect.innerHTML = '<option value="">เลือกห้อง...</option>';
            roomSelect.disabled = true;
            tbody.innerHTML = '<tr><td colspan="5" class="text-center py-6 text-gray-400">กรุณาเลือกห้อง</td></tr>';

            if (!dept) return;

            try {
                const res = await fetch(`api/rooms.php?department=${encodeURIComponent(dept)}`);
                const data = await res.json();
                if (data.success && data.data.length > 0) {
                    data.data.forEach(r => {
                        const opt = document.createElement('option');
                        opt.value = r.id;
                        opt.text = `ห้อง ${r.room_name}`;
                        roomSelect.appendChild(opt);
                    });
                    roomSelect.disabled = false;
                }
            } catch (e) { console.error(e); }
        }

        // Fetch Queue List for selected Room
        async function fetchList() {
            const room = roomSelect.value;
            if (!room) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center py-6 text-gray-400">กรุณาเลือกห้อง</td></tr>';
                return;
            }

            let url = `api/manage_queue.php?room=${room}`;

            try {
                const res = await fetch(url);
                const data = await res.json();
                renderTable(data.data || []);
            } catch (e) { console.error(e); }
        }

        function renderTable(list) {
            tbody.innerHTML = list.map((item, index) => `
                <tr class="border-b border-gray-200 hover:bg-gray-100">
                    <td class="py-3 px-6 text-center font-bold text-blue-600">${item.display_order}</td>
                    <td class="py-3 px-6 text-center whitespace-nowrap font-medium">${item.oqueue || item.vn}</td>
                    <td class="py-3 px-6 text-left">
                        <div class="flex items-center">
                            <span class="font-medium">${item.patient_name}</span>
                        </div>
                    </td>
                    <td class="py-3 px-6 text-center">
                        <span class="bg-blue-200 text-blue-600 py-1 px-3 rounded-full text-xs">${item.room_number}</span>
                    </td>
                    <td class="py-3 px-6 text-center">
                        <div class="flex item-center justify-center gap-2">
                             <button onclick="moveItem(${item.id}, 'up')" class="w-8 h-8 rounded-full bg-slate-200 hover:bg-slate-300 flex items-center justify-center text-slate-600 transition" title="Move Up">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                </svg>
                            </button>
                            <button onclick="moveItem(${item.id}, 'down')" class="w-8 h-8 rounded-full bg-slate-200 hover:bg-slate-300 flex items-center justify-center text-slate-600 transition" title="Move Down">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <button onclick="deleteItem(${item.id})" class="w-8 h-8 rounded-full bg-red-100 hover:bg-red-200 flex items-center justify-center text-red-500 transition ml-2" title="Delete">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
            `).join('') || '<tr><td colspan="5" class="text-center py-6 text-gray-400">No queues found</td></tr>';
        }

        async function moveItem(id, direction) {
            try {
                const res = await fetch('api/manage_queue.php', {
                    method: 'POST',
                    body: JSON.stringify({ action: 'move', id, direction })
                });
                const data = await res.json();
                if (data.success) {
                    fetchList();
                } else {
                    Swal.fire({ toast: true, position: 'top-end', icon: 'info', title: data.message, showConfirmButton: false, timer: 1500 });
                }
            } catch (e) { }
        }

        async function deleteItem(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: "Delete this queue?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    try {
                        const res = await fetch('api/manage_queue.php', {
                            method: 'POST',
                            body: JSON.stringify({ action: 'delete', id })
                        });
                        const data = await res.json();
                        if (data.success) {
                            fetchList();
                            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Deleted', showConfirmButton: false, timer: 1500 });
                        }
                    } catch (e) { }
                }
            })
        }
    </script>
</body>

</html>