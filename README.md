# 🎓 LinguaMax — English Learning Platform (BETA)

LinguaMax คือแพลตฟอร์มการเรียนรู้ภาษาอังกฤษแบบโต้ตอบ (Interactive English Learning Platform) ที่พัฒนาด้วย **PHP & MySQL (Vanilla Stack)** ออกแบบให้รองรับทั้งระบบเรียนรู้ของนักเรียน และระบบจัดการหลังบ้านสำหรับครู/ผู้ดูแลระบบ (Admin)

---

## ✨ ฟีเจอร์หลัก (Key Features)

### 🎓 สำหรับนักเรียน (Student Portal)
- 🏠 **Dashboard**: สรุปข้อมูลการเรียน Streak ประจำวัน กิจกรรม และบทเรียนแนะนำ
- 📚 **บทเรียน (Lessons)**: อ่านบทเรียนไวยากรณ์ คำศัพท์ และประโยคตัวอย่าง
- 🃏 **Flashcards (ระบบความจำ SM-2 Algorithm)**: ทบทวนคำศัพท์ด้วยอัลกอริทึมเว้นระยะการทบทวน (Spaced Repetition)
- 🎮 **มินิเกมฝึกภาษา (Practice Games)**:
  - **Vocab Matching**: จับคู่คำศัพท์ภาษาอังกฤษ-ไทย
  - **Word Scramble**: เรียงอักษรให้เป็นคำศัพท์ที่ถูกต้อง
  - **Listening Challenge**: ฟังเสียงและพิมพ์คำศัพท์ตามที่ได้ยิน
- 📖 **การอ่าน (Reading Passages)**: อ่านบทความตามระดับความยาก พร้อมแบบทดสอบความเข้าใจ
- 📝 **ระบบข้อสอบ (Exams)**: ทำข้อสอบจับเวลา พร้อมเฉลยและสรุปคะแนนอัตโนมัติ
- 🏆 **Leaderboard & Badges**: ตารางอันดับคะแนน XP และระบบเหรียญรางวัลความสำเร็จ

### 👨‍🏫 สำหรับครู/ผู้ดูแลระบบ (Admin Portal)
- 📊 **Admin Dashboard**: ดูสถิตินักเรียน ผู้เข้าใช้งาน และผลการเรียนรวม
- 👥 **จัดการนักเรียน (Student Management)**: เพิ่ม แก้ไข รหัสนักเรียน Reset พาสเวิร์ด
- 📖 **จัดการบทเรียน & คำศัพท์ (Lessons & Vocab)**: เพิ่ม/แก้ไขบทเรียน และรายการคำศัพท์
- 📝 **จัดการข้อสอบ & บทความอ่าน (Exams & Reading)**: สร้างคลังข้อสอบและบทความอ่าน
- 🏆 **จัดการเหรียญรางวัล (Badges Management)**: กำหนดเงื่อนไขเหรียญรางวัล

---

## 🛠️ เทคโนโลยีที่ใช้ (Tech Stack)

- **Backend**: PHP 8.x (Pure / Vanilla PHP)
- **Database**: MySQL / MariaDB (PDO Driver)
- **Frontend**: HTML5, CSS3 (Modern Flexbox/Grid, Color CSS Variables, Glassmorphism design), Vanilla JavaScript (ES6)
- **Icons & Fonts**: FontAwesome 6, Google Fonts (Nunito)
- **Speech Integration**: Web Speech API (Text-to-Speech สำหรับออกเสียงคำศัพท์)

---

## 📁โครงสร้างโฟลเดอร์ (Project Structure)

```text
English_web/
├── api/                  # REST API Endpoints (AJAX Handlers)
│   ├── admin.php
│   ├── exams.php
│   ├── flashcards.php
│   ├── games.php
│   └── reading.php
├── assets/               # Static Assets
│   ├── css/
│   │   └── style.css     # Design System & Main Styles
│   ├── img/              # Character & Icon Assets
│   └── js/
│       ├── app.js        # Core Utilities & UI Helpers
│       └── tts.js        # Text-to-Speech Engine
├── config/
│   └── database.php      # Database Connection Setup
├── includes/
│   ├── functions.php     # Global Helper Functions & Auth Rules
│   ├── header.php        # Navigation Header
│   └── footer.php        # Navigation Footer
├── pages/                # Application Views
│   ├── admin/            # Admin Backoffice Pages
│   ├── auth/             # Login & Authentication
│   ├── exam/             # Exam Engine
│   ├── learning/         # Lessons & Reading
│   ├── practice/         # Flashcards & Games
│   ├── student/          # Dashboard, Profile, Leaderboard
│   └── system/           # Installer
├── index.php             # Main Router / Entry Point
├── setup.sql             # Full Database Schema & Seed Data
└── README.md
```

---

## 🚀 วิธีการติดตั้ง (Installation & Setup)

1. **Clone Repository**
   ```bash
   git clone https://github.com/nutbbgna1/Linguamax_BETA.git
   cd Linguamax_BETA
   ```

2. **วางไฟล์ในเว็บเซิร์ฟเวอร์ (XAMPP / Apache / Nginx)**
   - นำโฟลเดอร์ไปไว้ใน `htdocs` (สำหรับ XAMPP) หรือ Web Root ของเซิร์ฟเวอร์

3. **ตั้งค่าฐานข้อมูล (Database Import)**
   - สร้าง Database ใหม่ใน phpMyAdmin หรือ MySQL CLI
   - นำเข้าไฟล์ `setup.sql` เพื่อสร้างตารางและข้อมูลเริ่มต้น:
     ```sql
     mysql -u root -p < setup.sql
     ```

4. **แก้ไขไฟล์ Config**
   - เปิดไฟล์ `config/database.php` แล้วปรับค่า Host, Username, Password และชื่อ Database ให้ตรงกับเครื่องของคุณ:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_USER', 'root');
     define('DB_PASS', '');
     define('DB_NAME', 'u865886212_english');
     ```

5. **เข้าใช้งาน**
   - เปิดบราวเซอร์ไปที่ `http://localhost/English_web/`
   - **รหัสนักเรียนสำหรับทดสอบ**: `STD001` (หรือดูรหัสเพิ่มเติมในตาราง `users`)
   - **เข้าสู่ระบบผู้ดูแลระบบ (Admin)**: Username: `admin` | Password: `password`

---

## 📄 License

พัฒนาขึ้นสำหรับโครงการ **LinguaMax English Learning Platform**
