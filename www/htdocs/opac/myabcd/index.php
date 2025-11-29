<?php
// --- 1. INCLUIR ESSENCIAIS E INICIAR SESSÃO ---
// (head-my.php NÃO PODE ser o primeiro a ser chamado)
// Usamos realpath para garantir que os caminhos funcionem

include_once(realpath(__DIR__ . '/../../central/config_opac.php'));
include_once(realpath(__DIR__ . '/../functions.php'));

// --- 2. VERIFICAÇÃO DE SEGURANÇA (ANTES DE QUALQUER HTML) ---
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {

    // Pega a URL exata que o usuário tentou acessar
    $RedirectUrl = $_SERVER['REQUEST_URI'];

    // $link_logo vem do config_opac.php
    $login_page_url = $link_logo . "login.php?RedirectUrl=" . urlencode($RedirectUrl);

    // Redireciona e PARA
    header("Location: " . $login_page_url);
    exit;
}

include("../head-my.php");       // 1. Inclui o <head> do OPAC
$page_title = $msgstr["my_account"]; // Define o título da página

include("../../$app_path/lang/prestamo.php");
include("../../$app_path/lang/mysite.php");

include("my-functions.php");

$user_iso = LeerRegistro();

$dataarr = getUserStatus();

include 'inc/user.php';

?>

<div class="container-fluid">
    <div class="row">

        <?php



        // Opcional: Incluir a sidebar de busca se desejar
        // include($Web_Dir . "views/sidebar.php"); 
        MenuFinalUser();
        ?>

        <main class="col-md-12 ms-sm-auto col-lg-12 px-md-4">

            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><?php echo $msgstr["my_account"]; ?></h1>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h3><i class="fas fa-book-reader"></i> <?php echo $msgstr["my_loans"]; ?></h3>
                </div>
                <div class="card-body">
                    <?php include("inc/loans.php"); // Inclui o miolo da lógica antiga 
                    ?>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h3><i class="fas fa-calendar-check"></i> <?php echo $msgstr["my_reservations"]; ?></h3>
                </div>
                <div class="card-body">
                    <?php include("inc/reserve.php"); // Inclui o miolo da lógica antiga 
                    ?>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h3><i class="fas fa-dollar-sign"></i> <?php echo $msgstr["my_fines"]; ?></h3>
                </div>
                <div class="card-body">
                    <?php include("inc/fines.php"); // Inclui o miolo da lógica antiga 
                    ?>
                </div>
            </div>

        </main>
    </div>
</div>

<?php
// --- 3. INÍCIO DO HTML (RODAPÉ DO OPAC) ---
include($Web_Dir . "views/footer.php"); // Inclui o rodapé padrão do OPAC
?>