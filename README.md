# Child Learning and Progress Management System

A comprehensive web-based platform for managing student learning activities, progress tracking, and communication in an educational setting.

## 📋 Features

### Multi-Role Support
- **Admin:** System administration, user management, class management
- **Teacher:** Create activities/quizzes, assign to students, track progress, post announcements
- **Student:** Access learning materials, attempt quizzes, track progress
- **Parent:** Monitor child's progress, view announcements

### Core Modules

1. **User Management**
   - Role-based authentication and authorization
   - User CRUD operations
   - Profile management
   - Secure password hashing with bcrypt

2. **Learning Content & Activities**
   - PDF-based learning materials
   - MCQ (Multiple Choice Questions) quizzes
   - Activity assignment to classes or individual students
   - Secure file upload handling

3. **Progress Tracking & Evaluation**
   - Student assignment status tracking
   - Quiz attempt recording with detailed answers
   - Automatic score calculation
   - Progress dashboards for teachers, students, and parents
   - Completion statistics

4. **Communication & Announcements**
   - System-wide announcements
   - Class-specific announcements
   - Posted by teachers and admins
   - Visible to all users

5. **Class & Student Management**
   - Class creation and management
   - Teacher assignment to classes
   - Student enrollment
   - Parent-student linking (1:1 relationship)

## 🚀 Quick Start

### Prerequisites
- XAMPP (with PHP 7.4+ and MySQL 5.7+)
- Web browser
- 50 MB disk space

### Installation

1. **Extract Files**
   ```bash
   # Extract to XAMPP htdocs
   C:\xampp\htdocs\child-learning-system\
   ```

2. **Create Database**
   - Open phpMyAdmin: `http://localhost/phpmyadmin`
   - Run `database.sql` to create tables
   - (Optional) Run `seed_data.sql` to load demo data

3. **Start XAMPP**
   - Start Apache server
   - Start MySQL server

4. **Access Application**
   ```
   http://localhost/child-learning-system/
   ```

### Demo Credentials

```
Admin
Email: admin@example.com
Password: password

Teacher
Email: teacher@example.com
Password: password

Student
Email: student1@example.com
Password: password

Parent
Email: parent@example.com
Password: password
```

## 📁 Project Structure

```
child-learning-system/
├── config/
│   ├── db.php              # Database configuration
│   └── config.php          # Application configuration
├── includes/
│   ├── auth.php           # Authentication functions
│   ├── header.php         # HTML header template
│   ├── footer.php         # HTML footer template
│   └── nav.php            # Navigation bar
├── admin/                 # Admin pages
├── teacher/              # Teacher pages
├── student/              # Student pages
├── parent/               # Parent pages
├── css/                  # Stylesheets
├── js/                   # JavaScript files
├── uploads/              # User-uploaded files
├── database.sql          # Database schema
├── seed_data.sql         # Sample data
├── login.php             # Login page
├── index.php             # Home page redirect
└── README.md             # This file
```

## 🔐 Security Features

- **Password Hashing:** bcrypt with PHP's password_hash()
- **SQL Injection Prevention:** Prepared statements with MySQLi
- **Session Management:** Secure session handling with HTTPOnly cookies
- **Role-Based Access Control:** Endpoint protection by user role
- **Input Validation:** Server-side validation of all inputs
- **CSRF Protection:** Form tokens (recommended for enhancement)

## 🎯 Admin Dashboard

Admin users can:
- View system statistics (users, classes, students, activities)
- Create, edit, and delete users
- Manage classes and teacher assignments
- Enroll and manage students
- Link parents to students
- View all announcements

**Access:** `http://localhost/child-learning-system/admin/dashboard.php`

## 👨‍🏫 Teacher Dashboard

Teachers can:
- View assigned classes and students
- Create PDF activities and MCQ quizzes
- Manage quiz questions
- Assign activities to classes
- View student progress and performance
- Post announcements

**Access:** `http://localhost/child-learning-system/teacher/dashboard.php`

## 👨‍🎓 Student Dashboard

Students can:
- View assigned activities
- Access PDF learning materials
- Attempt MCQ quizzes
- View quiz results with detailed feedback
- Track personal progress
- View announcements

**Access:** `http://localhost/child-learning-system/student/dashboard.php`

## 👨‍👩‍👧 Parent Dashboard

Parents can:
- View linked child's information
- Monitor child's progress
- View activity completion status
- See quiz performance
- View announcements
- Cannot access quiz questions or attempt quizzes

**Access:** `http://localhost/child-learning-system/parent/dashboard.php`

## 🗄️ Database Schema

### Users Table
- id, email, password, full_name, role, created_at

### Classes Table
- id, class_name, grade, section, teacher_id

### Students Table
- id, user_id, class_id, parent_user_id

### Activities Table
- id, title, description, activity_type, class_id, created_by, due_date, max_marks, file_path

### Quiz Questions Table
- id, activity_id, question_text, option_a, option_b, option_c, option_d, correct_option, marks

