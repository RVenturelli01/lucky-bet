<?php
require_once 'config.php';
$error = '';
$tab = 'login';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome     = trim($_POST['nome'] ?? '');
    $password = $_POST['password'] ?? '';
    $action   = $_POST['action'] ?? '';

    if ($nome === '' || $password === '') {
        $error = 'Compila tutti i campi.';
        $tab = $action === 'register' ? 'register' : 'login';
    } elseif ($action === 'login') {
        $db   = getDB();
        $stmt = $db->prepare("SELECT password, punteggio FROM utenti WHERE nome=?");
        $stmt->bind_param("s", $nome);
        $stmt->execute();
        $row  = $stmt->get_result()->fetch_assoc();
        $stmt->close(); $db->close();

        if ($row && password_verify($password, $row['password'])) {
            $_SESSION['nome']      = $nome;
            $_SESSION['punteggio'] = (int)$row['punteggio'];
            header('location: superenalotto.php');
            exit;
        } else {
            $error = 'Nome utente o password errati.';
            $tab   = 'login';
        }
    } elseif ($action === 'register') {
        $tab = 'register';
        $db  = getDB();
        $chk = $db->prepare("SELECT nome FROM utenti WHERE nome=?");
        $chk->bind_param("s", $nome);
        $chk->execute();
        $exists = $chk->get_result()->fetch_assoc();
        $chk->close();

        if ($exists) {
            $error = 'Nome utente gi&agrave; in uso, scegline un altro.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $ins  = $db->prepare("INSERT INTO utenti (nome, password, punteggio) VALUES (?, ?, 0)");
            $ins->bind_param("ss", $nome, $hash);
            $ins->execute();
            $ins->close();
            $db->close();
            $_SESSION['nome']      = $nome;
            $_SESSION['punteggio'] = 0;
            header('location: superenalotto.php');
            exit;
        }
        $db->close();
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Accedi &mdash; LuckyBet</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="auth-bg">
  <div class="auth-card">
    <div class="auth-logo">LuckyBet</div>

    <?php if ($error): ?>
    <div class="auth-err show"><?= $error ?></div>
    <?php endif; ?>

    <div class="tabs">
      <div class="tab <?= $tab === 'login'    ? 'active' : '' ?>" onclick="switchTab('login')">Accedi</div>
      <div class="tab <?= $tab === 'register' ? 'active' : '' ?>" onclick="switchTab('register')">Registrati</div>
    </div>

    <!-- Login -->
    <form method="POST" id="fLogin" <?= $tab !== 'login' ? 'style="display:none"' : '' ?>>
      <input type="hidden" name="action" value="login">
      <div class="fg">
        <label class="fl">Nome utente</label>
        <input type="text" name="nome" class="fi" placeholder="Il tuo nome" required>
      </div>
      <div class="fg">
        <label class="fl">Password</label>
        <input type="password" name="password" class="fi" placeholder="La tua password" required>
      </div>
      <button type="submit" class="submit-btn">Accedi</button>
    </form>

    <!-- Register -->
    <form method="POST" id="fReg" <?= $tab !== 'register' ? 'style="display:none"' : '' ?>>
      <input type="hidden" name="action" value="register">
      <div class="fg">
        <label class="fl">Nome utente</label>
        <input type="text" name="nome" class="fi" placeholder="Scegli un nome" required>
      </div>
      <div class="fg">
        <label class="fl">Password</label>
        <input type="password" name="password" class="fi" placeholder="Scegli una password" required>
      </div>
      <button type="submit" class="submit-btn">Registrati</button>
    </form>

    <a href="index.php" class="auth-back">Torna alla home</a>
  </div>
</div>
<script>
function switchTab(t) {
  document.getElementById('fLogin').style.display = t === 'login'    ? '' : 'none';
  document.getElementById('fReg').style.display   = t === 'register' ? '' : 'none';
  document.querySelectorAll('.tab').forEach((el, i) => {
    el.classList.toggle('active', (i === 0 && t === 'login') || (i === 1 && t === 'register'));
  });
}
</script>
</body>
</html>
