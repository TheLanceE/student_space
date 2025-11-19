(function(){
  const DB_KEY = 'EDUMIND_DB_v1';
  const DB_VERSION = 1;
  const nowISO = () => new Date().toISOString();
  const daysAgo = (d) => new Date(Date.now() - d * 86400000).toISOString();
  const clone = (obj) => JSON.parse(JSON.stringify(obj));
  const makeId = (prefix = 'id') => {
    const raw = (window.crypto && window.crypto.randomUUID) ? window.crypto.randomUUID() : `${Date.now().toString(36)}-${Math.random().toString(36).slice(2, 8)}`;
    return `${prefix}_${raw.replace(/[^a-zA-Z0-9]/g, '')}`;
  };

  const defaultStudents = [
    { id: 'stu_alice', username: 'alice', fullName: 'Alice Stone', email: 'alice@edumind.app', mobile: '+123456789', address: '123 Main St, City', gradeLevel: 'Grade 8', createdAt: daysAgo(20), lastLoginAt: daysAgo(1) },
    { id: 'stu_bob', username: 'bob', fullName: 'Bob Carter', email: 'bob@edumind.app', mobile: '+987654321', address: '456 Oak Ave, Town', gradeLevel: 'Grade 9', createdAt: daysAgo(28), lastLoginAt: daysAgo(3) }
  ];

  const defaultTeachers = [
    { id: 'teach_jane', username: 'teacher_jane', fullName: 'Jane Miller', email: 'jane@edumind.app', mobile: '+555100200', address: '789 Elm St, District', specialty: 'Mathematics', nationalId: 'NAT-001-JM', createdAt: daysAgo(60), lastLoginAt: daysAgo(2) },
    { id: 'teach_lee', username: 'teacher_lee', fullName: 'Lee Sanders', email: 'lee@edumind.app', mobile: '+555300400', address: '321 Pine Rd, Campus', specialty: 'Science', nationalId: 'NAT-002-LS', createdAt: daysAgo(55), lastLoginAt: daysAgo(4) }
  ];

  const defaultCourses = [
    { id: 'math101', title: 'Math Basics', description: 'Numbers, operations, and simple algebra.', teacherId: 'teach_jane' },
    { id: 'sci101', title: 'Science Basics', description: 'Intro to physics, chemistry, and biology.', teacherId: 'teach_lee' }
  ];

  const defaultQuizzes = [
    {
      id: 'math101_quiz1', courseId: 'math101', title: 'Math Basics · Quiz 1', durationSec: 60, difficulty: 'beginner', createdBy: 'teach_jane',
      questions: [
        { id: 'm1_q1', text: '2 + 2 = ?', options: ['3', '4', '5', '6'], correctIndex: 1 },
        { id: 'm1_q2', text: '5 - 3 = ?', options: ['1', '2', '3', '4'], correctIndex: 1 },
        { id: 'm1_q3', text: '10 / 2 = ?', options: ['2', '4', '5', '10'], correctIndex: 2 },
        { id: 'm1_q4', text: '3 × 3 = ?', options: ['6', '7', '8', '9'], correctIndex: 3 },
        { id: 'm1_q5', text: 'Solve for x: x + 1 = 4', options: ['1', '2', '3', '4'], correctIndex: 2 }
      ]
    },
    {
      id: 'sci101_quiz1', courseId: 'sci101', title: 'Science Basics · Quiz 1', durationSec: 60, difficulty: 'beginner', createdBy: 'teach_lee',
      questions: [
        { id: 's1_q1', text: 'Water boils at what °C?', options: ['50', '80', '100', '120'], correctIndex: 2 },
        { id: 's1_q2', text: 'What gas do plants produce?', options: ['CO₂', 'O₂', 'N₂', 'CH₄'], correctIndex: 1 },
        { id: 's1_q3', text: 'Earth is the ___ planet from the Sun.', options: ['2nd', '3rd', '4th', '5th'], correctIndex: 1 },
        { id: 's1_q4', text: 'Basic unit of life is the:', options: ['Atom', 'Molecule', 'Cell', 'Organ'], correctIndex: 2 },
        { id: 's1_q5', text: 'H₂O is:', options: ['Oxygen', 'Hydrogen', 'Water', 'Helium'], correctIndex: 2 }
      ]
    }
  ];

  const defaultScores = [
    { id: 'sc_1', userId: 'stu_alice', username: 'alice', courseId: 'math101', quizId: 'math101_quiz1', score: 4, total: 5, durationSec: 48, attempt: 1, timestamp: daysAgo(2), type: 'quiz' },
    { id: 'sc_2', userId: 'stu_bob', username: 'bob', courseId: 'sci101', quizId: 'sci101_quiz1', score: 3, total: 5, durationSec: 52, attempt: 1, timestamp: daysAgo(4), type: 'quiz' },
    { id: 'sc_3', userId: 'stu_alice', username: 'alice', courseId: 'math101', quizId: 'challenge_daily', score: 8, total: 10, durationSec: 120, attempt: 1, timestamp: daysAgo(1), type: 'challenge' }
  ];

  const defaultLogs = [
    { id: 'log_1', level: 'info', message: 'System initialized', ts: daysAgo(7) },
    { id: 'log_2', level: 'warn', message: 'Pending approvals detected', ts: daysAgo(2) }
  ];

  const futureDate = (days) => {
    const d = new Date();
    d.setDate(d.getDate() + days);
    return d.toISOString().split('T')[0];
  };

  const defaultEvents = [
    { id: 'evt_1', title: 'Math Review Session', date: futureDate(3), startTime: '14:00:00', endTime: '15:30:00', course: 'Math Basics', type: 'Lecture', location: 'Room 101', maxParticipants: 30, description: 'Review of algebra and equations before midterm exam', teacherId: 'teach_jane', createdAt: nowISO() },
    { id: 'evt_2', title: 'Science Lab: Chemical Reactions', date: futureDate(5), startTime: '10:00:00', endTime: '12:00:00', course: 'Science Basics', type: 'Lecture', location: 'Lab 2B', maxParticipants: 20, description: 'Hands-on experiments with chemical reactions', teacherId: 'teach_lee', createdAt: nowISO() },
    { id: 'evt_3', title: 'Weekly Quiz Challenge', date: futureDate(1), startTime: '16:00:00', endTime: '17:00:00', course: 'Math Basics', type: 'Quiz', location: '', maxParticipants: 50, description: 'Weekly competitive quiz for all students', teacherId: 'teach_jane', createdAt: nowISO() }
  ];

  const defaultDb = {
    meta: { version: DB_VERSION, seededAt: nowISO() },
    admins: [{ id: 'admin_root', username: 'admin', name: 'Admin', createdAt: daysAgo(90), lastLoginAt: daysAgo(1) }],
    students: defaultStudents,
    teachers: defaultTeachers,
    courses: defaultCourses,
    quizzes: defaultQuizzes,
    scores: defaultScores,
    events: defaultEvents,
    recommendations: [],
    logs: defaultLogs,
    quizReports: []
  };

  function readRaw(){
    let data = null;
    try {
      data = JSON.parse(localStorage.getItem(DB_KEY));
    } catch {
      data = null;
    }
    if(!data){
      data = clone(defaultDb);
      writeRaw(data);
      return data;
    }
    let mutated = false;
    if(!data.meta){ data.meta = {}; mutated = true; }
    if(data.meta.version !== DB_VERSION){ data.meta.version = DB_VERSION; mutated = true; }
    for(const key of Object.keys(defaultDb)){
      if(key === 'meta') continue;
      if(!Array.isArray(data[key])){
        data[key] = clone(defaultDb[key]);
        mutated = true;
      }
    }
    if(mutated){ writeRaw(data); }
    return data;
  }

  function writeRaw(db){
    localStorage.setItem(DB_KEY, JSON.stringify(db));
  }

  function withDb(callback){
    const db = readRaw();
    const result = callback(db);
    writeRaw(db);
    return result;
  }

  const Database = {
    version: DB_VERSION,
    key: DB_KEY,
    ensure(){ readRaw(); return this; },
    snapshot(){ return clone(readRaw()); },
    table(name){ const db = readRaw(); return clone(db[name] || []); },
    findById(name, id){ return this.table(name).find(row => row.id === id) || null; },
    insert(name, row){
      return withDb(db => {
        db[name] = db[name] || [];
        db[name].push(row);
        return row;
      });
    },
    replace(name, rows){
      withDb(db => { db[name] = Array.isArray(rows) ? rows : []; });
    },
    updateWhere(name, predicate, updater){
      return withDb(db => {
        db[name] = (db[name] || []).map(item => predicate(item) ? updater(clone(item)) : item);
      });
    },
    deleteWhere(name, predicate){
      return withDb(db => {
        db[name] = (db[name] || []).filter(item => !predicate(item));
      });
    },
    nextId(prefix){ return makeId(prefix); }
  };

  Database.ensure();
  window.Database = Database;
})();
