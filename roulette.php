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
  <title>Roulette &mdash; LuckyBet</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<nav class="navbar">
  <span class="nb-brand">LuckyBet</span>
  <span class="nb-link" onclick="goTo('superenalotto')">SuperEnalotto</span>
  <span class="nb-link" onclick="goTo('cavalli')">Cavalli</span>
  <span class="nb-link active">Roulette</span>
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
      <div class="sb-label">Vincite</div>
      <div class="roul-prizes">
        <div class="roul-prize-row"><span>Rosso / Nero</span><span>2:1</span></div>
        <div class="roul-prize-row"><span>Pari / Dispari</span><span>2:1</span></div>
        <div class="roul-prize-row"><span>1&ndash;18 / 19&ndash;36</span><span>2:1</span></div>
        <div class="roul-prize-row"><span>Dozzina</span><span>3:1</span></div>
        <div class="roul-prize-row"><span>Numero singolo</span><span>36:1</span></div>
      </div>
    </div>
  </aside>

  <div class="game-area">
    <div class="card" style="padding:14px">
      <div class="card-title" style="margin-bottom:8px">Roulette</div>

      <div id="result-msg" class="result-msg"></div>

      <div class="roul-controls">
        <div class="stake-wrap">
          <span class="stake-lbl">Puntata:</span>
          <button class="stake-btn" onclick="meno()">&#8722;</button>
          <span class="stake-val" id="puntata">10</span>
          <button class="stake-btn" onclick="piu()">+</button>
        </div>
        <button class="btn-play" onclick="gira()">Gira</button>
        <span style="font-size:12px;color:var(--muted)">oppure clicca sulla ruota</span>
      </div>

      <!-- Roulette table area -->
      <div class="sfondo2">
        <div class="tavolo" id="tavoloImg">
          <!-- Number overlays (0-36) -->
          <div class="tavolo1">
            <ul class="tavolo" id="numList"></ul>
          </div>
          <!-- Outside bets: dozzine + extra -->
          <div class="tavolo2">
            <ul class="tavolo2" id="outList"></ul>
          </div>
          <!-- Zero column -->
          <div class="tavolo3">
            <ul class="tavolo3">
              <li class="tavolo3" id="sl46" onclick="scegli(46)"></li>
              <li class="tavolo3" id="sl47" onclick="scegli(47)"></li>
            </ul>
          </div>
          <!-- Roulette wheel -->
          <div class="roulette-img" id="roulette" onclick="gira()">
            <img src="./assets/img/roulette.png" alt="roulette">
          </div>
        </div>

        <div style="margin-top:10px; display:flex; align-items:center; gap:14px; flex-wrap:wrap;">
          <span style="font-size:12px; color:var(--muted)">Numero uscito:</span>
          <div class="roul-result" id="colore">
            <span id="prova" style="font-size:18px; font-weight:800"></span>
            <span id="prova2" style="font-size:13px; margin-left:6px; color:var(--muted)"></span>
          </div>
        </div>
      </div>
    </div>

    <div class="instr">
      <h3>Come si gioca?</h3>
      <p>Clicca sulle celle della tavola per scegliere dove puntare (diventano gialle). Poi premi Gira o clicca sulla ruota. Puoi selezionare pi&ugrave; caselle contemporaneamente: ogni scommessa costa la puntata indicata.</p>
    </div>
  </div>
</div>

<script>
const list      = [0,28,9,26,30,11,7,20,32,17,5,22,34,15,3,24,36,13,1,'00',27,10,25,29,12,8,19,31,18,6,21,33,16,4,23,35,14,2,0];
const listRossi = [1,3,5,7,9,12,14,16,18,19,21,23,25,27,30,32,34,36];
const list2     = [3,6,9,12,15,18,21,24,27,30,33,36,2,5,8,11,14,17,20,23,26,29,32,35,1,4,7,10,13,16,19,22,25,28,31,34];
let spinning    = false;

// Build number overlays
(function () {
  const ul = document.getElementById('numList');
  for (let x = 0; x < 37; x++) {
    const li = document.createElement('li');
    li.className = 'tavolo';
    li.id = 'sl' + x;
    li.onclick = () => scegli(x);
    ul.appendChild(li);
    if (x % 12 === 0 && x > 0) {
      ul.parentNode.appendChild(ul.cloneNode(false));
    }
  }
  const ul2 = document.getElementById('outList');
  for (let x = 37; x < 40; x++) {
    const li = document.createElement('li'); li.className = 'tavolo1'; li.id = 'sl' + x; li.onclick = () => scegli(x); ul2.appendChild(li);
  }
  for (let x = 40; x < 46; x++) {
    const li = document.createElement('li'); li.className = 'tavolo2'; li.id = 'sl' + x; li.onclick = () => scegli(x); ul2.appendChild(li);
  }
})();

