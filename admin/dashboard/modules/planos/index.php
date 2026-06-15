<?php
$page_title = 'Gerenciar Planos';
require_once '../../includes/header.php';
require_once '../../includes/menu.php';
require_once 'config.php';

// Filtros
$filtro_perfil = isset($_GET['perfil']) ? limparInput($_GET['perfil']) : '';
$filtro_categoria = isset($_GET['categoria']) ? limparInput($_GET['categoria']) : '';
$filtro_ativo = isset($_GET['ativo']) ? limparInput($_GET['ativo']) : '';

// Monta a query com filtros
$sql = "SELECT p.*, c.nome as categoria_nome, c.icone as categoria_icone,
        (SELECT COUNT(*) FROM planos_caracteristicas WHERE plano_id = p.id) as total_caracteristicas
        FROM planos p
        LEFT JOIN planos_categorias c ON p.categoria_id = c.id
        WHERE 1=1";

$params = [];
$types = "";

if(!empty($filtro_perfil)) {
    if($filtro_perfil == 'ambos') {
        $sql .= " AND (p.perfil = 'ambos' OR p.perfil = ?)";
    } else {
        $sql .= " AND (p.perfil = ? OR p.perfil = 'ambos')";
    }
    $params[] = $filtro_perfil;
    $types .= "s";
}

if(!empty($filtro_categoria)) {
    $sql .= " AND p.categoria_id = ?";
    $params[] = $filtro_categoria;
    $types .= "i";
}

if($filtro_ativo !== '') {
    $sql .= " AND p.ativo = ?";
    $params[] = $filtro_ativo;
    $types .= "i";
}

$sql .= " ORDER BY p.ordem, p.nome";

$stmt = $conn->prepare($sql);
if(!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$planos = $stmt->get_result();

// Busca categorias para o filtro
$categorias = getCategorias();
$perfis = getPerfis();
?>

<style>
/* ESTILOS IGUAIS AO QUE EU MANDEI ANTES - MANTIVE IGUAL */
.planos-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.filtros-box {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 30px;
}

.filtros-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
}

.filtro-item {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.filtro-item label {
    font-size: 0.85rem;
    color: var(--text-muted);
}

.filtro-item select, .filtro-item input {
    padding: 10px;
    border-radius: 8px;
    border: 1px solid var(--border);
    background: var(--bg-secondary);
    color: var(--text-primary);
}

.btn-filtro {
    padding: 10px 20px;
    border-radius: 8px;
    border: none;
    background: var(--accent);
    color: white;
    cursor: pointer;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-filtro:hover {
    background: #2563eb;
}

.btn-limpar {
    background: var(--hover);
    color: var(--text-secondary);
    border: 1px solid var(--border);
}

.planos-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 25px;
    margin-top: 20px;
}

.plano-card {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 16px;
    overflow: hidden;
    transition: all 0.3s ease;
}

.plano-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);
    border-color: var(--accent);
}

.plano-card.destaque {
    border: 2px solid var(--accent);
    position: relative;
}

.plano-card.destaque::before {
    content: '⭐ DESTAQUE';
    position: absolute;
    top: 12px;
    right: 12px;
    background: var(--accent);
    color: white;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 0.7rem;
    font-weight: 600;
    z-index: 1;
}

.plano-header {
    padding: 20px;
    background: linear-gradient(135deg, var(--accent-light) 0%, transparent 100%);
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    gap: 15px;
}

.plano-icone {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    background: var(--accent);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.plano-titulo {
    flex: 1;
}

.plano-titulo h3 {
    font-size: 1.2rem;
    margin-bottom: 5px;
    color: var(--text-primary);
}

.plano-categoria {
    font-size: 0.8rem;
    color: var(--text-muted);
    display: flex;
    align-items: center;
    gap: 5px;
}

.plano-categoria i {
    color: var(--accent);
}

.plano-body {
    padding: 20px;
}

.plano-preco {
    font-size: 1.8rem;
    font-weight: 700;
    color: var(--accent);
    margin-bottom: 10px;
}

.plano-preco small {
    font-size: 0.9rem;
    color: var(--text-muted);
    font-weight: 400;
}

.plano-descricao {
    color: var(--text-secondary);
    font-size: 0.9rem;
    margin-bottom: 15px;
    line-height: 1.5;
}

.plano-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 15px;
    font-size: 0.85rem;
}

