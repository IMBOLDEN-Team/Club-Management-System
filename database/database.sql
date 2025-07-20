-- Sample of data

-- Insert Clubs
INSERT INTO CLUB (`name`, created_date) VALUES
('Badminton Club', '2023-01-15'),
('E-Sport Club', '2023-02-10'),
('Chess Club', '2023-01-20'),
('Debate Society', '2023-03-05'),
('Robotics Club', '2023-02-28'),
('Entrepreneurship Club', '2023-01-10');

-- Insert Clubers (Club Admins)
INSERT INTO CLUBER (username, `password`, club_id, created_date) VALUES
('badm01', SHA2('badminton123', 256), 1, '2023-01-16'),
('esport02', SHA2('gaming456', 256), 2, '2023-02-11'),
('chess03', SHA2('chess789', 256), 3, '2023-01-21'),
('debate04', SHA2('debate101', 256), 4, '2023-03-06'),
('robot05', SHA2('robot112', 256), 5, '2023-03-01'),
('entre06', SHA2('business131', 256), 6, '2023-01-11');

-- Insert Club Activities
INSERT INTO CLUB_ACTIVITY (`name`, `start`, `end`, merit_point, club_id, created_date) VALUES
-- Badminton Club
('KPMIM Badminton Open', '2023-04-15 09:00:00', '2023-04-15 17:00:00', 15, 1, '2023-03-01'),
('Inter-College Tournament', '2023-05-20 10:00:00', '2023-05-21 18:00:00', 25, 1, '2023-04-10'),

-- E-Sport Club
('Valorant Campus Championship', '2023-04-22 14:00:00', '2023-04-22 22:00:00', 10, 2, '2023-03-15'),
('Mobile Legends Tournament', '2023-05-12 13:00:00', '2023-05-14 21:00:00', 20, 2, '2023-04-01'),

-- Chess Club
('KPMIM Chess Rapid Challenge', '2023-04-08 10:00:00', '2023-04-08 16:00:00', 12, 3, '2023-03-10'),
('MARA Chess Grand Prix', '2023-06-10 09:00:00', '2023-06-11 17:00:00', 30, 3, '2023-05-01'),

-- Debate Society
('Freshman Debate Cup', '2023-04-05 08:30:00', '2023-04-05 16:30:00', 15, 4, '2023-03-01'),
('National MARA Debate', '2023-07-15 09:00:00', '2023-07-17 18:00:00', 40, 4, '2023-06-01'),

-- Robotics Club
('RoboDesign Workshop', '2023-04-18 14:00:00', '2023-04-18 17:00:00', 8, 5, '2023-03-20'),
('MARA Tech Innovation Challenge', '2023-08-12 09:00:00', '2023-08-13 18:00:00', 35, 5, '2023-07-01'),

-- Entrepreneurship Club
('Startup Pitch Day', '2023-05-05 10:00:00', '2023-05-05 15:00:00', 10, 6, '2023-04-01'),
('Business Plan Competition', '2023-09-10 09:00:00', '2023-09-12 17:00:00', 50, 6, '2023-08-01');