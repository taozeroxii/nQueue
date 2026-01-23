import tkinter as tk
from tkinter import ttk, messagebox
import requests
import threading
import ctypes
from ctypes import wintypes
import time
import json
import os
import sys

# --- Windows Raw Input Definitions ---
user32 = ctypes.windll.user32
kernel32 = ctypes.windll.kernel32

RIM_TYPEKEYBOARD = 1
RIDEV_INPUTSINK = 0x00000100
RID_INPUT = 0x10000003
WM_INPUT = 0x00FF
WM_KEYDOWN = 0x0100

# DEFINE ALL STRUCTURES MANUALLY TO AVOID MISSING ATTRIBUTES IN PYTHON < 3.12 OR WIN 11

class POINT(ctypes.Structure):
    _fields_ = [("x", ctypes.c_long), ("y", ctypes.c_long)]

class MSG(ctypes.Structure):
    _fields_ = [
        ("hwnd", wintypes.HWND),
        ("message", wintypes.UINT),
        ("wParam", wintypes.WPARAM),
        ("lParam", wintypes.LPARAM),
        ("time", wintypes.DWORD),
        ("pt", POINT),
        ("lPrivate", wintypes.DWORD),
    ]

class RAWINPUTHEADER(ctypes.Structure):
    _fields_ = [
        ("dwType", wintypes.DWORD),
        ("dwSize", wintypes.DWORD),
        ("hDevice", wintypes.HANDLE),
        ("wParam", wintypes.WPARAM),
    ]

class RAWKEYBOARD(ctypes.Structure):
    _fields_ = [
        ("MakeCode", wintypes.USHORT),
        ("Flags", wintypes.USHORT),
        ("Reserved", wintypes.USHORT),
        ("VKey", wintypes.USHORT),
        ("Message", wintypes.UINT),
        ("ExtraInformation", wintypes.ULONG),
    ]

class RAWINPUT(ctypes.Structure):
    _fields_ = [
        ("header", RAWINPUTHEADER),
        ("keyboard", RAWKEYBOARD),
    ]

class RAWINPUTDEVICE(ctypes.Structure):
    _fields_ = [
        ("usUsagePage", wintypes.USHORT),
        ("usUsage", wintypes.USHORT),
        ("dwFlags", wintypes.DWORD),
        ("hwndTarget", wintypes.HWND),
    ]

# WNDPROC Callback Type
if ctypes.sizeof(ctypes.c_void_p) == 8:
    LRESULT = ctypes.c_int64
else:
    LRESULT = ctypes.c_long

WNDPROC = ctypes.WINFUNCTYPE(LRESULT, wintypes.HWND, wintypes.UINT, wintypes.WPARAM, wintypes.LPARAM)

class WNDCLASS(ctypes.Structure):
    _fields_ = [
        ('style', wintypes.UINT),
        ('lpfnWndProc', WNDPROC),
        ('cbClsExtra', ctypes.c_int),
        ('cbWndExtra', ctypes.c_int),
        ('hInstance', wintypes.HANDLE),
        ('hIcon', wintypes.HANDLE),
        ('hCursor', wintypes.HANDLE),
        ('hbrBackground', wintypes.HANDLE),
        ('lpszMenuName', wintypes.LPCWSTR),
        ('lpszClassName', wintypes.LPCWSTR),
    ]

class RAWINPUTDEVICELIST(ctypes.Structure):
    _fields_ = [
        ("hDevice", wintypes.HANDLE),
        ("dwType", wintypes.DWORD),
    ]

