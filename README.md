
# nQueue - ระบบจัดการคิวอัจฉริยะ (Smart Queue Management System)

![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?style=for-the-badge&logo=php&logoColor=white)
![Python](https://img.shields.io/badge/Python-3.10%2B-3776AB?style=for-the-badge&logo=python&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0-00758F?style=for-the-badge&logo=mysql&logoColor=white)
![TailwindCSS](https://img.shields.io/badge/Tailwind_CSS-3.0-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)

nQueue คือระบบจัดการคิวแบบหลายหน้าจอที่ออกแบบมาสำหรับโรงพยาบาลและคลินิก รองรับการอัปเดตแบบเรียลไทม์ผ่าน WebSocket, ระบบเรียกคิวเสียงธรรมชาติ (Prompt4 Audio Engine), และระบบสแกนบาร์โค้ดผ่านฮาร์ดแวร์

---

## 🚀 คุณสมบัติเด่น (Features)

*   **Modern Dashboard**: หน้าจอแสดงผลดีไซน์ใหม่ Dark Mode + Glassmorphism สวยงามทันสมัย
*   **Prompt4 Audio Engine**: ระบบเรียกคิวเสียงภาษาไทยแบบธรรมชาติ (Native File-based) ไม่ต้องพึ่งพา Internet หรือ Google TTS อ่านเลขและห้องตรวจได้อย่างถูกต้อง
*   **Lab, X-Ray & Not Found Status**: แถบแสดงสถานะคิวรอผล Lab, รอ X-Ray และเรียกไม่พบ ด้านล่างหน้าจอ
*   **Real-time WebSocket**: อัปเดตคิวทันทีโดยไม่ต้องรีเฟรชหน้าจอ (Port 8765)
*   **Advanced Hardware Integration**:
    *   **Scanner Hub**: รองรับการต่อเครื่องสแกนบาร์โค้ด USB หลายตัวแยกอิสระ
    *   **Native Windows Hook**: ดักจับข้อมูลสแกนเนอร์ได้แม่นยำแม้ไม่ได้โฟกัสหน้าต่าง
*   **Dynamic Settings**: ปรับเปลี่ยนชื่อแผนก, กรองห้องตรวจ (Multi-select), หรือตั้งค่าการเชื่อมต่อได้โดยตรงจากหน้าจอ Dashboard
*   **Multi-Room Display**: รองรับแสดงผลหลายห้องตรวจในหน้าจอเดียว พร้อม Pagination อัตโนมัติ

---

## 🛠 สถาปัตยกรรมระบบ

| ส่วนประกอบ | เทคโนโลยี | รายละเอียด |
| :--- | :--- | :--- |
| **Backend** | PHP 8.x | RESTful API & WebSocket Server (Ratchet Port 8765) |
| **Database** | MySQL | จัดการข้อมูลคิว (Transaction) |
| **Frontend** | HTML5 / Tailwind | หน้าจอ Dashboard พร้อม Prompt4 Audio Engine |
| **Caller App** | Python (Tkinter) | หน้าต่างเล็ก "Always On Top" สำหรับแพทย์กดเรียกคิว |
| **Scanner Hub** | Python (ctypes) | โปรแกรมจัดการเครื่องสแกน แยกอุปกรณ์ด้วย Raw Input Hook |
| **Kiosk App** | Python (Tkinter) | หน้าจอ Self-service Kiosk สำหรับผู้ป่วยสแกนบัตรรับคิว |

### โครงสร้างไฟล์หลัก

```
nQueue/
├── public/                         # Web-accessible files
│   ├── api/                        # REST API endpoints
│   │   ├── update_status.php       # เรียกคิว, เปลี่ยนสถานะ (called/lab/xray/not_found)
│   │   ├── queue_data.php          # ดึงข้อมูลคิว
│   │   ├── departments.php         # ดึงข้อมูลแผนก
│   │   ├── rooms.php               # ดึงข้อมูลห้องตรวจ
│   │   ├── scan.php                # รับข้อมูลสแกนบาร์โค้ด
│   │   ├── settings.php            # จัดการตั้งค่าแผนก
│   │   ├── readq.php               # อ่านคิวจากระบบ HIS
│   │   └── manage_queue.php        # จัดการคิว (CRUD)
│   ├── multipledisplay.php         # 🖥 หน้าจอแสดงผลรวม (หลายห้อง + Lab/Xray/ไม่พบ)
│   ├── room.php                    # หน้าจอแสดงผลเฉพาะห้อง
│   ├── index.php                   # หน้า Landing
│   ├── kiosk.php / kiosk2.php      # หน้าจอ Kiosk (Browser-based)
│   ├── manage_queue.php            # หน้าจัดการคิว
│   ├── Prompt4/                    # ไฟล์เสียงภาษาไทย (.wav)
│   └── assets/                     # CSS, JS, vendor libraries
├── python_caller/                  # Python client applications
│   ├── caller.py                   # 🩺 โปรแกรมเรียกคิวสำหรับแพทย์
│   ├── raw_scanner.py              # 📟 โปรแกรมจัดการเครื่องสแกนบาร์โค้ด
│   └── kiosk_gui.py                # 🖥 Kiosk GUI (Native Python)
├── src/                            # PHP source classes
│   ├── Database.php                # Database connection (PDO)
│   ├── Notifier.php                # WebSocket notification sender
│   └── WebSocket/                  # WebSocket server handler
├── bin/
│   └── ws_server.php               # WebSocket server entry point
├── database.sql                    # Schema หลักของตาราง queues
├── not_found_migration.sql         # Migration: เพิ่มสถานะ not_found
├── rooms_migration.sql             # Migration: ตาราง rooms
├── settings_migration.sql          # Migration: ตาราง settings
└── .env                            # Environment configuration
```

---

## 📋 สถานะของคิว (Queue Statuses)

| สถานะ | คำอธิบาย | แสดงที่ |
| :--- | :--- | :--- |
| `waiting` | รอเรียก | Room Card (คิวรอ) |
| `called` | กำลังเรียก | Room Card (แสดงเด่น) |
| `lab` | รอผล Lab | แถบด้านล่าง (Laboratory) |
| `xray` | รอผล X-Ray | แถบด้านล่าง (Radiology) |
| `not_found` | เรียกไม่พบ | แถบด้านล่าง (Not Found - สีแดง) |
| `completed` | เสร็จสิ้น | ประวัติ (History) |

---

## 📦 การติดตั้ง

### 1. ความต้องการระบบเบื้องต้น
*   Web Server (Apache/Nginx เช่น Laragon, XAMPP)
*   PHP 8.0+ พร้อมส่วนเสริม `pdo_mysql`
*   Python 3.10+ (สำหรับการจัดการ Hardware)
*   Composer

### 2. ติดตั้งส่วน Backend (PHP)
1.  นำไฟล์โปรเจกต์ไปวางใน Web Root (เช่น `D:\laragon\www\nQueue`)
2.  ติดตั้ง Dependencies:
    ```bash
    composer install
    ```
3.  ตั้งค่า Environment:
    ```bash
    cp .env.example .env
    ```
    *แก้ไขไฟล์ `.env` ใส่ข้อมูล Database*

4.  **Database Import**: นำเข้าไฟล์ SQL ตามลำดับ:
    ```sql
    -- 1. สร้างตารางหลัก
    source database.sql;

    -- 2. สร้างตาราง rooms
    source rooms_migration.sql;

    -- 3. สร้างตาราง settings
    source settings_migration.sql;

    -- 4. เพิ่ม department ใน rooms
    source rooms_dept_migration.sql;

    -- 5. เพิ่มสถานะ not_found
    source not_found_migration.sql;
    ```

### 3. เตรียมไฟล์เสียง (สำคัญ)
ตรวจสอบว่ามีโฟลเดอร์ `public/Prompt4` และไฟล์เสียง `.wav` (Prompt4_Number.wav, Prompt4_0.wav ... Prompt4_Sir.wav, station_old.wav) อยู่ครบถ้วน เพื่อให้ระบบเรียกคิวทำงานได้

### 4. เริ่มต้นระบบ WebSocket
สำหรับการอัปเดตเรียลไทม์ ต้องรัน Service นี้ไว้เสมอ:
```bash
php bin/ws_server.php
```
*   WebSocket Server จะทำงานที่ Port **8765**
*   HTTP Notify Server จะทำงานที่ Port **8766**

หรือใช้ batch file ที่เตรียมไว้:
```bash
start_ws_server.bat
```

### 5. ติดตั้งโปรแกรมฝั่ง Client (Python)
สำหรับเครื่องแพทย์ (Caller) หรือเครื่องจุดซักประวัติ (Scanner):
```bash
cd python_caller
pip install -r requirements.txt
```

---

## 🖥 คู่มือการใช้งาน

### 1. หน้าจอแสดงผลรวม (Multi Display)
*   **URL:** `http://localhost/nQueue/public/multipledisplay.php`
*   **การตั้งค่า:** คลิกที่ชื่อแผนกมุมซ้ายบนเพื่อเปิด Settings:
    *   เปลี่ยนชื่อแผนก / Subtitle
    *   กรองแผนก (Department Filter)
    *   เลือกห้องตรวจที่ต้องการแสดง (Multi-select Room Filter)
    *   แก้ไข API Base URL และ WebSocket URL
    *   ตั้งจำนวนครั้งการเรียกซ้ำ (Call Repetitions)
*   **เสียงเรียก:** ระบบใช้ไฟล์เสียงจากโฟลเดอร์ `Prompt4` เสียงจะเล่นอัตโนมัติเมื่อมีคิวใหม่ (ต้องคลิกหน้าจอ 1 ครั้งเพื่ออนุญาตเสียงใน Browser)
*   **แถบด้านล่าง:** แสดง 3 ส่วน — **เรียกไม่พบ** (สีแดง) | **รอ Lab** | **รอ X-Ray**

### 2. โปรแกรมเรียกคิวสำหรับแพทย์ (Caller)
1.  รัน `python python_caller/caller.py`
2.  คลิก ⚙ **Settings** → เลือก Department → เลือก Room → **Save**
3.  **ปุ่มควบคุม:**

    | ปุ่ม | ฟังก์ชัน |
    | :--- | :--- |
    | 🔊 **CALL NEXT** | เรียกคิวถัดไป |
    | ↺ **Recall** | เรียกซ้ำคิวปัจจุบัน |
    | 🧪 **To Lab** | ส่งคิวปัจจุบันไปรอ Lab |
    | ☢ **To X-ray** | ส่งคิวปัจจุบันไปรอ X-ray |
    | ❌ **ไม่พบ** | ตั้งสถานะเรียกไม่พบ |
    | ☰ **List** | เปิดรายการคิว (Waiting/Lab/X-Ray/Not Found/History) |

4.  **List Window:** ดับเบิลคลิกคิวใน tab Waiting เพื่อเปิดเมนู (Call / Send to Lab / Send to X-ray / Not Found)

### 3. โปรแกรมจัดการสแกนเนอร์ (Scanner Hub)
สำหรับจุดซักประวัติที่มีเครื่องสแกนหลายตัว:
1.  รัน `python python_caller/raw_scanner.py`
2.  ไปที่แท็บ **Settings** → **Refresh Rooms**
3.  เลือกห้องและจับคู่กับเครื่องสแกน (Click **Select Device** → ยิงบาร์โค้ด)

### 4. Kiosk (ระบบรับคิวด้วยตนเอง)
*   **Browser-based:** `http://localhost/nQueue/public/kiosk.php`
*   **Native Python:** `python python_caller/kiosk_gui.py`

---

## 🔌 API Endpoints

| Method | Endpoint | คำอธิบาย |
| :--- | :--- | :--- |
| GET | `/api/queue_data.php` | ดึงข้อมูลคิว (params: `room`, `department`, `limit`) |
| GET | `/api/departments.php` | ดึงรายชื่อแผนก |
| GET | `/api/rooms.php` | ดึงรายชื่อห้อง (params: `department`) |
| GET | `/api/settings.php` | ดึงการตั้งค่า |
| POST | `/api/update_status.php` | เรียกคิว / เปลี่ยนสถานะ |
| POST | `/api/scan.php` | รับข้อมูลสแกนบาร์โค้ด |
| POST | `/api/readq.php` | อ่านคิวจากระบบ HIS |

### update_status.php Actions

```json
// เรียกคิวถัดไป
{"action": "call_next", "room": 1}

// เรียกซ้ำ
{"action": "recall", "room": 1}

// เรียกคิวเฉพาะ
{"action": "call_specific", "id": 123, "room": 1}

// เปลี่ยนสถานะโดยตรง
{"id": 123, "status": "lab"}       // ส่งรอ Lab
{"id": 123, "status": "xray"}      // ส่งรอ X-ray
{"id": 123, "status": "not_found"} // เรียกไม่พบ
{"id": 123, "status": "completed"} // เสร็จสิ้น
```

---

## 🔧 Google Chrome Kiosk Mode
สร้าง Shortcut เพื่อเปิดหน้าจอเต็มจอ:
```bat
"C:\Program Files\Google\Chrome\Application\chrome.exe" --kiosk --incognito --disable-pinch --overscroll-history-navigation=0 http://localhost/nQueue/public/multipledisplay.php
```

---

## 🐍 Python All-in-One Version (Alternative)
*ไฟล์ `app.py` เป็นเวอร์ชันทางเลือกที่เขียนด้วย Python Flask ทั้งหมด (Backend + WebSocket)*

*   **API**: ใช้ Flask แทน PHP
*   **TTS**: ใช้ Google Translate TTS (Online) แทน Prompt4 (File-based)
*   **Run**: `python app.py` (Port 5000)

---

## 🐛 Troubleshooting

| ปัญหา | วิธีแก้ไข |
| :--- | :--- |
| **แถบสีแดงขึ้นว่า Disconnected** | ตรวจสอบว่า `php bin/ws_server.php` รันอยู่หรือไม่ และ Port 8765 ไม่ถูกบล็อก |
| **เสียงไม่ดัง** | 1. คลิกหน้าจอ 1 ครั้งเพื่อ Unlock Audio Context<br>2. ตรวจสอบว่ามีโฟลเดอร์ `public/Prompt4` |
| **เรียกคิวแล้วหน้าจอไม่เปลี่ยน** | ตรวจสอบการเชื่อมต่อ WebSocket หรือลองกด Refresh หน้าจอ |
| **ปุ่ม Lab/Xray/ไม่พบ กดไม่ได้** | ต้องเรียกคิวก่อน (กด CALL NEXT) ปุ่มจะ enable เมื่อมีคิวที่ถูกเรียก |
| **Database error: Data truncated** | รัน `not_found_migration.sql` เพื่อเพิ่มสถานะ `not_found` ใน ENUM |

---

## ลิขสิทธิ์
สำหรับใช้งานภายในหน่วยงาน (Hospital / Clinic Internal Use)