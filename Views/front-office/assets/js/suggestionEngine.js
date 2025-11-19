(function(){
  function daysBetween(a, b){
    const ms = Math.abs(new Date(a).getTime() - new Date(b).getTime());
    return Math.floor(ms / (1000*60*60*24));
  }

  function lastLoginDays(user){
    const last = user?.lastLoginAt || user?.createdAt || new Date().toISOString();
    return daysBetween(new Date(), last);
  }

  function lastNScores(scores, n=3){
    return [...scores].sort((a,b)=>new Date(b.timestamp)-new Date(a.timestamp)).slice(0,n);
  }

  function averageScore(items){
    if(!items.length) return null;
    const pct = items.map(s=> (s.score / s.total) * 100);
    return Math.round(pct.reduce((a,b)=>a+b,0)/pct.length);
  }

  const SuggestionEngine = {
    generate(currentUser){
      if(!currentUser){ return []; }
      const suggestions = [];
      const scores = (window.Data && typeof Data.getScoresForUser === 'function') ? Data.getScoresForUser(currentUser.id) : [];
      const recent = lastNScores(scores, 3);
      const avg = averageScore(recent);
      const inactivityDays = lastLoginDays(currentUser);

      if(inactivityDays >= 7){ suggestions.push('You haven\'t logged in for 7 days — complete a quick refresher quiz.'); }

      if(avg === null){
        suggestions.push('Start with Math Basics - Quiz 1 to begin your journey.');
      } else if(avg < 60){
        suggestions.push('Review previous lesson and retake an easy quiz.');
        suggestions.push('Focus on topics where you scored below 60%.');
      } else if(avg < 80){
        suggestions.push('Try intermediate difficulty next to build mastery.');
      } else {
        suggestions.push('Great job! You can unlock the next chapter.');
      }

      // Difficulty progression based on last attempt for each course
      const last = scores.sort((a,b)=>new Date(b.timestamp)-new Date(a.timestamp))[0];
      if(last){
        const courseQuizzes = Data.getQuizzesForCourse(last.courseId);
        const nextQuiz = courseQuizzes.find(q=>q.id !== last.quizId) || courseQuizzes[0];
        if(nextQuiz){ suggestions.push(`Next recommended quiz: ${nextQuiz.title}`); }
      }

      const finalSuggestions = suggestions.slice(0,4);
      if(window.Database){
        Database.deleteWhere('recommendations', rec => rec.userId === currentUser.id && rec.source === 'engine');
        finalSuggestions.forEach(text => {
          Database.insert('recommendations', {
            id: Database.nextId('rec'),
            userId: currentUser.id,
            text,
            source: 'engine',
            createdAt: new Date().toISOString()
          });
        });
      }
      return finalSuggestions;
    }
  };

  window.SuggestionEngine = SuggestionEngine;
})();