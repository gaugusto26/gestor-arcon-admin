<?php
$page_title = 'Contratos do Cliente';
require_once '../../includes/header.php';
require_once '../../includes/menu.php';
require_once 'config.php';

if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id = $_GET['id'];

// Busca dados do cliente
$stmt = $conn->prepare("SELECT * FROM clientes WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$cliente = $stmt->get_result()->fetch_assoc();

if(!$cliente) {
    header('Location: index.php');
    exit;
}

// Busca contratos
$contratos = $conn->query("
    SELECT c.*, pc.nome_plano, pc.valor_plano, pc.valor_mensal
    FROM contratos c
    LEFT JOIN planos_contratados pc ON c.plano_contratado_id = pc.id
    WHERE c.cliente_id = $id
    ORDER BY c.created_at DESC
");
?>

<style>
/* Estilos similares aos anteriores */
.contratos-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.cliente-info {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 20px;
    padding: 25px;
    margin-bottom: 30px;
    color: white;
    display: flex;
    align-items: center;
    gap: 20px;
}

.cliente-avatar {
    width: 70px;
    height: 70px;
    background: rgba(255,255,255,0.2);
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    font-weight: 700;
}

.cliente-details h2 {
    font-size: 1.8rem;
    margin-bottom: 5px;
}

.cliente-details p {
    opacity: 0.9;
}

.table-container {
    background: #ffffff;
    border: 1px solid var(--border);
    border-radius: 20px;
    overflow: hidden;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th {
    text-align: left;
    padding: 16px 25px;
    background: #f8faff;
    color: var(--text-secondary);
    font-weight: 500;
}

td {
    padding: 16px 25px;
    border-bottom: 1px solid var(--border);
}

.status-badge {
    padding: 4px 12px;
    border-radius: 50px;
    font-size: 0.8rem;
    font-weight: 600;
    display: inline-block;
}

.status-ativo, .status-assinado {
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
}

.status-rascunho {
    background: rgba(249, 115, 22, 0.1);
    color: #f97316;
}

.status-cancelado {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
}

.btn {
    padding: 10px 20px;
    border-radius: 10px;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.btn-secondary {
    background: #f8faff;
    color: #4361ee;
    border: 1px solid var(--border);
}

.btn-icon {
    padding: 8px 12px;
    border-radius: 8px;
    border: 1px solid var(--border);
    background: #ffffff;
    color: var(--text-secondary);
    text-decoration: none;
    display: inline-block;
}
</style>

<div class="main-content" id="mainContent">
    <div class="top-bar">
        <h1 class="page-title">
            <i class="fas fa-file-contract" style="color: #4361ee; margin-right: 10px;"></i>
            Contratos do Cliente
        </h1>
    </div>

    <div class="content-area">
        <div class="contratos-header">
            <a href="visualizar.php?id=<?php echo $id; ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
            <a href="../contratos/criar.php?cliente_id=<?php echo $id; ?>" class="btn btn-primary">
                <i class="fas fa-plus"></i> Novo Contrato
            </a>
        </div>

        <div class="cliente-info">
            <div class="cliente-avatar">
                <?php echo strtoupper(substr($cliente['nome'], 0, 1)); ?>
            </div>
            <div class="cliente-details">
                <h2><?php echo $cliente['nome']; ?></h2>
                <p><?php echo $cliente['email']; ?> <?php echo $cliente['empresa'] ? ' • ' . $cliente['empresa'] : ''; ?></p>
            </div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Nº Contrato</th>
                        <th>Tipo</th>
                        <th>Plano</th>
                        <th>Valor Total</th>
                        <th>Mensalidade</th>
                        <th>Status</th>
                        <th>Data</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($contratos->num_rows == 0): ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 50px;">
                            <i class="fas fa-file-contract" style="font-size: 3rem; color: var(--text-muted);"></i>
                            <p style="margin-top: 15px;">Nenhum contrato encontrado</p>
                            <a href="../contratos/criar.php?cliente_id=<?php echo $id; ?>" class="btn btn-primary" style="margin-top: 15px;">
                                Criar Primeiro Contrato
                            </a>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php while($cont = $contratos->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo $cont['numero_contrato']; ?></strong></td>
                            <td><?php echo ucfirst($cont['tipo_contrato']); ?></td>
                            <td><?php echo $cont['nome_plano'] ?: 'Personalizado'; ?></td>
                            <td>R$ <?php echo number_format($cont['valor_total'] ?? 0, 2, ',', '.'); ?></td>
                            <td>R$ <?php echo number_format($cont['valor_mensal'] ?? 0, 2, ',', '.'); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo $cont['status']; ?>">
                                    <?php echo ucfirst($cont['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($cont['created_at'])); ?></td>
                            <td>
                                <a href="../contratos/visualizar.php?id=<?php echo $cont['id']; ?>" class="btn-icon" title="Ver">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="../contratos/pdf.php?id=<?php echo $cont['id']; ?>" class="btn-icon" title="PDF">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
const themeToggle = document.getElementById('themeToggle');
const themeIcon = document.getElementById('themeIcon');
const body = document.body;

if(themeToggle) {
    themeToggle.addEventListener('click', () => {
        const currentTheme = body.getAttribute('data-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        body.setAttribute('data-theme', newTheme);
        document.cookie = `admin_theme=${newTheme}; path=/`;
        themeIcon.className = newTheme === 'dark' ? 'fas fa-moon' : 'fas fa-sun';
    });
}
</script>

<?php require_once '../../includes/footer.php'; ?>