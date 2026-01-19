<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>nQueue - Menu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Prompt', sans-serif; }
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
            <a href="multipledisplay.php" class="group bg-slate-800 p-8 rounded-3xl border border-slate-700 hover:border-brand-500 hover:bg-slate-750 transition-all flex flex-col items-center text-center cursor-pointer shadow-lg hover:shadow-brand-500/20 hover:-translate-y-1">
                <div class="w-20 h-20 bg-blue-500/20 rounded-full flex items-center justify-center mb-6 group-hover:scale-110 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-white mb-2">Main Display</h2>
                <p class="text-slate-400">Dashboard for General Public (Waiting Hall)</p>
            </a>

            <!-- Room Display -->
            <a href="room.php" class="group bg-slate-800 p-8 rounded-3xl border border-slate-700 hover:border-emerald-500 hover:bg-slate-750 transition-all flex flex-col items-center text-center cursor-pointer shadow-lg hover:shadow-emerald-500/20 hover:-translate-y-1">
                <div class="w-20 h-20 bg-emerald-500/20 rounded-full flex items-center justify-center mb-6 group-hover:scale-110 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-white mb-2">Room Display</h2>
                <p class="text-slate-400">Display for Individual Examination Room</p>
            </a>

            <!-- Management -->
            <a href="manage_queue.php" class="group bg-slate-800 p-8 rounded-3xl border border-slate-700 hover:border-orange-500 hover:bg-slate-750 transition-all flex flex-col items-center text-center cursor-pointer shadow-lg hover:shadow-orange-500/20 hover:-translate-y-1">
                <div class="w-20 h-20 bg-orange-500/20 rounded-full flex items-center justify-center mb-6 group-hover:scale-110 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-white mb-2">Queue Manager</h2>
                <p class="text-slate-400">Reorder, Delete, and Manage Lists</p>
            </a>

             <!-- Setup (Optional) -->
             <a href="#" class="group bg-slate-800/50 p-8 rounded-3xl border border-slate-700 hover:border-slate-500 flex flex-col items-center text-center cursor-not-allowed opacity-50 grayscale">
                <div class="w-20 h-20 bg-slate-500/20 rounded-full flex items-center justify-center mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                       <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                       <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-white mb-2">Settings</h2>
                <p class="text-slate-400">System Configuration (Coming Soon)</p>
            </a>

        </div>

        <footer class="text-center mt-12 text-slate-500">
            &copy; 2024 nQueue System
        </footer>
    </div>

    <!-- Script to add brand colors to config -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                     colors: {
                        brand: {
                            500: '#3b82f6',
                        }
                    }
                }
            }
        }
    </script>
</body>
</html>
