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
  <title>Lotteria 50 Numeri &mdash; LuckyBet</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<nav class="navbar">
  <span class="nb-brand">LuckyBet</span>
  <span class="nb-link" onclick="goTo('superenalotto')">SuperEnalotto</span>
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
        <span class="sb-link" onclick="goTo('superenalotto')">SuperEnalotto (90 numeri)</span>
        <span class="sb-link" onclick="goTo('lotteria-40')">Lotteria 40 numeri</span>
      </div>
    </div>
  </aside>

  <div class="game-area">
    <div class="card">
      <div class="card-title">Lotteria 50 Numeri</div>
      <div class="card-sub">Scegli 6 numeri da 1 a 50 e premi Gioca</div>

      <div id="result-msg" class="result-msg"></div>

      <div class="num-grid lotto50">
        <?php for ($i = 1; $i <= 50; $i++): ?>
        <div class="num-cell" id="c<?= $i ?>" onclick="toggleNum(<?= $i ?>)"><?= $i ?></div>
        <?php endfor; ?>
      </div>

      <div class="gcontrols">
        <div class="stake-wrap">
          <span class="stake-lbl">Puntata:</span>
          <button class="stake-btn" onclick="changePuntata(-1)">&#8722;</button>
          <span class="stake-val" id="puntata">1</span>
          <button class="stake-btn" onclick="changePuntata(1)">+</button>
        </div>
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
        <div class="prize-row" id="pr2"><span class="prize-lbl">2 numeri</span><span class="prize-val" id="pv2">10</span></div>
        <div class="prize-row" id="pr3"><span class="prize-lbl">3 numeri</span><span class="prize-val" id="pv3">300</span></div>
        <div class="prize-row" id="pr4"><span class="prize-lbl">4 numeri</span><span class="prize-val" id="pv4">10.000</span></div>
        <div class="prize-row" id="pr5"><span class="prize-lbl">5 numeri</span><span class="prize-val" id="pv5">1.500.000</span></div>
        <div class="prize-row" id="pr6"><span class="prize-lbl">6 numeri</span><span class="prize-val" id="pv6">177.000.000</span></div>
      </div>
    </div>

    <div class="instr">
      <h3>Come si gioca?</h3>
      <p>Versione con 50 numeri. Scegli 6 numeri, imposta la puntata, poi premi Gioca. I premi si moltiplicano per la puntata scelta. I premi sono pi&ugrave; alti rispetto alle varianti con meno numeri.</p>
    </div>
  </div>
</div>

<script>
let selected = 0;
let puntata  = 1;
const BASE   = {2: 10, 3: 300, 4: 10000, 5: 1500000, 6: 177000000};

function toggleNum(n) {
  const el = document.getElementById('c' + n);
  if (el.classList.contains('sel')) {
    el.classList.remove('sel'); selected--;
  } else if (selected < 6) {
    el.classList.add('sel'); selected++;
  }
}

function changePuntata(d) {
  puntata = Math.max(1, Math.min(100, puntata + d));
  document.getElementById('puntata').textContent = puntata;
  [2,3,4,5,6].forEach(k => {
    document.getElementById('pv' + k).textContent = (BASE[k] * puntata).toLocaleString('it-IT');
  });
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
  score -= puntata;
  updateScore(score);

  const chosen = [];
  for (let i = 1; i <= 50; i++) {
    if (document.getElementById('c' + i).classList.contains('sel')) chosen.push(i);
  }

  const drawn = [];
  while (drawn.length < 6) {
    const n = Math.floor(Math.random() * 50) + 1;
    if (!drawn.includes(n)) drawn.push(n);
  }
  drawn.sort((a, b) => a - b);

  for (let i = 0; i < 6; i++) {
    setTimeout(() => {
      const b = document.getElementById('wb' + i);
      b.textContent = drawn[i]; b.classList.add('drawn');

      if (i === 5) {
        const matches = chosen.filter(n => drawn.includes(n)).length;
        if (matches >= 2) {
          score += BASE[matches] * puntata;
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
  for (let i = 1; i <= 50; i++) document.getElementById('c' + i).classList.remove('sel');
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
  window.location.href = 'lotteria-50.php?credito2=' + s;
}

function goTo(page) {
  const s = document.getElementById('punteggio2').value || 0;
  window.location.href = encodeURIComponent(page) + '.php?credito2=' + s;
}
</script>
</body>
</html>
