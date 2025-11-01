<?php
include 'db.php';
if (!isset($_SESSION['user'])) header("Location: login.php");
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Analytics</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>body{font-family:Arial;background:#f4f6f8;margin:0}nav{background:#222;width:220px;height:100vh;position:fixed;left:0;top:0;padding-top:60px}nav a{display:block;padding:12px;color:#ccc;text-decoration:none}.main{margin-left:240px;padding:20px}.card{background:#fff;padding:16px;border-radius:8px;margin-bottom:12px}</style>
</head><body>
<nav><a href="index.php">Dashboard</a><a href="projects.php">Projects</a></nav>
<div class="main">
  <div class="card"><h2>Task Status Distribution</h2><canvas id="statusChart" width="600" height="250"></canvas></div>
  <div class="card"><h2>Tasks by Priority</h2><canvas id="priorityChart" width="600" height="250"></canvas></div>
</div>
<script>
function ajaxGet(url,cb){const x=new XMLHttpRequest();x.open('GET',url);x.onload=()=>cb(JSON.parse(x.responseText));x.send();}
ajaxGet('analytics_data.php',function(data){
  const ctx1 = document.getElementById('statusChart').getContext('2d');
  new Chart(ctx1,{type:'pie',data:{labels:data.status.labels,datasets:[{data:data.status.values}]}});

  const ctx2 = document.getElementById('priorityChart').getContext('2d');
  new Chart(ctx2,{type:'bar',data:{labels:data.priority.labels,datasets:[{label:'Tasks',data:data.priority.values}]},options:{scales:{y:{beginAtZero:true}}}});
});
</script>
</body></html>
