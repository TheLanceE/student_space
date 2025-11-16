(function(){
  if(!window.Database){
    console.warn('Database module missing: teacher data helpers disabled.');
    return;
  }

  const TData = {
    get courses(){ return Database.table('courses'); },
    get quizzes(){ return Database.table('quizzes'); },
    get students(){ return Database.table('students'); },
    get scores(){ return Database.table('scores'); },
    getTeacherCourses(teacherId){ return this.courses.filter(c => c.teacherId === teacherId); },
    getTeacherQuizzes(teacherId){ return this.quizzes.filter(q => q.createdBy === teacherId); },
    getScoresForTeacher(teacherId){
      const courseIds = new Set(this.getTeacherCourses(teacherId).map(c => c.id));
      return this.scores.filter(score => courseIds.has(score.courseId));
    },
    getStudentsForTeacher(teacherId){
      const ids = new Set(this.getScoresForTeacher(teacherId).map(score => score.userId));
      return this.students.filter(student => ids.has(student.id));
    },
    saveCourse(course){ Database.insert('courses', course); },
    removeCourse(courseId){
      Database.deleteWhere('courses', c => c.id === courseId);
      Database.deleteWhere('quizzes', q => q.courseId === courseId);
    },
    saveQuiz(quiz){ Database.insert('quizzes', quiz); },
    overwriteQuiz(id, quiz){
      const existing = Database.findById('quizzes', id);
      if(existing){
        Database.updateWhere('quizzes', q => q.id === id, () => quiz);
      } else {
        Database.insert('quizzes', quiz);
      }
    },
    removeQuiz(quizId){ Database.deleteWhere('quizzes', q => q.id === quizId); }
  };

  window.TData = TData;
})();