.meta-item {
    background: var(--hover);
    padding: 5px 10px;
    border-radius: 20px;
    color: var(--text-secondary);
    display: flex;
    align-items: center;
    gap: 5px;
}

.meta-item i {
    color: var(--accent);
}

.plano-badge {
    display: inline-block;
    background: var(--accent-light);
    color: var(--accent);
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    margin-bottom: 15px;
}

.plano-caracteristicas {
    margin: 15px 0;
    max-height: 200px;
    overflow-y: auto;
    padding-right: 10px;
}

.caracteristica-item {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    font-size: 0.85rem;
    color: var(--text-secondary);
    margin-bottom: 8px;
    padding: 4px 0;
    border-bottom: 1px dashed var(--border);
}

.caracteristica-item i {
    color: #22c55e;
    font-size: 0.8rem;
    margin-top: 3px;
}

.plano-footer {
    padding: 20px;
    border-top: 1px solid var(--border);
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.btn-card {
    padding: 8px 16px;
    border-radius: 8px;
    border: 1px solid var(--border);
    background: var(--bg-secondary);
    color: var(--text-secondary);
    text-decoration: none;
    font-size: 0.9rem;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s ease;
    flex: 1;
    justify-content: center;
}

.btn-card:hover {
    background: var(--hover);
    color: var(--accent);
    border-color: var(--accent);
}

.btn-card.editar {
    background: var(--accent);
    color: white;
    border: none;
}

.btn-card.editar:hover {
    background: #2563eb;
}

.btn-card.visualizar {
    background: var(--accent-light);
    color: var(--accent);
}

.btn-card.excluir:hover {
    background: #ef4444;
    color: white;
    border-color: #ef4444;
}

.status-badge {
    padding: 4px 8px;
    border-radius: 20px;
    font-size: 0.7rem;
    font-weight: 600;
}

.status-ativo {
    background: #22c55e20;
    color: #22c55e;
}

.status-inativo {
    background: #ef444420;
    color: #ef4444;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 16px;
    grid-column: 1 / -1;
}

.empty-state i {
    font-size: 4rem;
    color: var(--text-muted);
    margin-bottom: 20px;
}

.empty-state h3 {
    font-size: 1.5rem;
    margin-bottom: 10px;
    color: var(--text-primary);
}

.empty-state p {
    color: var(--text-secondary);
    margin-bottom: 20px;
}
</style>

<div class="main-content" id="mainContent">
    <div class="top-bar">
        <h1 class="page-title">
            <i class="fas fa-crown" style="margin-right: 10px;"></i>
            <?php echo $page_title; ?>
        </h1>
        
        <div class="top-bar-actions">
            <div class="theme-toggle" id="themeToggle">
                <i class="fas <?php echo $tema == 'dark' ? 'fa-moon' : 'fa-sun'; ?>" id="themeIcon"></i>
            </div>
            
            <a href="criar.php" class="btn btn-primary" style="text-decoration: none; padding: 10px 20px;">
                <i class="fas fa-plus"></i> Novo Plano
            </a>
        </div>
    </div>

    <div class="content-area">
        <!-- Filtros -->
        <div class="filtros-box">
            <form method="GET" class="filtros-grid">
                <div class="filtro-item">
                    <label><i class="fas fa-user-tie"></i> Perfil</label>
                    <select name="perfil">
                        <option value="">Todos os perfis</option>
                        <?php foreach($perfis as $key => $nome): ?>
                        <option value="<?php echo $key; ?>" <?php echo $filtro_perfil == $key ? 'selected' : ''; ?>>
                            <?php echo $nome; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filtro-item">
                    <label><i class="fas fa-tag"></i> Categoria</label>
                    <select name="categoria">
                        <option value="">Todas as categorias</option>
                        <?php foreach($categorias as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo $filtro_categoria == $cat['id'] ? 'selected' : ''; ?>>
                            <?php echo $cat['nome']; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filtro-item">
                    <label><i class="fas fa-circle"></i> Status</label>
                    <select name="ativo">
                        <option value="">Todos</option>
                        <option value="1" <?php echo $filtro_ativo === '1' ? 'selected' : ''; ?>>Ativos</option>
                        <option value="0" <?php echo $filtro_ativo === '0' ? 'selected' : ''; ?>>Inativos</option>
                    </select>
                </div>
                
                <div class="filtro-item" style="display: flex; flex-direction: row; align-items: flex-end; gap: 10px;">
                    <button type="submit" class="btn-filtro">
                        <i class="fas fa-filter"></i> Filtrar
                    </button>
                    <a href="index.php" class="btn-filtro btn-limpar">
                        <i class="fas fa-times"></i> Limpar
                    </a>
                </div>
            </form>
        </div>

        <!-- Grid de Planos -->
        <div class="planos-grid">
            <?php if($planos->num_rows == 0): ?>
            <div class="empty-state">
                <i class="fas fa-box-open"></i>
                <h3>Nenhum plano encontrado</h3>
                <p>Comece criando seu primeiro plano personalizado.</p>
                <a href="criar.php" class="btn btn-primary" style="text-decoration: none; padding: 12px 30px;">
                    <i class="fas fa-plus"></i> Criar Primeiro Plano
                </a>
            </div>
            <?php else: ?>
                <?php while($plano = $planos->fetch_assoc()): 
                    // Busca características do plano
                    $stmt_carac = $conn->prepare("SELECT * FROM planos_caracteristicas WHERE plano_id = ? ORDER BY ordem");
                    $stmt_carac->bind_param("i", $plano['id']);
                    $stmt_carac->execute();
                    $caracteristicas = $stmt_carac->get_result();
                ?>
                <div class="plano-card <?php echo $plano['destaque'] ? 'destaque' : ''; ?>">
                    <div class="plano-header">
                        <div class="plano-icone">
                            <i class="fas <?php echo $plano['categoria_icone'] ?? 'fa-cube'; ?>"></i>
                        </div>
                        <div class="plano-titulo">
                            <h3><?php echo htmlspecialchars($plano['nome']); ?></h3>
                            <div class="plano-categoria">
                                <i class="fas <?php echo $plano['categoria_icone'] ?? 'fa-tag'; ?>"></i>
                                <?php echo $plano['categoria_nome'] ?? 'Sem categoria'; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="plano-body">
                        <div class="plano-preco">
                            <?php echo formatarPreco($plano['preco']); ?>
                            <small>/<?php echo $plano['periodo']; ?></small>
                        </div>
                        
                        <?php if(!empty($plano['descricao_curta'])): ?>
                        <div class="plano-descricao">
                            <?php echo htmlspecialchars($plano['descricao_curta']); ?>
                        </div>
                        <?php endif; ?>
                        
                        <div class="plano-meta">
                            <span class="meta-item">
                                <i class="fas fa-user-tie"></i>
                                <?php echo $perfis[$plano['perfil']] ?? $plano['perfil']; ?>
                            </span>
                            <span class="meta-item">
                                <i class="fas fa-clock"></i>
                                <?php echo $plano['prazo_entrega'] ?? 'Sob consulta'; ?>
                            </span>
                            <span class="meta-item">
                                <i class="fas fa-list"></i>
                                <?php echo $plano['total_caracteristicas']; ?> itens
                            </span>
                            <span class="meta-item">
                                <i class="fas <?php echo $plano['ativo'] ? 'fa-check-circle' : 'fa-times-circle'; ?>" 
                                   style="color: <?php echo $plano['ativo'] ? '#22c55e' : '#ef4444'; ?>"></i>
                                <?php echo $plano['ativo'] ? 'Ativo' : 'Inativo'; ?>
                            </span>
                        </div>
                        
                        <?php if(!empty($plano['badge_text'])): ?>
                        <div class="plano-badge">
                            <i class="fas fa-tag"></i>
                            <?php echo htmlspecialchars($plano['badge_text']); ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if($caracteristicas->num_rows > 0): ?>
                        <div class="plano-caracteristicas">
                            <?php while($carac = $caracteristicas->fetch_assoc()): ?>
                            <div class="caracteristica-item">
                                <i class="fas <?php echo $carac['icone'] ?? 'fa-check-circle'; ?>"></i>
                                <span><?php echo htmlspecialchars($carac['caracteristica']); ?></span>
                            </div>
                            <?php endwhile; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="plano-footer">
                        <a href="visualizar.php?id=<?php echo $plano['id']; ?>" class="btn-card visualizar">
                            <i class="fas fa-eye"></i> Ver
                        </a>
                        <a href="editar.php?id=<?php echo $plano['id']; ?>" class="btn-card editar">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                        <a href="excluir.php?id=<?php echo $plano['id']; ?>" class="btn-card excluir" onclick="return confirm('Tem certeza? Isso vai apagar TODAS as características também!')">
                            <i class="fas fa-trash"></i> Excluir
                        </a>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php endif; ?>
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