### Activity Assignments Table
- id, activity_id, student_id, status, score, completed_at

### Quiz Attempts Table
- id, activity_id, student_id, score, total_marks, attempted_at

### Student Answers Table
- id, quiz_attempt_id, question_id, selected_option, is_correct

### Announcements Table
- id, title, message, class_id, posted_by, posted_at

## 📝 Usage Examples

### Creating a Quiz
1. Login as teacher
2. Go to "Activities" → "Create Activity"
3. Select type "MCQ Quiz"
4. Enter title and description
5. Click "Create Activity"
6. Go to "Manage Questions" for the quiz
7. Add questions with 4 options and mark correct answer

### Assigning Activity to Students
1. Login as teacher/admin
2. Create or select activity
3. Activity will appear in student's assignment list when added to their class

### Viewing Student Progress
1. Login as teacher
2. Go to "Progress" or "Class Progress"
3. Select class and student
4. View completion status, scores, and history

### Posting Announcements
1. Login as teacher/admin
2. Go to "Announcements" → "Create Announcement"
3. Enter title and message
4. Optionally select class to broadcast to
5. Click "Post Announcement"

## ⚙️ Configuration

Edit `config/config.php` to customize:
- Session timeout (default: 3600 seconds)
- Records per page (default: 10)
- File upload settings
- Constants for roles and status values

Edit `config/db.php` to change database connection:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'child_learning_system');
```

## 🐛 Troubleshooting

### Can't Login
- Verify database has users table with data
- Check that MySQL is running
- Ensure email and password are correct
- Clear browser cache and cookies

### File Upload Issues
- Check `/uploads/` directory exists and is writable
- Verify file is PDF format
- Check file size is under 5 MB
- Review Apache error logs

### Database Connection Error
- Start MySQL in XAMPP
- Verify DB credentials match your setup
- Ensure `child_learning_system` database exists
- Check MySQL port (default: 3306)

### 404 Page Not Found
- Verify Apache is running
- Check file exists in correct directory
- Verify file name spelling and case
- Check .htaccess configuration

## 📦 Technology Stack

- **Frontend:** HTML5, CSS3, Bootstrap 5, JavaScript
- **Backend:** PHP 7.4+
- **Database:** MySQL 5.7+
- **Server:** Apache (XAMPP)
- **No Framework Dependencies:** Plain PHP with MySQLi

## 🔄 User Workflow

1. **Admin Setup**
   - Create users and assign roles
   - Create classes
   - Assign teachers to classes
   - Enroll students in classes
   - Link parents to students

2. **Teacher Workflow**
   - Create learning activities (PDF)
   - Create MCQ quizzes with questions
   - Assign activities to classes
   - Monitor student progress
   - Post announcements

3. **Student Workflow**
   - Login to dashboard
   - View assigned activities
   - Access PDF materials
   - Attempt quizzes
   - View results and progress

4. **Parent Workflow**
   - Login to dashboard
   - Monitor child's progress
   - View activity completion
   - See quiz scores
   - Read announcements

## 📊 Sample Reports

- Student progress by class
- Quiz performance analysis
- Activity completion rates
- User statistics by role
- Announcements log

## 🎨 UI/UX Features

- Responsive Bootstrap design
- Role-based navigation
- Clean, modern interface
- Progress bars and status badges
- Intuitive forms
- Mobile-friendly layout
- Dark navigation bar with user dropdown

## 🔄 Regular Maintenance

- Backup database monthly
- Clean up old quiz attempts quarterly
- Review user activity logs
- Update user credentials as needed
- Monitor disk space for uploads

## 📞 Support

For issues or questions:
1. Check SETUP_INSTRUCTIONS.md
2. Review error logs in Apache/MySQL
3. Verify database tables exist
4. Test with sample credentials
5. Clear browser cache and try again

## ✅ Testing Checklist

- [ ] Database connection working
- [ ] All users can login with correct credentials
- [ ] Admin can create/edit/delete users
- [ ] Admin can create/manage classes
- [ ] Teacher can create activities and quizzes
- [ ] Teacher can assign activities
- [ ] Student can view and attempt quizzes
- [ ] Parent can view child's progress
- [ ] File uploads work correctly
- [ ] Progress tracking is accurate
- [ ] Announcements display to all users

## 🎓 Educational Use

This system is designed for:
- K-12 schools
- Online learning platforms
- Tuition centers
- University departments
- Corporate training programs

## 📄 License

This project is provided as-is for educational purposes.

## 🔧 Future Enhancements

- Video content support
- Real-time chat functionality
- Assignment grading interface
- Student behavior analytics
- Parent-teacher messaging
- Mobile app version
- Google Classroom integration
- Certificate generation
- Advanced reporting

---

**Version:** 1.0  
**Last Updated:** December 2025  
**Status:** Production Ready

For detailed setup instructions, refer to **SETUP_INSTRUCTIONS.md**
