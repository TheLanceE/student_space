(function(){
  function avg(arr){ return arr.length ? Math.round(arr.reduce((a,b)=>a+b,0)/arr.length) : 0; }
  const TCharts = {
    renderCourseAverages(canvasId, scores){
      const ctx = document.getElementById(canvasId); if(!ctx) return;
      const byCourse = {};
      scores.forEach(s=>{
        byCourse[s.courseId] = byCourse[s.courseId] || [];
        byCourse[s.courseId].push(Math.round((s.score/s.total)*100));
      });
      const labels = Object.keys(byCourse);
      const data = labels.map(k=> avg(byCourse[k]));
      new Chart(ctx, { type: 'bar', data: { labels, datasets: [{ label: 'Avg %', data, backgroundColor: '#0ea5e9' }] }, options: { scales: { y: { beginAtZero:true, max:100 } }, plugins:{ legend:{ display:false } } } });
    },
    renderAttemptsOverTime(canvasId, scores){
      const ctx = document.getElementById(canvasId); if(!ctx) return;
      const daily = {};
      scores.forEach(s=>{ const d = new Date(s.timestamp).toLocaleDateString(); daily[d] = (daily[d]||0)+1; });
      const labels = Object.keys(daily).sort((a,b)=> new Date(a)-new Date(b));
      const data = labels.map(l=> daily[l]);
      new Chart(ctx, { type: 'line', data: { labels, datasets: [{ label: 'Attempts', data, borderColor:'#22c55e', fill:false }] }, options: { plugins:{ legend:{ display:false } } } });
    }
  };
  window.TCharts = TCharts;
})();