<?php
require_once 'config.php';
$nome        = getNome();
$punteggio   = handleScore($nome);
$leaderboard = getLeaderboard();
$loggedIn    = $nome !== 'OSPITE';
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Cavalli &mdash; LuckyBet</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<nav class="navbar">
  <span class="nb-brand">LuckyBet</span>
  <span class="nb-link" onclick="goTo('superenalotto')">SuperEnalotto</span>
  <span class="nb-link active">Cavalli</span>
  <span class="nb-link" onclick="goTo('roulette')">Roulette</span>
  <div class="nb-right">
    <?php if ($loggedIn): ?>
    <span class="nb-badge"><?= htmlspecialchars($nome) ?> &mdash; <span id="nav-score"><?= $punteggio ?></span> pt</span>
    <a href="logout.php" class="nb-btn nb-btn-o">Esci</a>
    <?php else: ?>
    <a href="accedi.php" class="nb-btn nb-btn-g">Accedi</a>
    <?php endif; ?>
  </div>
</nav>

<div class="pg">
  <aside class="sidebar">
    <div class="sb-section">
      <div class="sb-label">Profilo</div>
      <div class="user-card">
        <div class="u-name"><?= $loggedIn ? htmlspecialchars($nome) : 'Ospite' ?></div>
        <div class="u-slabel">punteggio</div>
        <div class="u-score" id="score-display"><?= $punteggio ?></div>
        <input type="hidden" id="punteggio1" value="<?= $punteggio ?>">
        <input type="hidden" id="punteggio2" value="<?= $punteggio ?>">
        <?php if ($loggedIn): ?>
        <button class="btn btn-g btn-full" onclick="saveScore()">Salva</button>
        <a href="logout.php" class="btn btn-d btn-full">Logout</a>
        <?php else: ?>
        <a href="accedi.php" class="btn btn-g btn-full" style="margin-top:8px">Accedi</a>
        <?php endif; ?>
      </div>
    </div>

    <div class="sb-section">
      <div class="sb-label">Classifica</div>
      <div class="lb">
        <div class="lb-head"><span>Giocatore</span><span>Punti</span></div>
        <?php foreach ($leaderboard as $i => $r): ?>
        <div class="lb-row <?= $r['nome'] === $nome ? 'me' : '' ?>">
          <span class="lb-pos"><?= $i + 1 ?></span>
          <span class="lb-name"><?= htmlspecialchars($r['nome']) ?></span>
          <span class="lb-pts" <?= $r['nome'] === $nome ? 'id="lb-my-score"' : '' ?>><?= $r['punteggio'] ?></span>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </aside>

  <div class="game-area">
    <div class="track-card">
      <div class="card-title">Corsa Cavalli</div>
      <div class="card-sub">Scegli un cavallo cliccandoci sopra, poi premi Gioca</div>

      <div id="result-msg" class="result-msg"></div>

      <!-- Track -->
      <div class="track-wrap" id="track">
        <img id="freccia" src="./assets/img/freccia.png" class="track-arrow">
        <div class="track-spacer"></div>
        <div class="horse-lane" id="lane1" onclick="selectHorse(1)">
          <img id="horse1" src="./assets/img/cavallo1.png">
        </div>
        <div class="horse-lane" id="lane2" onclick="selectHorse(2)">
          <img id="horse2" src="./assets/img/cavallo3.png">
        </div>
        <div class="horse-lane" id="lane3" onclick="selectHorse(3)">
          <img id="horse3" src="./assets/img/cavallo5.png">
        </div>
        <div class="horse-lane" id="lane4" onclick="selectHorse(4)">
          <img id="horse4" src="./assets/img/cavallo4.png">
        </div>
        <div class="horse-lane" id="lane5" onclick="selectHorse(5)">
          <img id="horse5" src="./assets/img/cavallo2.png">
        </div>
      </div>

      <div class="gcontrols">
        <div class="stake-wrap">
          <span class="stake-lbl">Puntata:</span>
          <button class="stake-btn" onclick="changePuntata(-10)">&#8722;</button>
          <span class="stake-val" id="puntata">10</span>
          <button class="stake-btn" onclick="changePuntata(10)">+</button>
        </div>
        <button class="btn-play" onclick="startRace()">Gioca</button>
        <button class="btn-reset" onclick="resetRace()">Reset</button>
      </div>
    </div>

    <div class="instr" id="regole">
      <h3>Come si gioca?</h3>
      <p>Clicca su un cavallo per selezionarlo (comparir&agrave; una freccia). Scegli la puntata (da 10 a 100) e premi Gioca. Se il tuo cavallo arriva primo vinci la puntata &times;5.</p>
    </div>
  </div>
