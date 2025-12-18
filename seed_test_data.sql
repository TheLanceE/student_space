/* EduMind+ Test Data Seed Script */
/* Run after database.sql and database_challenges_rewards.sql */
/* Generates comprehensive test data for development */

USE edumind;

/* Disable foreign key checks during import */
SET FOREIGN_KEY_CHECKS = 0;

/* STUDENTS (including SuperKid) - Password for all: password123 */
INSERT INTO students (id, username, password, fullName, email, mobile, address, gradeLevel, createdAt, lastLoginAt) VALUES
('stu_superkid', 'superkid', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Super Kid', 'superkid@edumind.app', '+1234567890', '100 Hero Lane, Metropolis', 'Grade 10', DATE_SUB(NOW(), INTERVAL 60 DAY), DATE_SUB(NOW(), INTERVAL 1 HOUR)),
('stu_emma', 'emma', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Emma Watson', 'emma@edumind.app', '+1112223333', '22 Reading Rd', 'Grade 9', DATE_SUB(NOW(), INTERVAL 45 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY)),
('stu_liam', 'liam', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Liam Smith', 'liam@edumind.app', '+4445556666', '33 Tech Blvd', 'Grade 11', DATE_SUB(NOW(), INTERVAL 38 DAY), DATE_SUB(NOW(), INTERVAL 5 DAY)),
('stu_sophia', 'sophia', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sophia Chen', 'sophia@edumind.app', '+7778889999', '44 Scholar Ave', 'Grade 8', DATE_SUB(NOW(), INTERVAL 30 DAY), DATE_SUB(NOW(), INTERVAL 1 DAY)),
('stu_noah', 'noah', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Noah Johnson', 'noah@edumind.app', '+1011121314', '55 Bright St', 'Grade 10', DATE_SUB(NOW(), INTERVAL 25 DAY), DATE_SUB(NOW(), INTERVAL 3 DAY)),
('stu_olivia', 'olivia', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Olivia Brown', 'olivia@edumind.app', '+1516171819', '66 Wisdom Way', 'Grade 9', DATE_SUB(NOW(), INTERVAL 22 DAY), DATE_SUB(NOW(), INTERVAL 4 DAY)),
('stu_james', 'james', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'James Wilson', 'james@edumind.app', '+2021222324', '77 Logic Lane', 'Grade 11', DATE_SUB(NOW(), INTERVAL 18 DAY), DATE_SUB(NOW(), INTERVAL 6 DAY)),
('stu_ava', 'ava', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ava Martinez', 'ava@edumind.app', '+2526272829', '88 Quest Ct', 'Grade 8', DATE_SUB(NOW(), INTERVAL 15 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY))
ON DUPLICATE KEY UPDATE lastLoginAt=NOW();

/* EXTRA STUDENTS FOR TESTING */
INSERT INTO students (id, username, password, fullName, email, mobile, address, gradeLevel, createdAt, lastLoginAt) VALUES
('stu_isabella', 'isabella', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Isabella Rivera', 'isabella@edumind.app', '+3031323334', '12 Skyline Dr', 'Grade 10', DATE_SUB(NOW(), INTERVAL 18 DAY), DATE_SUB(NOW(), INTERVAL 1 DAY)),
('stu_mason', 'mason', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mason Patel', 'mason@edumind.app', '+3536373839', '90 Park Ave', 'Grade 9', DATE_SUB(NOW(), INTERVAL 16 DAY), DATE_SUB(NOW(), INTERVAL 3 DAY)),
('stu_mia', 'mia', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mia Nguyen', 'mia@edumind.app', '+4041424344', '70 Garden St', 'Grade 8', DATE_SUB(NOW(), INTERVAL 12 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY)),
('stu_ethan', 'ethan', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ethan Garcia', 'ethan@edumind.app', '+4546474849', '11 Maple Cir', 'Grade 11', DATE_SUB(NOW(), INTERVAL 10 DAY), DATE_SUB(NOW(), INTERVAL 4 DAY)),
('stu_charlotte', 'charlotte', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Charlotte Brooks', 'charlotte@edumind.app', '+5051525354', '9 Lakeview Rd', 'Grade 9', DATE_SUB(NOW(), INTERVAL 8 DAY), DATE_SUB(NOW(), INTERVAL 1 DAY)),
('stu_ben', 'ben', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Benjamin Lopez', 'ben@edumind.app', '+5556575859', '81 River St', 'Grade 10', DATE_SUB(NOW(), INTERVAL 7 DAY), DATE_SUB(NOW(), INTERVAL 1 DAY)),
('stu_zara', 'zara', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Zara Ali', 'zara@edumind.app', '+6061626364', '3 Summit Pl', 'Grade 8', DATE_SUB(NOW(), INTERVAL 5 DAY), DATE_SUB(NOW(), INTERVAL 1 DAY))
ON DUPLICATE KEY UPDATE lastLoginAt=NOW();

/* ADDITIONAL TEACHERS */
INSERT INTO teachers (id, username, password, fullName, email, mobile, address, specialty, nationalId, createdAt, lastLoginAt) VALUES
('teach_kim', 'teacher_kim', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. Kim Park', 'kim@edumind.app', '+555111222', '100 Faculty Dr', 'Computer Science', 'NAT-003-KP', DATE_SUB(NOW(), INTERVAL 50 DAY), DATE_SUB(NOW(), INTERVAL 1 DAY)),
('teach_sarah', 'teacher_sarah', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sarah Adams', 'sarah@edumind.app', '+555333444', '200 Academic Way', 'English Literature', 'NAT-004-SA', DATE_SUB(NOW(), INTERVAL 45 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY)),
('teach_mark', 'teacher_mark', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mark Thompson', 'mark@edumind.app', '+555555666', '300 Campus Blvd', 'History', 'NAT-005-MT', DATE_SUB(NOW(), INTERVAL 40 DAY), DATE_SUB(NOW(), INTERVAL 3 DAY))
ON DUPLICATE KEY UPDATE lastLoginAt=NOW();

/* EXTRA TEACHERS */
INSERT INTO teachers (id, username, password, fullName, email, mobile, address, specialty, nationalId, createdAt, lastLoginAt) VALUES
('teach_nina', 'teacher_nina', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nina Torres', 'nina@edumind.app', '+555777888', '400 Learning Ln', 'Geography', 'NAT-006-NT', DATE_SUB(NOW(), INTERVAL 38 DAY), DATE_SUB(NOW(), INTERVAL 1 DAY)),
('teach_omar', 'teacher_omar', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Omar Khalil', 'omar@edumind.app', '+555888999', '500 Mentor Rd', 'Art & Design', 'NAT-007-OK', DATE_SUB(NOW(), INTERVAL 35 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY))
ON DUPLICATE KEY UPDATE lastLoginAt=NOW();

/* MORE COURSES */
INSERT INTO courses (id, title, description, teacherId, status, createdAt) VALUES
('cs101', 'Introduction to Programming', 'Learn the basics of coding with Python and JavaScript.', 'teach_kim', 'active', DATE_SUB(NOW(), INTERVAL 40 DAY)),
('eng101', 'Creative Writing', 'Express yourself through stories, poetry, and essays.', 'teach_sarah', 'active', DATE_SUB(NOW(), INTERVAL 35 DAY)),
('hist101', 'World History', 'Explore major events that shaped our world.', 'teach_mark', 'active', DATE_SUB(NOW(), INTERVAL 32 DAY)),
('math201', 'Algebra II', 'Advanced algebraic concepts and equations.', 'teach_jane', 'active', DATE_SUB(NOW(), INTERVAL 28 DAY)),
('sci201', 'Physics Fundamentals', 'Motion, forces, energy, and waves.', 'teach_lee', 'active', DATE_SUB(NOW(), INTERVAL 26 DAY)),
('cs201', 'Web Development', 'HTML, CSS, JavaScript, and modern frameworks.', 'teach_kim', 'pending', DATE_SUB(NOW(), INTERVAL 10 DAY)),
('eng201', 'Public Speaking', 'Build confidence and communication skills.', 'teach_sarah', 'pending', DATE_SUB(NOW(), INTERVAL 8 DAY)),
('hist201', 'Ancient Civilizations', 'Egypt, Rome, Greece, and Mesopotamia.', 'teach_mark', 'pending', DATE_SUB(NOW(), INTERVAL 5 DAY)),
('geo101', 'World Geography', 'Maps, regions, and climate systems.', 'teach_nina', 'active', DATE_SUB(NOW(), INTERVAL 20 DAY)),
('art101', 'Art Foundations', 'Color theory, sketching, and design basics.', 'teach_omar', 'active', DATE_SUB(NOW(), INTERVAL 18 DAY)),
('geo201', 'Climate Change Studies', 'Impacts, mitigation, and global policy.', 'teach_nina', 'pending', DATE_SUB(NOW(), INTERVAL 7 DAY)),
('art201', 'Digital Illustration', 'Tablets, vectors, and composition.', 'teach_omar', 'pending', DATE_SUB(NOW(), INTERVAL 6 DAY))
ON DUPLICATE KEY UPDATE title=title;

/* QUIZZES - Math */
INSERT INTO quizzes (id, courseId, title, durationSec, difficulty, questions, createdBy, createdAt) VALUES
('math101_quiz1', 'math101', 'Math Basics · Quiz 1', 60, 'beginner',
'[{"id":"m1_q1","text":"2 + 2 = ?","options":["3","4","5","6"],"correctIndex":1},{"id":"m1_q2","text":"5 - 3 = ?","options":["1","2","3","4"],"correctIndex":1},{"id":"m1_q3","text":"10 / 2 = ?","options":["2","4","5","10"],"correctIndex":2},{"id":"m1_q4","text":"3 × 3 = ?","options":["6","7","8","9"],"correctIndex":3},{"id":"m1_q5","text":"Solve for x: x + 1 = 4","options":["1","2","3","4"],"correctIndex":2}]',
'teach_jane', DATE_SUB(NOW(), INTERVAL 20 DAY)),
('math101_quiz2', 'math101', 'Math Basics Quiz 2', 90, 'beginner',
'[{"id":"m2_q1","text":"7 x 8 = ?","options":["54","56","58","60"],"correctIndex":1},{"id":"m2_q2","text":"What is 25% of 80?","options":["15","18","20","25"],"correctIndex":2},{"id":"m2_q3","text":"9 squared = ?","options":["72","81","90","99"],"correctIndex":1},{"id":"m2_q4","text":"What is the next prime after 13?","options":["15","16","17","19"],"correctIndex":2},{"id":"m2_q5","text":"15 / 3 + 2 = ?","options":["5","6","7","8"],"correctIndex":2}]',
'teach_jane', DATE_SUB(NOW(), INTERVAL 15 DAY)),
('math101_quiz3', 'math101', 'Fractions Challenge', 120, 'intermediate',
'[{"id":"m3_q1","text":"1/2 + 1/4 = ?","options":["1/6","2/6","3/4","1/4"],"correctIndex":2},{"id":"m3_q2","text":"3/5 x 10 = ?","options":["3","5","6","8"],"correctIndex":2},{"id":"m3_q3","text":"2/3 / 1/3 = ?","options":["1","2","3","6"],"correctIndex":1},{"id":"m3_q4","text":"Convert 0.75 to fraction","options":["1/2","2/3","3/4","4/5"],"correctIndex":2},{"id":"m3_q5","text":"What is 3/8 as decimal?","options":["0.375","0.38","0.35","0.325"],"correctIndex":0}]',
'teach_jane', DATE_SUB(NOW(), INTERVAL 12 DAY)),
('math201_quiz1', 'math201', 'Algebra II Equations', 150, 'advanced',
'[{"id":"a1_q1","text":"Solve: 2x + 5 = 17","options":["4","5","6","7"],"correctIndex":2},{"id":"a1_q2","text":"Solve: 3(x-2) = 15","options":["5","6","7","8"],"correctIndex":2},{"id":"a1_q3","text":"If y = 2x + 3, what is y when x = 5?","options":["10","11","12","13"],"correctIndex":3},{"id":"a1_q4","text":"Simplify: 4x + 3x - 2x","options":["3x","4x","5x","9x"],"correctIndex":2},{"id":"a1_q5","text":"Factor: x squared - 9","options":["(x-3)(x+3)","(x-9)(x+1)","(x-3) squared","Cannot factor"],"correctIndex":0}]',
'teach_jane', DATE_SUB(NOW(), INTERVAL 8 DAY))
ON DUPLICATE KEY UPDATE title=title;

/* QUIZZES - Science */
INSERT INTO quizzes (id, courseId, title, durationSec, difficulty, questions, createdBy, createdAt) VALUES
('sci101_quiz1', 'sci101', 'Science Basics · Quiz 1', 60, 'beginner',
'[{"id":"s1_q1","text":"Water boils at what °C?","options":["50","80","100","120"],"correctIndex":2},{"id":"s1_q2","text":"What gas do plants produce?","options":["CO₂","O₂","N₂","CH₄"],"correctIndex":1},{"id":"s1_q3","text":"Earth is the ___ planet from the Sun.","options":["2nd","3rd","4th","5th"],"correctIndex":1},{"id":"s1_q4","text":"Basic unit of life is the:","options":["Atom","Molecule","Cell","Organ"],"correctIndex":2},{"id":"s1_q5","text":"H₂O is:","options":["Oxygen","Hydrogen","Water","Helium"],"correctIndex":2}]',
'teach_lee', DATE_SUB(NOW(), INTERVAL 18 DAY)),
('sci101_quiz2', 'sci101', 'The Human Body', 90, 'beginner',
'[{"id":"sb_q1","text":"How many bones in adult body?","options":["186","206","226","246"],"correctIndex":1},{"id":"sb_q2","text":"Largest organ in the body?","options":["Heart","Liver","Skin","Brain"],"correctIndex":2},{"id":"sb_q3","text":"Blood is pumped by the:","options":["Brain","Lungs","Heart","Kidney"],"correctIndex":2},{"id":"sb_q4","text":"Oxygen is carried by:","options":["White blood cells","Platelets","Red blood cells","Plasma"],"correctIndex":2},{"id":"sb_q5","text":"The brain is part of the:","options":["Circulatory system","Nervous system","Digestive system","Respiratory system"],"correctIndex":1}]',
'teach_lee', DATE_SUB(NOW(), INTERVAL 14 DAY)),
('sci101_quiz3', 'sci101', 'Chemistry Basics', 120, 'intermediate',
'[{"id":"ch_q1","text":"Symbol for Gold?","options":["Go","Gd","Au","Ag"],"correctIndex":2},{"id":"ch_q2","text":"pH of pure water?","options":["5","6","7","8"],"correctIndex":2},{"id":"ch_q3","text":"Atomic number represents:","options":["Mass","Protons","Neutrons","Electrons"],"correctIndex":1},{"id":"ch_q4","text":"NaCl is:","options":["Sugar","Baking soda","Salt","Vinegar"],"correctIndex":2},{"id":"ch_q5","text":"Noble gases are in Group:","options":["1","8","17","18"],"correctIndex":3}]',
'teach_lee', DATE_SUB(NOW(), INTERVAL 10 DAY)),
('sci201_quiz1', 'sci201', 'Physics Motion', 150, 'intermediate',
'[{"id":"ph_q1","text":"Speed = Distance divided by ?","options":["Mass","Force","Time","Velocity"],"correctIndex":2},{"id":"ph_q2","text":"Unit of Force?","options":["Joule","Watt","Newton","Pascal"],"correctIndex":2},{"id":"ph_q3","text":"Acceleration due to gravity approx","options":["8 m/s2","9.8 m/s2","10.8 m/s2","12 m/s2"],"correctIndex":1},{"id":"ph_q4","text":"F = m times ?","options":["v","t","a","d"],"correctIndex":2},{"id":"ph_q5","text":"Object at rest stays at rest - this is:","options":["Newton 1st Law","Newton 2nd Law","Newton 3rd Law","Gravity Law"],"correctIndex":0}]',
'teach_lee', DATE_SUB(NOW(), INTERVAL 6 DAY))
ON DUPLICATE KEY UPDATE title=title;

/* QUIZZES - Computer Science */
INSERT INTO quizzes (id, courseId, title, durationSec, difficulty, questions, createdBy, createdAt) VALUES
('cs101_quiz1', 'cs101', 'Programming Basics', 90, 'beginner',
'[{"id":"cs1_q1","text":"What does HTML stand for?","options":["Hyper Text Markup Language","High Tech Modern Language","Home Tool Markup Language","Hyper Tool Multi Language"],"correctIndex":0},{"id":"cs1_q2","text":"Which is NOT a programming language?","options":["Python","JavaScript","HTTP","Java"],"correctIndex":2},{"id":"cs1_q3","text":"What is a variable?","options":["A constant value","A container for data","A type of loop","A function"],"correctIndex":1},{"id":"cs1_q4","text":"What does CSS style?","options":["Logic","Databases","Web pages","Servers"],"correctIndex":2},{"id":"cs1_q5","text":"Loop that runs at least once:","options":["for","while","do-while","if"],"correctIndex":2}]',
'teach_kim', DATE_SUB(NOW(), INTERVAL 20 DAY)),
('cs101_quiz2', 'cs101', 'Python Fundamentals', 120, 'beginner',
'[{"id":"py_q1","text":"Python comment starts with:","options":["//","/*","#","--"],"correctIndex":2},{"id":"py_q2","text":"Print function in Python:","options":["echo()","console.log()","print()","printf()"],"correctIndex":2},{"id":"py_q3","text":"Python list is defined with:","options":["{}","[]","()","<>"],"correctIndex":1},{"id":"py_q4","text":"What is len([1,2,3])?","options":["1","2","3","4"],"correctIndex":2},{"id":"py_q5","text":"Python is case-sensitive?","options":["Yes","No","Sometimes","Only for variables"],"correctIndex":0}]',
'teach_kim', DATE_SUB(NOW(), INTERVAL 16 DAY)),
('cs101_quiz3', 'cs101', 'JavaScript Intro', 120, 'intermediate',
'[{"id":"js_q1","text":"Declare a variable in JS:","options":["var x = 5","x := 5","int x = 5","variable x = 5"],"correctIndex":0},{"id":"js_q2","text":"JS array method to add item:","options":["add()","push()","insert()","append()"],"correctIndex":1},{"id":"js_q3","text":"=== checks:","options":["Value only","Type only","Value and type","Reference"],"correctIndex":2},{"id":"js_q4","text":"undefined vs null:","options":["Same thing","undefined=not assigned, null=intentional","undefined=error","null=not assigned"],"correctIndex":1},{"id":"js_q5","text":"Event listener syntax:","options":["on.click()","addEvent()","addEventListener()","attachEvent()"],"correctIndex":2}]',
'teach_kim', DATE_SUB(NOW(), INTERVAL 11 DAY))
ON DUPLICATE KEY UPDATE title=title;

/* QUIZZES - English */
INSERT INTO quizzes (id, courseId, title, durationSec, difficulty, questions, createdBy, createdAt) VALUES
('eng101_quiz1', 'eng101', 'Grammar Essentials', 90, 'beginner',
'[{"id":"eng1_q1","text":"Plural of child:","options":["Childs","Children","Childes","Childern"],"correctIndex":1},{"id":"eng1_q2","text":"Past tense of go:","options":["Goed","Gone","Went","Going"],"correctIndex":2},{"id":"eng1_q3","text":"A noun names a:","options":["Action","Person/place/thing","Description","Connection"],"correctIndex":1},{"id":"eng1_q4","text":"Their/There/They are - which is possessive?","options":["There","Their","They are","All of them"],"correctIndex":1},{"id":"eng1_q5","text":"An adjective describes a:","options":["Verb","Noun","Preposition","Conjunction"],"correctIndex":1}]',
'teach_sarah', DATE_SUB(NOW(), INTERVAL 25 DAY)),
('eng101_quiz2', 'eng101', 'Vocabulary Builder', 90, 'intermediate',
'[{"id":"voc_q1","text":"Synonym for happy:","options":["Sad","Joyful","Angry","Tired"],"correctIndex":1},{"id":"voc_q2","text":"Antonym for brave:","options":["Fearless","Bold","Cowardly","Strong"],"correctIndex":2},{"id":"voc_q3","text":"Benevolent means:","options":["Evil","Kind","Neutral","Angry"],"correctIndex":1},{"id":"voc_q4","text":"A metaphor is:","options":["A direct comparison","An implied comparison","An exaggeration","A question"],"correctIndex":1},{"id":"voc_q5","text":"Ubiquitous means:","options":["Rare","Common everywhere","Hidden","Ancient"],"correctIndex":1}]',
'teach_sarah', DATE_SUB(NOW(), INTERVAL 18 DAY))
ON DUPLICATE KEY UPDATE title=title;

/* QUIZZES - History */
INSERT INTO quizzes (id, courseId, title, durationSec, difficulty, questions, createdBy, createdAt) VALUES
('hist101_quiz1', 'hist101', 'World History Basics', 90, 'beginner',
'[{"id":"h1_q1","text":"World War II ended in:","options":["1943","1944","1945","1946"],"correctIndex":2},{"id":"h1_q2","text":"Who discovered America?","options":["Columbus","Magellan","Cook","Vespucci"],"correctIndex":0},{"id":"h1_q3","text":"Great Wall is in:","options":["Japan","India","China","Korea"],"correctIndex":2},{"id":"h1_q4","text":"French Revolution started:","options":["1689","1789","1889","1989"],"correctIndex":1},{"id":"h1_q5","text":"First President of USA:","options":["Lincoln","Jefferson","Adams","Washington"],"correctIndex":3}]',
'teach_mark', DATE_SUB(NOW(), INTERVAL 22 DAY)),
('hist101_quiz2', 'hist101', 'Ancient Empires', 120, 'intermediate',
'[{"id":"anc_q1","text":"Capital of Roman Empire:","options":["Athens","Rome","Constantinople","Alexandria"],"correctIndex":1},{"id":"anc_q2","text":"Pyramids built by:","options":["Greeks","Romans","Egyptians","Persians"],"correctIndex":2},{"id":"anc_q3","text":"Alexander the Great was from:","options":["Rome","Persia","Macedonia","Egypt"],"correctIndex":2},{"id":"anc_q4","text":"Democracy originated in:","options":["Rome","Athens","Sparta","Persia"],"correctIndex":1},{"id":"anc_q5","text":"Silk Road connected:","options":["Africa-Europe","Europe-Americas","Asia-Europe","Australia-Asia"],"correctIndex":2}]',
'teach_mark', DATE_SUB(NOW(), INTERVAL 15 DAY))
ON DUPLICATE KEY UPDATE title=title;

/* QUIZZES - Geography */
INSERT INTO quizzes (id, courseId, title, durationSec, difficulty, questions, createdBy, createdAt) VALUES
('geo101_quiz1', 'geo101', 'Map Skills', 90, 'beginner',
'[{"id":"g1_q1","text":"Largest ocean:","options":["Atlantic","Indian","Pacific","Arctic"],"correctIndex":2},{"id":"g1_q2","text":"The Equator is a line of:","options":["Longitude","Latitude","Altitude","Attitude"],"correctIndex":1},{"id":"g1_q3","text":"Mount Everest is in:","options":["Andes","Alps","Himalayas","Rockies"],"correctIndex":2},{"id":"g1_q4","text":"Sahara is in:","options":["Asia","Europe","Africa","Australia"],"correctIndex":2},{"id":"g1_q5","text":"Number of continents:","options":["5","6","7","8"],"correctIndex":2}]',
'teach_nina', DATE_SUB(NOW(), INTERVAL 14 DAY)),
('geo201_quiz1', 'geo201', 'Climate Systems', 120, 'intermediate',
'[{"id":"gc_q1","text":"Greenhouse gas NOT commonly cited:","options":["CO2","CH4","N2","N2O"],"correctIndex":2},{"id":"gc_q2","text":"Current CO2 ppm ~:","options":["150","280","420","600"],"correctIndex":2},{"id":"gc_q3","text":"El Niño impacts:","options":["Pacific temperatures","Atlantic salinity","Indian Ocean depth","Arctic ice age"],"correctIndex":0},{"id":"gc_q4","text":"Main driver of sea level rise:","options":["Tectonics","Thermal expansion + ice melt","River flooding","Sand mining"],"correctIndex":1},{"id":"gc_q5","text":"IPCC stands for:","options":["International Panel on Climate Change","Intergovernmental Panel on Climate Change","Interplanetary Climate Council","International Pollution Control Commission"],"correctIndex":1}]',
'teach_nina', DATE_SUB(NOW(), INTERVAL 9 DAY))
ON DUPLICATE KEY UPDATE title=title;

/* QUIZZES - Art */
INSERT INTO quizzes (id, courseId, title, durationSec, difficulty, questions, createdBy, createdAt) VALUES
('art101_quiz1', 'art101', 'Color Theory', 80, 'beginner',
'[{"id":"a101_q1","text":"Primary colors (RGB model):","options":["Red, Green, Blue","Red, Yellow, Blue","Cyan, Magenta, Yellow","Red, Green, Yellow"],"correctIndex":0},{"id":"a101_q2","text":"Complement of blue in RGB:","options":["Red","Green","Cyan","Orange"],"correctIndex":1},{"id":"a101_q3","text":"Value refers to:","options":["Lightness/darkness","Texture","Hue","Saturation"],"correctIndex":0},{"id":"a101_q4","text":"Warm color example:","options":["Blue","Green","Purple","Orange"],"correctIndex":3},{"id":"a101_q5","text":"Analogous palette uses colors:","options":["Opposite on wheel","Next to each other","Randomly picked","Only neutrals"],"correctIndex":1}]',
'teach_omar', DATE_SUB(NOW(), INTERVAL 12 DAY)),
('art201_quiz1', 'art201', 'Digital Illustration Basics', 110, 'intermediate',
'[{"id":"a201_q1","text":"Common vector format:","options":["SVG","JPG","BMP","TIFF"],"correctIndex":0},{"id":"a201_q2","text":"Tablet pressure helps control:","options":["Line weight","Canvas size","File type","Export speed"],"correctIndex":0},{"id":"a201_q3","text":"Layer blending mode for lightening:","options":["Multiply","Screen","Overlay","Difference"],"correctIndex":1},{"id":"a201_q4","text":"Shortcut concept:","options":["Use only mouse","Flatten often","Name layers","Avoid references"],"correctIndex":2},{"id":"a201_q5","text":"Resolution for print (dpi):","options":["72","96","150","300"],"correctIndex":3}]',
'teach_omar', DATE_SUB(NOW(), INTERVAL 7 DAY))
ON DUPLICATE KEY UPDATE title=title;

/* QUIZZES - Challenge Daily (from database.sql) */
INSERT INTO quizzes (id, courseId, title, durationSec, difficulty, questions, createdBy, createdAt) VALUES
('challenge_daily', 'math101', 'Daily Math Challenge', 120, 'intermediate',
'[{"id":"c1_q1","text":"15 + 27 = ?","options":["40","41","42","43"],"correctIndex":2},{"id":"c1_q2","text":"100 - 37 = ?","options":["62","63","64","65"],"correctIndex":1},{"id":"c1_q3","text":"12 × 8 = ?","options":["84","92","96","104"],"correctIndex":2},{"id":"c1_q4","text":"144 / 12 = ?","options":["10","11","12","13"],"correctIndex":2},{"id":"c1_q5","text":"Solve: 2x + 5 = 15","options":["3","4","5","6"],"correctIndex":2},{"id":"c1_q6","text":"√64 = ?","options":["6","7","8","9"],"correctIndex":2},{"id":"c1_q7","text":"3² + 4² = ?","options":["20","23","25","27"],"correctIndex":2},{"id":"c1_q8","text":"50% of 200 = ?","options":["75","100","125","150"],"correctIndex":1},{"id":"c1_q9","text":"Prime number after 7:","options":["8","9","10","11"],"correctIndex":3},{"id":"c1_q10","text":"Area of 5×6 rectangle:","options":["11","22","30","36"],"correctIndex":2}]',
'teach_jane', DATE_SUB(NOW(), INTERVAL 15 DAY))
ON DUPLICATE KEY UPDATE title=title;

/* CHALLENGES */
INSERT INTO challenges (id, title, description, level, points, category, skillTags, prerequisiteLevel, createdBy, createdAt) VALUES
('ch_math_basic', 'Math Novice', 'Complete 3 math quizzes with at least 60% score', 1, 50, 'Mathematics', '["arithmetic", "basics"]', NULL, 'teach_jane', DATE_SUB(NOW(), INTERVAL 30 DAY)),
('ch_math_adept', 'Math Adept', 'Score 80%+ on any math quiz', 2, 100, 'Mathematics', '["algebra", "problem-solving"]', 1, 'teach_jane', DATE_SUB(NOW(), INTERVAL 28 DAY)),
('ch_math_master', 'Math Master', 'Score 100% on an advanced math quiz', 3, 250, 'Mathematics', '["mastery", "advanced"]', 2, 'teach_jane', DATE_SUB(NOW(), INTERVAL 25 DAY)),
('ch_sci_explorer', 'Science Explorer', 'Complete your first science quiz', 1, 50, 'Science', '["biology", "chemistry", "physics"]', NULL, 'teach_lee', DATE_SUB(NOW(), INTERVAL 30 DAY)),
('ch_sci_researcher', 'Science Researcher', 'Complete 5 science quizzes', 2, 150, 'Science', '["research", "experimentation"]', 1, 'teach_lee', DATE_SUB(NOW(), INTERVAL 26 DAY)),
('ch_lab_expert', 'Lab Expert', 'Score 90%+ on Chemistry Basics', 3, 200, 'Science', '["chemistry", "lab-skills"]', 2, 'teach_lee', DATE_SUB(NOW(), INTERVAL 22 DAY)),
('ch_code_beginner', 'Code Beginner', 'Complete Introduction to Programming quiz', 1, 75, 'Computer Science', '["coding", "basics"]', NULL, 'teach_kim', DATE_SUB(NOW(), INTERVAL 28 DAY)),
('ch_code_builder', 'Code Builder', 'Complete both Python and JavaScript quizzes', 2, 150, 'Computer Science', '["python", "javascript"]', 1, 'teach_kim', DATE_SUB(NOW(), INTERVAL 24 DAY)),
('ch_full_stack', 'Full Stack Ready', 'Score 85%+ on all CS quizzes', 3, 300, 'Computer Science', '["full-stack", "mastery"]', 2, 'teach_kim', DATE_SUB(NOW(), INTERVAL 20 DAY)),
('ch_wordsmith', 'Wordsmith', 'Complete Grammar Essentials quiz', 1, 50, 'English', '["grammar", "writing"]', NULL, 'teach_sarah', DATE_SUB(NOW(), INTERVAL 27 DAY)),
('ch_storyteller', 'Storyteller', 'Score 80%+ on Vocabulary Builder', 2, 125, 'English', '["vocabulary", "creativity"]', 1, 'teach_sarah', DATE_SUB(NOW(), INTERVAL 23 DAY)),
('ch_historian', 'History Buff', 'Complete any history quiz', 1, 50, 'History', '["world-history", "ancient"]', NULL, 'teach_mark', DATE_SUB(NOW(), INTERVAL 26 DAY)),
('ch_time_traveler', 'Time Traveler', 'Score 80%+ on Ancient Empires', 2, 150, 'History', '["ancient-history", "civilizations"]', 1, 'teach_mark', DATE_SUB(NOW(), INTERVAL 21 DAY)),
('ch_streak_3', '3-Day Streak', 'Complete quizzes 3 days in a row', 1, 100, 'Consistency', '["dedication", "streak"]', NULL, 'teach_jane', DATE_SUB(NOW(), INTERVAL 30 DAY)),
('ch_streak_7', 'Weekly Warrior', 'Complete quizzes 7 days in a row', 2, 250, 'Consistency', '["dedication", "streak", "weekly"]', 1, 'teach_jane', DATE_SUB(NOW(), INTERVAL 28 DAY)),
('ch_perfectionist', 'Perfectionist', 'Get 100% on any 3 quizzes', 3, 500, 'Achievement', '["mastery", "perfection"]', NULL, 'teach_jane', DATE_SUB(NOW(), INTERVAL 25 DAY))
ON DUPLICATE KEY UPDATE title=title;

/* REWARDS */
INSERT INTO rewards (id, name, category, costPoints, tierRequired, stock, createdAt) VALUES
('rw_avatar_gold', 'Golden Avatar Frame', 'Cosmetic', 100, 1, NULL, DATE_SUB(NOW(), INTERVAL 30 DAY)),
('rw_avatar_diamond', 'Diamond Avatar Frame', 'Cosmetic', 500, 3, 50, DATE_SUB(NOW(), INTERVAL 30 DAY)),
('rw_badge_scholar', 'Scholar Badge', 'Badge', 75, 1, NULL, DATE_SUB(NOW(), INTERVAL 28 DAY)),
('rw_badge_genius', 'Genius Badge', 'Badge', 300, 2, 100, DATE_SUB(NOW(), INTERVAL 28 DAY)),
('rw_badge_legend', 'Legend Badge', 'Badge', 1000, 3, 25, DATE_SUB(NOW(), INTERVAL 28 DAY)),
('rw_theme_dark', 'Dark Theme Unlock', 'Theme', 50, 1, NULL, DATE_SUB(NOW(), INTERVAL 26 DAY)),
('rw_theme_ocean', 'Ocean Theme', 'Theme', 150, 2, NULL, DATE_SUB(NOW(), INTERVAL 26 DAY)),
('rw_theme_sunset', 'Sunset Theme', 'Theme', 200, 2, NULL, DATE_SUB(NOW(), INTERVAL 26 DAY)),
('rw_cert_math', 'Math Completion Certificate', 'Certificate', 250, 2, NULL, DATE_SUB(NOW(), INTERVAL 24 DAY)),
('rw_cert_science', 'Science Completion Certificate', 'Certificate', 250, 2, NULL, DATE_SUB(NOW(), INTERVAL 24 DAY)),
('rw_cert_coding', 'Coding Completion Certificate', 'Certificate', 300, 2, NULL, DATE_SUB(NOW(), INTERVAL 24 DAY)),
('rw_voucher_5', '$5 Gift Voucher', 'Voucher', 500, 2, 20, DATE_SUB(NOW(), INTERVAL 22 DAY)),
('rw_voucher_10', '$10 Gift Voucher', 'Voucher', 900, 3, 10, DATE_SUB(NOW(), INTERVAL 22 DAY)),
('rw_merch_shirt', 'EduMind T-Shirt', 'Merchandise', 750, 3, 15, DATE_SUB(NOW(), INTERVAL 20 DAY)),
('rw_merch_cap', 'EduMind Cap', 'Merchandise', 400, 2, 30, DATE_SUB(NOW(), INTERVAL 20 DAY)),
('rw_extra_attempt', 'Extra Quiz Attempt', 'Power-up', 25, 1, NULL, DATE_SUB(NOW(), INTERVAL 18 DAY)),
('rw_hint_pack', 'Hint Pack (5 hints)', 'Power-up', 60, 1, NULL, DATE_SUB(NOW(), INTERVAL 18 DAY)),
('rw_time_boost', 'Time Boost (+30 sec)', 'Power-up', 40, 1, NULL, DATE_SUB(NOW(), INTERVAL 18 DAY))
ON DUPLICATE KEY UPDATE name=name;

/* Safety: ensure foreign key checks remain off before inserting scores */
SET FOREIGN_KEY_CHECKS = 0;

/* SUPERKID SCORES - Math */
INSERT INTO scores (id, userId, username, courseId, quizId, score, total, durationSec, attempt, type, timestamp) VALUES
('sc_sk_1', 'stu_superkid', 'superkid', 'math101', 'math101_quiz1', 5, 5, 42, 1, 'quiz', DATE_SUB(NOW(), INTERVAL 14 DAY)),
('sc_sk_2', 'stu_superkid', 'superkid', 'math101', 'math101_quiz2', 4, 5, 65, 1, 'quiz', DATE_SUB(NOW(), INTERVAL 12 DAY)),
('sc_sk_3', 'stu_superkid', 'superkid', 'math101', 'math101_quiz3', 5, 5, 88, 1, 'quiz', DATE_SUB(NOW(), INTERVAL 10 DAY)),
('sc_sk_4', 'stu_superkid', 'superkid', 'math101', 'challenge_daily', 9, 10, 115, 1, 'challenge', DATE_SUB(NOW(), INTERVAL 8 DAY)),
('sc_sk_5', 'stu_superkid', 'superkid', 'math201', 'math201_quiz1', 4, 5, 130, 1, 'quiz', DATE_SUB(NOW(), INTERVAL 5 DAY))
ON DUPLICATE KEY UPDATE score=score;

/* SUPERKID SCORES - Science */
INSERT INTO scores (id, userId, username, courseId, quizId, score, total, durationSec, attempt, type, timestamp) VALUES
('sc_sk_6', 'stu_superkid', 'superkid', 'sci101', 'sci101_quiz1', 5, 5, 48, 1, 'quiz', DATE_SUB(NOW(), INTERVAL 13 DAY)),
('sc_sk_7', 'stu_superkid', 'superkid', 'sci101', 'sci101_quiz2', 4, 5, 72, 1, 'quiz', DATE_SUB(NOW(), INTERVAL 11 DAY)),
('sc_sk_8', 'stu_superkid', 'superkid', 'sci101', 'sci101_quiz3', 5, 5, 95, 1, 'quiz', DATE_SUB(NOW(), INTERVAL 9 DAY)),
('sc_sk_9', 'stu_superkid', 'superkid', 'sci201', 'sci201_quiz1', 4, 5, 125, 1, 'quiz', DATE_SUB(NOW(), INTERVAL 4 DAY))
ON DUPLICATE KEY UPDATE score=score;

/* SUPERKID SCORES - CS */
INSERT INTO scores (id, userId, username, courseId, quizId, score, total, durationSec, attempt, type, timestamp) VALUES
('sc_sk_10', 'stu_superkid', 'superkid', 'cs101', 'cs101_quiz1', 5, 5, 55, 1, 'quiz', DATE_SUB(NOW(), INTERVAL 7 DAY)),
('sc_sk_11', 'stu_superkid', 'superkid', 'cs101', 'cs101_quiz2', 4, 5, 78, 1, 'quiz', DATE_SUB(NOW(), INTERVAL 6 DAY)),
('sc_sk_12', 'stu_superkid', 'superkid', 'cs101', 'cs101_quiz3', 5, 5, 102, 1, 'quiz', DATE_SUB(NOW(), INTERVAL 3 DAY))
ON DUPLICATE KEY UPDATE score=score;

/* SUPERKID SCORES - English */
INSERT INTO scores (id, userId, username, courseId, quizId, score, total, durationSec, attempt, type, timestamp) VALUES
('sc_sk_13', 'stu_superkid', 'superkid', 'eng101', 'eng101_quiz1', 4, 5, 62, 1, 'quiz', DATE_SUB(NOW(), INTERVAL 6 DAY)),
('sc_sk_14', 'stu_superkid', 'superkid', 'eng101', 'eng101_quiz2', 5, 5, 70, 1, 'quiz', DATE_SUB(NOW(), INTERVAL 2 DAY))
ON DUPLICATE KEY UPDATE score=score;

/* SUPERKID SCORES - History */
INSERT INTO scores (id, userId, username, courseId, quizId, score, total, durationSec, attempt, type, timestamp) VALUES
('sc_sk_15', 'stu_superkid', 'superkid', 'hist101', 'hist101_quiz1', 5, 5, 58, 1, 'quiz', DATE_SUB(NOW(), INTERVAL 5 DAY)),
('sc_sk_16', 'stu_superkid', 'superkid', 'hist101', 'hist101_quiz2', 4, 5, 98, 1, 'quiz', DATE_SUB(NOW(), INTERVAL 1 DAY))
ON DUPLICATE KEY UPDATE score=score;

/* OTHER STUDENTS SCORES */
INSERT INTO scores (id, userId, username, courseId, quizId, score, total, durationSec, attempt, type, timestamp) VALUES
('sc_emma_1', 'stu_emma', 'emma', 'math101', 'math101_quiz1', 4, 5, 55, 1, 'quiz', DATE_SUB(NOW(), INTERVAL 10 DAY)),
('sc_emma_2', 'stu_emma', 'emma', 'eng101', 'eng101_quiz1', 5, 5, 60, 1, 'quiz', DATE_SUB(NOW(), INTERVAL 8 DAY)),
('sc_liam_1', 'stu_liam', 'liam', 'cs101', 'cs101_quiz1', 5, 5, 45, 1, 'quiz', DATE_SUB(NOW(), INTERVAL 9 DAY)),
('sc_liam_2', 'stu_liam', 'liam', 'cs101', 'cs101_quiz2', 4, 5, 80, 1, 'quiz', DATE_SUB(NOW(), INTERVAL 7 DAY)),
('sc_sophia_1', 'stu_sophia', 'sophia', 'sci101', 'sci101_quiz1', 5, 5, 50, 1, 'quiz', DATE_SUB(NOW(), INTERVAL 11 DAY)),
('sc_sophia_2', 'stu_sophia', 'sophia', 'sci101', 'sci101_quiz2', 4, 5, 85, 1, 'quiz', DATE_SUB(NOW(), INTERVAL 6 DAY)),
('sc_noah_1', 'stu_noah', 'noah', 'hist101', 'hist101_quiz1', 3, 5, 70, 1, 'quiz', DATE_SUB(NOW(), INTERVAL 8 DAY)),
('sc_olivia_1', 'stu_olivia', 'olivia', 'math101', 'math101_quiz1', 5, 5, 48, 1, 'quiz', DATE_SUB(NOW(), INTERVAL 7 DAY)),
('sc_olivia_2', 'stu_olivia', 'olivia', 'math101', 'math101_quiz2', 4, 5, 75, 1, 'quiz', DATE_SUB(NOW(), INTERVAL 5 DAY)),
('sc_james_1', 'stu_james', 'james', 'cs101', 'cs101_quiz3', 4, 5, 95, 1, 'quiz', DATE_SUB(NOW(), INTERVAL 4 DAY)),
('sc_ava_1', 'stu_ava', 'ava', 'eng101', 'eng101_quiz1', 4, 5, 65, 1, 'quiz', DATE_SUB(NOW(), INTERVAL 3 DAY)),
('sc_ava_2', 'stu_ava', 'ava', 'eng101', 'eng101_quiz2', 5, 5, 72, 1, 'quiz', DATE_SUB(NOW(), INTERVAL 1 DAY))
ON DUPLICATE KEY UPDATE score=score;

/* EXTRA SCORES FOR NEW STUDENTS/COURSES */
INSERT INTO scores (id, userId, username, courseId, quizId, score, total, durationSec, attempt, type, timestamp) VALUES
('sc_isabella_1', 'stu_isabella', 'isabella', 'geo101', 'geo101_quiz1', 5, 5, 78, 1, 'quiz', DATE_SUB(NOW(), INTERVAL 4 DAY)),
('sc_mason_1', 'stu_mason', 'mason', 'art101', 'art101_quiz1', 4, 5, 82, 1, 'quiz', DATE_SUB(NOW(), INTERVAL 5 DAY)),
('sc_mia_1', 'stu_mia', 'mia', 'geo101', 'geo101_quiz1', 4, 5, 93, 1, 'quiz', DATE_SUB(NOW(), INTERVAL 3 DAY)),
('sc_ethan_1', 'stu_ethan', 'ethan', 'art201', 'art201_quiz1', 5, 5, 110, 1, 'quiz', DATE_SUB(NOW(), INTERVAL 2 DAY)),
('sc_charlotte_1', 'stu_charlotte', 'charlotte', 'geo201', 'geo201_quiz1', 3, 5, 120, 1, 'quiz', DATE_SUB(NOW(), INTERVAL 2 DAY)),
('sc_ben_1', 'stu_ben', 'ben', 'art101', 'art101_quiz1', 5, 5, 70, 1, 'quiz', DATE_SUB(NOW(), INTERVAL 1 DAY)),
('sc_zara_1', 'stu_zara', 'zara', 'geo201', 'geo201_quiz1', 4, 5, 130, 1, 'quiz', DATE_SUB(NOW(), INTERVAL 1 DAY))
ON DUPLICATE KEY UPDATE score=score;

/* CHALLENGE COMPLETIONS FOR SUPERKID */
INSERT INTO challenge_completions (id, challengeId, studentId, rating, completedAt) VALUES
('cc_sk_1', 'ch_math_basic', 'stu_superkid', 5, DATE_SUB(NOW(), INTERVAL 10 DAY)),
('cc_sk_2', 'ch_math_adept', 'stu_superkid', 5, DATE_SUB(NOW(), INTERVAL 8 DAY)),
('cc_sk_3', 'ch_sci_explorer', 'stu_superkid', 4, DATE_SUB(NOW(), INTERVAL 12 DAY)),
('cc_sk_4', 'ch_sci_researcher', 'stu_superkid', 5, DATE_SUB(NOW(), INTERVAL 6 DAY)),
('cc_sk_5', 'ch_code_beginner', 'stu_superkid', 5, DATE_SUB(NOW(), INTERVAL 7 DAY)),
('cc_sk_6', 'ch_code_builder', 'stu_superkid', 4, DATE_SUB(NOW(), INTERVAL 3 DAY)),
('cc_sk_7', 'ch_wordsmith', 'stu_superkid', 4, DATE_SUB(NOW(), INTERVAL 5 DAY)),
('cc_sk_8', 'ch_historian', 'stu_superkid', 5, DATE_SUB(NOW(), INTERVAL 4 DAY)),
('cc_sk_9', 'ch_streak_3', 'stu_superkid', 5, DATE_SUB(NOW(), INTERVAL 9 DAY)),
('cc_sk_10', 'ch_streak_7', 'stu_superkid', 5, DATE_SUB(NOW(), INTERVAL 2 DAY))
ON DUPLICATE KEY UPDATE rating=rating;

/* POINTS LEDGER - Challenge completions */
INSERT INTO points_ledger (id, studentId, delta, reason, refType, refId, createdAt) VALUES
('pl_sk_1', 'stu_superkid', 50, 'Completed: Math Novice challenge', 'challenge', 'ch_math_basic', DATE_SUB(NOW(), INTERVAL 10 DAY)),
('pl_sk_2', 'stu_superkid', 100, 'Completed: Math Adept challenge', 'challenge', 'ch_math_adept', DATE_SUB(NOW(), INTERVAL 8 DAY)),
('pl_sk_3', 'stu_superkid', 50, 'Completed: Science Explorer challenge', 'challenge', 'ch_sci_explorer', DATE_SUB(NOW(), INTERVAL 12 DAY)),
('pl_sk_4', 'stu_superkid', 150, 'Completed: Science Researcher challenge', 'challenge', 'ch_sci_researcher', DATE_SUB(NOW(), INTERVAL 6 DAY)),
('pl_sk_5', 'stu_superkid', 75, 'Completed: Code Beginner challenge', 'challenge', 'ch_code_beginner', DATE_SUB(NOW(), INTERVAL 7 DAY)),
('pl_sk_6', 'stu_superkid', 150, 'Completed: Code Builder challenge', 'challenge', 'ch_code_builder', DATE_SUB(NOW(), INTERVAL 3 DAY)),
('pl_sk_7', 'stu_superkid', 50, 'Completed: Wordsmith challenge', 'challenge', 'ch_wordsmith', DATE_SUB(NOW(), INTERVAL 5 DAY)),
('pl_sk_8', 'stu_superkid', 50, 'Completed: History Buff challenge', 'challenge', 'ch_historian', DATE_SUB(NOW(), INTERVAL 4 DAY)),
('pl_sk_9', 'stu_superkid', 100, 'Completed: 3-Day Streak challenge', 'challenge', 'ch_streak_3', DATE_SUB(NOW(), INTERVAL 9 DAY)),
('pl_sk_10', 'stu_superkid', 250, 'Completed: Weekly Warrior challenge', 'challenge', 'ch_streak_7', DATE_SUB(NOW(), INTERVAL 2 DAY))
ON DUPLICATE KEY UPDATE delta=delta;

/* POINTS LEDGER - Perfect quiz scores */
INSERT INTO points_ledger (id, studentId, delta, reason, refType, refId, createdAt) VALUES
('pl_sk_11', 'stu_superkid', 25, 'Perfect score: Math Basics Quiz 1', 'quiz', 'math101_quiz1', DATE_SUB(NOW(), INTERVAL 14 DAY)),
('pl_sk_12', 'stu_superkid', 25, 'Perfect score: Fractions Challenge', 'quiz', 'math101_quiz3', DATE_SUB(NOW(), INTERVAL 10 DAY)),
('pl_sk_13', 'stu_superkid', 25, 'Perfect score: Science Basics Quiz 1', 'quiz', 'sci101_quiz1', DATE_SUB(NOW(), INTERVAL 13 DAY)),
('pl_sk_14', 'stu_superkid', 25, 'Perfect score: Chemistry Basics', 'quiz', 'sci101_quiz3', DATE_SUB(NOW(), INTERVAL 9 DAY)),
('pl_sk_15', 'stu_superkid', 25, 'Perfect score: Programming Basics', 'quiz', 'cs101_quiz1', DATE_SUB(NOW(), INTERVAL 7 DAY)),
('pl_sk_16', 'stu_superkid', 25, 'Perfect score: JavaScript Intro', 'quiz', 'cs101_quiz3', DATE_SUB(NOW(), INTERVAL 3 DAY)),
('pl_sk_17', 'stu_superkid', 25, 'Perfect score: Vocabulary Builder', 'quiz', 'eng101_quiz2', DATE_SUB(NOW(), INTERVAL 2 DAY)),
('pl_sk_18', 'stu_superkid', 25, 'Perfect score: World History Basics', 'quiz', 'hist101_quiz1', DATE_SUB(NOW(), INTERVAL 5 DAY))
ON DUPLICATE KEY UPDATE delta=delta;

/* POINTS LEDGER - Reward redemptions */
INSERT INTO points_ledger (id, studentId, delta, reason, refType, refId, createdAt) VALUES
('pl_sk_19', 'stu_superkid', -100, 'Redeemed: Golden Avatar Frame', 'reward', 'rw_avatar_gold', DATE_SUB(NOW(), INTERVAL 6 DAY)),
('pl_sk_20', 'stu_superkid', -50, 'Redeemed: Dark Theme Unlock', 'reward', 'rw_theme_dark', DATE_SUB(NOW(), INTERVAL 4 DAY))
ON DUPLICATE KEY UPDATE delta=delta;

/* REWARD REDEMPTIONS FOR SUPERKID */
INSERT INTO reward_redemptions (id, rewardId, studentId, status, requestedBalance, shortBy, note, requestedAt) VALUES
('rr_sk_1', 'rw_avatar_gold', 'stu_superkid', 'redeemed', 650, 0, 'First reward!', DATE_SUB(NOW(), INTERVAL 6 DAY)),
('rr_sk_2', 'rw_theme_dark', 'stu_superkid', 'redeemed', 600, 0, 'Love dark mode!', DATE_SUB(NOW(), INTERVAL 4 DAY)),
('rr_sk_3', 'rw_badge_scholar', 'stu_superkid', 'pending', 1075, 0, 'Want this badge!', DATE_SUB(NOW(), INTERVAL 1 DAY))
ON DUPLICATE KEY UPDATE status=status;

/* MORE EVENTS */
INSERT INTO events (id, title, date, startTime, endTime, course, type, location, maxParticipants, nbrParticipants, description, teacherId, createdAt) VALUES
('evt_4', 'Python Workshop', DATE_ADD(CURDATE(), INTERVAL 7 DAY), '13:00:00', '15:00:00', 'Introduction to Programming', 'Lecture', 'Computer Lab A', 25, 12, 'Hands-on Python coding session for beginners', 'teach_kim', NOW()),
('evt_5', 'Essay Writing Tips', DATE_ADD(CURDATE(), INTERVAL 4 DAY), '11:00:00', '12:30:00', 'Creative Writing', 'Lecture', 'Room 205', 30, 8, 'Learn techniques for compelling essays', 'teach_sarah', NOW()),
('evt_6', 'History Documentary Screening', DATE_ADD(CURDATE(), INTERVAL 6 DAY), '14:00:00', '16:00:00', 'World History', 'Other', 'Auditorium', 100, 45, 'Screening of Ancient Rome documentary', 'teach_mark', NOW()),
('evt_7', 'Math Olympiad Prep', DATE_ADD(CURDATE(), INTERVAL 2 DAY), '15:00:00', '17:00:00', 'Algebra II', 'Lecture', 'Room 101', 20, 15, 'Preparation for upcoming Math Olympiad', 'teach_jane', NOW()),
('evt_8', 'Science Fair Info Session', DATE_ADD(CURDATE(), INTERVAL 8 DAY), '09:00:00', '10:00:00', 'Science Basics', 'Webinar', 'Online', 200, 67, 'Information about the annual science fair', 'teach_lee', NOW())
ON DUPLICATE KEY UPDATE title=title;

/* MORE LOGS */
INSERT INTO logs (id, level, message, ts) VALUES
('log_3', 'info', 'Student superkid completed Weekly Warrior challenge', DATE_SUB(NOW(), INTERVAL 2 DAY)),
('log_4', 'info', 'New course submitted: Web Development', DATE_SUB(NOW(), INTERVAL 10 DAY)),
('log_5', 'info', 'New course submitted: Public Speaking', DATE_SUB(NOW(), INTERVAL 8 DAY)),
('log_6', 'warn', 'High traffic detected on quiz server', DATE_SUB(NOW(), INTERVAL 5 DAY)),
('log_7', 'info', 'Student emma achieved perfect score on Grammar Essentials', DATE_SUB(NOW(), INTERVAL 8 DAY)),
('log_8', 'info', 'New course submitted: Ancient Civilizations', DATE_SUB(NOW(), INTERVAL 5 DAY)),
('log_9', 'error', 'Database connection timeout (recovered)', DATE_SUB(NOW(), INTERVAL 3 DAY)),
('log_10', 'info', 'Backup completed successfully', DATE_SUB(NOW(), INTERVAL 1 DAY))
ON DUPLICATE KEY UPDATE message=message;

/* QUIZ REPORTS */
INSERT INTO quiz_reports (id, quizId, questionId, reportedBy, reportType, description, status, createdAt) VALUES
('qr_1', 'math101_quiz2', 'm2_q3', 'stu_emma', 'typo', 'The question shows 9 squared but the answer options are confusing', 'pending', DATE_SUB(NOW(), INTERVAL 4 DAY)),
('qr_2', 'sci101_quiz3', 'ch_q5', 'stu_liam', 'incorrect_answer', 'Noble gases are in Group 18, not Group 8 - please verify', 'reviewed', DATE_SUB(NOW(), INTERVAL 6 DAY)),
('qr_3', 'cs101_quiz1', 'cs1_q2', 'stu_sophia', 'other', 'HTTP could be considered a language in some contexts', 'dismissed', DATE_SUB(NOW(), INTERVAL 5 DAY)),
('qr_4', 'hist101_quiz1', 'h1_q2', 'stu_noah', 'incorrect_answer', 'Columbus didnt really discover America, Vikings were first', 'pending', DATE_SUB(NOW(), INTERVAL 2 DAY))
ON DUPLICATE KEY UPDATE description=description;

/* RECOMMENDATIONS FOR SUPERKID */
INSERT INTO recommendations (id, userId, courseId, reason, createdAt) VALUES
('rec_sk_1', 'stu_superkid', 'math201', 'Based on your excellent performance in Math Basics, try Algebra II!', DATE_SUB(NOW(), INTERVAL 7 DAY)),
('rec_sk_2', 'stu_superkid', 'cs201', 'You aced the CS quizzes! Web Development is the natural next step.', DATE_SUB(NOW(), INTERVAL 3 DAY)),
('rec_sk_3', 'stu_superkid', 'sci201', 'Your science scores are impressive. Physics Fundamentals awaits!', DATE_SUB(NOW(), INTERVAL 5 DAY))
ON DUPLICATE KEY UPDATE reason=reason;

/* Re-enable foreign key checks */
SET FOREIGN_KEY_CHECKS = 1;
