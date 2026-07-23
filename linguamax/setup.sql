-- ============================================================
-- LinguaMax — English Learning Platform
-- Database Setup Script
-- ============================================================

CREATE DATABASE IF NOT EXISTS u865886212_english CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE u865886212_english;

-- ── USERS ───────────────────────────────────────────────────
DROP TABLE IF EXISTS user_badges, user_daily_progress, user_streaks, flashcard_progress, exam_results, game_scores, reading_progress, lesson_progress;
DROP TABLE IF EXISTS reading_questions, reading_passages, badges, questions, exams, vocabulary, lessons, daily_challenges, users;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(10) UNIQUE NOT NULL,
    username VARCHAR(50) DEFAULT NULL,
    password VARCHAR(255) DEFAULT NULL,
    fname VARCHAR(100) NOT NULL,
    lname VARCHAR(100) NOT NULL,
    nickname VARCHAR(50) NOT NULL,
    role ENUM('student','admin') DEFAULT 'student',
    level ENUM('beginner','intermediate','advanced') DEFAULT 'beginner',
    xp INT DEFAULT 0,
    coins INT DEFAULT 0,
    avatar_color VARCHAR(7) DEFAULT '#6C63FF',
    profile_pic VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ── STREAKS ─────────────────────────────────────────────────
CREATE TABLE user_streaks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    current_streak INT DEFAULT 0,
    longest_streak INT DEFAULT 0,
    last_activity_date DATE DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── LESSONS ─────────────────────────────────────────────────
CREATE TABLE lessons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    level ENUM('beginner','intermediate','advanced') NOT NULL,
    description TEXT,
    content LONGTEXT,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ── LESSON PROGRESS ─────────────────────────────────────────
CREATE TABLE lesson_progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    lesson_id INT NOT NULL,
    completed BOOLEAN DEFAULT FALSE,
    completed_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE,
    UNIQUE KEY uk_user_lesson (user_id, lesson_id)
) ENGINE=InnoDB;

-- ── VOCABULARY ──────────────────────────────────────────────
CREATE TABLE vocabulary (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lesson_id INT DEFAULT NULL,
    word_en VARCHAR(255) NOT NULL,
    word_th VARCHAR(255) NOT NULL,
    pronunciation VARCHAR(255) DEFAULT NULL,
    example_sentence TEXT DEFAULT NULL,
    level ENUM('beginner','intermediate','advanced') DEFAULT 'beginner',
    category VARCHAR(50) DEFAULT 'general',
    FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ── FLASHCARD PROGRESS (Spaced Repetition SM-2) ────────────
CREATE TABLE flashcard_progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    vocabulary_id INT NOT NULL,
    ease_factor FLOAT DEFAULT 2.5,
    interval_days INT DEFAULT 0,
    repetitions INT DEFAULT 0,
    next_review DATE DEFAULT NULL,
    last_reviewed DATETIME DEFAULT NULL,
    times_correct INT DEFAULT 0,
    times_wrong INT DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (vocabulary_id) REFERENCES vocabulary(id) ON DELETE CASCADE,
    UNIQUE KEY uk_user_vocab (user_id, vocabulary_id)
) ENGINE=InnoDB;

-- ── EXAMS ───────────────────────────────────────────────────
CREATE TABLE exams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    lesson_id INT DEFAULT NULL,
    level ENUM('beginner','intermediate','advanced') NOT NULL,
    total_questions INT DEFAULT 20,
    time_minutes INT DEFAULT 30,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ── QUESTIONS ───────────────────────────────────────────────
