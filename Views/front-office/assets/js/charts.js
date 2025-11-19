(function(){
  function groupByCourse(scores){
    const map = {};
    for(const s of scores){
      map[s.courseId] = map[s.courseId] || [];
      map[s.courseId].push(s);
    }
    return map;
  }

  function avg(arr){ return Math.round(arr.reduce((a,b)=>a+b,0)/arr.length); }

  const Charts = {
    renderProgress(canvasId, scores){
      const ctx = document.getElementById(canvasId);
      if(!ctx) return;
      const byCourse = groupByCourse(scores);
      const labels = Object.keys(byCourse);
      const data = labels.map(cid => avg(byCourse[cid].map(s=> Math.round((s.score/s.total)*100))));

      new Chart(ctx, {
        type: 'bar',
        data: { labels, datasets: [{ label: 'Avg Score %', data, backgroundColor: '#4f46e5' }] },
        options: {
          responsive: true,
          scales: {
            y: { beginAtZero: true, max: 100 }
          },
          plugins: { legend: { display: false } }
        }
      });
    },
    renderHistory(canvasId, scores){
      const ctx = document.getElementById(canvasId);
      if(!ctx) return;
      const sorted = [...scores].sort((a,b)=>new Date(a.timestamp)-new Date(b.timestamp));
      const labels = sorted.map(s=> new Date(s.timestamp).toLocaleDateString());
      const data = sorted.map(s=> Math.round((s.score/s.total)*100));
      new Chart(ctx, {
        type: 'line',
        data: { labels, datasets: [{ label: 'Score %', data, borderColor: '#22c55e', fill: false }] },
        options: {
          responsive: true,
          scales: { y: { beginAtZero: true, max: 100 } },
          plugins: { legend: { display: false } }
        }
      });
    }
  };

  window.Charts = Charts;
})();