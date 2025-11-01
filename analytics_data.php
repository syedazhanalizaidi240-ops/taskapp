<?php
include 'db.php';
header('Content-Type: application/json');
$status = $conn->query("SELECT status,COUNT(*) as c FROM tasks GROUP BY status");
$slabels=[];$svals=[];
while($r=$status->fetch_assoc()){ $slabels[]=$r['status']; $svals[]=(int)$r['c']; }
$pr = $conn->query("SELECT priority,COUNT(*) as c FROM tasks GROUP BY priority");
$pl=[];$pv=[];
while($r=$pr->fetch_assoc()){ $pl[]=$r['priority']; $pv[]=(int)$r['c']; }
echo json_encode(['status'=>['labels'=>$slabels,'values'=>$svals],'priority'=>['labels'=>$pl,'values'=>$pv]]);
?>