CREATE TABLE questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    exam_id INT DEFAULT NULL,
    question_text TEXT NOT NULL,
    choice_a VARCHAR(500) NOT NULL,
    choice_b VARCHAR(500) NOT NULL,
    choice_c VARCHAR(500) NOT NULL,
    choice_d VARCHAR(500) NOT NULL,
    choice_e VARCHAR(500) DEFAULT NULL,
    correct_answer TINYINT NOT NULL COMMENT '0=A,1=B,2=C,3=D,4=E',
    level ENUM('beginner','intermediate','advanced') DEFAULT 'beginner',
    FOREIGN KEY (exam_id) REFERENCES exams(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ── EXAM RESULTS ────────────────────────────────────────────
CREATE TABLE exam_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    exam_id INT NOT NULL,
    score INT NOT NULL,
    total INT NOT NULL,
    percentage FLOAT NOT NULL,
    time_spent INT DEFAULT 0 COMMENT 'seconds',
    answers_json JSON DEFAULT NULL,
    completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (exam_id) REFERENCES exams(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── USER SURVEYS (Kids Survey) ──────────────────────────────
CREATE TABLE user_surveys (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    answers_json JSON NOT NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── DAILY CHALLENGES ────────────────────────────────────────
CREATE TABLE daily_challenges (
    id INT AUTO_INCREMENT PRIMARY KEY,
    challenge_date DATE NOT NULL UNIQUE,
    challenge_type ENUM('flashcard','quiz','reading','game') NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    challenge_data JSON DEFAULT NULL,
    xp_reward INT DEFAULT 15
) ENGINE=InnoDB;

-- ── USER DAILY PROGRESS ─────────────────────────────────────
CREATE TABLE user_daily_progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    challenge_date DATE NOT NULL,
    completed BOOLEAN DEFAULT FALSE,
    completed_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY uk_user_date (user_id, challenge_date)
) ENGINE=InnoDB;

-- ── GAME SENTENCES ──────────────────────────────────────────
CREATE TABLE game_sentences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sentence_en TEXT NOT NULL,
    sentence_th TEXT NOT NULL
) ENGINE=InnoDB;

-- ── GAME FILL BLANKS ────────────────────────────────────────
CREATE TABLE game_fill_blanks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question_text TEXT NOT NULL,
    correct_answer VARCHAR(255) NOT NULL,
    choice_1 VARCHAR(255) NOT NULL,
    choice_2 VARCHAR(255) NOT NULL,
    choice_3 VARCHAR(255) NOT NULL,
    choice_4 VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

-- ── BADGES & REWARDS ────────────────────────────────────────
CREATE TABLE badges (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    name_th VARCHAR(100) NOT NULL,
    description TEXT,
    icon VARCHAR(50) NOT NULL,
    color VARCHAR(7) DEFAULT '#FFB347',
    requirement_type VARCHAR(50) NOT NULL,
    requirement_value INT NOT NULL,
    xp_reward INT DEFAULT 20,
    sort_order INT DEFAULT 0
) ENGINE=InnoDB;

-- ── USER BADGES ─────────────────────────────────────────────
CREATE TABLE user_badges (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    badge_id INT NOT NULL,
    earned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (badge_id) REFERENCES badges(id) ON DELETE CASCADE,
    UNIQUE KEY uk_user_badge (user_id, badge_id)
) ENGINE=InnoDB;

-- ── GAME SCORES ─────────────────────────────────────────────
CREATE TABLE game_scores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    game_type ENUM('match_pairs','sentence_order','fill_blank') NOT NULL,
    score INT NOT NULL,
    max_score INT DEFAULT 100,
    time_spent INT DEFAULT 0,
    played_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── READING PASSAGES ────────────────────────────────────────
CREATE TABLE reading_passages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    title_th VARCHAR(255) DEFAULT NULL,
    content LONGTEXT NOT NULL,
    level ENUM('beginner','intermediate','advanced') NOT NULL,
    word_count INT DEFAULT 0,
    category VARCHAR(50) DEFAULT 'story',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ── READING QUESTIONS ───────────────────────────────────────
CREATE TABLE reading_questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    passage_id INT NOT NULL,
    question_text TEXT NOT NULL,
    choice_a VARCHAR(500) NOT NULL,
    choice_b VARCHAR(500) NOT NULL,
    choice_c VARCHAR(500) NOT NULL,
    choice_d VARCHAR(500) NOT NULL,
    correct_answer TINYINT NOT NULL COMMENT '0=A,1=B,2=C,3=D',
    FOREIGN KEY (passage_id) REFERENCES reading_passages(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── READING PROGRESS ────────────────────────────────────────
CREATE TABLE reading_progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    passage_id INT NOT NULL,
    score INT DEFAULT 0,
    total INT DEFAULT 0,
    completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (passage_id) REFERENCES reading_passages(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ═══════════════════════════════════════════════════════════
-- SEED DATA
-- ═══════════════════════════════════════════════════════════

-- ── USERS ───────────────────────────────────────────────────
INSERT INTO users (code, username, password, fname, lname, nickname, role, level, xp, coins, avatar_color) VALUES
('ADMIN01', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ครูสมศรี', 'ใจดี', 'ครูศรี', 'admin', 'advanced', 0, 0, '#6C63FF'),
('STD001', NULL, NULL, 'สมชาย', 'ใจดี', 'เจ', 'student', 'beginner', 250, 80, '#FF6B9D'),
('STD002', NULL, NULL, 'สมหญิง', 'รักเรียน', 'หญิง', 'student', 'intermediate', 520, 150, '#45B7D1'),
('STD003', NULL, NULL, 'วิชัย', 'เก่งมาก', 'ชัย', 'student', 'beginner', 120, 40, '#2ED573'),
('STD004', NULL, NULL, 'พิมพ์', 'สวยงาม', 'พิม', 'student', 'advanced', 980, 300, '#FFB347'),
('STD005', NULL, NULL, 'ธนา', 'รวยล้น', 'ต้น', 'student', 'intermediate', 380, 110, '#A29BFE');

-- ── STREAKS ─────────────────────────────────────────────────
INSERT INTO user_streaks (user_id, current_streak, longest_streak, last_activity_date) VALUES
(2, 3, 7, CURDATE()),
(3, 5, 12, CURDATE()),
(4, 1, 5, DATE_SUB(CURDATE(), INTERVAL 1 DAY)),
(5, 15, 15, CURDATE()),
(6, 0, 8, DATE_SUB(CURDATE(), INTERVAL 3 DAY));

-- ── LESSONS ─────────────────────────────────────────────────
INSERT INTO lessons (title, level, description, content, sort_order) VALUES
('Greetings & Self-Introduction', 'beginner', 'Hello, My name is..., How are you?',
'<h3>🎯 วัตถุประสงค์</h3>
<p>เรียนรู้การทักทายและแนะนำตัวเป็นภาษาอังกฤษ</p>
<h3>📖 Key Phrases</h3>
<ul>
<li><strong>Hello / Hi</strong> — สวัสดี</li>
<li><strong>Good morning / afternoon / evening</strong> — สวัสดีตอนเช้า/บ่าย/เย็น</li>
<li><strong>My name is...</strong> — ฉันชื่อ...</li>
<li><strong>What is your name?</strong> — คุณชื่ออะไร?</li>
<li><strong>Nice to meet you</strong> — ยินดีที่ได้รู้จัก</li>
<li><strong>How are you?</strong> — สบายดีไหม?</li>
<li><strong>I am fine, thank you.</strong> — ฉันสบายดี ขอบคุณ</li>
</ul>
<h3>💬 Dialogue</h3>
<div class="dialogue-box">
<p><strong>A:</strong> Hello! My name is Somchai. What''s your name?</p>
<p><strong>B:</strong> Hi! I''m Suda. Nice to meet you!</p>
<p><strong>A:</strong> Nice to meet you too! How are you?</p>
<p><strong>B:</strong> I''m fine, thank you. And you?</p>
<p><strong>A:</strong> I''m great! Where are you from?</p>
<p><strong>B:</strong> I''m from Thailand.</p>
</div>
<h3>📝 Tips</h3>
<p>เวลาทักทายให้ยิ้มและสบตา จะทำให้สนทนาเป็นธรรมชาติมากขึ้น!</p>', 1),

('Numbers & Counting', 'beginner', 'One, two, three... to one hundred',
'<h3>🎯 วัตถุประสงค์</h3>
<p>เรียนรู้การนับเลข 1–100 เป็นภาษาอังกฤษ</p>
<h3>📖 Numbers 1-20</h3>
<div class="number-grid">1 = one, 2 = two, 3 = three, 4 = four, 5 = five, 6 = six, 7 = seven, 8 = eight, 9 = nine, 10 = ten, 11 = eleven, 12 = twelve, 13 = thirteen, 14 = fourteen, 15 = fifteen, 16 = sixteen, 17 = seventeen, 18 = eighteen, 19 = nineteen, 20 = twenty</div>
<h3>📖 Tens</h3>
<p>30 = thirty, 40 = forty, 50 = fifty, 60 = sixty, 70 = seventy, 80 = eighty, 90 = ninety, 100 = one hundred</p>', 2),

('Days & Months', 'beginner', 'Monday, January, today, tomorrow',
'<h3>🎯 วัตถุประสงค์</h3><p>เรียนรู้วันในสัปดาห์และเดือนต่างๆ</p>
<h3>📅 Days of the Week</h3>
<p>Monday (จันทร์), Tuesday (อังคาร), Wednesday (พุธ), Thursday (พฤหัสบดี), Friday (ศุกร์), Saturday (เสาร์), Sunday (อาทิตย์)</p>
<h3>📅 Months of the Year</h3>
<p>January, February, March, April, May, June, July, August, September, October, November, December</p>', 3),

('Family Members', 'beginner', 'Father, mother, brother, sister',
'<h3>🎯 วัตถุประสงค์</h3><p>เรียนรู้คำศัพท์เกี่ยวกับครอบครัว</p>
<h3>👨‍👩‍👧‍👦 Family</h3>
<p>Father (พ่อ), Mother (แม่), Brother (พี่/น้องชาย), Sister (พี่/น้องสาว), Grandfather (ปู่/ตา), Grandmother (ย่า/ยาย), Uncle (ลุง/อา), Aunt (ป้า/น้า)</p>', 4),

('Colors & Shapes', 'beginner', 'Red, blue, circle, square',
'<h3>🎯 วัตถุประสงค์</h3><p>เรียนรู้สีและรูปทรงต่างๆ</p>
<h3>🎨 Colors</h3>
<p>Red (แดง), Blue (น้ำเงิน), Green (เขียว), Yellow (เหลือง), Orange (ส้ม), Purple (ม่วง), Pink (ชมพู), Black (ดำ), White (ขาว), Brown (น้ำตาล)</p>
<h3>🔷 Shapes</h3>
<p>Circle (วงกลม), Square (สี่เหลี่ยมจัตุรัส), Triangle (สามเหลี่ยม), Rectangle (สี่เหลี่ยมผืนผ้า), Star (ดาว), Heart (หัวใจ)</p>', 5),

('Present Simple Tense', 'intermediate', 'I go, She goes, Do you...?',
'<h3>🎯 วัตถุประสงค์</h3><p>เข้าใจหลักการใช้ Present Simple Tense</p>
<h3>📖 Structure</h3>
<p><strong>Positive:</strong> Subject + V1 (เติม s/es สำหรับ he, she, it)</p>
<p><strong>Negative:</strong> Subject + do/does + not + V1</p>
<p><strong>Question:</strong> Do/Does + Subject + V1?</p>
<h3>📝 Examples</h3>
<p>I <strong>go</strong> to school every day.</p>
<p>She <strong>goes</strong> to school every day.</p>
<p><strong>Do</strong> you like ice cream?</p>
<p>He <strong>does not</strong> (doesn''t) play football.</p>', 6),

('Past Simple Tense', 'intermediate', 'I went, She visited, Did you...?',
'<h3>🎯 วัตถุประสงค์</h3><p>เข้าใจหลักการใช้ Past Simple Tense</p>
<h3>📖 Structure</h3>
<p><strong>Positive:</strong> Subject + V2</p>
<p><strong>Negative:</strong> Subject + did not + V1</p>
<p><strong>Question:</strong> Did + Subject + V1?</p>
<h3>📝 Examples</h3>
<p>I <strong>went</strong> to the park yesterday.</p>
<p>She <strong>visited</strong> her grandmother last week.</p>
<p><strong>Did</strong> you eat breakfast this morning?</p>', 7),

('Future Tense', 'intermediate', 'Will, going to, shall',
'<h3>🎯 วัตถุประสงค์</h3><p>เข้าใจหลักการใช้ Future Tense</p>
<h3>📖 Will</h3>
<p>Subject + will + V1</p>
<p>I <strong>will go</strong> to the beach tomorrow.</p>
<h3>📖 Going to</h3>
<p>Subject + am/is/are + going to + V1</p>
<p>She <strong>is going to</strong> study tonight.</p>', 8),

('Comparative & Superlative', 'intermediate', 'bigger, the biggest, more beautiful',
'<h3>🎯 วัตถุประสงค์</h3><p>เรียนรู้การเปรียบเทียบ</p>
<h3>📖 Comparative (-er / more)</h3>
<p>big → bigger, tall → taller, beautiful → more beautiful</p>
<h3>📖 Superlative (-est / most)</h3>
<p>big → the biggest, tall → the tallest, beautiful → the most beautiful</p>', 9),

('Conditionals', 'advanced', 'If I were..., If I had...',
'<h3>🎯 วัตถุประสงค์</h3><p>เรียนรู้ประโยคเงื่อนไข (If clauses)</p>
<h3>📖 Zero Conditional</h3><p>If + present simple, present simple (ความจริงทั่วไป)</p>
<h3>📖 First Conditional</h3><p>If + present simple, will + V1 (เป็นไปได้ในอนาคต)</p>
<h3>📖 Second Conditional</h3><p>If + past simple, would + V1 (สมมติไม่จริง)</p>
<h3>📖 Third Conditional</h3><p>If + past perfect, would have + V3 (เสียดายอดีต)</p>', 10),

('Passive Voice', 'advanced', 'The cake was eaten, It is being built',
'<h3>🎯 วัตถุประสงค์</h3><p>เรียนรู้ Passive Voice (ประโยคถูกกระทำ)</p>
<h3>📖 Structure</h3>
<p>Subject + be + V3 (Past Participle)</p>
<h3>📝 Examples</h3>
<p>Active: The dog <strong>bites</strong> the man.</p>
<p>Passive: The man <strong>is bitten</strong> by the dog.</p>', 11),

('Reported Speech', 'advanced', 'He said that..., She told me...',
'<h3>🎯 วัตถุประสงค์</h3><p>เรียนรู้การเปลี่ยนคำพูดตรงเป็นคำพูดอ้อม</p>
<h3>📖 Rules</h3>
<p>Direct: "I <strong>am</strong> happy."</p>
<p>Reported: He said he <strong>was</strong> happy.</p>
<p>เลื่อน tense ลงหนึ่งขั้น เช่น am→was, will→would, can→could</p>', 12);

-- ── VOCABULARY ──────────────────────────────────────────────
INSERT INTO vocabulary (lesson_id, word_en, word_th, pronunciation, example_sentence, level, category) VALUES
-- Greetings
(1, 'Hello', 'สวัสดี', '/həˈloʊ/', 'Hello! How are you?', 'beginner', 'greetings'),
(1, 'Good morning', 'สวัสดีตอนเช้า', '/ɡʊd ˈmɔːrnɪŋ/', 'Good morning, teacher!', 'beginner', 'greetings'),
(1, 'Good afternoon', 'สวัสดีตอนบ่าย', '/ɡʊd ˌæftərˈnuːn/', 'Good afternoon, everyone!', 'beginner', 'greetings'),
(1, 'Good evening', 'สวัสดีตอนเย็น', '/ɡʊd ˈiːvnɪŋ/', 'Good evening, sir.', 'beginner', 'greetings'),
(1, 'Goodbye', 'ลาก่อน', '/ɡʊdˈbaɪ/', 'Goodbye! See you tomorrow.', 'beginner', 'greetings'),
(1, 'Thank you', 'ขอบคุณ', '/θæŋk juː/', 'Thank you very much!', 'beginner', 'greetings'),
(1, 'Please', 'ได้โปรด', '/pliːz/', 'Please sit down.', 'beginner', 'greetings'),
(1, 'Sorry', 'ขอโทษ', '/ˈsɒri/', 'I''m sorry for being late.', 'beginner', 'greetings'),
(1, 'Excuse me', 'ขอโทษ (ขออนุญาต)', '/ɪkˈskjuːz miː/', 'Excuse me, where is the restroom?', 'beginner', 'greetings'),
(1, 'Nice to meet you', 'ยินดีที่ได้รู้จัก', '/naɪs tuː miːt juː/', 'Nice to meet you, Suda!', 'beginner', 'greetings'),

-- Numbers
(2, 'One', 'หนึ่ง', '/wʌn/', 'I have one dog.', 'beginner', 'numbers'),
(2, 'Two', 'สอง', '/tuː/', 'She has two cats.', 'beginner', 'numbers'),
(2, 'Three', 'สาม', '/θriː/', 'There are three apples.', 'beginner', 'numbers'),
(2, 'Four', 'สี่', '/fɔːr/', 'I see four birds.', 'beginner', 'numbers'),
(2, 'Five', 'ห้า', '/faɪv/', 'Give me five minutes.', 'beginner', 'numbers'),
(2, 'Ten', 'สิบ', '/tɛn/', 'I am ten years old.', 'beginner', 'numbers'),
(2, 'Twenty', 'ยี่สิบ', '/ˈtwɛnti/', 'There are twenty students.', 'beginner', 'numbers'),
(2, 'Fifty', 'ห้าสิบ', '/ˈfɪfti/', 'The book costs fifty baht.', 'beginner', 'numbers'),
(2, 'Hundred', 'ร้อย', '/ˈhʌndrɪd/', 'I scored one hundred!', 'beginner', 'numbers'),
(2, 'Thousand', 'พัน', '/ˈθaʊzənd/', 'A thousand stars in the sky.', 'beginner', 'numbers'),

-- Days & Months
(3, 'Monday', 'วันจันทร์', '/ˈmʌndeɪ/', 'I go to school on Monday.', 'beginner', 'time'),
(3, 'Tuesday', 'วันอังคาร', '/ˈtjuːzdeɪ/', 'We have English on Tuesday.', 'beginner', 'time'),
(3, 'Wednesday', 'วันพุธ', '/ˈwɛnzdeɪ/', 'Wednesday is the middle of the week.', 'beginner', 'time'),
(3, 'Thursday', 'วันพฤหัสบดี', '/ˈθɜːrzdeɪ/', 'Thursday comes after Wednesday.', 'beginner', 'time'),
(3, 'Friday', 'วันศุกร์', '/ˈfraɪdeɪ/', 'I love Fridays!', 'beginner', 'time'),
(3, 'Saturday', 'วันเสาร์', '/ˈsætərdeɪ/', 'We play football on Saturday.', 'beginner', 'time'),
(3, 'Sunday', 'วันอาทิตย์', '/ˈsʌndeɪ/', 'Sunday is a day of rest.', 'beginner', 'time'),
(3, 'Today', 'วันนี้', '/təˈdeɪ/', 'Today is a beautiful day.', 'beginner', 'time'),
(3, 'Tomorrow', 'พรุ่งนี้', '/təˈmɒroʊ/', 'See you tomorrow!', 'beginner', 'time'),
(3, 'Yesterday', 'เมื่อวาน', '/ˈjɛstərdeɪ/', 'I went to the park yesterday.', 'beginner', 'time'),

-- Family
(4, 'Father', 'พ่อ', '/ˈfɑːðər/', 'My father is a teacher.', 'beginner', 'family'),
(4, 'Mother', 'แม่', '/ˈmʌðər/', 'My mother cooks delicious food.', 'beginner', 'family'),
(4, 'Brother', 'พี่/น้องชาย', '/ˈbrʌðər/', 'My brother is older than me.', 'beginner', 'family'),
(4, 'Sister', 'พี่/น้องสาว', '/ˈsɪstər/', 'My sister likes reading.', 'beginner', 'family'),
(4, 'Grandfather', 'ปู่/ตา', '/ˈɡrændfɑːðər/', 'My grandfather tells great stories.', 'beginner', 'family'),
(4, 'Grandmother', 'ย่า/ยาย', '/ˈɡrændmʌðər/', 'My grandmother makes cookies.', 'beginner', 'family'),
(4, 'Uncle', 'ลุง/อา', '/ˈʌŋkl/', 'My uncle lives in Bangkok.', 'beginner', 'family'),
(4, 'Aunt', 'ป้า/น้า', '/ænt/', 'My aunt is a doctor.', 'beginner', 'family'),
(4, 'Cousin', 'ลูกพี่ลูกน้อง', '/ˈkʌzn/', 'I play with my cousin.', 'beginner', 'family'),
(4, 'Baby', 'ทารก', '/ˈbeɪbi/', 'The baby is sleeping.', 'beginner', 'family'),

-- Colors & Shapes
(5, 'Red', 'แดง', '/rɛd/', 'The apple is red.', 'beginner', 'colors'),
(5, 'Blue', 'น้ำเงิน', '/bluː/', 'The sky is blue.', 'beginner', 'colors'),
(5, 'Green', 'เขียว', '/ɡriːn/', 'The grass is green.', 'beginner', 'colors'),
(5, 'Yellow', 'เหลือง', '/ˈjɛloʊ/', 'The sun is yellow.', 'beginner', 'colors'),
(5, 'Orange', 'ส้ม', '/ˈɒrɪndʒ/', 'I like orange juice.', 'beginner', 'colors'),
(5, 'Purple', 'ม่วง', '/ˈpɜːrpl/', 'She wears a purple dress.', 'beginner', 'colors'),
(5, 'Pink', 'ชมพู', '/pɪŋk/', 'The flower is pink.', 'beginner', 'colors'),
(5, 'Circle', 'วงกลม', '/ˈsɜːrkl/', 'Draw a circle on the paper.', 'beginner', 'shapes'),
(5, 'Square', 'สี่เหลี่ยมจัตุรัส', '/skwɛr/', 'A square has four equal sides.', 'beginner', 'shapes'),
(5, 'Triangle', 'สามเหลี่ยม', '/ˈtraɪæŋɡl/', 'A triangle has three sides.', 'beginner', 'shapes'),

-- Intermediate Vocabulary
(6, 'Always', 'เสมอ', '/ˈɔːlweɪz/', 'I always brush my teeth.', 'intermediate', 'adverbs'),
(6, 'Usually', 'ปกติ', '/ˈjuːʒuəli/', 'She usually wakes up at 7.', 'intermediate', 'adverbs'),
(6, 'Sometimes', 'บางครั้ง', '/ˈsʌmtaɪmz/', 'I sometimes eat pizza.', 'intermediate', 'adverbs'),
(6, 'Never', 'ไม่เคย', '/ˈnɛvər/', 'He never lies.', 'intermediate', 'adverbs'),
(6, 'Often', 'บ่อย', '/ˈɒfn/', 'We often go to the park.', 'intermediate', 'adverbs'),

(7, 'Visited', 'เยี่ยม (อดีต)', '/ˈvɪzɪtɪd/', 'I visited my grandma last week.', 'intermediate', 'verbs'),
(7, 'Bought', 'ซื้อ (อดีต)', '/bɔːt/', 'She bought a new bag.', 'intermediate', 'verbs'),
(7, 'Ate', 'กิน (อดีต)', '/eɪt/', 'We ate sushi for dinner.', 'intermediate', 'verbs'),
(7, 'Went', 'ไป (อดีต)', '/wɛnt/', 'They went to the beach.', 'intermediate', 'verbs'),
(7, 'Saw', 'เห็น (อดีต)', '/sɔː/', 'I saw a rainbow yesterday.', 'intermediate', 'verbs'),

(8, 'Will', 'จะ', '/wɪl/', 'I will help you.', 'intermediate', 'grammar'),
(8, 'Going to', 'กำลังจะ', '/ˈɡoʊɪŋ tuː/', 'She is going to study tonight.', 'intermediate', 'grammar'),
(8, 'Shall', 'จะ (เสนอ)', '/ʃæl/', 'Shall we go?', 'intermediate', 'grammar'),
(8, 'Probably', 'อาจจะ', '/ˈprɒbəbli/', 'It will probably rain.', 'intermediate', 'grammar'),
(8, 'Definitely', 'แน่นอน', '/ˈdɛfɪnɪtli/', 'I will definitely come.', 'intermediate', 'grammar'),

-- Comparatives
(9, 'Bigger', 'ใหญ่กว่า', '/ˈbɪɡər/', 'An elephant is bigger than a cat.', 'intermediate', 'comparison'),
(9, 'Smaller', 'เล็กกว่า', '/ˈsmɔːlər/', 'An ant is smaller than a dog.', 'intermediate', 'comparison'),
(9, 'Faster', 'เร็วกว่า', '/ˈfæstər/', 'A car is faster than a bicycle.', 'intermediate', 'comparison'),
(9, 'Better', 'ดีกว่า', '/ˈbɛtər/', 'This book is better than that one.', 'intermediate', 'comparison'),
(9, 'The best', 'ดีที่สุด', '/ðə bɛst/', 'She is the best student.', 'intermediate', 'comparison'),

-- Advanced
(10, 'Although', 'ถึงแม้ว่า', '/ɔːlˈðoʊ/', 'Although it rained, we went out.', 'advanced', 'connectors'),
(10, 'However', 'อย่างไรก็ตาม', '/haʊˈɛvər/', 'It was cold. However, we played outside.', 'advanced', 'connectors'),
(10, 'Therefore', 'ดังนั้น', '/ˈðɛrfɔːr/', 'I studied hard; therefore, I passed.', 'advanced', 'connectors'),
(10, 'Meanwhile', 'ในขณะเดียวกัน', '/ˈmiːnwaɪl/', 'She cooked. Meanwhile, I cleaned.', 'advanced', 'connectors'),
(10, 'Furthermore', 'นอกจากนี้', '/ˈfɜːrðərmɔːr/', 'The food was good. Furthermore, it was cheap.', 'advanced', 'connectors'),

(11, 'Built', 'สร้าง (V3)', '/bɪlt/', 'The house was built in 2020.', 'advanced', 'verbs'),
(11, 'Written', 'เขียน (V3)', '/ˈrɪtn/', 'The book was written by her.', 'advanced', 'verbs'),
(11, 'Spoken', 'พูด (V3)', '/ˈspoʊkən/', 'English is spoken worldwide.', 'advanced', 'verbs'),
(11, 'Taken', 'เอา (V3)', '/ˈteɪkən/', 'The photo was taken yesterday.', 'advanced', 'verbs'),
(11, 'Chosen', 'เลือก (V3)', '/ˈtʃoʊzn/', 'She was chosen as the leader.', 'advanced', 'verbs'),

(12, 'Claimed', 'อ้าง', '/kleɪmd/', 'He claimed he was innocent.', 'advanced', 'verbs'),
(12, 'Mentioned', 'กล่าวถึง', '/ˈmɛnʃənd/', 'She mentioned the meeting.', 'advanced', 'verbs'),
(12, 'Admitted', 'ยอมรับ', '/ədˈmɪtɪd/', 'He admitted his mistake.', 'advanced', 'verbs'),
(12, 'Denied', 'ปฏิเสธ', '/dɪˈnaɪd/', 'She denied the rumor.', 'advanced', 'verbs'),
(12, 'Suggested', 'แนะนำ', '/səˈdʒɛstɪd/', 'He suggested going to the park.', 'advanced', 'verbs'),

-- Extra common words
(NULL, 'Dog', 'สุนัข', '/dɒɡ/', 'The dog is running.', 'beginner', 'animals'),
(NULL, 'Cat', 'แมว', '/kæt/', 'The cat is sleeping.', 'beginner', 'animals'),
(NULL, 'Bird', 'นก', '/bɜːrd/', 'A bird is singing.', 'beginner', 'animals'),
(NULL, 'Fish', 'ปลา', '/fɪʃ/', 'The fish is in the water.', 'beginner', 'animals'),
(NULL, 'Elephant', 'ช้าง', '/ˈɛlɪfənt/', 'The elephant is big.', 'beginner', 'animals'),
(NULL, 'Apple', 'แอปเปิ้ล', '/ˈæpl/', 'I eat an apple every day.', 'beginner', 'food'),
(NULL, 'Banana', 'กล้วย', '/bəˈnænə/', 'Monkeys love bananas.', 'beginner', 'food'),
(NULL, 'Rice', 'ข้าว', '/raɪs/', 'Thai people eat rice.', 'beginner', 'food'),
(NULL, 'Water', 'น้ำ', '/ˈwɔːtər/', 'I drink water every day.', 'beginner', 'food'),
(NULL, 'Milk', 'นม', '/mɪlk/', 'I drink milk in the morning.', 'beginner', 'food'),
(NULL, 'School', 'โรงเรียน', '/skuːl/', 'I go to school at 8 AM.', 'beginner', 'places'),
(NULL, 'House', 'บ้าน', '/haʊs/', 'My house is near the park.', 'beginner', 'places'),
(NULL, 'Park', 'สวนสาธารณะ', '/pɑːrk/', 'We play in the park.', 'beginner', 'places'),
(NULL, 'Hospital', 'โรงพยาบาล', '/ˈhɒspɪtl/', 'The doctor works at the hospital.', 'beginner', 'places'),
(NULL, 'Library', 'ห้องสมุด', '/ˈlaɪbrəri/', 'I read books in the library.', 'beginner', 'places'),
(NULL, 'Happy', 'มีความสุข', '/ˈhæpi/', 'I am happy today!', 'beginner', 'feelings'),
(NULL, 'Sad', 'เศร้า', '/sæd/', 'She is sad because it rained.', 'beginner', 'feelings'),
(NULL, 'Angry', 'โกรธ', '/ˈæŋɡri/', 'Don''t be angry!', 'beginner', 'feelings'),
(NULL, 'Tired', 'เหนื่อย', '/taɪərd/', 'I am tired after running.', 'beginner', 'feelings'),
(NULL, 'Excited', 'ตื่นเต้น', '/ɪkˈsaɪtɪd/', 'I am excited about the trip!', 'beginner', 'feelings');

-- ── EXAMS ───────────────────────────────────────────────────
INSERT INTO exams (title, lesson_id, level, total_questions, time_minutes) VALUES
('แบบทดสอบ Greetings', 1, 'beginner', 10, 15),
('แบบทดสอบ Numbers', 2, 'beginner', 10, 15),
('แบบทดสอบ Present Tense', 6, 'intermediate', 10, 20),
('ข้อสอบรวม Beginner', NULL, 'beginner', 20, 30),
('ข้อสอบรวม Intermediate', NULL, 'intermediate', 20, 30);

-- ── QUESTIONS ───────────────────────────────────────────────
INSERT INTO questions (exam_id, question_text, choice_a, choice_b, choice_c, choice_d, choice_e, correct_answer, level) VALUES
(1, 'What is the correct greeting for the morning?', 'Good night', 'Good morning', 'Good bye', 'Good luck', 'Good grief', 1, 'beginner'),
(1, '"How are you?" — ตอบว่าอะไร?', 'I am 10 years old.', 'My name is Tom.', 'I''m fine, thank you.', 'I live in Bangkok.', 'I like ice cream.', 2, 'beginner'),
(1, 'What does "Nice to meet you" mean?', 'ลาก่อน', 'ขอโทษ', 'ยินดีที่ได้รู้จัก', 'ขอบคุณ', 'ไม่เป็นไร', 2, 'beginner'),
(1, '"My name _____ Suda."', 'am', 'is', 'are', 'was', 'be', 1, 'beginner'),
(1, 'Which word means "สวัสดี"?', 'Sorry', 'Thank you', 'Hello', 'Goodbye', 'Please', 2, 'beginner'),
(1, '"Good _____" — กล่าวตอนเย็น', 'morning', 'afternoon', 'evening', 'night', 'day', 2, 'beginner'),
(1, '"I _____ a student."', 'is', 'am', 'are', 'was', 'be', 1, 'beginner'),
(1, '"_____ is your name?" — "My name is Tom."', 'How', 'Where', 'What', 'When', 'Why', 2, 'beginner'),
(1, '"See you _____" means "แล้วพบกันพรุ่งนี้"', 'yesterday', 'today', 'tomorrow', 'now', 'later', 2, 'beginner'),
(1, '"Thank you" ตอบว่า _____', 'Sorry', 'Hello', 'You''re welcome', 'Goodbye', 'Excuse me', 2, 'beginner'),

(2, 'How do you say "5" in English?', 'Three', 'Five', 'Seven', 'Nine', NULL, 1, 'beginner'),
(2, 'What number comes after twelve?', 'Eleven', 'Thirteen', 'Fourteen', 'Ten', NULL, 1, 'beginner'),
(2, 'How do you spell 20?', 'Twanty', 'Twenty', 'Twentie', 'Twenti', NULL, 1, 'beginner'),
(2, 'What is 10 + 5?', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', NULL, 2, 'beginner'),
(2, '"One hundred" equals what number?', '10', '50', '100', '1000', NULL, 2, 'beginner'),
(2, 'How many days are in a week?', 'Five', 'Six', 'Seven', 'Eight', NULL, 2, 'beginner'),
(2, 'What is the number after "nineteen"?', 'Eighteen', 'Twenty', 'Twenty-one', 'Seventeen', NULL, 1, 'beginner'),
(2, '"Fifty" means _____', '15', '50', '500', '5', NULL, 1, 'beginner'),
(2, 'How do you say "1000"?', 'One hundred', 'One thousand', 'Ten hundred', 'One million', NULL, 1, 'beginner'),
(2, 'What is 7 + 3?', 'Nine', 'Ten', 'Eleven', 'Eight', NULL, 1, 'beginner'),

(3, 'She _____ to school every day.', 'go', 'goes', 'going', 'went', 'gone', 1, 'intermediate'),
(3, 'They _____ playing football now.', 'is', 'am', 'are', 'was', 'be', 2, 'intermediate'),
(3, 'I _____ breakfast at 7 AM.', 'has', 'have', 'having', 'had', 'haves', 1, 'intermediate'),
(3, '_____ you like ice cream?', 'Does', 'Do', 'Is', 'Are', 'Was', 1, 'intermediate'),
(3, 'He _____ not speak Japanese.', 'do', 'does', 'is', 'are', 'have', 1, 'intermediate'),
(3, 'We _____ to the park yesterday.', 'go', 'goes', 'going', 'went', 'gone', 3, 'intermediate'),
(3, 'The cat is _____ the table.', 'in', 'on', 'at', 'under', 'by', 1, 'intermediate'),
(3, 'She is _____ than her sister.', 'tall', 'taller', 'tallest', 'more tall', 'most tall', 1, 'intermediate'),
(3, 'I will _____ you tomorrow.', 'see', 'sees', 'seeing', 'saw', 'seen', 0, 'intermediate'),
(3, 'This is _____ best movie I have ever seen.', 'a', 'an', 'the', '-', 'some', 2, 'intermediate'),

-- General beginner questions
(4, 'What color is the sky?', 'Red', 'Blue', 'Green', 'Yellow', NULL, 1, 'beginner'),
(4, '"Dog" in Thai is _____', 'แมว', 'สุนัข', 'นก', 'ปลา', NULL, 1, 'beginner'),
(4, 'Which day comes after Monday?', 'Wednesday', 'Tuesday', 'Sunday', 'Thursday', NULL, 1, 'beginner'),
(4, '"Mother" means _____', 'พ่อ', 'แม่', 'พี่', 'น้อง', NULL, 1, 'beginner'),
(4, 'What shape has 3 sides?', 'Square', 'Circle', 'Triangle', 'Rectangle', NULL, 2, 'beginner'),
(4, 'I _____ happy today.', 'is', 'am', 'are', 'was', NULL, 1, 'beginner'),
(4, '"Apple" is a _____', 'animal', 'fruit', 'color', 'day', NULL, 1, 'beginner'),
(4, 'How many months in a year?', 'Ten', 'Eleven', 'Twelve', 'Thirteen', NULL, 2, 'beginner'),
(4, '"Elephant" in Thai is _____', 'แมว', 'สุนัข', 'ช้าง', 'ม้า', NULL, 2, 'beginner'),
(4, 'Which word means "น้ำ"?', 'Fire', 'Water', 'Air', 'Earth', NULL, 1, 'beginner'),
(4, 'What is the opposite of "happy"?', 'Angry', 'Sad', 'Tired', 'Excited', NULL, 1, 'beginner'),
(4, '"School" means _____', 'บ้าน', 'ตลาด', 'โรงเรียน', 'โรงพยาบาล', NULL, 2, 'beginner'),
(4, 'Which is NOT a color?', 'Red', 'Blue', 'Dog', 'Green', NULL, 2, 'beginner'),
(4, '"Brother" means _____', 'พี่สาว', 'พ่อ', 'น้องชาย/พี่ชาย', 'ลุง', NULL, 2, 'beginner'),
(4, 'I eat _____ every day.', 'rice', 'school', 'house', 'park', NULL, 0, 'beginner'),
(4, '"Library" is a place for _____', 'eating', 'sleeping', 'reading', 'swimming', NULL, 2, 'beginner'),
(4, 'What color is a banana?', 'Red', 'Blue', 'Yellow', 'Purple', NULL, 2, 'beginner'),
(4, '"Fish" lives in _____', 'tree', 'water', 'sky', 'house', NULL, 1, 'beginner'),
(4, 'What day is after Friday?', 'Thursday', 'Saturday', 'Sunday', 'Monday', NULL, 1, 'beginner'),
(4, '"Goodbye" means _____', 'สวัสดี', 'ขอบคุณ', 'ลาก่อน', 'ขอโทษ', NULL, 2, 'beginner'),

-- General intermediate questions
(5, 'She _____ to school every day.', 'go', 'goes', 'going', 'went', NULL, 1, 'intermediate'),
(5, 'I _____ breakfast at 7 AM.', 'has', 'have', 'having', 'had', NULL, 1, 'intermediate'),
(5, '_____ you like ice cream?', 'Does', 'Do', 'Is', 'Are', NULL, 1, 'intermediate'),
(5, 'We _____ to the park yesterday.', 'go', 'goes', 'going', 'went', NULL, 3, 'intermediate'),
(5, 'She is _____ than her sister.', 'tall', 'taller', 'tallest', 'more tall', NULL, 1, 'intermediate'),
(5, 'I will _____ you tomorrow.', 'see', 'sees', 'seeing', 'saw', NULL, 0, 'intermediate'),
(5, 'This is _____ best movie ever.', 'a', 'an', 'the', '-', NULL, 2, 'intermediate'),
(5, 'He _____ not speak Japanese.', 'do', 'does', 'is', 'are', NULL, 1, 'intermediate'),
(5, 'They _____ playing football now.', 'is', 'am', 'are', 'was', NULL, 2, 'intermediate'),
(5, 'I _____ never been to Japan.', 'has', 'have', 'had', 'having', NULL, 1, 'intermediate'),
(5, 'The cat is _____ the table.', 'in', 'on', 'at', 'under', NULL, 1, 'intermediate'),
(5, 'If it rains, I _____ stay home.', 'will', 'would', 'can', 'may', NULL, 0, 'intermediate'),
(5, 'She asked me _____ I was from.', 'what', 'where', 'when', 'who', NULL, 1, 'intermediate'),
(5, 'He runs _____ than his brother.', 'fast', 'faster', 'fastest', 'more fast', NULL, 1, 'intermediate'),
(5, 'I enjoy _____ English.', 'learn', 'learning', 'learned', 'learns', NULL, 1, 'intermediate'),
(5, 'The movie was _____ interesting.', 'much', 'many', 'very', 'lot', NULL, 2, 'intermediate'),
(5, 'She _____ here since 2020.', 'is', 'was', 'has been', 'had been', NULL, 2, 'intermediate'),
(5, 'Would you mind _____ the window?', 'open', 'opening', 'opened', 'opens', NULL, 1, 'intermediate'),
(5, 'I wish I _____ fly.', 'can', 'could', 'will', 'am', NULL, 1, 'intermediate'),
(5, 'Neither Tom _____ Jerry is here.', 'or', 'and', 'nor', 'but', NULL, 2, 'intermediate');

-- ── BADGES ──────────────────────────────────────────────────
-- ── GAME SENTENCES ──────────────────────────────────────────
INSERT INTO game_sentences (sentence_en, sentence_th) VALUES
('I go to school every day', 'ฉันไปโรงเรียนทุกวัน'),
('She likes to eat ice cream', 'เธอชอบกินไอศกรีม'),
('We play football in the park', 'เราเล่นฟุตบอลในสวน'),
('He is a good student', 'เขาเป็นนักเรียนที่ดี'),
('My mother cooks delicious food', 'แม่ทำอาหารอร่อย'),
('The cat is on the table', 'แมวอยู่บนโต๊ะ'),
('They are playing in the garden', 'พวกเขากำลังเล่นในสวน'),
('I have two brothers and one sister', 'ฉันมีพี่ชาย 2 คน และน้องสาว 1 คน');

-- ── GAME FILL BLANKS ────────────────────────────────────────
INSERT INTO game_fill_blanks (question_text, correct_answer, choice_1, choice_2, choice_3, choice_4) VALUES
('I ___ a student.', 'am', 'am', 'is', 'are', 'was'),
('She ___ to school every day.', 'goes', 'go', 'goes', 'going', 'went'),
('They ___ playing football.', 'are', 'is', 'am', 'are', 'was'),
('He ___ not like coffee.', 'does', 'do', 'does', 'is', 'was'),
('We ___ to the park yesterday.', 'went', 'go', 'goes', 'went', 'going'),
('My mother ___ very kind.', 'is', 'am', 'is', 'are', 'be'),
('The dog ___ in the garden.', 'is', 'am', 'is', 'are', 'were'),
('I ___ English every day.', 'study', 'study', 'studies', 'studying', 'studied');

-- ── BADGES ────────────────────────────────────────────────
INSERT INTO badges (name, name_th, description, icon, color, requirement_type, requirement_value, xp_reward, sort_order) VALUES
('First Steps', 'ก้าวแรก', 'เรียนบทเรียนแรกสำเร็จ', '🌟', '#FFD700', 'lessons_completed', 1, 10, 1),
('Bookworm', 'หนอนหนังสือ', 'เรียนครบ 5 บทเรียน', '📚', '#4ECDC4', 'lessons_completed', 5, 30, 2),
('Scholar', 'นักปราชญ์', 'เรียนครบ 10 บทเรียน', '🎓', '#6C63FF', 'lessons_completed', 10, 50, 3),
('Word Collector', 'นักสะสมคำ', 'ท่องศัพท์ครบ 30 คำ', '📖', '#FF6B9D', 'vocab_learned', 30, 20, 4),
('Word Master', 'ราชาคำศัพท์', 'ท่องศัพท์ครบ 100 คำ', '👑', '#FFB347', 'vocab_learned', 100, 50, 5),
('Quiz Whiz', 'เทพข้อสอบ', 'สอบผ่าน 5 ครั้ง', '📝', '#45B7D1', 'exams_passed', 5, 30, 6),
('Perfect Score', 'คะแนนเต็ม', 'ได้คะแนนเต็ม 100%', '💯', '#2ED573', 'perfect_score', 1, 50, 7),
('On Fire', 'ร้อนแรง', 'เรียนต่อเนื่อง 3 วัน', '🔥', '#FF4757', 'streak_days', 3, 20, 8),
('Unstoppable', 'หยุดไม่อยู่', 'เรียนต่อเนื่อง 7 วัน', '⚡', '#FFA502', 'streak_days', 7, 40, 9),
('Champion', 'แชมป์เปี้ยน', 'เรียนต่อเนื่อง 30 วัน', '🏆', '#FFD700', 'streak_days', 30, 100, 10),
('Game Pro', 'เซียนเกม', 'เล่นเกมครบ 10 ครั้ง', '🎮', '#A29BFE', 'games_played', 10, 30, 11),
('Speed Reader', 'นักอ่านเร็ว', 'อ่านจบ 5 เรื่อง', '📕', '#E17055', 'readings_completed', 5, 30, 12);

-- ── READING PASSAGES ────────────────────────────────────────
INSERT INTO reading_passages (title, title_th, content, level, word_count, category) VALUES
('My Pet Dog', 'สุนัขของฉัน',
'<p>My name is Ploy. I am ten years old. I have a pet dog. His name is Lucky. Lucky is a small brown dog. He has big brown eyes and a short tail.</p>
<p>Every morning, I wake up and feed Lucky. He likes to eat rice and chicken. After eating, Lucky likes to play in the garden. He runs very fast!</p>
<p>In the evening, I take Lucky for a walk. We walk around the park near my house. Lucky is very friendly. He likes to play with other dogs.</p>
<p>At night, Lucky sleeps next to my bed. He is my best friend. I love Lucky very much!</p>',
'beginner', 95, 'story'),

('A Day at School', 'วันหนึ่งที่โรงเรียน',
'<p>Today is Monday. I go to school at 7:30 in the morning. My school is not far from my house. I walk to school with my friend, Nong.</p>
<p>At school, we study many subjects. In the morning, we have English class. I like English because the teacher is very kind. She teaches us new words every day.</p>
<p>At lunchtime, I eat lunch in the cafeteria. I usually eat rice with chicken. Sometimes I eat noodles. After lunch, we play in the playground.</p>
<p>In the afternoon, we have Math and Science. Math is a little difficult, but I try my best. School finishes at 3:30 PM. I go home and do my homework. Then I play with my friends.</p>',
'beginner', 120, 'daily_life'),

('The Little Red Hen', 'แม่ไก่ตัวน้อยสีแดง',
'<p>Once upon a time, there was a little red hen. She lived on a farm with a dog, a cat, and a duck.</p>
<p>One day, the little red hen found some wheat seeds. "Who will help me plant these seeds?" she asked.</p>
<p>"Not I," said the dog. "Not I," said the cat. "Not I," said the duck.</p>
<p>"Then I will do it myself," said the little red hen. And she did.</p>
<p>The wheat grew tall and golden. "Who will help me cut the wheat?" she asked. Again, nobody wanted to help.</p>
<p>The little red hen cut the wheat, made flour, and baked a delicious bread all by herself.</p>
<p>"Who will help me eat this bread?" she asked. "I will!" said the dog, the cat, and the duck.</p>
<p>"No," said the little red hen. "I planted the seeds. I cut the wheat. I made the bread. I will eat it myself!" And she did.</p>
<p><strong>Moral:</strong> If you want to enjoy the rewards, you must help with the work.</p>',
'beginner', 165, 'fable'),

('My Summer Vacation', 'ปิดเทอมของฉัน',
'<p>Last summer, my family and I went to Phuket for a vacation. We traveled by airplane. It was my first time on a plane, and I was very excited!</p>
<p>We stayed at a hotel near the beach. Every morning, we woke up early and went swimming in the sea. The water was warm and clear. I could see many colorful fish!</p>
<p>One day, we took a boat to a small island. There were beautiful coral reefs under the water. My father taught me how to snorkel. It was amazing! I saw starfish, sea urchins, and even a small octopus.</p>
<p>In the evening, we walked along the beach and watched the sunset. The sky turned orange, pink, and purple. It was the most beautiful thing I had ever seen.</p>
<p>We also visited a night market. There was delicious seafood everywhere. I tried grilled squid and mango sticky rice. Everything was so yummy!</p>
<p>The vacation lasted five days, but it felt too short. I want to go back to Phuket again next year!</p>',
'intermediate', 170, 'story'),

('The Importance of Learning English', 'ความสำคัญของการเรียนภาษาอังกฤษ',
'<p>English is one of the most widely spoken languages in the world. It is the official language of over 50 countries and is used as a second language in many more. Learning English can open many doors in your life.</p>
<p>First, English is the language of technology and the internet. Most websites, apps, and computer programs are written in English. If you understand English, you can access a huge amount of information online.</p>
<p>Second, English is important for your future career. Many international companies require their employees to speak English. If you can communicate well in English, you will have more job opportunities.</p>
<p>Third, English helps you understand different cultures. Through English books, movies, and music, you can learn about people from all around the world. This makes you more open-minded and understanding.</p>
<p>Finally, learning English improves your brain power. Studies show that bilingual people have better memory, problem-solving skills, and multitasking abilities.</p>
<p>So, keep studying English every day! Even learning a few new words each day will make a big difference over time. Remember, practice makes perfect!</p>',
'intermediate', 175, 'educational'),

('The Mystery of the Missing Homework', 'ปริศนาการบ้านหาย',
'<p>It was a typical Thursday morning when Tom walked into his classroom. He reached into his backpack to take out his homework, but it was gone! He had spent two hours working on it the night before.</p>
<p>"Where could it be?" Tom wondered. He checked every pocket of his bag. He looked under his desk. He even asked his classmates if they had accidentally taken it. Nobody had seen it.</p>
<p>The teacher, Mrs. Johnson, was not happy. "Tom, this is the third time this month that you haven''t submitted your homework," she said with a disappointed expression.</p>
<p>"But I really did it this time!" Tom insisted. He felt frustrated because he was telling the truth.</p>
<p>During lunch break, Tom decided to investigate. He retraced his steps from the morning. First, he had walked from his house to the bus stop. Then he took the bus to school. Finally, he walked from the bus stop to the classroom.</p>
<p>Suddenly, he remembered something. On the bus, he had taken out his homework to review it one more time. Could he have left it on the bus?</p>
<p>After school, Tom went to the bus company''s lost and found office. There, sitting on a shelf, was his homework! He was so relieved.</p>
<p>The next day, Tom showed his homework to Mrs. Johnson and explained what had happened. She smiled and said, "I''m glad you found it, Tom. But from now on, keep your homework safely in your bag!"</p>',
'advanced', 240, 'story');

-- ── READING QUESTIONS ───────────────────────────────────────
-- My Pet Dog
INSERT INTO reading_questions (passage_id, question_text, choice_a, choice_b, choice_c, choice_d, correct_answer) VALUES
(1, 'What is the dog''s name?', 'Ploy', 'Lucky', 'Buddy', 'Max', 1),
(1, 'What color is Lucky?', 'White', 'Black', 'Brown', 'Golden', 2),
(1, 'What does Lucky like to eat?', 'Fish and bread', 'Rice and chicken', 'Dog food', 'Fruits', 1),
(1, 'Where does Ploy walk Lucky?', 'At school', 'In the garden', 'In the park', 'On the road', 2),
(1, 'Where does Lucky sleep?', 'In the garden', 'Next to Ploy''s bed', 'On the sofa', 'In his own room', 1),

-- A Day at School
(2, 'What time does the student go to school?', '7:00 AM', '7:30 AM', '8:00 AM', '8:30 AM', 1),
(2, 'Which subject does the student like?', 'Math', 'Science', 'English', 'Art', 2),
(2, 'Where does the student eat lunch?', 'At home', 'In the classroom', 'In the cafeteria', 'In the park', 2),
(2, 'What time does school finish?', '3:00 PM', '3:30 PM', '4:00 PM', '4:30 PM', 1),
(2, 'What does the student do after school?', 'Go shopping', 'Do homework and play', 'Watch TV', 'Go to sleep', 1),

-- The Little Red Hen
(3, 'Who lived on the farm with the hen?', 'A cow, a pig, and a horse', 'A dog, a cat, and a duck', 'A rabbit, a mouse, and a bird', 'A sheep, a goat, and a pig', 1),
(3, 'What did the hen find?', 'Corn seeds', 'Wheat seeds', 'Rice seeds', 'Flower seeds', 1),
(3, 'Who helped the hen plant the seeds?', 'The dog', 'The cat', 'The duck', 'Nobody', 3),
(3, 'What did the hen bake?', 'A cake', 'A pie', 'Bread', 'Cookies', 2),
(3, 'What is the moral of the story?', 'Sharing is caring', 'Help with work to enjoy rewards', 'Always be kind', 'Never give up', 1),

-- My Summer Vacation
(4, 'Where did the family go for vacation?', 'Chiang Mai', 'Phuket', 'Hua Hin', 'Pattaya', 1),
(4, 'How did they travel?', 'By car', 'By bus', 'By airplane', 'By train', 2),
(4, 'What did the father teach?', 'Swimming', 'Snorkeling', 'Diving', 'Fishing', 1),
(4, 'What did they eat at the night market?', 'Pizza and pasta', 'Grilled squid and mango sticky rice', 'Hamburgers and fries', 'Sushi and ramen', 1),
(4, 'How long was the vacation?', 'Three days', 'Five days', 'One week', 'Two weeks', 1),

-- The Importance of Learning English
(5, 'How many countries use English as an official language?', 'Over 30', 'Over 50', 'Over 70', 'Over 100', 1),
(5, 'Why is English important for technology?', 'Because computers are made in England', 'Because most websites and programs are in English', 'Because English is easy', 'Because scientists speak English', 1),
(5, 'What does learning English improve?', 'Physical strength', 'Brain power', 'Cooking skills', 'Dancing ability', 1),
(5, 'What is the advice given in the passage?', 'Study English once a week', 'Learn a few new words each day', 'Only watch English movies', 'Travel to English-speaking countries', 1),
(5, 'What makes bilingual people better?', 'They earn more money', 'They have better memory and problem-solving', 'They are taller', 'They run faster', 1),

-- The Mystery of the Missing Homework
(6, 'On which day did Tom lose his homework?', 'Monday', 'Wednesday', 'Thursday', 'Friday', 2),
(6, 'How many times had Tom not submitted homework that month?', 'One', 'Two', 'Three', 'Four', 2),
(6, 'Where did Tom leave his homework?', 'At home', 'At school', 'On the bus', 'At the library', 2),
(6, 'How did Tom feel when the teacher scolded him?', 'Happy', 'Frustrated', 'Scared', 'Bored', 1),
(6, 'Where did Tom find his homework?', 'In his backpack', 'Under his desk', 'At the lost and found office', 'At home', 2);

-- ── EXAM RESULTS (Seed some history) ────────────────────────
INSERT INTO exam_results (user_id, exam_id, score, total, percentage, time_spent) VALUES
(2, 1, 8, 10, 80.0, 420),
(2, 4, 15, 20, 75.0, 1200),
(2, 1, 9, 10, 90.0, 380),
(3, 1, 9, 10, 90.0, 500),
(3, 2, 8, 10, 80.0, 450),
(3, 4, 18, 20, 90.0, 1100),
(4, 1, 6, 10, 60.0, 600),
(4, 4, 13, 20, 65.0, 1400),
(5, 1, 10, 10, 100.0, 300),
(5, 2, 10, 10, 100.0, 280),
(5, 3, 9, 10, 90.0, 600),
(5, 5, 19, 20, 95.0, 900),
(6, 1, 8, 10, 80.0, 450),
(6, 3, 7, 10, 70.0, 700),
(6, 5, 16, 20, 80.0, 1000);

-- ── LESSON PROGRESS ─────────────────────────────────────────
INSERT INTO lesson_progress (user_id, lesson_id, completed, completed_at) VALUES
(2, 1, TRUE, NOW()), (2, 2, TRUE, NOW()), (2, 3, TRUE, NOW()), (2, 4, TRUE, NOW()), (2, 5, TRUE, NOW()),
(3, 1, TRUE, NOW()), (3, 2, TRUE, NOW()), (3, 3, TRUE, NOW()), (3, 4, TRUE, NOW()), (3, 5, TRUE, NOW()), (3, 6, TRUE, NOW()), (3, 7, TRUE, NOW()), (3, 8, TRUE, NOW()),
(4, 1, TRUE, NOW()), (4, 2, TRUE, NOW()), (4, 3, TRUE, NOW()),
(5, 1, TRUE, NOW()), (5, 2, TRUE, NOW()), (5, 3, TRUE, NOW()), (5, 4, TRUE, NOW()), (5, 5, TRUE, NOW()), (5, 6, TRUE, NOW()), (5, 7, TRUE, NOW()), (5, 8, TRUE, NOW()), (5, 9, TRUE, NOW()), (5, 10, TRUE, NOW()), (5, 11, TRUE, NOW()), (5, 12, TRUE, NOW()),
(6, 1, TRUE, NOW()), (6, 2, TRUE, NOW()), (6, 3, TRUE, NOW()), (6, 4, TRUE, NOW()), (6, 5, TRUE, NOW()), (6, 6, TRUE, NOW());

-- ── GAME SCORES ─────────────────────────────────────────────
INSERT INTO game_scores (user_id, game_type, score, max_score, time_spent) VALUES
(2, 'match_pairs', 85, 100, 45), (2, 'sentence_order', 70, 100, 60),
(3, 'match_pairs', 95, 100, 30), (3, 'fill_blank', 80, 100, 50),
(5, 'match_pairs', 100, 100, 25), (5, 'sentence_order', 95, 100, 35), (5, 'fill_blank', 90, 100, 40);

-- ── USER BADGES ─────────────────────────────────────────────
INSERT INTO user_badges (user_id, badge_id) VALUES
(2, 1), (2, 2),
(3, 1), (3, 2), (3, 3),
(4, 1),
(5, 1), (5, 2), (5, 3), (5, 7), (5, 8), (5, 9), (5, 10),
(6, 1), (6, 2);
