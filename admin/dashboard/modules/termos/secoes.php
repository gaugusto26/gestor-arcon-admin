<?php
$page_title = 'Gerenciar Seções';
require_once '../../includes/header.php';
require_once '../../includes/menu.php';
require_once 'config.php';

// Verifica se tem política ativa
$termos_ativa = getTermosAtivos();
if(!$termos_ativa) {
    $_SESSION['mensagem'] = [
        'tipo' => 'erro',
        'texto' => 'Crie uma política de privacidade antes de gerenciar as seções.'
    ];
    header('Location: index.php');
    exit;
}

// Processa ações
if(isset($_GET['acao'])) {
    $id = (int)$_GET['id'];
    
    if($_GET['acao'] == 'excluir') {
        $conn->query("DELETE FROM termos_secoes WHERE id = $id");
        $_SESSION['mensagem'] = ['tipo' => 'sucesso', 'texto' => 'Seção excluída com sucesso!'];
        header('Location: secoes.php');
        exit;
    }
    
    if($_GET['acao'] == 'up') {
        // Move para cima (diminui ordem)
        $secao = $conn->query("SELECT ordem FROM termos_secoes WHERE id = $id")->fetch_assoc();
        $nova_ordem = $secao['ordem'] - 1;
        $conn->query("UPDATE termos_secoes SET ordem = ordem + 1 WHERE ordem = $nova_ordem AND termos_id = {$termos_ativa['id']}");
        $conn->query("UPDATE termos_secoes SET ordem = $nova_ordem WHERE id = $id");
        header('Location: secoes.php');
        exit;
    }
    
    if($_GET['acao'] == 'down') {
        // Move para baixo (aumenta ordem)
        $secao = $conn->query("SELECT ordem FROM termos_secoes WHERE id = $id")->fetch_assoc();
        $nova_ordem = $secao['ordem'] + 1;
        $conn->query("UPDATE termos_secoes SET ordem = ordem - 1 WHERE ordem = $nova_ordem AND termos_id = {$termos_ativa['id']}");
        $conn->query("UPDATE termos_secoes SET ordem = $nova_ordem WHERE id = $id");
        header('Location: secoes.php');
        exit;
    }
}

// Busca seções da política ativa
$secoes = $conn->query("
    SELECT * FROM termos_secoes 
    WHERE termos_id = {$termos_ativa['id']} 
    ORDER BY ordem
");

// Ícones disponíveis
$icones_disponiveis = [
    'fa-circle' => 'Círculo',
    'fa-shield-alt' => 'Escudo',
    'fa-lock' => 'Cadeado',
    'fa-eye' => 'Olho',
    'fa-database' => 'Banco de dados',
    'fa-user-secret' => 'Privacidade',
    'fa-file-contract' => 'Contrato',
    'fa-gavel' => 'Lei',
    'fa-handshake' => 'Acordo',
    'fa-check-circle' => 'Check',
    'fa-info-circle' => 'Informação',
    'fa-exclamation-triangle' => 'Aviso',
    'fa-cookie-bite' => 'Cookie',
    'fa-share-alt' => 'Compartilhamento',
    'fa-trash-alt' => 'Exclusão',
    'fa-envelope' => 'Contato',
];
?>

<style>
.secoes-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    flex-wrap: wrap;
    gap: 20px;
}

.secoes-header h2 {
    font-size: 1.5rem;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 10px;
}

