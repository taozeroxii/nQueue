import tkinter as tk
from tkinter import messagebox, ttk
import json
import requests
import threading
import os

# --- Premium Configuration & Theme (Matched with caller.py) ---
DEFAULT_CONFIG = {
    'api_base': "http://localhost/nQueue/public/api", 
    'selected_dept': None,
    'selected_room': None
}

CONFIG_FILE = 'kiosk_config.json'

# Premium Dark Palette
THEME = {
    'bg_main': '#0f172a',      # Background Dark
    'bg_card': '#1e293b',      # Card Background
    'text_main': '#f8fafc',    # White Text
    'text_dim': '#94a3b8',     # Grey Text
    'accent': '#4f46e5',       # Indigo
    'accent_h': '#4338ca',   
    'success': '#10b981',      # Emerald
    'error': '#ef4444'         # Red
}

class KioskApp:
    def __init__(self, root):
        self.root = root
        self.root.title("Min Kiosk")
        # Small Premium Size
        self.root.geometry("300x200") 
        self.root.configure(bg=THEME['bg_main'])
        self.root.attributes('-topmost', True)
        self.root.resizable(False, False)
        
        self.config = self.load_config()
        self.api_base = self.config.get('api_base', "http://localhost/nQueue/public/api")
        
        self.setup_styles()
        
        # State
        self.departments = []
        self.rooms = []
        self.current_dept = self.config.get('selected_dept')
        self.current_room = self.config.get('selected_room')
        
        self.container = tk.Frame(self.root, bg=THEME['bg_main'])
        self.container.pack(fill='both', expand=True)
        
        # Initial View
        if self.current_dept:
            self.load_rooms_and_show_kiosk(self.current_dept)
        else:
            self.show_dept_selection()

    def load_config(self):
        if os.path.exists(CONFIG_FILE):
            try:
                with open(CONFIG_FILE, 'r') as f:
                    return {**DEFAULT_CONFIG, **json.load(f)}
            except: pass
        return DEFAULT_CONFIG.copy()

    def save_config(self):
        try:
            self.config['selected_dept'] = self.current_dept
            self.config['selected_room'] = self.current_room
            with open(CONFIG_FILE, 'w') as f:
                json.dump(self.config, f, indent=4)
        except: pass

    def setup_styles(self):
        self.style = ttk.Style()
        self.style.theme_use('clam')
        
        # Tabs Style
        self.style.configure('TNotebook', background=THEME['bg_main'], borderwidth=0)
        self.style.configure('TNotebook.Tab', background=THEME['bg_card'], foreground=THEME['text_dim'], padding=[10, 2], font=('Segoe UI', 9))
        self.style.map('TNotebook.Tab', background=[('selected', THEME['accent'])], foreground=[('selected', 'white')])

    def clear_container(self):
        for widget in self.container.winfo_children():
            widget.destroy()

    # --- View: Department Selection ---
    def show_dept_selection(self):
        self.clear_container()
        self.current_dept = None
        self.current_room = None
        self.save_config() # Clear saved state

        lbl = tk.Label(self.container, text="Select Department", font=("Segoe UI", 12, "bold"), bg=THEME['bg_main'], fg=THEME['text_main'])
        lbl.pack(pady=10)

        # Scrollable Frame for departments if many
        canvas = tk.Canvas(self.container, bg=THEME['bg_main'], highlightthickness=0)
        scrollbar = ttk.Scrollbar(self.container, orient="vertical", command=canvas.yview)
        scrollable_frame = tk.Frame(canvas, bg=THEME['bg_main'])

        scrollable_frame.bind(
            "<Configure>",
            lambda e: canvas.configure(scrollregion=canvas.bbox("all"))
        )

        canvas.create_window((0, 0), window=scrollable_frame, anchor="nw")
        canvas.configure(yscrollcommand=scrollbar.set)

        canvas.pack(side="left", fill="both", expand=True, padx=10, pady=5)
        scrollbar.pack(side="right", fill="y")
        
        # Loading Info
        tk.Label(scrollable_frame, text="Loading...", bg=THEME['bg_main'], fg=THEME['text_dim']).pack()
        
        threading.Thread(target=self.fetch_departments, args=(scrollable_frame,)).start()

    def fetch_departments(self, parent):
        try:
            r = requests.get(f"{self.api_base}/departments.php", timeout=3)
            data = r.json()
            if data['success']:
                self.departments = data['data']
                self.root.after(0, lambda: self.render_departments(parent))
        except Exception as e:
            print(e)

    def render_departments(self, parent):
        for widget in parent.winfo_children(): widget.destroy()
        
        for dept in self.departments:
            btn = tk.Button(parent, text=dept, command=lambda d=dept: self.load_rooms_and_show_kiosk(d),
                            bg=THEME['bg_card'], fg=THEME['text_main'],
                            font=("Segoe UI", 10), bd=0, relief='flat', cursor='hand2',
                            activebackground=THEME['accent'], activeforeground='white')
            btn.pack(fill='x', pady=2, ipady=5)
            
            # Hover effect
            btn.bind("<Enter>", lambda e, b=btn: b.config(bg=THEME['accent']))
            btn.bind("<Leave>", lambda e, b=btn: b.config(bg=THEME['bg_card']))

    # --- View: Kiosk (Room Tabs & Input) ---
    def load_rooms_and_show_kiosk(self, dept):
        self.current_dept = dept
        self.save_config()
        self.fetch_rooms(dept)

    def fetch_rooms(self, dept):
        try:
            r = requests.get(f"{self.api_base}/rooms.php?department={dept}", timeout=3)
            data = r.json()
            if data['success']:
                self.rooms = data['data']
                self.root.after(0, self.show_kiosk_ui)
        except: pass

    def show_kiosk_ui(self):
        self.clear_container()

        # Header: Back Button + Dept Name
        header = tk.Frame(self.container, bg=THEME['bg_main'])
        header.pack(fill='x', padx=5, pady=5)
        
        btn_back = tk.Button(header, text="←", command=self.show_dept_selection,
                             bg=THEME['bg_main'], fg=THEME['text_dim'], font=("Segoe UI", 10, "bold"),
                             bd=0, cursor='hand2')
        btn_back.pack(side='left')
        
        lbl_dept = tk.Label(header, text=self.current_dept, font=("Segoe UI", 10, "bold"), bg=THEME['bg_main'], fg=THEME['text_main'])
        lbl_dept.pack(side='left', padx=10)

        # Config Button (Right) to change API URL
        btn_conf = tk.Button(header, text="⚙", command=self.open_settings,
                             bg=THEME['bg_main'], fg=THEME['text_dim'], bd=0, cursor='hand2')
        btn_conf.pack(side='right')

        # Room Tabs (Custom implementation using Notebook)
        # Note: Notebook headers in Tkinter are hard to style perfectly flat without images, but we try.
        
        self.notebook = ttk.Notebook(self.container)
        self.notebook.pack(fill='both', expand=True, padx=5, pady=0)
        
        # If we have a saved room, find its index
        select_index = 0
        
        self.room_pages = {} # room_id -> frame

        for idx, room in enumerate(self.rooms):
            frame = tk.Frame(self.notebook, bg=THEME['bg_card'])
            self.notebook.add(frame, text=f" {room['room_name']} ")
            self.room_pages[room['room_name']] = frame
            
            if str(room['id']) == str(self.current_room):
                select_index = idx

            # Content for each room tab (Just the Input)
            self.build_room_input(frame, room['id'])
        
        if self.notebook.tabs():
            self.notebook.select(select_index)
            
        self.notebook.bind("<<NotebookTabChanged>>", self.on_tab_change)

    def on_tab_change(self, event):
        # Update current room based on selection
        try:
            idx = self.notebook.index(self.notebook.select())
            self.current_room = self.rooms[idx]['id']
            self.save_config()
            
            # Focus the input of the active tab
            for child in self.notebook.winfo_children():
                if str(child) == str(self.notebook.select()):
                     # Find entry widget
                     for widget in child.winfo_children():
                         if isinstance(widget, tk.Entry):
                             widget.focus()
        except: pass

    def build_room_input(self, parent, room_id):
        # Center Content
        center = tk.Frame(parent, bg=THEME['bg_card'])
        center.place(relx=0.5, rely=0.5, anchor='center')
        
        tk.Label(center, text="SCAN / ENTER QUEUE", font=("Segoe UI", 8), bg=THEME['bg_card'], fg=THEME['text_dim']).pack(pady=(0,5))
        
        entry = tk.Entry(center, font=("Segoe UI", 24, "bold"), justify='center', width=10,
                         bd=0, bg=THEME['bg_main'], fg=THEME['text_main'], insertbackground='white')
        entry.pack(ipady=5)
        entry.bind("<Return>", lambda e: self.submit_queue(entry, room_id))
        
        # Focus handling
        entry.focus()
        
    def submit_queue(self, entry, room_id):
        val = entry.get().strip()
        if not val: return
        
        # Disable input
        entry.config(state='disabled')
        
        # Submit
        threading.Thread(target=lambda: self._do_submit(val, room_id, entry)).start()

    def _do_submit(self, oqueue, room_id, entry_widget):
        try:
            # api/readq.php expects 'oqueue' and 'room'
            r = requests.post(f"{self.api_base}/readq.php", data={'oqueue': oqueue, 'room': room_id}, timeout=3)
            data = r.json()
            
            self.root.after(0, lambda: self.handle_submit_response(data, entry_widget))
        except Exception as e:
             self.root.after(0, lambda: self.show_toast(f"Error: {str(e)}", False))
             self.root.after(0, lambda: self.reset_entry(entry_widget))

    def handle_submit_response(self, data, entry_widget):
        if data.get('success'):
            self.show_toast(f"✅ {data['data']['oqueue']} : {data['data']['patient_name']}", True)
        else:
            self.show_toast(f"❌ {data.get('message', 'Unknown Error')}", False)
        
        self.reset_entry(entry_widget)

    def reset_entry(self, entry):
        entry.config(state='normal')
        entry.delete(0, 'end')
        entry.focus()

    def show_toast(self, msg, success=True):
        # Overlay a temporary label
        color = THEME['success'] if success else THEME['error']
        
        toast = tk.Label(self.root, text=msg, bg=color, fg='white', font=("Segoe UI", 10, "bold"), pady=10)
        toast.place(relx=0.5, rely=0.9, anchor='center', relwidth=0.9)
        
        self.root.after(3000, toast.destroy)

    def open_settings(self):
        if hasattr(self, 'settings_win') and self.settings_win is not None and self.settings_win.winfo_exists():
            self.settings_win.lift()
            self.settings_win.focus_force()
            return

        self.settings_win = tk.Toplevel(self.root)
        self.settings_win.title("URL Config")
        self.settings_win.geometry("300x120")
        self.settings_win.transient(self.root)
        self.settings_win.grab_set()
        
        tk.Label(self.settings_win, text="API Base URL:").pack(anchor='w', padx=10, pady=5)
        e = tk.Entry(self.settings_win, width=35)
        e.insert(0, self.api_base)
        e.pack(padx=10)
        
        def save():
            self.api_base = e.get()
            self.config['api_base'] = self.api_base
            self.save_config()
            self.show_dept_selection() # Reset to reload
            self.settings_win.destroy()
            self.settings_win = None
            
        def on_close():
             self.settings_win.destroy()
             self.settings_win = None

        self.settings_win.protocol("WM_DELETE_WINDOW", on_close)
            
        tk.Button(self.settings_win, text="Save & Reload", command=save).pack(pady=10)

if __name__ == "__main__":
    root = tk.Tk()
    app = KioskApp(root)
    root.mainloop()
