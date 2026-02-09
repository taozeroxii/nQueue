<?php
require __DIR__ . '/../vendor/autoload.php';
use App\Database;

$db = new Database();
$mysql = $db->getMySQL();

// Get Selected Department and Room
$selectedDept = $_GET['dept'] ?? null;
$room = $_GET['room'] ?? null;

// Fetch Departments
$stmt = $mysql->query("SELECT DISTINCT department FROM rooms WHERE department IS NOT NULL AND department != '' ORDER BY department ASC");
$departments = $stmt->fetchAll(PDO::FETCH_COLUMN);

// If department is selected, fetch rooms for that department
$rooms = [];
if ($selectedDept) {
    $stmt = $mysql->prepare("SELECT id, room_name FROM rooms WHERE department = ? ORDER BY room_name ASC");
    $stmt->execute([$selectedDept]);
    $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // If room is not set or invalid for this department, default to the first one
    if (!$room && count($rooms) > 0) {
        $room = $rooms[0]['id'];
    } elseif ($room) {
        // Verify room belongs to department
        $validRoom = false;
        foreach ($rooms as $r) {
            if ($r['id'] == $room) {
                $validRoom = true;
                break;
            }
        }
        if (!$validRoom && count($rooms) > 0) {
            $room = $rooms[0]['id'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kiosk <?php echo $selectedDept ? "- $selectedDept" : ""; ?></title>
    <script src="assets/vendor/tailwind/tailwind.js"></script>
    <script src="assets/vendor/sweetalert2/sweetalert2.js"></script>
    <link href="assets/vendor/css/prompt.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Prompt', sans-serif;
        }

        .scan-line {
            height: 2px;
            width: 100%;
            background: #ef4444;
            box-shadow: 0 0 10px #ef4444;
            animation: scan 2s linear infinite;
        }

        /* Hide scrollbar for room tabs */
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        @keyframes scan {
            0% {
                transform: translateY(0);
                opacity: 0;
            }

            10% {
                opacity: 1;
            }

            90% {
                opacity: 1;
            }

            100% {
                transform: translateY(300px);
                opacity: 0;
            }
        }
    </style>
</head>

<body class="bg-gray-100 min-h-screen flex flex-col items-center justify-center p-4">

    <?php if (!$selectedDept): ?>
        <!-- Department Selection Screen -->
        <div class="w-full max-w-4xl text-center">
            <h1 class="text-4xl font-bold text-gray-800 mb-2">เลือกแผนก</h1>
            <p class="text-gray-500 mb-8">Select Department</p>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($departments as $dept): ?>
                    <a href="?dept=<?php echo urlencode($dept); ?>"
                        class="group bg-white p-8 rounded-2xl shadow-lg hover:shadow-xl transition-all border-2 border-transparent hover:border-indigo-500 flex flex-col items-center justify-center h-48">
                        <div
                            class="w-16 h-16 bg-indigo-50 text-indigo-600 rounded-full flex items-center justify-center mb-4 group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-800 group-hover:text-indigo-600 transition-colors">
                            <?php echo htmlspecialchars($dept); ?>
                        </h2>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

    <?php else: ?>
        <!-- Kiosk Screen -->

        <!-- Back Button & Rooms -->
        <div
            class="fixed top-0 left-0 right-0 p-4 z-50 bg-white/80 backdrop-blur shadow-sm flex items-center justify-between">
            <a href="kiosk2.php" class="flex items-center text-gray-500 hover:text-indigo-600 font-bold px-4 py-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back
            </a>

            <div class="flex space-x-2 overflow-x-auto no-scrollbar mx-4">
                <?php foreach ($rooms as $r): ?>
                    <?php $isActive = ($r['id'] == $room); ?>
                    <a href="?dept=<?php echo urlencode($selectedDept); ?>&room=<?php echo $r['id']; ?>"
                        class="px-6 py-2 rounded-full font-bold transition-all transform hover:scale-105 whitespace-nowrap <?php echo $isActive ? 'bg-indigo-600 text-white shadow-lg' : 'bg-white text-gray-500 hover:bg-indigo-50 border border-gray-200'; ?>">
                        <?php echo htmlspecialchars($r['room_name']); ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <div class="w-20"></div> <!-- Spacer for balance -->
        </div>

        <!-- Main Content -->
        <div
            class="mt-24 bg-white p-8 rounded-3xl shadow-2xl w-full max-w-md text-center border-t-8 border-indigo-600 relative overflow-hidden animate-fade-in-up">
            <div class="mb-8">
                <div class="w-32 h-32 bg-indigo-50 rounded-full flex items-center justify-center mx-auto mb-4 relative">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-indigo-500" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M12 4v1m6 11h2m-6 0h-2v4h2v-4zM6 16v4h2v-4H6zm0-7v3m0 0h12V9H6z" />
                    </svg>
                    <div class="absolute w-full h-full top-0 left-0 flex items-center justify-center pointer-events-none">
                        <div
                            class="w-24 h-0.5 bg-red-500/50 shadow-[0_0_15px_rgba(239,68,68,0.5)] animate-[scan_1.5s_ease-in-out_infinite]">
                        </div>
                    </div>
                </div>
                <h1 class="text-2xl font-bold text-gray-800">จุดรับบริการคิว</h1>
                <p class="text-indigo-600 font-medium mb-1"><?php echo htmlspecialchars($selectedDept); ?></p>
                <p class="text-gray-400 text-sm">Scan QR Code / Barcode (VN)</p>

                <div class="mt-4 inline-block px-4 py-1 bg-indigo-100 text-indigo-700 rounded-full font-semibold text-sm">
                    ห้องตรวจที่
                    <?php echo $room; ?>
                </div>
            </div>

            <div class="relative group">
                <input type="text" id="vn-input"
                    class="w-full text-center text-2xl font-mono tracking-widest border-2 border-gray-200 rounded-xl p-4 focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/20 transition-all uppercase placeholder-gray-300"
                    placeholder="SCAN HERE" autofocus autocomplete="off" onblur="this.focus()">
                <div
                    class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none text-gray-300 group-focus-within:text-indigo-500 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
            </div>

            <p class="mt-6 text-sm text-gray-400">System Ready • Waiting for input</p>

        </div>

        <!-- Hidden audio for feedback -->
        <audio id="beep-success" src="assets/vendor/audio/success.mp3"></audio>
        <audio id="beep-error" src="assets/vendor/audio/error.mp3"></audio>

        <script>
            const room = "<?php echo $room; ?>";
            const input = document.getElementById('vn-input');

            // Ensure focus is always on input
            document.addEventListener('click', () => {
                if (input) input.focus();
            });

            if (input) {
                input.addEventListener('keypress', async (e) => {
                    if (e.key === 'Enter') {
                        const vn = input.value.trim();
                        if (vn) {
                            input.disabled = true;
                            await processQueue(vn);
                            input.value = '';
                            input.disabled = false;
                            input.focus();
                        }
                    }
                });
            }

            async function processQueue(vn) {
                try {
                    // Show loading state
                    Swal.fire({
                        title: 'Processing...',
                        text: 'Fetching patient data',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });

                    const res = await fetch('api/readq.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `oqueue=${encodeURIComponent(vn)}&room=${room}`
                    });

                    const data = await res.json();

                    if (data.success) {
                        document.getElementById('beep-success').play();
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            html: `<div class="text-left bg-gray-50 p-4 rounded-lg text-sm">
                                    <p><strong>Queue:</strong> <span class="text-xl text-indigo-600 font-bold">${data.data.oqueue || 'N/A'}</span></p>
                                    <p><strong>Name:</strong> ${data.data.patient_name}</p>
                                   </div>`,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        throw new Error(data.message);
                    }

                } catch (err) {
                    document.getElementById('beep-error').play();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: err.message || 'Failed to process queue',
                        timer: 3000,
                        showConfirmButton: false
                    });
                }
            }
        </script>
    <?php endif; ?>
</body>

</html>