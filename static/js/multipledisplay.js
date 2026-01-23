// State
let currentDeptFilter = localStorage.getItem('dept_filter') || '';
let currentRoomFilter = localStorage.getItem('room_filter') || '';
let ttsPrefix = localStorage.getItem('tts_prefix') || 'ขอเชิญหมายเลข';
let ttsMiddle = localStorage.getItem('tts_middle') || 'ที่ห้องตรวจ';
let ttsRepeat = parseInt(localStorage.getItem('tts_repeat')) || 1;

let allRooms = [];
let allQueues = [];
let deptList = [];

let calledPage = 0;
let waitingPage = 0;
const CALLED_PAGE_SIZE = 15;
const WAITING_PAGE_SIZE = 10;

// Socket.IO connection
const socket = io();

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

// Socket.IO event handlers
socket.on('connect', function() {
    console.log('Connected to server');
    document.body.style.borderTop = "4px solid #10b981";
});

socket.on('disconnect', function() {
    console.log('Disconnected from server');
    document.body.style.borderTop = "4px solid #ef4444";
});

socket.on('recall', function(data) {
    console.log("Recall event received", data);
    if (data.data) {
        speakQueue(data.data);
    }
    fetchQueue();
});

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

    closeSettings();

    // Reload Data with new filters
    calledPage = 0;
    loadRoomsAndQueue();
}

async function onDeptFilterChange() {
    const dept = inputFilter.value;
    await updateRoomFilterOptions(dept, '');
}

async function updateRoomFilterOptions(dept, selectedRoom) {
    try {
        let url = '/api/rooms';
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
        const res = await fetch('/api/settings');
        const data = await res.json();
        if (data.success && data.data) {
            if (data.data.dept_name) deptNameEl.innerText = data.data.dept_name;
            if (data.data.dept_sub) deptSubEl.innerText = data.data.dept_sub;
        }
    } catch (e) { }

    // Dept Options
    try {
        const res2 = await fetch('/api/departments');
        const data2 = await res2.json();
        if (data2.success) {
            const depts = data2.data;
            deptList = depts;
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
        let url = '/api/rooms';
        if (currentDeptFilter) url += `?department=${encodeURIComponent(currentDeptFilter)}`;
        const r = await fetch(url);
        const d = await r.json();
        if (d.success) {
            allRooms = d.data;

            // Header Subtitle should be Department Name + Room Description
            if (currentDeptFilter) {
                const uniqueDescriptions = [...new Set(
                    allRooms.map(r => r.description).filter(desc => desc && desc.trim().length > 0)
                )];
                const descText = uniqueDescriptions.join(' / ');

                if (descText) {
                    deptSubEl.innerText = `${currentDeptFilter} ${descText}`;
                } else {
                    deptSubEl.innerText = currentDeptFilter;
                }
            }
        }
    } catch (e) { allRooms = []; }

    fetchQueue();
}

async function fetchQueue() {
    try {
        let url = '/api/queue_data?limit=50';
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
                const latest = called.reduce((prev, current) => {
                    const prevTime = new Date(prev.updated_at || prev.created_at).getTime();
                    const currTime = new Date(current.updated_at || current.created_at).getTime();
                    return (prevTime > currTime) ? prev : current;
                });

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

// Logic to Merge Rooms + Queues and Render Pages
function processAndRender() {
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

        let cardContent = '';

        if (activeCall) {
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
            if (currentRoomFilter && String(room.id) !== String(currentRoomFilter)) return '';

            cardContent = `
                <div class="bg-slate-800/60 p-4 rounded-3xl border-2 border-slate-700/50 flex flex-col items-center justify-center text-center opacity-75 min-h-[300px]">
                    <span class="text-lg text-slate-400 font-semibold block truncate">ห้อง ${room.room_name}</span>
                    <h3 class="text-5xl font-bold text-slate-600 tracking-tight my-4">ว่าง</h3>
                    <p class="text-sm text-slate-500 truncate">รอเรียก...</p>
                </div>
            `;
        }

        if (currentRoomFilter && String(room.id) !== String(currentRoomFilter)) return '';

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
let lastWaitingData = '';

function renderPagination(roomCards) {
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
let lastCallTimes = {};

function speakQueue(item) {
    console.log("Queueing TTS:", item);

    // Reload settings from localStorage
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

async function processTTSQueue() {
    if (isSpeaking || ttsQueue.length === 0) return;

    const item = ttsQueue.shift();
    isSpeaking = true;

    console.log("Processing TTS Item:", item);

    try {
        // Try Google Translate TTS with female voice
        await playGoogleTTS(item.text);
    } catch (err) {
        console.warn("Google TTS failed, falling back to Native:", err);
        // Fallback to Native
        await playNativeTTS(item);
    }

    isSpeaking = false;
    setTimeout(processTTSQueue, 500);
}

function playGoogleTTS(text) {
    return new Promise((resolve, reject) => {
        // Use our Python backend to get TTS URL
        fetch(`/api/tts_url?text=${encodeURIComponent(text)}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const audio = new Audio(data.url);
                    audio.onended = resolve;
                    audio.onerror = reject;
                    
                    const timeout = setTimeout(() => {
                        audio.pause();
                        reject("Timeout");
                    }, 5000);

                    audio.play().catch(reject);
                } else {
                    reject("Failed to get TTS URL");
                }
            })
            .catch(reject);
    });
}

function playNativeTTS(item) {
    return new Promise((resolve) => {
        const utterance = new SpeechSynthesisUtterance(item.text);
        utterance.lang = item.lang;
        utterance.rate = 1.0;
        utterance.pitch = 1.2;

        const voices = window.speechSynthesis.getVoices();
        let thaiVoice = voices.find(v => v.lang.includes('th') && (v.name.includes('Google') || v.name.includes('Premwadee') || v.name.includes('Kanya')));
        if (!thaiVoice) thaiVoice = voices.find(v => v.lang.includes('th'));
        if (thaiVoice) utterance.voice = thaiVoice;

        const savedVoiceURI = localStorage.getItem('tts_voice');
        if (savedVoiceURI) {
            const specificVoice = voices.find(v => v.voiceURI === savedVoiceURI);
            if (specificVoice) utterance.voice = specificVoice;
        }

        utterance.onend = () => {
            resolve();
        };
        utterance.onerror = (e) => {
            console.error("Native TTS Error", e);
            resolve(); // Resolve anyway to unblock queue
        };

        window.speechSynthesis.speak(utterance);
    });
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

// Fallback polling
setInterval(fetchQueue, 30000);
