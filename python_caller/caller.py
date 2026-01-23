import tkinter as tk
from tkinter import messagebox, ttk
import json
import requests
import threading
import os
import asyncio
import websockets

# --- Premium Configuration & Theme ---
DEFAULT_CONFIG = {
    'api_url': "http://localhost/nQueue/public/api/update_status.php",
    'ws_url': "ws://localhost:8765",
    'api_base': "http://localhost/nQueue/public/api", 
    'room_id': 1
}

CONFIG_FILE = 'config.json'

# Premium Dark Palette
THEME = {
    'bg_main': '#0f172a',      # Background Dark
    'bg_card': '#1e293b',      # Card Background
    'text_main': '#f8fafc',    # White Text
    'text_dim': '#94a3b8',     # Grey Text
    'btn_call': '#4f46e5',     # Indigo (Call)
    'btn_call_h': '#4338ca',   
    'btn_recall': '#f59e0b',   # Amber (Recall)
    'btn_recall_h': '#d97706',
    'btn_sec': '#334155',      # Secondary (Lab/Xray/List)
    'btn_sec_h': '#475569'
}

class MiniCallerApp:
    def __init__(self, root):
        self.root = root
        self.root.title("Caller")
        # Fixed Size as requested
        self.root.geometry("280x230") 
        self.root.configure(bg=THEME['bg_main'])
        self.root.attributes('-topmost', True)
        self.root.resizable(False, False)
        
        self.config = self.load_config()
        self.room_id = self.config.get('room_id', 1)
        self.api_base = self.config.get('api_base', "http://localhost/nQueue/public/api")
        
        self.setup_styles()
        self.create_ui()
        self.refresh_queue()
        
        self.loop = None
        self.ws_thread = threading.Thread(target=self.start_ws_listener, daemon=True)
        self.ws_thread.start()

    def load_config(self):
        if os.path.exists(CONFIG_FILE):
            try:
                with open(CONFIG_FILE, 'r') as f:
                    return {**DEFAULT_CONFIG, **json.load(f)}
            except: pass
        return DEFAULT_CONFIG.copy()

    def save_config(self):
        try:
            with open(CONFIG_FILE, 'w') as f:
                json.dump(self.config, f, indent=4)
        except: pass

    def setup_styles(self):
        self.style = ttk.Style()
        self.style.theme_use('clam')
        self.style.configure("Treeview", rowheight=25, font=('Segoe UI', 9))

    def create_ui(self):
        # --- 1. Header (Room & Settings) ---
        header = tk.Frame(self.root, bg=THEME['bg_main'], height=25)
        header.pack(fill='x', padx=8, pady=(8, 0))
        
        lbl_room = tk.Label(header, text=f"ROOM {self.room_id}", 
                            font=("Segoe UI", 8, "bold"), 
                            bg=THEME['bg_card'], fg=THEME['text_dim'],
                            padx=6, pady=2)
        lbl_room.pack(side='left')
        
        btn_set = tk.Button(header, text="⚙", command=self.open_settings,
                            font=("Segoe UI", 10), bg=THEME['bg_main'], fg=THEME['text_dim'],
                            bd=0, activebackground=THEME['bg_main'], cursor="hand2")
        btn_set.pack(side='right')

        # --- 2. Display Area (Queue & Name) ---
        display_frame = tk.Frame(self.root, bg=THEME['bg_card'])
        display_frame.pack(fill='both', expand=True, padx=8, pady=8)
        
        # Queue Number
        self.lbl_q = tk.Label(display_frame, text="-", 
                              font=("Segoe UI", 32, "bold"), 
                              bg=THEME['bg_card'], fg=THEME['text_main'])
        self.lbl_q.pack(pady=(2, 0))
        
        # Patient Name
        self.lbl_status = tk.Label(display_frame, text="Standby", 
                                   font=("Segoe UI", 9), 
                                   bg=THEME['bg_card'], fg=THEME['text_dim'])
        self.lbl_status.pack(pady=(0, 5))

        # --- 3. Buttons Grid (Redesigned) ---
        btn_frame = tk.Frame(self.root, bg=THEME['bg_main'])
        btn_frame.pack(fill='x', side='bottom', padx=8, pady=(0, 10))

        # Configure Grid: 3 Columns
        btn_frame.columnconfigure(0, weight=1)
        btn_frame.columnconfigure(1, weight=1)
        btn_frame.columnconfigure(2, weight=1)

        # Helper for Hover Buttons
        def mk_btn(txt, cmd, bg, h_bg, row, col, colspan=1):
            b = tk.Button(btn_frame, text=txt, command=cmd,
                          bg=bg, fg='white',
                          font=("Segoe UI", 8, "bold"),
                          bd=0, relief='flat', cursor='hand2')
            b.grid(row=row, column=col, columnspan=colspan, sticky='nsew', padx=1, pady=1, ipady=12) # Increased height
            b.bind("<Enter>", lambda e: b.config(bg=h_bg))
            b.bind("<Leave>", lambda e: b.config(bg=bg))
            return b

        # Single Row Layout
        mk_btn("↺ Recall", self.recall_prev, THEME['btn_recall'], THEME['btn_recall_h'], 0, 0)
        mk_btn("☰ List", self.show_list, THEME['btn_sec'], THEME['btn_sec_h'], 0, 1)
        mk_btn("🔊 CALL NEXT", self.call_next, THEME['btn_call'], THEME['btn_call_h'], 0, 2)

    # --- Logic Section (Same Logic) ---

    def open_settings(self):
        win = tk.Toplevel(self.root)
        win.title("Config")
        win.geometry("280x320")
        
        tk.Label(win, text="Base URL:").pack(anchor='w', padx=10, pady=(10,0))
        e_base = tk.Entry(win, width=30)
        e_base.insert(0, self.api_base)
        e_base.pack(padx=10)

        tk.Label(win, text="Dept:").pack(anchor='w', padx=10, pady=(5,0))
        c_dept = ttk.Combobox(win)
        c_dept.pack(fill='x', padx=10)
        
        tk.Label(win, text="Room:").pack(anchor='w', padx=10, pady=(5,0))
        c_room = ttk.Combobox(win)
        c_room.pack(fill='x', padx=10)
        
        self.room_map = {} 
        
        def load_depts():
            try:
                r = requests.get(f"{e_base.get()}/departments.php", timeout=2)
                if r.json()['success']: c_dept['values'] = r.json()['data']
            except: pass

        def load_rooms(event=None):
            dept = c_dept.get()
            if dept: threading.Thread(target=lambda: _do_load_rooms_bg(dept)).start()

        def _do_load_rooms_bg(dept):
            try:
                r = requests.get(f"{e_base.get()}/rooms.php?department={dept}", timeout=2)
                data = r.json()
                if data['success']:
                    names = []
                    self.room_map = {}
                    for item in data['data']:
                        n = f"{item['room_name']} ({item['id']})"
                        names.append(n)
                        self.room_map[n] = item['id']
                    win.after(0, lambda: [c_room.config(values=names), c_room.current(0) if names else None])
            except: pass

        c_dept.bind("<<ComboboxSelected>>", load_rooms)
        threading.Thread(target=load_depts).start()

        def save():
            self.config['api_base'] = e_base.get()
            self.config['api_url'] = f"{e_base.get()}/update_status.php"
            sel = c_room.get()
            if sel in self.room_map:
                self.room_id = self.room_map[sel]
                self.config['room_id'] = self.room_id
            self.save_config()
            self.refresh_queue()
            win.destroy()

        ttk.Button(win, text="Save", command=save).pack(pady=15)

    def call_next(self):
        threading.Thread(target=lambda: self._post_action('call_next')).start()

    def recall_prev(self):
        threading.Thread(target=lambda: self._post_action('recall')).start()

    def set_status(self, status):
        threading.Thread(target=lambda: self._do_set_status_room_active(status)).start()
        
    def _do_set_status_room_active(self, status):
        try:
            r = requests.get(f"{self.api_base}/queue_data.php?room={self.room_id}&limit=1", timeout=2)
            d = r.json()
            found_id = None
            if d['success']:
                for item in d['data']:
                    if item['status'] == 'called':
                        found_id = item['id']
                        break
            if found_id:
                requests.post(self.config['api_url'], json={'id': found_id, 'status': status}, timeout=2)
                self.root.after(100, self.refresh_queue)
        except: pass

    def _post_action(self, action):
        try:
            r = requests.post(self.config['api_url'], json={'action': action, 'room': self.room_id}, timeout=2)
            if r.status_code == 200: self.root.after(100, self.refresh_queue)
        except: pass

    def show_list(self):
        self.list_win = tk.Toplevel(self.root)
        self.list_win.title(f"Room {self.room_id}")
        self.list_win.geometry("500x400")
        
        tabs = ttk.Notebook(self.list_win)
        tabs.pack(fill='both', expand=True, padx=5, pady=5)
        
        self.tab_waiting = ttk.Frame(tabs); tabs.add(self.tab_waiting, text='Waiting')
        self.tab_lab = ttk.Frame(tabs); tabs.add(self.tab_lab, text='Lab')
        self.tab_xray = ttk.Frame(tabs); tabs.add(self.tab_xray, text='X-Ray')
        self.tab_processed = ttk.Frame(tabs); tabs.add(self.tab_processed, text='History')
        
        self.tree_waiting = self._create_tree(self.tab_waiting, 'waiting')
        self.tree_lab = self._create_tree(self.tab_lab, 'lab')
        self.tree_xray = self._create_tree(self.tab_xray, 'xray')
        self.tree_processed = self._create_tree(self.tab_processed, 'history')
        
        ttk.Button(self.list_win, text="Refresh", command=self.load_all_lists).pack(pady=5)
        self.load_all_lists()

    def _create_tree(self, parent, type_key):
        tree = ttk.Treeview(parent, columns=('q', 'name', 'status'), show='headings')
        tree.heading('q', text='No.'); tree.column('q', width=50, anchor='center')
        tree.heading('name', text='Name'); tree.column('name', width=200)
        tree.heading('status', text='Sts'); tree.column('status', width=60, anchor='center')
        tree.pack(fill='both', expand=True)
        tree.bind("<Double-1>", lambda e: self.on_list_action(e, tree, type_key))
        return tree

    def on_list_action(self, event, tree, type_key):
        selection = tree.selection()
        if not selection: return
        db_id = selection[0]
        
        if type_key == 'waiting':
            # Create Pop-up Menu
            menu = tk.Menu(self.root, tearoff=0)
            menu.add_command(label="🔊 Call This Patient", command=lambda: threading.Thread(target=lambda: self._call_specific(db_id)).start())
            menu.add_separator()
            menu.add_command(label="🧪 Send to Lab", command=lambda: threading.Thread(target=lambda: self._update_status(db_id, 'lab')).start())
            menu.add_command(label="☢ Send to X-ray", command=lambda: threading.Thread(target=lambda: self._update_status(db_id, 'xray')).start())
            menu.tk_popup(event.x_root, event.y_root)
        else:
             # Allow calling from history (Recall) and other lists
             threading.Thread(target=lambda: self._call_specific(db_id)).start()

    def _call_specific(self, db_id):
        try:
            requests.post(self.config['api_url'], json={'action': 'call_specific', 'id': db_id, 'room': self.room_id}, timeout=2)
            self.root.after(100, lambda: [self.refresh_queue(), self.load_all_lists()])
        except: pass

    def _update_status(self, db_id, status):
         try:
            requests.post(self.config['api_url'], json={'id': db_id, 'status': status}, timeout=2)
            self.root.after(100, lambda: [self.refresh_queue(), self.load_all_lists()])
         except: pass

    def load_all_lists(self):
        threading.Thread(target=self._do_fetch_all_lists).start()

    def _do_fetch_all_lists(self):
        try:
            r = requests.get(f"{self.api_base}/queue_data.php?room={self.room_id}&limit=200", timeout=2)
            data = r.json()
            if data['success']:
                wait, lab, xray, history = [], [], [], []
                for q in data['data']:
                    st = q['status']
                    if st == 'waiting': wait.append(q)
                    elif st == 'lab': lab.append(q)
                    elif st == 'xray': xray.append(q)
                    if st in ['completed', 'called']: history.append(q)
                self.root.after(0, lambda: self._update_trees(wait, lab, xray, history))
        except: pass

    def _update_trees(self, wait, lab, xray, history):
        if not hasattr(self, 'list_win') or not self.list_win.winfo_exists(): return
        self._fill_tree(self.tree_waiting, wait)
        self._fill_tree(self.tree_lab, lab)
        self._fill_tree(self.tree_xray, xray)
        self._fill_tree(self.tree_processed, history)

    def _fill_tree(self, tree, items):
        for i in tree.get_children(): tree.delete(i)
        for q in items: tree.insert('', 'end', iid=q['id'], values=(q.get('oqueue') or q.get('vn'), q.get('patient_name'), q.get('status')))

    def refresh_queue(self):
        threading.Thread(target=self._do_fetch_status).start()

    def _do_fetch_status(self):
        try:
            # Change limit 1 -> 10 to fix update issue
            r = requests.get(f"{self.api_base}/queue_data.php?room={self.room_id}&limit=10", timeout=2)
            data = r.json()
            found = False
            if data['success']:
                for item in data['data']:
                    if item['status'] == 'called':
                        self.root.after(0, lambda: self.update_ui(item))
                        found = True
                        break
            if not found:
                 self.root.after(0, lambda: [self.lbl_status.config(text="Standby"), self.lbl_q.config(text="-")])
        except: pass

    def update_ui(self, data):
        if str(data.get('room_number')) == str(self.room_id):
             name = data.get('patient_name', '')
             self.lbl_q.config(text=data.get('oqueue') or data.get('vn', '-'))
             self.lbl_status.config(text=name[:18] + ".." if len(name)>18 else name)

    def start_ws_listener(self):
        async def listen():
            while True:
                try:
                    async with websockets.connect(self.config['ws_url']) as websocket:
                        while True:
                            msg = await websocket.recv()
                            try:
                                if 'room' in json.loads(msg) and str(json.loads(msg)['room']) == str(self.room_id): self.refresh_queue()
                            except: pass
                except: await asyncio.sleep(5)
        loop = asyncio.new_event_loop()
        asyncio.set_event_loop(loop)
        loop.run_until_complete(listen())

if __name__ == "__main__":
    root = tk.Tk()
    app = MiniCallerApp(root)
    root.mainloop()
