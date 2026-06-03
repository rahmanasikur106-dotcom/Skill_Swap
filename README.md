# 🔄 SkillSwap — Share Skills, Build Connections, Grow Together

> *"Knowledge increases by sharing but not by saving."*

SkillSwap is a web-based platform where people can **exchange skills with each other for free** — no money involved. If you know something someone else wants to learn, and they know something you want to learn, you swap. Simple as that.

---

## 👥 Team — Agile Dynamics

| Name | Role | Component |
|------|------|-----------|
| Asikur Rahman | Developer | **Chat & Messaging** |
| Imran Hossain | Developer | User and Authentication |
| Kritika Singh | Scrum Master | Search and Matchmaking |
| Radiah Anan | Developer | Skill Management |
| Jiasmin | Developer | Engagement and Feedback |
| Sudikshya | Developer | Session Management |

---

## ✨ Features

- 🔐 **User Registration & Login** — Secure authentication with hashed passwords and password reset via email
- 👤 **Profile Management** — Users can set up profiles with skills, bio, location and profile picture
- 🔍 **Search & Matchmaking** — Find users by skill and location; get notified when a match is found
- 💬 **Chat & Messaging** — Private real-time messaging between matched users with file sharing support
- 📅 **Events** — Create and join local skill-swap events
- 🗓️ **Session Management** — Schedule and track skill swap sessions (Pending / Accepted / Rejected)
- ⭐ **Ratings & Feedback** — Rate swap partners after sessions to build trust in the community
- 📞 **Video Calling** — WebRTC-based peer-to-peer video calls between matched users

---

## 🛠️ Tech Stack

| Layer | Technology |
|-------|-----------|
| Frontend | HTML, CSS, JavaScript |
| Backend | PHP 8.2 |
| Database | MySQL (MariaDB 10.4) |
| Server | Apache via XAMPP |
| Version Control | Git & GitHub |

---

## 🗄️ Database

The system uses a MySQL database named `skillswap` with the following tables:

- `users` — user accounts and profile info
- `messages` — chat messages between users
- `matches` — skill swap matches between users
- `swaps` — swap requests and their status
- `sessions` — scheduled skill swap sessions
- `skills` — master list of available skills
- `user_skills` — skills each user can teach or wants to learn
- `events` — community events
- `event_participant` — event attendance
- `rating` — user ratings after swaps
- `notification` — in-app notifications
- `call_signals` — WebRTC signalling data
- `password_resets` — password reset tokens

---

## 💬 Chat & Messaging Module

> Developed by: **Asikur Rahman**

The messaging module is the communication layer of the platform. Once two users are matched, they get a private space to organise their skill swap.

**Key functionality:**
- Send and receive direct messages
- Mark messages as read/unread
- Edit and delete messages
- Share files and images in chat
- Conversation history stored and retrieved in correct order
- Real-time message polling

**Database table: `messages`**

| Column | Type | Description |
|--------|------|-------------|
| MessageID | INT (PK) | Unique message identifier |
| MessageText | VARCHAR(255) | Text content of the message |
| IsRead | BOOLEAN | 0 = unread, 1 = read |
| Timestamp | DATETIME | When the message was sent |
| sender_id | INT (FK) | User who sent the message |
| receiver_id | INT (FK) | User who received the message |
| file_path | VARCHAR(500) | Path to attached file (if any) |
| file_name | VARCHAR(255) | Original filename |
| file_type | VARCHAR(100) | MIME type of attached file |
| file_size | BIGINT | File size in bytes |

---

## 🚀 Installation & Setup

### Requirements
- XAMPP (PHP 8.2 + Apache + MySQL)
- Git

### Steps

1. **Clone the repository**
   ```bash
   git clone https://github.com/rahmanasikur106-dotcom/Skill_Swap.git
   ```

2. **Move to XAMPP htdocs**
   ```bash
   # Place the skillswap folder inside:
   C:/xampp/htdocs/skillswap
   ```

3. **Import the database**
   - Open phpMyAdmin: `http://localhost/phpmyadmin`
   - Create a new database called `skillswap`
   - Import the `skillswap.sql` file

4. **Configure database connection**
   - Open `db.php` and update your credentials if needed:
   ```php
   $host = 'localhost';
   $db   = 'skillswap';
   $user = 'root';
   $pass = '';
   ```

5. **Run the app**
   - Start Apache and MySQL in XAMPP
   - Visit: `http://localhost/skillswap`

---

## 📁 Project Structure

```
skillswap/
├── index.php              # Landing page
├── login.php              # Login page
├── register.php           # Registration page
├── dashboard.php          # Main dashboard
├── chat.php               # Chat & messaging
├── profile.php            # User profile
├── discovery.php          # Skill discovery / search
├── matchmaking.php        # Match logic
├── session_list.php       # Skill swap sessions
├── create_session.php     # Schedule a session
├── swaps.php              # Swap requests
├── events/                # Event pages
├── includes/              # Header & footer
├── uploads/               # User uploaded files
├── style.css              # Global stylesheet
├── db.php                 # Database connection
└── skillswap.sql          # Database dump
```

---

## 📌 Module: CTEC2713 — Agile Development Team Project
**Faculty of Computing, Engineering & Media (CEM)**
De Montfort University / Niels Brock — 2024/25
