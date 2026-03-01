-- seed.sql: add sample student and logs
USE ojt_tracker;

INSERT IGNORE INTO users (full_name,email,password,required_hours,role,course,photo) VALUES
('Student One','student1@example.com', 'REPLACE_WITH_HASH',600,'student','',NULL);

INSERT INTO ojt_logs (user_id,date,time_in,time_out,total_hours,description,status) VALUES
(1,'2026-02-25','08:00','12:00',4.00,'Orientation','approved'),
(1,'2026-02-26','09:00','17:00',8.00,'Training','approved');