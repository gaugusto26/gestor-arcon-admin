<?php
$page_title = 'Visualizar Plano';
require_once '../../includes/header.php';
require_once '../../includes/menu.php';
require_once 'config.php';

// Verifica se tem ID
if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id = $_GET['id'];

// Busca o plano
$stmt = $conn->prepare("
    SELECT p.*, c.nome as categoria_nome, c.icone as categoria_icone 
    FROM planos p 
    LEFT JOIN planos_categorias c ON p.categoria_id = c.id 
    WHERE p.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$plano = $stmt->get_result()->fetch_assoc();

if(!$plano) {
    header('Location: index.php');
    exit;
}

// Busca características
$stmt_carac = $conn->prepare("SELECT * FROM planos_caracteristicas WHERE plano_id = ? ORDER BY ordem");
$stmt_carac->bind_param("i", $id);
$stmt_carac->execute();
$caracteristicas = $stmt_carac->get_result();

$perfis = getPerfis();
$periodos = getPeriodos();
?>

<style>
.view-container {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 30px;
    max-width: 800px;
    margin: 0 auto;
}

.view-header {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid var(--border);
}

.view-icon {
    width: 70px;
    height: 70px;
    border-radius: 16px;
    background: var(--accent);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
}

.view-title h2 {
    font-size: 2rem;
    margin-bottom: 5px;
    color: var(--text-primary);
}

.view-title .categoria {
    color: var(--text-muted);
    font-size: 1rem;
    display: flex;
    align-items: center;
    gap: 5px;
}

.view-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
    margin-bottom: 30px;
}

.view-item {
    background: var(--bg-secondary);
    padding: 15px;
    border-radius: 12px;
    border: 1px solid var(--border);
}

.view-item.full-width {
    grid-column: span 2;
}

.view-label {
    font-size: 0.85rem;
    color: var(--text-muted);
    margin-bottom: 5px;
    display: flex;
    align-items: center;
    gap: 5px;
}

.view-label i {
    color: var(--accent);
}

.view-value {
    font-size: 1.1rem;
    font-weight: 500;
    color: var(--text-primary);
}

.view-value.preco {
    font-size: 2rem;
    color: var(--accent);
    font-weight: 700;
}

.view-value.preco small {
    font-size: 0.9rem;
    color: var(--text-muted);
    font-weight: 400;
}

.view-descricao {
    background: var(--bg-secondary);
    padding: 20px;
    border-radius: 12px;
    border: 1px solid var(--border);
    margin-bottom: 30px;
    line-height: 1.6;
    color: var(--text-secondary);
}

.view-caracteristicas {
    background: var(--bg-secondary);
    padding: 20px;
    border-radius: 12px;
    border: 1px solid var(--border);
    margin-bottom: 30px;
}

.view-caracteristicas h3 {
    margin-bottom: 15px;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 8px;
}

.view-caracteristicas h3 i {
    color: var(--accent);
}

.view-caracteristicas ul {
    list-style: none;
}

.view-caracteristicas li {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 0;
    border-bottom: 1px dashed var(--border);
    color: var(--text-secondary);
}

.view-caracteristicas li:last-child {
    border-bottom: none;
}

.view-caracteristicas li i {
    color: #22c55e;
    width: 20px;
}

.badge-status {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}

.badge-ativo {
    background: #22c55e20;
    color: #22c55e;
}

.badge-inativo {
    background: #ef444420;
    color: #ef4444;
}

