-- ============================================================================
-- NSU Helpdesk Ticketing System - Sample Data
-- Run AFTER schema.sql
-- ============================================================================

USE helpdesk_db;

-- ============================================================================
-- Users  (passwords are MD5 of the plaintext shown in the comment)
-- ============================================================================
INSERT INTO users (name, email, password_md5, role) VALUES
-- Staff accounts (password: staff123)
('Admin User',         'admin@northsouth.edu',     MD5('staff123'),   'staff'),
('Rafiq Ahmed',        'rafiq.ahmed@northsouth.edu', MD5('staff123'), 'staff'),
('Nusrat Jahan',       'nusrat.jahan@northsouth.edu', MD5('staff123'), 'staff'),

-- Faculty accounts (password: faculty123)
('Dr. Kamal Hossain',  'kamal.hossain@northsouth.edu',  MD5('faculty123'), 'faculty'),
('Dr. Faria Rahman',   'faria.rahman@northsouth.edu',   MD5('faculty123'), 'faculty'),
('Dr. Tanvir Alam',    'tanvir.alam@northsouth.edu',    MD5('faculty123'), 'faculty'),

-- Student accounts (password: student123)
('Sakib Hasan',        'sakib.hasan@northsouth.edu',    MD5('student123'), 'student'),
('Tasnim Akter',       'tasnim.akter@northsouth.edu',   MD5('student123'), 'student'),
('Arif Mahmud',        'arif.mahmud@northsouth.edu',    MD5('student123'), 'student'),
('Fatima Noor',        'fatima.noor@northsouth.edu',    MD5('student123'), 'student'),
('Raihan Kabir',       'raihan.kabir@northsouth.edu',   MD5('student123'), 'student'),
('Mithila Das',        'mithila.das@northsouth.edu',    MD5('student123'), 'student'),
('Zahid Islam',        'zahid.islam@northsouth.edu',    MD5('student123'), 'student'),
('Nadia Sultana',      'nadia.sultana@northsouth.edu',  MD5('student123'), 'student');

-- ============================================================================
-- Categories
-- ============================================================================
INSERT INTO categories (name, description) VALUES
('Technical Issue',    'Hardware, software, and network problems'),
('Account Access',     'Login issues, password resets, account lockouts'),
('Course Registration','Enrollment, section changes, prerequisite overrides'),
('Billing & Payment',  'Tuition fees, payment gateway, refunds'),
('General Inquiry',    'Campus info, policies, and other questions'),
('Lab & Equipment',    'Lab access, equipment booking, borrowed items');

-- ============================================================================
-- Tickets
-- ============================================================================
INSERT INTO tickets (user_id, assigned_staff_id, category_id, subject, description, priority, status, created_at) VALUES
-- Student tickets
(7, 1, 1, 'WiFi not connecting in Building 7',
    'I have been unable to connect to NSU-WiFi from any device since this morning. Other students nearby are also affected. The network shows up but authentication fails every time.',
    'high', 'open', NOW() - INTERVAL 2 HOUR),

(8, 2, 2, 'Cannot access RDS portal after password change',
    'I changed my NSU password yesterday and now I get "Invalid Credentials" when logging into the RDS student portal. My email login works fine.',
    'medium', 'in_progress', NOW() - INTERVAL 1 DAY),

(9, NULL, 3, 'Prerequisite override request for CSE311',
    'I completed CSE221 at another university before transfer. My transcript has been submitted but the system still blocks CSE311 enrollment. Requesting manual override.',
    'medium', 'open', NOW() - INTERVAL 3 DAY),

(10, 1, 4, 'Duplicate tuition charge on my account',
    'My spring 2026 tuition was charged twice on April 1st. Total overcharge is BDT 52,000. I have the bank statement to prove this. Please issue a reversal.',
    'critical', 'in_progress', NOW() - INTERVAL 5 DAY),

(11, 3, 1, 'Projector not working in Room NAC-401',
    'The ceiling-mounted projector in NAC-401 shows no signal regardless of which laptop is connected. The HDMI cable was also tested with a different projector.',
    'high', 'resolved', NOW() - INTERVAL 7 DAY),

(12, NULL, 5, 'Parking pass renewal process',
    'What is the process to renew my campus parking pass for the summer semester? The admin office told me to submit a ticket.',
    'low', 'open', NOW() - INTERVAL 4 DAY),

(7, 2, 6, 'Need access to Robotics Lab on weekends',
    'Our senior project group needs access to the Robotics Lab (SAC-901) on Saturdays from 10am to 4pm for the next 6 weeks. Faculty advisor Dr. Tanvir Alam has approved.',
    'medium', 'open', NOW() - INTERVAL 1 DAY),

(13, 1, 1, 'MATLAB license expired on lab computers',
    'All 30 workstations in the CSE lab show "License expired" when launching MATLAB R2025b. This is affecting multiple courses.',
    'critical', 'in_progress', NOW() - INTERVAL 6 HOUR),

(14, NULL, 2, 'Email forwarding not working',
    'I set up email forwarding from my NSU account to my Gmail two weeks ago, but emails are not being forwarded. I verified the forwarding address is correct.',
    'low', 'open', NOW() - INTERVAL 2 DAY),

-- Faculty tickets
(4, 3, 1, 'Smartboard calibration off in SAC-201',
    'The interactive smartboard in SAC-201 has significant touch offset - pointer appears about 3cm to the right of where I actually touch. Recalibration via the on-screen tool does not persist.',
    'medium', 'resolved', NOW() - INTERVAL 10 DAY),