.secoes-header h2 i {
    color: #4361ee;
    width: 45px;
    height: 45px;
    background: #f8faff;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.termos-info {
    background: linear-gradient(135deg, #f8faff 0%, #ffffff 100%);
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: 20px 25px;
    margin-bottom: 30px;
    display: flex;
    align-items: center;
    gap: 15px;
    flex-wrap: wrap;
    box-shadow: 0 10px 30px rgba(0,0,0,0.02);
}

.termos-info i {
    width: 48px;
    height: 48px;
    background: rgba(67, 97, 238, 0.1);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #4361ee;
    font-size: 1.3rem;
}

.termos-info .info-content {
    flex: 1;
}

.termos-info h3 {
    font-size: 1.1rem;
    margin-bottom: 5px;
    color: var(--text-primary);
}

.termos-info p {
    color: var(--text-muted);
    font-size: 0.9rem;
}

.termos-info .versao-badge {
    background: #4361ee;
    color: white;
    padding: 6px 16px;
    border-radius: 50px;
    font-size: 0.85rem;
    font-weight: 600;
}

.secoes-grid {
    display: flex;
    flex-direction: column;
    gap: 15px;
    margin-top: 20px;
}

.secao-card {
    background: #ffffff;
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 20px 25px;
    display: flex;
    align-items: center;
    gap: 20px;
    transition: all 0.3s ease;
    box-shadow: 0 5px 15px rgba(0,0,0,0.02);
}

.secao-card:hover {
    transform: translateX(5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    border-color: #4361ee;
}

.secao-ordem {
    width: 50px;
    height: 50px;
    background: #f8faff;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.2rem;
    color: #4361ee;
    border: 1px solid var(--border);
}

.secao-icon {
    width: 50px;
    height: 50px;
    background: rgba(67, 97, 238, 0.1);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #4361ee;
    font-size: 1.3rem;
}

.secao-content {
    flex: 1;
}

.secao-content h4 {
    font-size: 1.1rem;
    margin-bottom: 5px;
    color: var(--text-primary);
}

.secao-content p {
    color: var(--text-muted);
    font-size: 0.9rem;
    max-width: 600px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.secao-actions {
    display: flex;
    gap: 8px;
}

.btn {
    padding: 10px 18px;
    border-radius: 10px;
    font-weight: 500;
    font-size: 0.9rem;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s ease;
    border: none;
    cursor: pointer;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    box-shadow: 0 10px 20px rgba(102,126,234,0.2);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 15px 30px rgba(102,126,234,0.3);
}

.btn-secondary {
    background: #f8faff;
    color: #4361ee;
    border: 1px solid var(--border);
}

.btn-secondary:hover {
    background: #ffffff;
    border-color: #4361ee;
}

.btn-icon {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    border: 1px solid var(--border);
    background: #ffffff;
    color: var(--text-secondary);
    cursor: pointer;
    transition: all 0.2s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.btn-icon:hover {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-color: transparent;
    transform: translateY(-2px);
}

.btn-icon.up:hover { background: #f97316; color: white; }
.btn-icon.down:hover { background: #8b5cf6; color: white; }
.btn-icon.edit:hover { background: #4361ee; color: white; }
.btn-icon.delete:hover { background: #ef4444; color: white; }

.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 9999;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background: #ffffff;
    border-radius: 24px;
    max-width: 600px;
    width: 90%;
    max-height: 80vh;
    overflow-y: auto;
    box-shadow: 0 20px 40px rgba(0,0,0,0.2);
}

.modal-header {
    padding: 25px;
    border-bottom: 1px solid var(--border);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    font-size: 1.3rem;
    color: var(--text-primary);
}

.modal-close {
    font-size: 1.5rem;
    cursor: pointer;
    color: var(--text-muted);
    transition: all 0.2s ease;
}

.modal-close:hover {
    color: #ef4444;
}

.modal-body {
    padding: 25px;
}

.modal-footer {
    padding: 20px 25px;
    border-top: 1px solid var(--border);
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    color: var(--text-primary);
    font-weight: 500;
}

.form-group label i {
    color: #4361ee;
    margin-right: 8px;
}

.form-control {
    width: 100%;
    padding: 12px 15px;
    border: 1px solid var(--border);
    border-radius: 10px;
    background: #ffffff;
    color: var(--text-primary);
    font-size: 0.95rem;
    transition: all 0.2s ease;
}

.form-control:focus {
    outline: none;
    border-color: #4361ee;
    box-shadow: 0 0 0 3px rgba(67,97,238,0.1);
}

textarea.form-control {
    min-height: 100px;
    resize: vertical;
}

.icones-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 10px;
    margin-top: 10px;
}

.icone-item {
    padding: 10px;
    border: 1px solid var(--border);
    border-radius: 8px;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s ease;
}

.icone-item:hover {
    background: #f8faff;
    border-color: #4361ee;
}

.icone-item.selected {
    background: #4361ee;
    color: white;
    border-color: #4361ee;
}

.icone-item i {
    font-size: 1.3rem;
    margin-bottom: 5px;
    display: block;
}

.icone-item span {
    font-size: 0.7rem;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: #ffffff;
    border: 1px solid var(--border);
    border-radius: 20px;
}

.empty-state i {
    font-size: 3rem;
    color: var(--text-muted);
    margin-bottom: 15px;
    opacity: 0.5;
}

.empty-state h3 {
    font-size: 1.3rem;
    margin-bottom: 10px;
    color: var(--text-primary);
}

.empty-state p {
    color: var(--text-muted);
    margin-bottom: 25px;
}

.alert {
    padding: 15px 20px;
    border-radius: 12px;
    margin-bottom: 20px;
}

.alert-success {
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
    border: 1px solid #10b981;
}

.alert-error {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
    border: 1px solid #ef4444;
}
</style>

<div class="main-content" id="mainContent">
    <div class="top-bar">
        <h1 class="page-title">
            <i class="fas fa-list" style="color: #4361ee; margin-right: 10px;"></i>
            Gerenciar Seções
        </h1>
        
        <div class="top-bar-actions">
            <div class="theme-toggle" id="themeToggle">
                <i class="fas <?php echo $tema == 'dark' ? 'fa-moon' : 'fa-sun'; ?>" id="themeIcon"></i>
            </div>
        </div>
    </div>

    <div class="content-area">
        <!-- Mensagem de sucesso/erro -->
        <?php if(isset($_SESSION['mensagem'])): ?>
        <div class="alert alert-<?php echo $_SESSION['mensagem']['tipo']; ?>">
            <i class="fas <?php echo $_SESSION['mensagem']['tipo'] == 'sucesso' ? 'fa-check-circle' : 'fa-exclamation-triangle'; ?>"></i>
            <?php 
            echo $_SESSION['mensagem']['texto'];
            unset($_SESSION['mensagem']);
            ?>
        </div>
        <?php endif; ?>

        <!-- Info da Política Ativa -->
        <div class="termos-info">
            <i class="fas fa-shield-alt"></i>
            <div class="info-content">
                <h3>Política: <?php echo $termos_ativa['titulo']; ?></h3>
                <p>Adicione seções para organizar melhor o conteúdo da sua política de privacidade</p>
            </div>
            <span class="versao-badge">v<?php echo $termos_ativa['versao']; ?></span>
        </div>

        <!-- Header -->
        <div class="secoes-header">
            <h2>
                <i class="fas fa-layer-group"></i>
                Seções da Política
            </h2>
            <button class="btn btn-primary" onclick="abrirModalCriar()">
                <i class="fas fa-plus"></i> Nova Seção
            </button>
        </div>

        <!-- Lista de Seções -->
        <?php if($secoes->num_rows == 0): ?>
        <div class="empty-state">
            <i class="fas fa-folder-open"></i>
            <h3>Nenhuma seção criada</h3>
            <p>Comece adicionando seções para organizar sua política de privacidade</p>
            <button class="btn btn-primary" onclick="abrirModalCriar()">
                <i class="fas fa-plus"></i> Adicionar Primeira Seção
            </button>
        </div>
        <?php else: ?>
        <div class="secoes-grid">
            <?php 
            $total_secoes = $secoes->num_rows;
            while($secao = $secoes->fetch_assoc()): 
            ?>
            <div class="secao-card">
                <div class="secao-ordem">#<?php echo $secao['ordem'] + 1; ?></div>
                
                <div class="secao-icon">
                    <i class="fas <?php echo $secao['icone']; ?>"></i>
                </div>
                
                <div class="secao-content">
                    <h4><?php echo $secao['titulo']; ?></h4>
                    <p><?php echo strip_tags(substr($secao['conteudo'], 0, 150)) . (strlen($secao['conteudo']) > 150 ? '...' : ''); ?></p>
                </div>
                
                <div class="secao-actions">
                    <?php if($secao['ordem'] > 0): ?>
                    <a href="?acao=up&id=<?php echo $secao['id']; ?>" class="btn-icon up" title="Mover para cima">
                        <i class="fas fa-arrow-up"></i>
                    </a>
                    <?php endif; ?>
                    
                    <?php if($secao['ordem'] < $total_secoes - 1): ?>
                    <a href="?acao=down&id=<?php echo $secao['id']; ?>" class="btn-icon down" title="Mover para baixo">
                        <i class="fas fa-arrow-down"></i>
                    </a>
                    <?php endif; ?>
                    
                    <button class="btn-icon edit" title="Editar" onclick="abrirModalEditar(<?php echo htmlspecialchars(json_encode($secao)); ?>)">
                        <i class="fas fa-edit"></i>
                    </button>
                    
                    <a href="?acao=excluir&id=<?php echo $secao['id']; ?>" class="btn-icon delete" title="Excluir" onclick="return confirm('Tem certeza que deseja excluir esta seção?')">
                        <i class="fas fa-trash"></i>
                    </a>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <?php endif; ?>

        <!-- Dicas -->
        <div style="margin-top: 40px; background: #f8faff; border-radius: 16px; padding: 25px;">
            <h3 style="display: flex; align-items: center; gap: 10px; margin-bottom: 15px; color: var(--text-primary);">
                <i class="fas fa-lightbulb" style="color: #f97316;"></i>
                Dicas para organizar suas seções
            </h3>
            <ul style="color: var(--text-secondary); line-height: 1.8; padding-left: 20px;">
                <li>Organize as seções em ordem lógica de leitura</li>
                <li>Use títulos claros e objetivos para cada seção</li>
                <li>Mantenha o conteúdo conciso e fácil de entender</li>
                <li>Utilize ícones para facilitar a identificação visual</li>
                <li>Agrupe assuntos relacionados na mesma seção</li>
            </ul>
        </div>
    </div>
</div>

<!-- Modal Criar/Editar Seção -->
<div id="secaoModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Nova Seção</h3>
            <span class="modal-close" onclick="fecharModal()">&times;</span>
        </div>
        <form method="POST" action="salvar_secao.php" id="secaoForm">
            <div class="modal-body">
                <input type="hidden" name="id" id="secaoId">
                <input type="hidden" name="termos_id" value="<?php echo $termos_ativa['id']; ?>">
                
                <div class="form-group">
                    <label><i class="fas fa-heading"></i> Título da Seção</label>
                    <input type="text" name="titulo" id="secaoTitulo" class="form-control" required placeholder="Ex: Informações que coletamos">
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-palette"></i> Ícone</label>
                    <select name="icone" id="secaoIcone" class="form-control">
                        <?php foreach($icones_disponiveis as $valor => $nome): ?>
                        <option value="<?php echo $valor; ?>"><?php echo $nome; ?></option>
                        <?php endforeach; ?>
                    </select>
                    
                    <div class="icones-grid" id="iconesGrid">
                        <?php foreach($icones_disponiveis as $valor => $nome): ?>
                        <div class="icone-item" onclick="selecionarIcone('<?php echo $valor; ?>')">
                            <i class="fas <?php echo $valor; ?>"></i>
                            <span><?php echo $nome; ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-align-left"></i> Conteúdo</label>
                    <textarea name="conteudo" id="secaoConteudo" class="form-control" required rows="5" placeholder="Digite o conteúdo desta seção..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="fecharModal()">
                    Cancelar
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Salvar Seção
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function abrirModalCriar() {
    document.getElementById('modalTitle').textContent = 'Nova Seção';
    document.getElementById('secaoId').value = '';
    document.getElementById('secaoTitulo').value = '';
    document.getElementById('secaoConteudo').value = '';
    document.getElementById('secaoIcone').value = 'fa-circle';
    document.querySelectorAll('.icone-item').forEach(item => item.classList.remove('selected'));
    document.getElementById('secaoModal').style.display = 'flex';
}

function abrirModalEditar(secao) {
    document.getElementById('modalTitle').textContent = 'Editar Seção';
    document.getElementById('secaoId').value = secao.id;
    document.getElementById('secaoTitulo').value = secao.titulo;
    document.getElementById('secaoConteudo').value = secao.conteudo;
    document.getElementById('secaoIcone').value = secao.icone;
    
    document.querySelectorAll('.icone-item').forEach(item => item.classList.remove('selected'));
    document.querySelector(`.icone-item i.fa-${secao.icone.replace('fa-', '')}`)?.parentElement.classList.add('selected');
    
    document.getElementById('secaoModal').style.display = 'flex';
}

function selecionarIcone(icone) {
    document.getElementById('secaoIcone').value = icone;
    document.querySelectorAll('.icone-item').forEach(item => item.classList.remove('selected'));
    event.currentTarget.classList.add('selected');
}

function fecharModal() {
    document.getElementById('secaoModal').style.display = 'none';
}

// Fecha modal ao clicar fora
window.onclick = function(event) {
    const modal = document.getElementById('secaoModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}

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