# --- Define Function Prototypes for 64-bit Safety ---
def _setup_apis():
    try:
        user32.RegisterClassW.argtypes = [ctypes.POINTER(WNDCLASS)]
        user32.RegisterClassW.restype = wintypes.ATOM
        
        user32.CreateWindowExW.argtypes = [
            wintypes.DWORD, wintypes.LPCWSTR, wintypes.LPCWSTR, wintypes.DWORD,
            ctypes.c_int, ctypes.c_int, ctypes.c_int, ctypes.c_int,
            wintypes.HWND, wintypes.HMENU, wintypes.HINSTANCE, wintypes.LPVOID
        ]
        user32.CreateWindowExW.restype = wintypes.HWND
        
        user32.RegisterRawInputDevices.argtypes = [ctypes.POINTER(RAWINPUTDEVICE), wintypes.UINT, wintypes.UINT]
        user32.RegisterRawInputDevices.restype = wintypes.BOOL
        
        user32.GetMessageW.argtypes = [ctypes.POINTER(MSG), wintypes.HWND, wintypes.UINT, wintypes.UINT]
        user32.GetMessageW.restype = wintypes.BOOL
        
        user32.DefWindowProcW.argtypes = [wintypes.HWND, wintypes.UINT, wintypes.WPARAM, wintypes.LPARAM]
        user32.DefWindowProcW.restype = wintypes.LPARAM
        
        # New Definitions for Listing
        user32.GetRawInputDeviceList.argtypes = [ctypes.POINTER(RAWINPUTDEVICELIST), wintypes.PUINT, wintypes.UINT]
        user32.GetRawInputDeviceList.restype = wintypes.UINT
        
        user32.GetRawInputDeviceInfoW.argtypes = [wintypes.HANDLE, wintypes.UINT, wintypes.LPVOID, wintypes.PUINT]
        user32.GetRawInputDeviceInfoW.restype = wintypes.UINT
        
        kernel32.GetModuleHandleW.restype = wintypes.HMODULE
        kernel32.GetLastError.restype = wintypes.DWORD
    except Exception as e:
        print(f"API Setup Error: {e}")

_setup_apis()

# Config
DEFAULT_CONFIG = {
    'api_base': "http://172.16.0.251/nQueue/public/api",
    'device_map': {}, # "handle_str": room_id
    'last_dept': ''
}
CONFIG_FILE = 'scanner_config.json'

