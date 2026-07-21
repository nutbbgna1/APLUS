<?php
// ============================================================
// LinguaMax — Admin API
// Handles CRUD operations for students and content
// ============================================================
session_start();
require_once __DIR__ . '/../includes/functions.php';

// Strict Admin Check
if (!isAdmin()) {
    jsonResponse(['error' => 'Unauthorized Access. Admin privileges required.'], 403);
}

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';
$db = getDB();

switch ($action) {
    // ── STUDENTS ──────────────────────────────────────────────────
    case 'add_student':
        $fname = trim($input['fname'] ?? '');
        $lname = trim($input['lname'] ?? '');
        $nickname = trim($input['nickname'] ?? '');
        $code = trim($input['code'] ?? '');
        $password = trim($input['password'] ?? '');

        if (!$fname || !$code || !$password) jsonResponse(['error' => 'Missing required fields'], 400);

        try {
            $stmt = $db->prepare("INSERT INTO users (code, password, role, fname, lname, nickname, avatar_color) VALUES (?, ?, 'student', ?, ?, ?, ?)");
            $stmt->execute([
                $code, 
                password_hash($password, PASSWORD_DEFAULT),
                $fname, 
                $lname, 
                $nickname, 
                ['#10B981','#3B82F6','#F59E0B','#EF4444','#8B5CF6'][rand(0,4)]
            ]);
            jsonResponse(['success' => true]);
        } catch (PDOException $e) {
            jsonResponse(['error' => 'Error adding student, code might exist.'], 400);
        }
        break;

    case 'edit_student':
        $id = intval($input['id'] ?? 0);
        $fname = trim($input['fname'] ?? '');
        $lname = trim($input['lname'] ?? '');
        $nickname = trim($input['nickname'] ?? '');
        $password = trim($input['password'] ?? '');

        if (!$id || !$fname) jsonResponse(['error' => 'Missing required fields'], 400);

        if ($password) {
            $stmt = $db->prepare("UPDATE users SET fname=?, lname=?, nickname=?, password=? WHERE id=? AND role='student'");
            $stmt->execute([$fname, $lname, $nickname, password_hash($password, PASSWORD_DEFAULT), $id]);
        } else {
            $stmt = $db->prepare("UPDATE users SET fname=?, lname=?, nickname=? WHERE id=? AND role='student'");
            $stmt->execute([$fname, $lname, $nickname, $id]);
        }
        jsonResponse(['success' => true]);
        break;

    case 'delete_student':
        $id = intval($input['id'] ?? 0);
        if (!$id) jsonResponse(['error' => 'Missing student ID'], 400);
        // Cascade deletes are handled by DB schema if ON DELETE CASCADE is set, otherwise delete manually
        $db->prepare("DELETE FROM users WHERE id=? AND role='student'")->execute([$id]);
        jsonResponse(['success' => true]);
        break;

    // ── LESSONS ───────────────────────────────────────────────────
    case 'save_lesson':
        $id = intval($input['id'] ?? 0);
        $title = trim($input['title'] ?? '');
        $description = trim($input['description'] ?? '');
        $content = trim($input['content'] ?? '');
        $level = trim($input['level'] ?? 'beginner');
        $sort = intval($input['sort_order'] ?? 1);

        if (!$title) jsonResponse(['error' => 'Title is required'], 400);

        if ($id > 0) {
            $stmt = $db->prepare("UPDATE lessons SET title=?, description=?, content=?, level=?, sort_order=? WHERE id=?");
            $stmt->execute([$title, $description, $content, $level, $sort, $id]);
        } else {
            $stmt = $db->prepare("INSERT INTO lessons (title, description, content, level, sort_order) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$title, $description, $content, $level, $sort]);
        }
        jsonResponse(['success' => true]);
        break;

    case 'delete_lesson':
        $id = intval($input['id'] ?? 0);
        if ($id) $db->prepare("DELETE FROM lessons WHERE id=?")->execute([$id]);
        jsonResponse(['success' => true]);
        break;

    // ── VOCABULARY ────────────────────────────────────────────────
    case 'save_vocab':
        $id = intval($input['id'] ?? 0);
        $lessonId = intval($input['lesson_id'] ?? 0) ?: null;
        $en = trim($input['word_en'] ?? '');
        $th = trim($input['word_th'] ?? '');
        $pho = trim($input['phonetics'] ?? '');
        $ex = trim($input['example_sentence'] ?? '');
        $level = trim($input['level'] ?? 'beginner');
        $cat = trim($input['category'] ?? '');

        if (!$en || !$th) jsonResponse(['error' => 'Word is required'], 400);

        if ($id > 0) {
            $stmt = $db->prepare("UPDATE vocabulary SET lesson_id=?, word_en=?, word_th=?, phonetics=?, example_sentence=?, level=?, category=? WHERE id=?");
            $stmt->execute([$lessonId, $en, $th, $pho, $ex, $level, $cat, $id]);
        } else {
            $stmt = $db->prepare("INSERT INTO vocabulary (lesson_id, word_en, word_th, phonetics, example_sentence, level, category) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$lessonId, $en, $th, $pho, $ex, $level, $cat]);
        }
        jsonResponse(['success' => true]);
        break;

    case 'delete_vocab':
        $id = intval($input['id'] ?? 0);
        if ($id) $db->prepare("DELETE FROM vocabulary WHERE id=?")->execute([$id]);
        jsonResponse(['success' => true]);
        break;

    // ── EXAMS ─────────────────────────────────────────────────────
    case 'save_exam':
        $id = intval($input['id'] ?? 0);
        $lessonId = intval($input['lesson_id'] ?? 0) ?: null;
        $title = trim($input['title'] ?? '');
        $level = trim($input['level'] ?? 'beginner');
        $time = intval($input['time_minutes'] ?? 10);
        $total = intval($input['total_questions'] ?? 10);

        if (!$title) jsonResponse(['error' => 'Title is required'], 400);

        if ($id > 0) {
            $stmt = $db->prepare("UPDATE exams SET lesson_id=?, title=?, level=?, time_minutes=?, total_questions=? WHERE id=?");
            $stmt->execute([$lessonId, $title, $level, $time, $total, $id]);
        } else {
            $stmt = $db->prepare("INSERT INTO exams (lesson_id, title, level, time_minutes, total_questions) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$lessonId, $title, $level, $time, $total]);
        }
        jsonResponse(['success' => true]);
        break;

    case 'delete_exam':
        $id = intval($input['id'] ?? 0);
        if ($id) $db->prepare("DELETE FROM exams WHERE id=?")->execute([$id]);
        jsonResponse(['success' => true]);
        break;

    case 'save_question':
        $id = intval($input['id'] ?? 0);
        $examId = intval($input['exam_id'] ?? 0) ?: null;
        $text = trim($input['question_text'] ?? '');
        $ca = trim($input['choice_a'] ?? '');
        $cb = trim($input['choice_b'] ?? '');
        $cc = trim($input['choice_c'] ?? '');
        $cd = trim($input['choice_d'] ?? '');
        $ce = trim($input['choice_e'] ?? '');
        $correct = intval($input['correct_answer'] ?? 0);
        $level = trim($input['level'] ?? 'beginner');

        if (!$text || !$ca) jsonResponse(['error' => 'Question and Choice A are required'], 400);

        if ($id > 0) {
            $stmt = $db->prepare("UPDATE questions SET exam_id=?, question_text=?, choice_a=?, choice_b=?, choice_c=?, choice_d=?, choice_e=?, correct_answer=?, level=? WHERE id=?");
            $stmt->execute([$examId, $text, $ca, $cb, $cc, $cd, $ce, $correct, $level, $id]);
        } else {
            $stmt = $db->prepare("INSERT INTO questions (exam_id, question_text, choice_a, choice_b, choice_c, choice_d, choice_e, correct_answer, level) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$examId, $text, $ca, $cb, $cc, $cd, $ce, $correct, $level]);
        }
        jsonResponse(['success' => true]);
        break;

    case 'delete_question':
        $id = intval($input['id'] ?? 0);
        if ($id) $db->prepare("DELETE FROM questions WHERE id=?")->execute([$id]);
        jsonResponse(['success' => true]);
        break;

    // ── READING ───────────────────────────────────────────────────
    case 'save_passage':
        $id = intval($input['id'] ?? 0);
        $title = trim($input['title'] ?? '');
        $content = trim($input['content'] ?? '');
        $level = trim($input['level'] ?? 'beginner');

        if (!$title || !$content) jsonResponse(['error' => 'Title and content are required'], 400);

        if ($id > 0) {
            $stmt = $db->prepare("UPDATE reading_passages SET title=?, content=?, level=? WHERE id=?");
            $stmt->execute([$title, $content, $level, $id]);
        } else {
            $stmt = $db->prepare("INSERT INTO reading_passages (title, content, level) VALUES (?, ?, ?)");
            $stmt->execute([$title, $content, $level]);
        }
        jsonResponse(['success' => true]);
        break;

    case 'delete_passage':
        $id = intval($input['id'] ?? 0);
        if ($id) $db->prepare("DELETE FROM reading_passages WHERE id=?")->execute([$id]);
        jsonResponse(['success' => true]);
        break;

    case 'save_reading_question':
        $id = intval($input['id'] ?? 0);
        $passageId = intval($input['passage_id'] ?? 0);
        $text = trim($input['question_text'] ?? '');
        $ca = trim($input['choice_a'] ?? '');
        $cb = trim($input['choice_b'] ?? '');
        $cc = trim($input['choice_c'] ?? '');
        $cd = trim($input['choice_d'] ?? '');
        $correct = intval($input['correct_answer'] ?? 0);

        if (!$text || !$ca || !$passageId) jsonResponse(['error' => 'Question, passage ID and Choice A are required'], 400);

        if ($id > 0) {
            $stmt = $db->prepare("UPDATE reading_questions SET passage_id=?, question_text=?, choice_a=?, choice_b=?, choice_c=?, choice_d=?, correct_answer=? WHERE id=?");
            $stmt->execute([$passageId, $text, $ca, $cb, $cc, $cd, $correct, $id]);
        } else {
            $stmt = $db->prepare("INSERT INTO reading_questions (passage_id, question_text, choice_a, choice_b, choice_c, choice_d, correct_answer) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$passageId, $text, $ca, $cb, $cc, $cd, $correct]);
        }
        jsonResponse(['success' => true]);
        break;

    case 'delete_reading_question':
        $id = intval($input['id'] ?? 0);
        if ($id) $db->prepare("DELETE FROM reading_questions WHERE id=?")->execute([$id]);
        jsonResponse(['success' => true]);
        break;

    case 'save_game_sentence':
        $id = intval($input['id'] ?? 0);
        $en = trim($input['sentence_en'] ?? '');
        $th = trim($input['sentence_th'] ?? '');
        if (!$en || !$th) jsonResponse(['error' => 'English and Thai sentences are required'], 400);

        if ($id > 0) {
            $stmt = $db->prepare("UPDATE game_sentences SET sentence_en=?, sentence_th=? WHERE id=?");
            $stmt->execute([$en, $th, $id]);
        } else {
            $stmt = $db->prepare("INSERT INTO game_sentences (sentence_en, sentence_th) VALUES (?, ?)");
            $stmt->execute([$en, $th]);
        }
        jsonResponse(['success' => true]);
        break;

    case 'delete_game_sentence':
        $id = intval($input['id'] ?? 0);
        if ($id) $db->prepare("DELETE FROM game_sentences WHERE id=?")->execute([$id]);
        jsonResponse(['success' => true]);
        break;

    case 'save_game_fill':
        $id = intval($input['id'] ?? 0);
        $q = trim($input['question_text'] ?? '');
        $ans = trim($input['correct_answer'] ?? '');
        $c1 = trim($input['choice_1'] ?? '');
        $c2 = trim($input['choice_2'] ?? '');
        $c3 = trim($input['choice_3'] ?? '');
        $c4 = trim($input['choice_4'] ?? '');

        if (!$q || !$ans || !$c1 || !$c2 || !$c3 || !$c4) {
            jsonResponse(['error' => 'All fields are required'], 400);
        }

        if ($id > 0) {
            $stmt = $db->prepare("UPDATE game_fill_blanks SET question_text=?, correct_answer=?, choice_1=?, choice_2=?, choice_3=?, choice_4=? WHERE id=?");
            $stmt->execute([$q, $ans, $c1, $c2, $c3, $c4, $id]);
        } else {
            $stmt = $db->prepare("INSERT INTO game_fill_blanks (question_text, correct_answer, choice_1, choice_2, choice_3, choice_4) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$q, $ans, $c1, $c2, $c3, $c4]);
        }
        jsonResponse(['success' => true]);
        break;

    case 'delete_game_fill':
        $id = intval($input['id'] ?? 0);
        if ($id) $db->prepare("DELETE FROM game_fill_blanks WHERE id=?")->execute([$id]);
        jsonResponse(['success' => true]);
        break;

    default:
        jsonResponse(['error' => 'Invalid admin action'], 400);
}