.badge-destaque {
    background: var(--accent-light);
    color: var(--accent);
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.view-actions {
    display: flex;
    gap: 15px;
    justify-content: center;
    margin-top: 30px;
}

.btn {
    padding: 12px 30px;
    border-radius: 8px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-primary {
    background: var(--accent);
    color: white;
}

.btn-primary:hover {
    background: #2563eb;
    transform: translateY(-2px);
}

.btn-secondary {
    background: var(--hover);
    color: var(--text-secondary);
    border: 1px solid var(--border);
}

.btn-secondary:hover {
    background: var(--border);
}
</style>

<div class="main-content" id="mainContent">
    <div class="top-bar">
        <h1 class="page-title">
            <i class="fas fa-eye" style="margin-right: 10px;"></i>
            Visualizar Plano
        </h1>
    </div>

    <div class="content-area">
        <div class="view-container">
            <div class="view-header">
                <div class="view-icon">
                    <i class="fas <?php echo $plano['categoria_icone'] ?? 'fa-cube'; ?>"></i>
                </div>
                <div class="view-title">
                    <h2><?php echo $plano['nome']; ?></h2>
                    <div class="categoria">
                        <i class="fas <?php echo $plano['categoria_icone'] ?? 'fa-tag'; ?>"></i>
                        <?php echo $plano['categoria_nome'] ?? 'Sem categoria'; ?>
                    </div>
                </div>
                <div style="margin-left: auto;">
                    <?php if($plano['destaque']): ?>
                        <span class="badge-destaque">
                            <i class="fas fa-star"></i> Destaque
                        </span>
                    <?php endif; ?>
                    <?php if($plano['badge_text']): ?>
                        <span class="badge-destaque" style="background: var(--accent); color: white;">
                            <i class="fas fa-tag"></i> <?php echo $plano['badge_text']; ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="view-grid">
                <div class="view-item">
                    <div class="view-label">
                        <i class="fas fa-dollar-sign"></i> Preço
                    </div>
                    <div class="view-value preco">
                        <?php echo formatarPreco($plano['preco']); ?>
                        <small>/<?php echo $periodos[$plano['periodo']] ?? $plano['periodo']; ?></small>
                    </div>
                </div>

                <div class="view-item">
                    <div class="view-label">
                        <i class="fas fa-user-tie"></i> Perfil
                    </div>
                    <div class="view-value">
                        <?php echo $perfis[$plano['perfil']] ?? $plano['perfil']; ?>
                    </div>
                </div>

                <div class="view-item">
                    <div class="view-label">
                        <i class="fas fa-clock"></i> Prazo de Entrega
                    </div>
                    <div class="view-value">
                        <?php echo $plano['prazo_entrega'] ?? 'Não informado'; ?>
                    </div>
                </div>

                <div class="view-item">
                    <div class="view-label">
                        <i class="fas fa-sort"></i> Ordem
                    </div>
                    <div class="view-value">
                        <?php echo $plano['ordem']; ?>
                    </div>
                </div>

                <div class="view-item">
                    <div class="view-label">
                        <i class="fas fa-circle"></i> Status
                    </div>
                    <div class="view-value">
                        <span class="badge-status <?php echo $plano['ativo'] ? 'badge-ativo' : 'badge-inativo'; ?>">
                            <?php echo $plano['ativo'] ? 'Ativo' : 'Inativo'; ?>
                        </span>
                    </div>
                </div>

                <div class="view-item">
                    <div class="view-label">
                        <i class="fas fa-hashtag"></i> ID
                    </div>
                    <div class="view-value">
                        #<?php echo $plano['id']; ?>
                    </div>
                </div>

                <?php if($plano['descricao_curta']): ?>
                <div class="view-item full-width">
                    <div class="view-label">
                        <i class="fas fa-align-left"></i> Descrição Curta
                    </div>
                    <div class="view-value">
                        <?php echo $plano['descricao_curta']; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <?php if($plano['descricao_completa']): ?>
            <div class="view-descricao">
                <strong style="display: block; margin-bottom: 10px; color: var(--text-primary);">
                    <i class="fas fa-align-justify" style="margin-right: 5px;"></i> Descrição Completa
                </strong>
                <?php echo nl2br($plano['descricao_completa']); ?>
            </div>
            <?php endif; ?>

            <?php if($plano['observacao']): ?>
            <div class="view-descricao" style="background: var(--accent-light);">
                <strong style="display: block; margin-bottom: 10px; color: var(--accent);">
                    <i class="fas fa-info-circle"></i> Observação
                </strong>
                <?php echo nl2br($plano['observacao']); ?>
            </div>
            <?php endif; ?>

            <?php if($caracteristicas->num_rows > 0): ?>
            <div class="view-caracteristicas">
                <h3>
                    <i class="fas fa-list"></i>
                    Características do Plano
                </h3>
                <ul>
                    <?php while($carac = $caracteristicas->fetch_assoc()): ?>
                    <li>
                        <i class="fas <?php echo $carac['icone']; ?>"></i>
                        <?php echo $carac['caracteristica']; ?>
                    </li>
                    <?php endwhile; ?>
                </ul>
            </div>
            <?php endif; ?>

            <?php if($plano['link_whatsapp'] || $plano['mensagem_whatsapp']): ?>
            <div class="view-caracteristicas" style="background: #25D36610;">
                <h3 style="color: #25D366;">
                    <i class="fab fa-whatsapp"></i>
                    Configurações WhatsApp
                </h3>
                <?php if($plano['link_whatsapp']): ?>
                <p style="margin-bottom: 10px;">
                    <strong>Link:</strong> 
                    <a href="<?php echo $plano['link_whatsapp']; ?>" target="_blank" style="color: #25D366;">
                        <?php echo $plano['link_whatsapp']; ?>
                    </a>
                </p>
                <?php endif; ?>
                <?php if($plano['mensagem_whatsapp']): ?>
                <p>
                    <strong>Mensagem:</strong><br>
                    <small><?php echo $plano['mensagem_whatsapp']; ?></small>
                </p>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <div class="view-actions">
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
                <a href="editar.php?id=<?php echo $plano['id']; ?>" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Editar Plano
                </a>
            </div>
        </div>
    </div>
</div>

<script>
// Theme Toggle
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