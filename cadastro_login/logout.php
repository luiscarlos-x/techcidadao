<?php
session_start();
// Limpar todas as variáveis de sessão
$_SESSION = array();

// Destruir a sessão
session_destroy();

// Redirecionar para a página inicial
header('Location: /tech01/main/index.php');
exit;
?>
