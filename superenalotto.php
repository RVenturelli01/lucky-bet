<?php
require_once 'config.php';
$nome       = getNome();
$punteggio  = handleScore($nome);
$leaderboard = getLeaderboard();
$loggedIn   = $nome !== 'OSPITE';
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>SuperEnalotto &mdash; LuckyBet</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<nav class="navbar">
  <span class="nb-brand">LuckyBet</span>
  <span class="nb-link active">SuperEnalotto</span>
  <span class="nb-link" onclick="goTo('cavalli')">Cavalli</span>
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
  <!-- Sidebar -->
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

    <div class="sb-section">
      <div class="sb-label">Varianti</div>
      <div class="sb-links">
        <span class="sb-link" onclick="goTo('lotteria-40')">Lotteria 40 numeri</span>
        <span class="sb-link" onclick="goTo('lotteria-50')">Lotteria 50 numeri</span>
      </div>
    </div>
  </aside>

  <!-- Game area -->
  <div class="game-area">
    <div class="card">
      <div class="card-title">SuperEnalotto</div>
      <div class="card-sub">Scegli 6 numeri da 1 a 90 e premi Gioca</div>

      <div id="result-msg" class="result-msg"></div>

      <div class="num-grid lotto90">
        <?php for ($i = 1; $i <= 90; $i++): ?>
        <div class="num-cell" id="c<?= $i ?>" onclick="toggleNum(<?= $i ?>)"><?= $i ?></div>
        <?php endfor; ?>
      </div>

      <div class="gcontrols">
        <button class="btn-play" onclick="play()">Gioca</button>
        <button class="btn-reset" onclick="resetGame()">Reset</button>
      </div>

      <div class="win-label">Numeri estratti</div>
      <div class="win-balls">
        <?php for ($i = 0; $i < 6; $i++): ?>
        <div class="win-ball" id="wb<?= $i ?>">?</div>
        <?php endfor; ?>
      </div>
    </div>

    <div class="card">
      <div class="card-title">Possibili vincite</div>
      <div class="prizes">
        <div class="prize-row" id="pr2"><span class="prize-lbl">2 numeri indovinati</span><span class="prize-val">×11</span></div>
        <div class="prize-row" id="pr3"><span class="prize-lbl">3 numeri indovinati</span><span class="prize-val">×160</span></div>
        <div class="prize-row" id="pr4"><span class="prize-lbl">4 numeri indovinati</span><span class="prize-val">×6.000</span></div>
        <div class="prize-row" id="pr5"><span class="prize-lbl">5 numeri indovinati</span><span class="prize-val">×620.000</span></div>
        <div class="prize-row" id="pr6"><span class="prize-lbl">6 numeri indovinati</span><span class="prize-val">×310.000.000</span></div>
      </div>
    </div>

    <div class="instr" id="regole">
      <h3>Come si gioca?</h3>
      <p>Scegli sei numeri tra 1 e 90 cliccando sulle caselle (diventano rosse). Premi <strong>Gioca</strong>: vengono estratti 6 numeri casuali. A seconda di quanti ne indovini vinci punti. La puntata fissa &egrave; 1 punto per turno.</p>
    </div>
  </div>
</div>

<script>
let selected = 0;
const PRIZES = {2: 11, 3: 160, 4: 6000, 5: 620000, 6: 310000000};

function toggleNum(n) {
  const el = document.getElementById('c' + n);
  if (el.classList.contains('sel')) {
    el.classList.remove('sel'); selected--;
  } else if (selected < 6) {
    el.classList.add('sel'); selected++;
  }
}

function play() {
  if (selected !== 6) { alert('Devi scegliere esattamente 6 numeri.'); return; }

  ['pr2','pr3','pr4','pr5','pr6'].forEach(id => document.getElementById(id).classList.remove('lit'));
  document.getElementById('result-msg').style.display = 'none';
  for (let i = 0; i < 6; i++) {
    const b = document.getElementById('wb' + i);
    b.textContent = '?'; b.classList.remove('drawn');
  }

  let score = parseInt(document.getElementById('punteggio2').value) || 0;
  score -= 1;
  updateScore(score);

  const chosen = [];
  for (let i = 1; i <= 90; i++) {
    if (document.getElementById('c' + i).classList.contains('sel')) chosen.push(i);
  }

  const drawn = [];
  while (drawn.length < 6) {
    const n = Math.floor(Math.random() * 90) + 1;
    if (!drawn.includes(n)) drawn.push(n);
  }
  drawn.sort((a, b) => a - b);

  for (let i = 0; i < 6; i++) {
    setTimeout(() => {
      const ball = document.getElementById('wb' + i);
      ball.textContent = drawn[i];
      ball.classList.add('drawn');

      if (i === 5) {
        const matches = chosen.filter(n => drawn.includes(n)).length;
        if (matches >= 2) {
          score += PRIZES[matches];
          document.getElementById('pr' + matches).classList.add('lit');
        }
        updateScore(score);
        showResult(matches >= 2, matches);
      }
    }, (i + 1) * 350);
  }
}

function resetGame() {
  selected = 0;
  for (let i = 1; i <= 90; i++) document.getElementById('c' + i).classList.remove('sel');
  for (let i = 0; i < 6; i++) {
    const b = document.getElementById('wb' + i);
    b.textContent = '?'; b.classList.remove('drawn');
  }
  document.getElementById('result-msg').style.display = 'none';
}

function showResult(win, matches) {
  const el = document.getElementById('result-msg');
  el.textContent = win ? 'HAI VINTO! ' + matches + ' numeri indovinati' : 'HAI PERSO';
  el.className = 'result-msg ' + (win ? 'win' : 'lose');
  el.style.display = 'block';
}

function updateScore(s) {
  document.getElementById('punteggio1').value = s;
  document.getElementById('punteggio2').value = s;
  const el = document.getElementById('score-display');
  if (el) el.textContent = s;
  const nav = document.getElementById('nav-score');
  if (nav) nav.textContent = s;
  const lb = document.getElementById('lb-my-score');
  if (lb) lb.textContent = s;
}

function saveScore() {
  const s = document.getElementById('punteggio2').value;
  window.location.href = 'superenalotto.php?credito2=' + s;
}

function goTo(page) {
  const s = document.getElementById('punteggio2').value || 0;
  window.location.href = encodeURIComponent(page) + '.php?credito2=' + s;
}
</script>
</body>
</html>
