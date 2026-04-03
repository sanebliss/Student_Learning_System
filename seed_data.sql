USE learning_system;

-- Insert Sample Users
INSERT INTO users (email, password, full_name, role) VALUES
('admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9F8J9lG1X1tZ0eF5Q8ZK8W', 'Admin User', 'admin'),
('teacher@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9F8J9lG1X1tZ0eF5Q8ZK8W', 'John Teacher', 'teacher'),
('student1@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9F8J9lG1X1tZ0eF5Q8ZK8W', 'Alice Student', 'student'),
('student2@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9F8J9lG1X1tZ0eF5Q8ZK8W', 'Bob Student', 'student'),
('parent@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9F8J9lG1X1tZ0eF5Q8ZK8W', 'Parent User', 'parent'),
('teacher2@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9F8J9lG1X1tZ0eF5Q8ZK8W', 'Sarah Teacher', 'teacher');

-- Insert Sample Classes
INSERT INTO classes (class_name, grade, section, teacher_id) VALUES
('Class A', '1st', 'A', 2),
('Class B', '1st', 'B', 2),
('Class C', '2nd', 'A', 6);

-- Insert Sample Students
INSERT INTO students (user_id, class_id, parent_user_id) VALUES
(3, 1, 5),
(4, 1, NULL);

-- Insert Sample Activities (PDF)
INSERT INTO activities (title, description, activity_type, class_id, created_by, due_date, max_marks, file_path) VALUES
('Introduction to English', 'Learn basic English grammar and vocabulary', 'pdf', 1, 2, '2025-12-31 23:59:59', 100, NULL),
('Mathematics Basics', 'Introduction to basic mathematics concepts', 'pdf', 1, 2, '2025-12-31 23:59:59', 100, NULL),
('Science Fundamentals', 'Basic science concepts for beginners', 'pdf', 1, 2, '2025-12-31 23:59:59', 100, NULL);

-- Insert Sample Quiz Activity
INSERT INTO activities (title, description, activity_type, class_id, created_by, due_date, max_marks) VALUES
('English Quiz 1', 'Test your English knowledge', 'quiz', 1, 2, '2025-12-31 23:59:59', 10);

-- Insert Sample Quiz Questions (IDs will be 1–10 automatically)
INSERT INTO quiz_questions (activity_id, question_text, option_a, option_b, option_c, option_d, correct_option, marks) VALUES
(4, 'What is the past tense of "go"?', 'goes', 'went', 'going', 'gone', 'b', 1),
(4, 'Which of these is a noun?', 'run', 'quickly', 'table', 'beautiful', 'c', 1),
(4, 'What is the plural of "child"?', 'childs', 'children', 'childes', 'childly', 'b', 1),
(4, 'Complete: "She ___ to school every day"', 'go', 'goes', 'going', 'gone', 'b', 1),
(4, 'Which sentence is correct?', 'He do not like pizza', 'He does not likes pizza', 'He does not like pizza', 'He not like pizza', 'c', 1),
(4, 'What is an adjective?', 'A word that describes a noun', 'A word that shows action', 'A joining word', 'A small word', 'a', 1),
(4, 'Choose the correct form: "If I ___ you were coming"', 'knew', 'know', 'have known', 'will know', 'a', 1),
(4, '"Cest la vie" means:', 'That is life', 'Good morning', 'Thank you', 'Beautiful day', 'a', 1),
(4, 'Which is spelled correctly?', 'receieve', 'receive', 'recieve', 'receve', 'b', 1),
(4, 'Synonym of "happy":', 'sad', 'joyful', 'angry', 'tired', 'b', 1);

-- Insert Activity Assignments
INSERT INTO activity_assignments (activity_id, student_id, status, score, completed_at) VALUES
(1, 1, 'completed', 85, '2025-06-15 10:30:00'),
(2, 1, 'completed', 92, '2025-06-16 14:20:00'),
(3, 1, 'in_progress', NULL, NULL),
(4, 1, 'completed', 9, '2025-06-17 11:45:00'),
(1, 2, 'not_started', NULL, NULL),
(2, 2, 'not_started', NULL, NULL),
(3, 2, 'not_started', NULL, NULL),
(4, 2, 'in_progress', NULL, NULL);

-- Insert Sample Quiz Attempt (for student 1)
INSERT INTO quiz_attempts (activity_id, student_id, attempt_number, score, total_marks, attempted_at) VALUES
(4, 1, 1, 9, 10, '2025-06-17 11:45:00');

-- Insert Student Answers (Corrected IDs: 1–10)
INSERT INTO student_answers (quiz_attempt_id, question_id, selected_option, is_correct) VALUES
(1, 1, 'b', 1),
(1, 2, 'c', 1),
(1, 3, 'b', 1),
(1, 4, 'b', 1),
(1, 5, 'c', 1),
(1, 6, 'a', 1),
(1, 7, 'a', 1),
(1, 8, 'a', 1),
(1, 9, 'b', 1),
(1, 10, 'b', 0);

-- Insert Announcements
INSERT INTO announcements (title, message, class_id, posted_by, posted_at) VALUES
('Welcome to the Learning Platform', 'Welcome to our new online learning platform. This is where you will find all your learning materials and assignments.', 1, 2, '2025-06-01 08:00:00'),
('Reminder: Complete Your Assignments', 'Please remember to complete all assigned activities by the due dates.', 1, 2, '2025-06-10 09:30:00'),
('Summer Break Schedule', 'The summer break will commence from July 1st.', NULL, 1, '2025-06-15 10:00:00'),
('New Quiz Available', 'A new English quiz has been added. Please attempt it.', 1, 2, '2025-06-17 14:00:00');