</div>

<script>
let selectedHorse = 0;
let raceState     = 'ready';
let puntata       = 10;

// Vertical positions (top %) of each horse lane within the track
// track-spacer = 28%, each lane = 13% → centers at 34.5%, 47.5%, 60.5%, 73.5%, 86.5%
const LANE_TOP = { 1: 30, 2: 44, 3: 57, 4: 70, 5: 83 };

function selectHorse(n) {
  if (raceState !== 'ready') return;
  selectedHorse = n;
  const track = document.getElementById('track');
  const freccia = document.getElementById('freccia');
  freccia.style.top  = LANE_TOP[n] + '%';
  freccia.style.left = '0%';
  freccia.style.display = 'block';
}

function changePuntata(d) {
  if (raceState !== 'ready') return;
  puntata = Math.max(10, Math.min(100, puntata + d));
  document.getElementById('puntata').textContent = puntata;
}

function startRace() {
  if (raceState !== 'ready') return;
  if (!selectedHorse) { alert('Scegli prima un cavallo!'); return; }

  raceState = 'racing';

  // Deduct stake
  let score = parseInt(document.getElementById('punteggio2').value) || 0;
  score -= puntata;
  updateScore(score);

  // Reset positions
  for (let i = 1; i <= 5; i++) document.getElementById('horse' + i).style.left = '0%';

  // Random speeds (interval delay ms — smaller = faster)
  const speeds = [];
  for (let i = 0; i < 5; i++) speeds.push(Math.round(Math.random() * 20 + 14));

  let finished = 0;

  function animateHorse(id, delay) {
    const img     = document.getElementById('horse' + id);
    const freccia = document.getElementById('freccia');
    let pos = 0;
    const iv = setInterval(() => {
      pos += 0.15;
      img.style.left = pos + '%';
      if (selectedHorse === id) {
        freccia.style.left = Math.max(0, pos - 2) + '%';
      }
      if (pos >= 85) {
        clearInterval(iv);
        finished++;
        if (finished === 1) {
          raceState = 'done';
          if (id === selectedHorse) {
            let s = parseInt(document.getElementById('punteggio2').value) || 0;
            s += puntata * 5;
            updateScore(s);
            showResult(true);
          } else {
            showResult(false);
          }
        }
      }
    }, delay);
  }

  for (let i = 1; i <= 5; i++) animateHorse(i, speeds[i - 1]);
}

function resetRace() {
  if (raceState === 'racing') return;
  raceState     = 'ready';
  selectedHorse = 0;
  for (let i = 1; i <= 5; i++) document.getElementById('horse' + i).style.left = '0%';
  document.getElementById('freccia').style.display = 'none';
  document.getElementById('result-msg').style.display = 'none';
}

function showResult(win) {
  const el = document.getElementById('result-msg');
  el.textContent = win ? 'HAI VINTO! Puntata x5' : 'HAI PERSO';
  el.className = 'result-msg ' + (win ? 'win' : 'lose');
  el.style.display = 'block';
}

function updateScore(s) {
  document.getElementById('punteggio1').value = s;
  document.getElementById('punteggio2').value = s;
  const el  = document.getElementById('score-display');
  if (el) el.textContent = s;
  const nav = document.getElementById('nav-score');
  if (nav) nav.textContent = s;
  const lb  = document.getElementById('lb-my-score');
  if (lb) lb.textContent = s;
}

function saveScore() {
  const s = document.getElementById('punteggio2').value;
  window.location.href = 'cavalli.php?credito2=' + s;
}

function goTo(page) {
  const s = document.getElementById('punteggio2').value || 0;
  window.location.href = encodeURIComponent(page) + '.php?credito2=' + s;
}
</script>
</body>
</html>
