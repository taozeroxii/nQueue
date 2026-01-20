import tkinter as tk
from tkinter import messagebox, ttk
import json
import requests
import threading
import os
import asyncio
import websockets

# Default Config
DEFAULT_CONFIG = {
    'api_url': "http://localhost/nQueue/public/api/update_status.php",
    'ws_url': "ws://localhost:8765",
    'api_base': "http://localhost/nQueue/public/api", 
    'room_id': 1
}

CONFIG_FILE = 'config.json'

class MiniCallerApp:
    def __init__(self, root):
        self.root = root
        self.root.title("Caller")
        self.root.geometry("280x200") # Slightly taller for 3 buttons + Status
        self.root.attributes('-topmost', True)
        self.root.resizable(False, False)
        
        self.config = self.load_config()
        self.room_id = self.config.get('room_id', 1)
        self.api_base = self.config.get('api_base', "http://localhost/nQueue/public/api")
        
        self.style = ttk.Style()
        self.style.theme_use('clam')
        self.style.configure('Action.TButton', font=('Segoe UI', 10, 'bold'), foreground='#4338ca')
        self.style.configure('Next.TButton', font=('Segoe UI', 12, 'bold'), foreground='#4338ca')
        
        self.create_mini_ui()
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

    def create_mini_ui(self):
        # 1. Top Bar: Room ID + Settings (Tiny)
        top = tk.Frame(self.root, bg="#f1f5f9", height=25)
        top.pack(fill='x')
        top.pack_propagate(False)
        
        self.lbl_room = tk.Label(top, text=f"Room {self.room_id}", font=("Arial", 9, "bold"), bg="#f1f5f9", fg="#64748b")
        self.lbl_room.pack(side='left', padx=5)
        
        tk.Button(top, text="⚙", command=self.open_settings, font=("Arial", 8), bd=0, bg="#f1f5f9").pack(side='right', padx=2)

        # 2. Status Display (Middle)
        mid = tk.Frame(self.root, bg="white")
        mid.pack(fill='both', expand=True, padx=5, pady=5)
        
        # Current Queue Number
        self.lbl_q = tk.Label(mid, text="-", font=("Arial", 32, "bold"), bg="white", fg="#ef4444")
        self.lbl_q.pack(pady=(5,0))
        
        # Name
        self.lbl_status = tk.Label(mid, text="Ready", font=("Arial", 10), bg="white", fg="gray")
        self.lbl_status.pack(pady=(0, 5))

        # 3. Action Buttons (Bottom - 3 Buttons)
        # Layout: [ Next (Big?) ] or [ Recall | Next | List ]
        # User asked for "3 buttons call next callback calllist 3 slots"
        # Let's do a uniform grid or Next bigger. 
        # "3 slots" implies equal width? Let's try equal.
        
        # 3. Action Buttons (Single Row, Icons Only)
        # Recall | Next | Lab | X-Ray | Lists
        
        btn_frame = tk.Frame(self.root, pady=5)
        btn_frame.pack(fill='x', padx=5, pady=5)
        
        # Weighted grid (5 columns)
        for i in range(5):
             btn_frame.columnconfigure(i, weight=1)

        # Style override for text
        self.style.configure('Short.TButton', font=('Segoe UI', 10, 'bold'))

        b_recall = ttk.Button(btn_frame, text="reCall", command=self.recall_prev, style='Short.TButton')
        b_recall.grid(row=0, column=0, sticky='ew', padx=1)
        
        b_next = ttk.Button(btn_frame, text="call", command=self.call_next, style='Short.TButton')
        b_next.grid(row=0, column=1, sticky='ew', padx=1)

        b_lab = ttk.Button(btn_frame, text="lab", command=lambda: self.set_status('lab'), style='Short.TButton')
        b_lab.grid(row=0, column=2, sticky='ew', padx=1)

        b_xray = ttk.Button(btn_frame, text="xray", command=lambda: self.set_status('xray'), style='Short.TButton')
        b_xray.grid(row=0, column=3, sticky='ew', padx=1)

        b_list = ttk.Button(btn_frame, text="list", command=self.show_list, style='Short.TButton')
        b_list.grid(row=0, column=4, sticky='ew', padx=1)

    def open_settings(self):
        win = tk.Toplevel(self.root)
        win.title("Setup")
        win.geometry("300x400")
        
        tk.Label(win, text="API Base URL:").pack(anchor='w', padx=5)
        e_base = tk.Entry(win, width=40)
        e_base.insert(0, self.api_base)
        e_base.pack(padx=5, pady=2)

        tk.Label(win, text="Department:").pack(anchor='w', padx=5, pady=(10,0))
        c_dept = ttk.Combobox(win)
        c_dept.pack(fill='x', padx=5)
        
        tk.Label(win, text="Room:").pack(anchor='w', padx=5, pady=(5,0))
        c_room = ttk.Combobox(win)
        c_room.pack(fill='x', padx=5)
        
        self.room_map = {} 
        
        def load_depts():
            try:
                r = requests.get(f"{e_base.get()}/departments.php", timeout=2)
                if r.json()['success']:
                    c_dept['values'] = r.json()['data']
            except: pass

        def load_rooms(event=None):
            dept = c_dept.get()
            if not dept: return
            threading.Thread(target=lambda: _do_load_rooms_bg(dept)).start()

        def _do_load_rooms_bg(dept):
            try:
                r = requests.get(f"{e_base.get()}/rooms.php?department={dept}", timeout=2)
                data = r.json()
                if data['success']:
                    names = []
                    self.room_map = {}
                    for item in data['data']:
                        n = f"{item['room_name']} (ID:{item['id']})"
                        names.append(n)
                        self.room_map[n] = item['id']
                    
                    # Updates back on Main Thread
                    def update_combo():
                        c_room['values'] = names
                        if names: c_room.current(0)
                        
                    win.after(0, update_combo)
            except: pass

        c_dept.bind("<<ComboboxSelected>>", load_rooms)
        
        threading.Thread(target=load_depts).start()

        def save():
            self.config['api_base'] = e_base.get()
            self.config['api_url'] = f"{e_base.get()}/update_status.php"
            
            sel = c_room.get()
            if sel and sel in self.room_map:
                self.room_id = self.room_map[sel]
                self.config['room_id'] = self.room_id
            
            self.save_config()
            self.lbl_room.config(text=f"Room {self.room_id}")
            self.refresh_queue()
            win.destroy()

        ttk.Button(win, text="Save", command=save).pack(pady=20)

    def call_next(self):
        threading.Thread(target=lambda: self._post_action('call_next')).start()

    def recall_prev(self):
        threading.Thread(target=lambda: self._post_action('recall')).start()

    def set_status(self, status):
        # Update current called patient (ID?)
        # We need to know who is currently called? 
        # Ideally we store current displayed ID or just fetch active one
        # Let's assume we can pass room and status to 'update_status.php' if we modify it
        # BUT standard pattern is `id` + `status`.
        # So we first need to know the ID of the 'called' patient in this room.
        threading.Thread(target=lambda: self._do_set_status_room_active(status)).start()
        
    def _do_set_status_room_active(self, status):
        try:
            # 1. Get Active
            r = requests.get(f"{self.api_base}/queue_data.php?room={self.room_id}&limit=1", timeout=2)
            d = r.json()
            found_id = None
            if d['success']:
                for item in d['data']:
                    if item['status'] == 'called':
                        found_id = item['id']
                        break
            
            if found_id:
                # 2. Update
                payload = {'id': found_id, 'status': status}
                requests.post(self.config['api_url'], json=payload, timeout=2)
                self.root.after(100, self.refresh_queue)
            else:
                pass # No active patient
        except: pass

    def _post_action(self, action):
        try:
            r = requests.post(self.config['api_url'], json={'action': action, 'room': self.room_id}, timeout=2)
            if r.status_code == 200:
                # Force immediate refresh
                self.root.after(100, self.refresh_queue)
        except Exception as e:
            print(f"API Error: {e}")
            pass

    def show_list(self):
        # Create Popup
        self.list_win = tk.Toplevel(self.root)
        self.list_win.title(f"Lists - Room {self.room_id}")
        self.list_win.geometry("500x400")
        
        # Tabs: Waiting | Lab | X-Ray | Processed
        tabs = ttk.Notebook(self.list_win)
        tabs.pack(fill='both', expand=True, padx=5, pady=5)
        
        self.tab_waiting = ttk.Frame(tabs)
        self.tab_lab = ttk.Frame(tabs)
        self.tab_xray = ttk.Frame(tabs)
        self.tab_processed = ttk.Frame(tabs) # For Completed/Called
        
        tabs.add(self.tab_waiting, text='Waiting')
        tabs.add(self.tab_lab, text='Lab')
        tabs.add(self.tab_xray, text='X-Ray')
        tabs.add(self.tab_processed, text='History') # Called + Completed
        
        # Setup Trees
        self.tree_waiting = self._create_tree(self.tab_waiting, 'waiting')
        self.tree_lab = self._create_tree(self.tab_lab, 'lab')
        self.tree_xray = self._create_tree(self.tab_xray, 'xray')
        self.tree_processed = self._create_tree(self.tab_processed, 'history')
        
        # Refresh Button
        btn = ttk.Button(self.list_win, text="Refresh All", command=self.load_all_lists)
        btn.pack(pady=5)
        
        # Initial Load
        self.load_all_lists()

    def _create_tree(self, parent, type_key):
        columns = ('q', 'name', 'status', 'action')
        tree = ttk.Treeview(parent, columns=columns, show='headings')
        tree.heading('q', text='Queue')
        tree.heading('name', text='Name')
        tree.heading('status', text='Status')
        tree.heading('action', text='Double Click to Call')
        
        tree.column('q', width=80, anchor='center')
        tree.column('name', width=180)
        tree.column('status', width=80, anchor='center')
        tree.column('action', width=120, anchor='center')
        
        tree.pack(fill='both', expand=True, padx=5, pady=5)
        
        # Bind Double Click
        tree.bind("<Double-1>", lambda e: self.on_list_action(e, tree, type_key))
        return tree

    def on_list_action(self, event, tree, type_key):
        item_id = tree.selection()[0]
        item = tree.item(item_id)
        # We stored real ID in tags or valid? 
        # Treeview item iid is obscure. Best store ID in values or iid.
        # Let's use IID as ID.
        real_id = item_id 
        
        # Action: Call Specific
        threading.Thread(target=lambda: self._call_specific(real_id)).start()

    def _call_specific(self, db_id):
        try:
            payload = {'action': 'call_specific', 'id': db_id, 'room': self.room_id}
            requests.post(self.config['api_url'], json=payload, timeout=2)
            self.root.after(100, self.refresh_queue)
            self.root.after(200, self.load_all_lists) # Refresh lists too which removes the item
        except: pass

    def load_all_lists(self):
        threading.Thread(target=self._do_fetch_all_lists).start()

    def _do_fetch_all_lists(self):
        try:
            # Fetch ALL for room (or filter if API supports array)
            # Simplest: Fetch all limit 100, client filter
            # Logic Update: Now fetching today's queues with all statuses
            url = f"{self.api_base}/queue_data.php?room={self.room_id}&limit=200" 
            r = requests.get(url, timeout=2)
            data = r.json()
            
            if data['success']:
                wait = []
                lab = []
                xray = []
                history = []
                
                for q in data['data']:
                    st = q['status']
                    if st == 'waiting': wait.append(q)
                    elif st == 'lab': lab.append(q)
                    elif st == 'xray': xray.append(q)
                    
                    # History includes 'completed', 'called', or any others
                    # Also maybe duplicate 'called' in history if needed?
                    # User requested "Every status". Let's put everything else in History, OR duplicate.
                    # Usually history means "Done".
                    
                    if st in ['completed', 'called']:
                        history.append(q)
                
                self.root.after(0, lambda: self._update_trees(wait, lab, xray, history))
        except: pass

    def _update_trees(self, wait, lab, xray, history):
        if not hasattr(self, 'list_win') or not self.list_win.winfo_exists(): return
        
        self._fill_tree(self.tree_waiting, wait)
        self._fill_tree(self.tree_lab, lab)
        self._fill_tree(self.tree_xray, xray)
        self._fill_tree(self.tree_processed, history)

    def _fill_tree(self, tree, items):
        for i in tree.get_children():
            tree.delete(i)
        for q in items:
            # Use ID as IID
            tree.insert('', 'end', iid=q['id'], values=(
                q.get('oqueue') or q.get('vn'),
                q.get('patient_name'),
                q.get('status'),
                'Double Click Call'
            ))

    def refresh_queue(self):
        # Initial Poll
        self.fetch_status()

    def update_ui(self, data):
        if str(data.get('room_number')) == str(self.room_id):
             vn = data.get('vn', '-')
             oq = data.get('oqueue', '-')
             name = data.get('patient_name', '')
             status = data.get('status', '')
             
             if status == 'called':
                 self.lbl_q.config(text=oq or vn)
                 self.lbl_status.config(text=name[:20] + ".." if len(name)>20 else name)
             # If status becomes completed/waiting, technically we should clear, 
             # but usually we keep last called until next one.

    def start_ws_listener(self):
        async def listen():
            while True:
                try:
                    async with websockets.connect(self.config['ws_url']) as websocket:
                        while True:
                            msg = await websocket.recv()
                            try:
                                data = json.loads(msg)
                                if 'room' in data and str(data['room']) == str(self.room_id):
                                     self.fetch_status() # Fetch full details to be safe
                            except: pass
                except:
                    await asyncio.sleep(5)

        loop = asyncio.new_event_loop()
        asyncio.set_event_loop(loop)
        loop.run_until_complete(listen())
        
    def fetch_status(self):
        threading.Thread(target=self._do_fetch_status).start()

    def _do_fetch_status(self):
        try:
            r = requests.get(f"{self.api_base}/queue_data.php?room={self.room_id}&limit=1", timeout=2)
            data = r.json()
            # Find the called item
            found = False
            if data['success']:
                for item in data['data']:
                    if item['status'] == 'called':
                        self.root.after(0, lambda: self.update_ui(item))
                        found = True
                        break
            
            if not found:
                 self.root.after(0, lambda: self.lbl_status.config(text="Waiting..."))
                 self.root.after(0, lambda: self.lbl_q.config(text="-"))

        except: pass

    def load_rooms(self, event=None):
        threading.Thread(target=self._do_load_rooms).start()

    def _do_load_rooms(self):
        dept = self.c_dept_value_safe() # Need safe access to widget on main thread? 
        # CAUTION: accessing c_dept.get() from thread might be unsafe in some Tk implementations, but mostly ok for read. 
        # Better: Pass value to thread.
        # However, `load_rooms` args is `event`. 
        # Let's read value in main thread, then spawn thread.
        pass

if __name__ == "__main__":
    root = tk.Tk()
    app = MiniCallerApp(root)
    root.mainloop()