class RawScannerApp:
    def __init__(self, root):
        self.root = root
        self.root.title("Multi-Scanner Hub")
        self.root.geometry("600x600")
        
        self.config = self.load_config()
        self.device_map = self.config.get('device_map', {})
        self.api_base = self.config.get('api_base', "http://172.16.0.251/nQueue/public/api")
        
        self.buffer_map = {} # handle -> string buffer
        
        # Log config
        self.log_dir = "logs"
        if not os.path.exists(self.log_dir):
            os.makedirs(self.log_dir)
        
        self.setup_tabs()
        
        # Start message loop hook in separate thread
        self.thread = threading.Thread(target=self.raw_input_loop, daemon=True)
        self.thread.start()

    def load_config(self):
        if os.path.exists(CONFIG_FILE):
            try:
                with open(CONFIG_FILE, 'r') as f:
                    return {**DEFAULT_CONFIG, **json.load(f)}
            except: pass
        return DEFAULT_CONFIG.copy()

    def save_config(self):
        self.config['device_map'] = self.device_map
        try:
            with open(CONFIG_FILE, 'w') as f:
                json.dump(self.config, f, indent=4)
        except: pass

    def setup_tabs(self):
        self.notebook = ttk.Notebook(self.root)
        self.notebook.pack(fill='both', expand=True)

        self.tab_settings = tk.Frame(self.notebook)
        self.tab_monitor = tk.Frame(self.notebook)

        self.notebook.add(self.tab_settings, text="Settings")
        self.notebook.add(self.tab_monitor, text="Monitor & Logs")

        self.setup_settings_tab()
        self.setup_monitor_tab()
        
        # Log Status Bar (Global)
        self.lbl_status = tk.Label(self.root, text="Initializing...", bd=1, relief=tk.SUNKEN, anchor='w')
        self.lbl_status.pack(fill='x', side='bottom')

        # Init Load
        self.load_depts()

    def setup_settings_tab(self):
        # Header
        top_frame = tk.Frame(self.tab_settings, pady=10)
        top_frame.pack(fill='x', padx=10)
        
        tk.Label(top_frame, text="Dept:", font=("bold", 10)).pack(side='left')
        self.c_dept = ttk.Combobox(top_frame, state="readonly", width=25)
        self.c_dept.pack(side='left', padx=5)
        self.c_dept.bind("<<ComboboxSelected>>", self.load_rooms)
        
        ttk.Button(top_frame, text="Refresh Rooms", command=self.load_depts).pack(side='left')
        
        # Debug Checkbox
        self.debug_var = tk.BooleanVar(value=False)
        tk.Checkbutton(top_frame, text="Debug Log", variable=self.debug_var).pack(side='left', padx=10)

        # Room List (Scrollable)
        container = tk.Frame(self.tab_settings)
        container.pack(fill='both', expand=True, padx=10, pady=5)
        
        canvas = tk.Canvas(container, bg="#f9fafb")
        scrollbar = ttk.Scrollbar(container, orient="vertical", command=canvas.yview)
        self.scrollable_frame = tk.Frame(canvas, bg="#f9fafb")

        self.scrollable_frame.bind(
            "<Configure>",
            lambda e: canvas.configure(scrollregion=canvas.bbox("all"))
        )

        canvas.create_window((0, 0), window=self.scrollable_frame, anchor="nw")
        canvas.configure(yscrollcommand=scrollbar.set)

        canvas.pack(side="left", fill="both", expand=True)
        scrollbar.pack(side="right", fill="y")
        
        self.room_list_inner = self.scrollable_frame

    def setup_monitor_tab(self):
        frame = tk.Frame(self.tab_monitor, padx=20, pady=20)
        frame.pack(fill='both', expand=True)
        
        tk.Label(frame, text="Latest Scan Activity", font=("Arial", 14, "bold")).pack(anchor='w')
        
        self.lbl_last_scan = tk.Label(frame, text="Waiting...", font=("Arial", 24), fg="#0ea5e9", bg="#f0f9ff", pady=20)
        self.lbl_last_scan.pack(fill='x', pady=10)
        
        tk.Label(frame, text="System Logs:", font=("Arial", 10, "bold")).pack(anchor='w', pady=(20, 5))
        
        self.log_text = tk.Text(frame, height=15, state='disabled', bg="#1e293b", fg="#e2e8f0", font=("Consolas", 10))
        self.log_text.pack(fill='both', expand=True)

    def log(self, msg):
        ts = time.strftime('%Y-%m-%d %H:%M:%S')
        date_str = time.strftime('%Y-%m-%d')
        full_msg = f"[{ts}] {msg}"
        print(f"[LOG] {msg}") # Console

        # UI Log
        try:
            self.log_text.config(state='normal')
            self.log_text.insert('end', f"{ts} - {msg}\n")
            self.log_text.see('end')
            self.log_text.config(state='disabled')
        except: pass

        # File Log
        try:
            log_file = os.path.join(self.log_dir, f"scanner_log_{date_str}.txt")
            with open(log_file, 'a', encoding='utf-8') as f:
                f.write(full_msg + "\n")
        except Exception as e:
            print(f"Log File Error: {e}")

    def load_depts(self):
        def _fetch():
            try:
                r = requests.get(f"{self.api_base}/departments.php", timeout=2)
                if r.json()['success']:
                    depts = r.json()['data']
                    self.root.after(0, lambda: self.c_dept.config(values=depts))
                    # Auto select last
                    last = self.config.get('last_dept')
                    if last and last in depts:
                        self.root.after(0, lambda: self.c_dept.set(last))
                        self.root.after(0, lambda: self.load_rooms())
            except Exception as e:
                self.root.after(0, lambda: self.log(f"Error fetching depts: {e}"))
        threading.Thread(target=_fetch, daemon=True).start()

    def load_rooms(self, event=None):
        dept = self.c_dept.get()
        if not dept: return
        self.config['last_dept'] = dept
        self.save_config()
        
        # Clear list
        for widget in self.room_list_inner.winfo_children():
            widget.destroy()
            
        def _fetch():
            try:
                r = requests.get(f"{self.api_base}/rooms.php?department={dept}", timeout=2)
                data = r.json()
                if data['success']:
                    self.root.after(0, lambda: self._render_rooms(data['data']))
            except Exception as e:
                self.root.after(0, lambda: self.log(f"Error fetching rooms: {e}"))
        threading.Thread(target=_fetch, daemon=True).start()

    def _render_rooms(self, rooms):
        tk.Label(self.room_list_inner, text="Room Name", font=("bold", 9), bg="#f9fafb", width=25, anchor='w').grid(row=0, column=0, padx=5, pady=5)
        tk.Label(self.room_list_inner, text="Device Handle", font=("bold", 9), bg="#f9fafb", width=20, anchor='w').grid(row=0, column=1, padx=5, pady=5)
        tk.Label(self.room_list_inner, text="Action", font=("bold", 9), bg="#f9fafb").grid(row=0, column=2, padx=5, pady=5)

        for i, room in enumerate(rooms):
            rid = room['id']
            rname = room['room_name']
            
            # Find if mapped
            mapped_handle = "None"
            for h, mapped_rid in self.device_map.items():
                if str(mapped_rid) == str(rid):
                    mapped_handle = h
                    break
            
            tk.Label(self.room_list_inner, text=rname, bg="#f9fafb", anchor='w').grid(row=i+1, column=0, padx=5, pady=2, sticky='w')
            
            lbl_h = tk.Label(self.room_list_inner, text=mapped_handle, bg="#e5e7eb", width=20, anchor='w')
            lbl_h.grid(row=i+1, column=1, padx=5, pady=2)
            
            # Change Logic: Open Selection Dialog
            btn = tk.Button(self.room_list_inner, text="Select Device", bg="#dbeafe", 
                            command=lambda r=rid: self.open_device_selector(r))
            btn.grid(row=i+1, column=2, padx=5, pady=2)

    def get_connected_keyboards(self):
        devices_list = []
        try:
            n_devices = wintypes.UINT(0)
            if user32.GetRawInputDeviceList(None, ctypes.byref(n_devices), ctypes.sizeof(RAWINPUTDEVICELIST)) != 0:
                pass # Expected
            
            if n_devices.value == 0: return []
            
            buffer = (RAWINPUTDEVICELIST * n_devices.value)()
            user32.GetRawInputDeviceList(ctypes.cast(buffer, ctypes.POINTER(RAWINPUTDEVICELIST)), ctypes.byref(n_devices), ctypes.sizeof(RAWINPUTDEVICELIST))
            
            for i in range(n_devices.value):
                d = buffer[i]
                if d.dwType == RIM_TYPEKEYBOARD:
                    # Get Name
                    size = wintypes.UINT(0)
                    user32.GetRawInputDeviceInfoW(d.hDevice, 0x20000007, None, ctypes.byref(size)) # RIDI_DEVICENAME
                    if size.value > 0:
                        name_buf = ctypes.create_unicode_buffer(size.value)
                        user32.GetRawInputDeviceInfoW(d.hDevice, 0x20000007, name_buf, ctypes.byref(size))
                        name = name_buf.value
                        devices_list.append({"handle": d.hDevice, "name": name})
        except Exception as e:
            print(e)
            
        return devices_list

    def open_device_selector(self, room_id):
        # Dialog
        top = tk.Toplevel(self.root)
        top.title(f"Select Scanner for Room {room_id}")
        top.geometry("500x350")
        
        tk.Label(top, text="Select a USB Device (Scanner) from the list:", font=("bold", 10)).pack(pady=10)
        
        listbox = tk.Listbox(top, width=70, height=10)
        listbox.pack(pady=5, padx=10)
        
        self._mapping_ref = [] # index -> handle_str

        def _refresh_list():
            listbox.delete(0, 'end')
            self._mapping_ref = []
            devices = self.get_connected_keyboards()
            if not devices:
                listbox.insert('end', "No Devices Found")
                return

            for dev in devices:
                name = dev['name']
                clean_name = name.split('#')[1] if '#' in name else name[-20:]
                display_text = f"Device: {clean_name}"
                listbox.insert('end', display_text)
                self._mapping_ref.append(str(dev['handle']))
        
        # Initial Load
        _refresh_list()
        
        # Refresh Button
        tk.Button(top, text="Refresh List", command=_refresh_list).pack(pady=2)

        def on_select():
            sel = listbox.curselection()
            if not sel: return
            
            if not self._mapping_ref: return # Empty list
            
            idx = sel[0]
            if idx < len(self._mapping_ref):
                handle_str = self._mapping_ref[idx]
                
                # Map it
                self.device_map[handle_str] = room_id
                self.save_config()
                self.log(f"MAPPED Device {handle_str} -> Room {room_id}")
                
                top.destroy()
        tk.Button(top, text="Confirm Selection", command=on_select, bg="#bef264").pack(pady=10)

    def raw_input_loop(self):
        self.log(f"Thread: Starting Window Hook... (PtrSize={ctypes.sizeof(ctypes.c_void_p)})")
        try:
            def wnd_proc(hwnd, msg, wParam, lParam):
                if msg == WM_INPUT:
                    try:
                        self.process_raw_input(lParam)
                    except Exception as e:
                        print(f"Proc Error: {e}")
                    return 0 # processed
                return user32.DefWindowProcW(hwnd, msg, wParam, lParam)

            hinst = kernel32.GetModuleHandleW(None)
            self.log(f"Thread: HINST={hinst}")

            # KEEP REFERENCE TO CALLBACK TO PREVENT GC
            self.wnd_proc_cb = WNDPROC(wnd_proc)
            
            # Register Class
            class_name = "RawInputReceiver_v5"
            wndclass = WNDCLASS()
            wndclass.style = 0
            wndclass.lpfnWndProc = self.wnd_proc_cb
            wndclass.hInstance = hinst
            wndclass.lpszClassName = class_name
            
            atom = user32.RegisterClassW(ctypes.byref(wndclass))
            self.log(f"Thread: ATOM={atom}")
            
            if not atom:
                err = kernel32.GetLastError()
                if err != 1410: # Class already exists
                    self.log(f"Thread: RegisterClassW failed. Error: {err}")
            
            # Create Window - MESSAGE ONLY WINDOW (-3)
            # Correct Argument Order: 9th arg is Parent.
            HWND_MESSAGE = ctypes.cast(-3, wintypes.HWND)
            
            self.log("Thread: Calling CreateWindowExW with HWND_MESSAGE (-3)...")
            
            hwnd = user32.CreateWindowExW(
                0,                  # dwExStyle
                class_name,         # lpClassName
                "Hidden",           # lpWindowName
                0,                  # dwStyle
                0, 0, 0, 0,         # x, y, w, h
                HWND_MESSAGE,       # hWndParent (9th arg)
                0,                  # hMenu (10th arg)
                hinst,              # hInstance
                0                   # lpParam
            )
            
            if not hwnd:
                err = kernel32.GetLastError()
                self.log(f"Thread: FAILED to create window. Error: {err}")
                self.root.after(0, lambda: self.lbl_status.config(text=f"Error: Window Create Failed {err}", bg="red"))
                return

            self.log(f"Thread: Message Window Created. HWND: {hwnd}")

            # Register Raw Input
            rid = RAWINPUTDEVICE()
            rid.usUsagePage = 0x01
            rid.usUsage = 0x06 # Keyboard
            rid.dwFlags = RIDEV_INPUTSINK
            rid.hwndTarget = hwnd
            
            if not user32.RegisterRawInputDevices(ctypes.byref(rid), 1, ctypes.sizeof(rid)):
                err = kernel32.GetLastError()
                self.log(f"Thread: RegisterRawInputDevices failed. Error: {err}")
                self.root.after(0, lambda: self.lbl_status.config(text=f"Error: RID Reg Failed {err}", bg="red"))
                return
            
            self.log("Thread: RawInputDevices Registered Successfully")
            self.root.after(0, lambda: self.lbl_status.config(text="Scanner Hook Active - Ready", bg="#bef264"))

            # Message Loop
            msg = MSG()
            while user32.GetMessageW(ctypes.byref(msg), 0, 0, 0) != 0:
                user32.TranslateMessage(ctypes.byref(msg))
                user32.DispatchMessageW(ctypes.byref(msg))
        except Exception as e:
            self.log(f"Thread CRASH: {e}")
            self.root.after(0, lambda: self.lbl_status.config(text=f"Thread Crash: {e}", bg="red"))

    def process_raw_input(self, hRawInput):
        data = RAWINPUT()
        size = wintypes.UINT(ctypes.sizeof(data))
        user32.GetRawInputData(hRawInput, RID_INPUT, ctypes.byref(data), ctypes.byref(size), ctypes.sizeof(RAWINPUTHEADER))
        
        if data.header.dwType == RIM_TYPEKEYBOARD:
            hDevice = data.header.hDevice
            vkey = data.keyboard.VKey
            flags = data.keyboard.Flags
            
            # KeyDown (Flags & 1 == 0)
            if (flags & 1) == 0: 
                char = self.vk_to_char(vkey)
                if char:
                    self.handle_key(hDevice, char)

    def vk_to_char(self, vkey):
        if vkey == 0x0D: return 'ENTER'
        if 48 <= vkey <= 57: return chr(vkey)
        if 65 <= vkey <= 90: return chr(vkey)
        return None

    def handle_key(self, hDevice, char):
        self.root.after(0, lambda: self._safe_handle_key(hDevice, char))

    def _safe_handle_key(self, hDevice, char):
        dev_id = str(hDevice)

        # Logging
        if self.debug_var.get():
             self.log(f"DEBUG: Device {dev_id} pressed '{char}'")

        # Scanning Mode
        if dev_id not in self.device_map:
            if self.debug_var.get():
                 self.log(f"IGNORING unmapped: {dev_id}")
            return
            
        room = self.device_map[dev_id]
        if dev_id not in self.buffer_map: self.buffer_map[dev_id] = ""
        
        if char == 'ENTER':
            vn = self.buffer_map[dev_id]
            self.buffer_map[dev_id] = ""
            if vn:
                self.log(f"SCAN {vn} -> Room {room}")
                
                # --- PREFIX LOGIC START ---
                if vn.startswith('204'):
                    vn = vn[3:]
                    self.log(f"PREFIX DETECTED: 204. Modified VN -> {vn}")
                # --- PREFIX LOGIC END ---
                
                # Update Last Scan UI
                self.lbl_last_scan.config(text=f"VN: {vn} -> Room {room}")
                
                self.send_scan(vn, room)
        else:
            self.buffer_map[dev_id] += char

    def send_scan(self, vn, room):
        # Add to Log Immediately
        self.log_text.config(state='normal')
        self.log_text.insert('end', f"Sending {vn} to Room {room}...\n")
        self.log_text.config(state='disabled')
        threading.Thread(target=self._post_api, args=(vn, room)).start()

    def _post_api(self, vn, room):
        try:
            r = requests.post(f"{self.api_base}/scan.php", data={'vn': vn, 'room': room}, timeout=2)
            res = r.json()
            if res.get('success'):
                self.root.after(0, lambda: self.log(f"SUCCESS: VN {vn} added to Room {room}"))
            else:
                msg = res.get('message', 'Unknown Error')
                self.root.after(0, lambda: self.log(f"FAILED: {msg}"))
        except Exception as e:
            self.root.after(0, lambda: self.log(f"API ERROR: {e}"))

if __name__ == "__main__":
    root = tk.Tk()
    app = RawScannerApp(root)
    root.mainloop()