function scegli(n) {
  if (spinning) return;
  const el = document.getElementById('sl' + n);
  if (!el) return;
  if (el.style.backgroundColor === 'yellow') {
    el.style.backgroundColor = '';
    el.style.opacity = '0';
  } else {
    el.style.backgroundColor = 'yellow';
    el.style.opacity = '0.5';
  }
}

function gira() {
  if (spinning) return;

  // Count bets
  let bets = 0;
  for (let x = 0; x < 48; x++) {
    const el = document.getElementById('sl' + x);
    if (el && el.style.backgroundColor === 'yellow') bets++;
  }

  const puntata = parseInt(document.getElementById('puntata').textContent);
  let score = parseInt(document.getElementById('punteggio2').value) || 0;
  score -= puntata * bets;
  updateScore(score);

  spinning = true;
  document.getElementById('result-msg').style.display = 'none';

  const wheel = document.getElementById('roulette').querySelector('img');
  const n1    = Math.round(Math.random() * 360 + 600);
  let deg     = 0;
  let dec     = 1;
  let count   = 0;

  wheel.style.transform = 'rotate(0deg)';

  const iv = setInterval(() => {
    if (n1 - deg < 400) dec += 1 / (n1 - deg);
    count += 1 / (5 * dec);
    deg   += 360 / (38 * 5 * dec);
    wheel.style.transform = 'rotate(' + deg + 'deg)';

    if (deg >= n1) {
      clearInterval(iv);
      spinning = false;

      let cnt = count;
      while (cnt - 38 > 0) cnt -= 38;
      cnt = 38 - Math.round(cnt);

      const num    = list[cnt];
      const isRed  = listRossi.includes(num);
      const colEl  = document.getElementById('colore');
      const provaEl = document.getElementById('prova');
      const prova2El = document.getElementById('prova2');

      provaEl.textContent  = num;
      let desc = '';

      if (num === '00' || num === 0) {
        colEl.style.backgroundColor = 'var(--green2)';
        desc = 'verde';
      } else if (isRed) {
        colEl.style.backgroundColor = 'var(--red)';
        desc = 'rosso';
      } else {
        colEl.style.backgroundColor = '#222';
        desc = 'nero';
      }

      if (typeof num === 'number' && num > 0) {
        desc += num % 2 === 0 ? ' pari' : ' dispari';
      }
      prova2El.textContent = desc;

      // Pay out
      const preWin = parseInt(document.getElementById('punteggio2').value) || 0;
      let win = preWin;

      if (typeof num === 'number' && num > 0) {
        if (num % 2 === 0  && document.getElementById('sl41')?.style.backgroundColor === 'yellow') win += puntata * 2;
        if (num % 2 === 1  && document.getElementById('sl44')?.style.backgroundColor === 'yellow') win += puntata * 2;
        if (isRed          && document.getElementById('sl42')?.style.backgroundColor === 'yellow') win += puntata * 2;
        if (!isRed         && document.getElementById('sl43')?.style.backgroundColor === 'yellow') win += puntata * 2;
        if (num < 19       && document.getElementById('sl40')?.style.backgroundColor === 'yellow') win += puntata * 2;
        if (num > 18       && document.getElementById('sl45')?.style.backgroundColor === 'yellow') win += puntata * 2;
        if (num < 13       && document.getElementById('sl37')?.style.backgroundColor === 'yellow') win += puntata * 3;
        if (num > 13 && num < 24 && document.getElementById('sl38')?.style.backgroundColor === 'yellow') win += puntata * 3;
        if (num > 24       && document.getElementById('sl39')?.style.backgroundColor === 'yellow') win += puntata * 3;
      }
      for (let x = 0; x < 37; x++) {
        if (list2[x] === num && document.getElementById('sl' + x)?.style.backgroundColor === 'yellow') {
          win += puntata * 36;
        }
      }

      updateScore(win);
      showResult(win > preWin, num, desc);
    }
  }, 10);
}

function showResult(won, num, desc) {
  const el = document.getElementById('result-msg');
  el.textContent = won ? 'HAI VINTO! Uscito: ' + num : 'HAI PERSO. Uscito: ' + num;
  el.className = 'result-msg ' + (won ? 'win' : 'lose');
  el.style.display = 'block';
}

function meno() {
  const el = document.getElementById('puntata');
  const v  = parseInt(el.textContent);
  if (v > 10) el.textContent = v - 10;
}
function piu() {
  const el = document.getElementById('puntata');
  const v  = parseInt(el.textContent);
  if (v < 100) el.textContent = v + 10;
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
  window.location.href = 'roulette.php?credito2=' + s;
}

function goTo(page) {
  const s = document.getElementById('punteggio2').value || 0;
  window.location.href = encodeURIComponent(page) + '.php?credito2=' + s;
}
</script>
</body>
</html>
