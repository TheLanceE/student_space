(function(){
  if(!window.Database){
    console.warn('Database module missing: admin data helpers disabled.');
    return;
  }

  const asUser = (record, role) => ({
    id: record.id,
    username: record.username,
    role,
    fullName: record.fullName || record.name || record.username,
    email: record.email || ''
  });

  const AData = {
    get admins(){ return Database.table('admins'); },
    get students(){ return Database.table('students'); },
    get teachers(){ return Database.table('teachers'); },
    get users(){
      const students = this.students.map(s => asUser(s, 'student'));
      const teachers = this.teachers.map(t => asUser(t, 'teacher'));
      const admins = this.admins.map(a => asUser(a, 'admin'));
      return [...students, ...teachers, ...admins];
    },
    get courses(){ return Database.table('courses'); },
    get quizzes(){ return Database.table('quizzes'); },
    get scores(){ return Database.table('scores'); },
    get events(){ return Database.table('events'); },
    get logs(){ return Database.table('logs'); },
    get recommendations(){ return Database.table('recommendations'); },
    nextId(prefix){ return Database.nextId(prefix); },
    saveAdmin(record){ Database.insert('admins', record); },
    updateAdmin(id, updater){ Database.updateWhere('admins', entry => entry.id === id, updater); },
    removeAdmin(id){ Database.deleteWhere('admins', entry => entry.id === id); },
    saveStudent(record){ Database.insert('students', record); },
    saveTeacher(record){ Database.insert('teachers', record); },
    updateStudent(id, updater){ Database.updateWhere('students', entry => entry.id === id, updater); },
    updateTeacher(id, updater){ Database.updateWhere('teachers', entry => entry.id === id, updater); },
    removeStudent(id){ Database.deleteWhere('students', entry => entry.id === id); },
    removeTeacher(id){ Database.deleteWhere('teachers', entry => entry.id === id); },
    saveCourse(course){ Database.insert('courses', course); },
    updateCourse(id, updater){ Database.updateWhere('courses', entry => entry.id === id, updater); },
    removeCourse(id){
      Database.deleteWhere('courses', entry => entry.id === id);
      Database.deleteWhere('quizzes', quiz => quiz.courseId === id);
    },
    removeQuiz(id){ Database.deleteWhere('quizzes', q => q.id === id); },
    removeEvent(eventId){ Database.deleteWhere('events', e => e.id === eventId); },
    appendLog(entry){ Database.insert('logs', entry); }
  };

  window.AData = AData;
})();