(5, 1, 3, 'Cannot add TA to course section in RDS',
    'The "Add Teaching Assistant" option is greyed out for my CSE482 section. I need to assign two TAs before the semester starts next week.',
    'high', 'in_progress', NOW() - INTERVAL 3 DAY),

(6, NULL, 5, 'Request for guest WiFi account for visiting researcher',
    'Dr. Sarah Chen from MIT is visiting our department April 20-25. She will need campus WiFi access for the duration. What is the process to arrange a guest network account?',
    'medium', 'open', NOW() - INTERVAL 12 HOUR);

-- ============================================================================
-- Comments (replies / internal notes)
-- ============================================================================

-- Ticket 1: WiFi issue
INSERT INTO comments (ticket_id, user_id, body, is_internal, created_at) VALUES
(1, 1, 'We are aware of the WiFi outage in Building 7. The network team is investigating. Will update once we have an ETA.', 0, NOW() - INTERVAL 1 HOUR),
(1, 1, 'Root cause: a firmware update on the access points failed overnight. Rolling back now.', 1, NOW() - INTERVAL 45 MINUTE);

-- Ticket 2: RDS portal
INSERT INTO comments (ticket_id, user_id, body, is_internal, created_at) VALUES
(2, 2, 'Hi Tasnim, this is a known sync delay between the central directory and RDS. I have manually triggered a sync. Please try again in 15 minutes.', 0, NOW() - INTERVAL 20 HOUR),
(2, 8, 'Thank you! It works now.', 0, NOW() - INTERVAL 18 HOUR);

-- Ticket 4: Duplicate charge
INSERT INTO comments (ticket_id, user_id, body, is_internal, created_at) VALUES
(4, 1, 'Escalated to the finance department with reference #FIN-2026-0412. They will process the reversal within 3-5 business days.', 0, NOW() - INTERVAL 4 DAY),
(4, 10, 'Thanks for the quick response. I will check my bank statement next week.', 0, NOW() - INTERVAL 3 DAY);

-- Ticket 5: Projector issue (resolved)
INSERT INTO comments (ticket_id, user_id, body, is_internal, created_at) VALUES
(5, 3, 'Inspected the projector. The HDMI port on the projector side was damaged. Replaced with a new unit from inventory.', 0, NOW() - INTERVAL 6 DAY),
(5, 3, 'Replacement projector model: Epson EB-X51. Asset tag: NSU-PROJ-0087.', 1, NOW() - INTERVAL 6 DAY),
(5, 11, 'Projector is working perfectly now. Thank you!', 0, NOW() - INTERVAL 5 DAY);

-- Ticket 8: MATLAB license
INSERT INTO comments (ticket_id, user_id, body, is_internal, created_at) VALUES
(8, 1, 'Contacted MathWorks support. Our campus-wide license renewal was delayed in processing. They are expediting it.', 0, NOW() - INTERVAL 4 HOUR),
(8, 1, 'MathWorks ticket ref: MW-2026-88421. Expected resolution: within 24 hours.', 1, NOW() - INTERVAL 4 HOUR);

-- Ticket 10: Smartboard (resolved)
INSERT INTO comments (ticket_id, user_id, body, is_internal, created_at) VALUES
(10, 3, 'Performed a factory reset and full recalibration of the smartboard. The touch alignment is now accurate. Tested with both pen and finger input.', 0, NOW() - INTERVAL 8 DAY),
(10, 4, 'Working great now. Appreciate the quick fix!', 0, NOW() - INTERVAL 7 DAY);

-- ============================================================================
-- Ticket History (audit log entries)
-- ============================================================================
INSERT INTO ticket_history (ticket_id, changed_by, field_changed, old_value, new_value, created_at) VALUES
-- Ticket 2: status change
(2, 2, 'status', 'open', 'in_progress', NOW() - INTERVAL 20 HOUR),
-- Ticket 4: assignment + status change
(4, 1, 'assigned_staff_id', 'none', '1', NOW() - INTERVAL 5 DAY),
(4, 1, 'status', 'open', 'in_progress', NOW() - INTERVAL 5 DAY),
-- Ticket 5: full lifecycle
(5, 3, 'assigned_staff_id', 'none', '3', NOW() - INTERVAL 7 DAY),
(5, 3, 'status', 'open', 'in_progress', NOW() - INTERVAL 7 DAY),
(5, 3, 'status', 'in_progress', 'resolved', NOW() - INTERVAL 6 DAY),
-- Ticket 8: assignment + status change
(8, 1, 'assigned_staff_id', 'none', '1', NOW() - INTERVAL 5 HOUR),
(8, 1, 'status', 'open', 'in_progress', NOW() - INTERVAL 5 HOUR),
-- Ticket 10: full lifecycle
(10, 3, 'assigned_staff_id', 'none', '3', NOW() - INTERVAL 9 DAY),
(10, 3, 'status', 'open', 'in_progress', NOW() - INTERVAL 9 DAY),
(10, 3, 'status', 'in_progress', 'resolved', NOW() - INTERVAL 8 DAY),
-- Ticket 11: assignment
(11, 1, 'assigned_staff_id', 'none', '1', NOW() - INTERVAL 3 DAY),
(11, 1, 'status', 'open', 'in_progress', NOW() - INTERVAL 3 DAY);
