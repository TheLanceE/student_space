(function(){
  if(!window.Database){
    console.warn('Database module missing. Data helpers disabled.');
    return;
  }

  const Data = {
    get courses(){ return Database.table('courses'); },
    get quizzes(){ return Database.table('quizzes'); },
    get scores(){ return Database.table('scores'); },
    get recommendations(){ return Database.table('recommendations'); },
    getQuizById(id){ return this.quizzes.find(q => q.id === id) || null; },
    getQuizzesForCourse(courseId){ return this.quizzes.filter(q => q.courseId === courseId); },
    getQuizzesForTeacher(teacherId){ return this.quizzes.filter(q => q.createdBy === teacherId); },
    getScoresForUser(userId){ return this.scores.filter(s => s.userId === userId); },
    saveQuiz(quiz){ return Database.insert('quizzes', quiz); },
    deleteQuiz(quizId){ return Database.deleteWhere('quizzes', q => q.id === quizId); },
    saveScore(score){ return Database.insert('scores', score); },
    saveRecommendation(rec){ return Database.insert('recommendations', rec); }
  };

  window.Data = Data;
})();