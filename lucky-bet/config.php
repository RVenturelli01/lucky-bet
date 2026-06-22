<?php
if (session_status() === PHP_SESSION_NONE) session_start();

function getDB(): mysqli {
    $c = new mysqli('localhost', 'root', '', 'sito');
    $c->set_charset('utf8');
    return $c;
}

function getNome(): string {
    return $_SESSION['nome'] ?? 'OSPITE';
}

function handleScore(string $nome): int {
    if (isset($_GET['credito2'])) {
        $s = (int)$_GET['credito2'];
        if ($nome !== 'OSPITE') {
            $db = getDB();
            $q = $db->prepare("UPDATE utenti SET punteggio=? WHERE nome=?");
            $q->bind_param("is", $s, $nome);
            $q->execute();
            $q->close();
            $db->close();
            $_SESSION['punteggio'] = $s;
        }
        return $s;
    }
    if (isset($_SESSION['punteggio'])) return (int)$_SESSION['punteggio'];
    if ($nome === 'OSPITE') return 0;
    $db = getDB();
    $q = $db->prepare("SELECT punteggio FROM utenti WHERE nome=?");
    $q->bind_param("s", $nome);
    $q->execute();
    $row = $q->get_result()->fetch_assoc();
    $q->close();
    $db->close();
    $s = $row ? (int)$row['punteggio'] : 0;
    $_SESSION['punteggio'] = $s;
    return $s;
}

function getLeaderboard(): array {
    $db = getDB();
    $res = $db->query("SELECT nome, punteggio FROM utenti ORDER BY punteggio DESC");
    $rows = [];
    if ($res) while ($r = $res->fetch_assoc()) $rows[] = $r;
    $db->close();
    return $rows;
}
