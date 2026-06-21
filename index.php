<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>LuckyBet</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="landing">
  <div class="land-title">LuckyBet</div>
  <div class="land-sub">Gioca, punta, vinci &mdash; senza rischi reali</div>

  <div class="game-cards">
    <div class="game-card" onclick="window.location='superenalotto.php'">
      <img src="./assets/img/superenalotto.jpg" alt="SuperEnalotto">
      <div class="game-card-name">SuperEnalotto</div>
    </div>
    <div class="game-card" onclick="window.location='cavalli.php'">
      <img src="./assets/img/pistacavalli.jpg" alt="Cavalli">
      <div class="game-card-name">Cavalli</div>
    </div>
    <div class="game-card" onclick="window.location='roulette.php'">
      <img src="./assets/img/tavoloroulette.jpg" alt="Roulette">
      <div class="game-card-name">Roulette</div>
    </div>
  </div>

  <div class="land-actions">
    <a href="superenalotto.php" class="btn-big btn-big-g">Gioca come ospite</a>
    <a href="accedi.php" class="btn-big btn-big-o">Accedi / Registrati</a>
  </div>
</div>
</body>
</html>
