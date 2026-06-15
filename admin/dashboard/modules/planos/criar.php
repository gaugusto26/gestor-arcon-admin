<?php
$page_title = 'Criar Novo Plano';
require_once '../../includes/header.php';
require_once '../../includes/menu.php';
require_once 'config.php';

// Busca categorias para o select
$categorias = getCategorias();
$perfis = getPerfis();
$periodos = getPeriodos();

// Processa o formulário
$erros = [];
$sucesso = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validações
    if(empty($_POST['nome'])) {
        $erros[] = 'Nome do plano é obrigatório';
    }
    if(empty($_POST['preco']) || !is_numeric($_POST['preco'])) {
        $erros[] = 'Preço inválido';
    }
    
    if(empty($erros)) {
        // Gera slug
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $_POST['nome'])));
        
        // Insere plano
        $sql = "INSERT INTO planos (
            categoria_id, nome, slug, descricao_curta, descricao_completa,
            preco, periodo, destaque, badge_text, prazo_entrega, observacao,
            perfil, link_whatsapp, mensagem_whatsapp, ativo, ordem
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $destaque = isset($_POST['destaque']) ? 1 : 0;
        $ativo = isset($_POST['ativo']) ? 1 : 1;
        
        $stmt->bind_param(
            "issssdssisssssii",
            $_POST['categoria_id'],
            $_POST['nome'],
            $slug,
            $_POST['descricao_curta'],
            $_POST['descricao_completa'],
            $_POST['preco'],
            $_POST['periodo'],
            $destaque,
            $_POST['badge_text'],
            $_POST['prazo_entrega'],
            $_POST['observacao'],
            $_POST['perfil'],
            $_POST['link_whatsapp'],
            $_POST['mensagem_whatsapp'],
            $ativo,
            $_POST['ordem']
        );
        
        if($stmt->execute()) {
            $plano_id = $conn->insert_id;
            
            // Insere características
            if(!empty($_POST['caracteristicas'])) {
                $stmt_carac = $conn->prepare("INSERT INTO planos_caracteristicas (plano_id, caracteristica, icone, ordem) VALUES (?, ?, ?, ?)");
                
                foreach($_POST['caracteristicas'] as $ordem => $carac) {
                    if(!empty($carac['texto'])) {
                        $icone = !empty($carac['icone']) ? $carac['icone'] : 'fa-check-circle';
                        $stmt_carac->bind_param("issi", $plano_id, $carac['texto'], $icone, $ordem);
                        $stmt_carac->execute();
                    }
                }
            }
            
            // Log
            registrarLog($conn, $_SESSION['admin_id'], "Criou plano: {$_POST['nome']}");
            
            $sucesso = 'Plano criado com sucesso!';
            echo "<script>setTimeout(() => { window.location.href = 'index.php'; }, 2000);</script>";
        } else {
            $erros[] = 'Erro ao criar plano: ' . $conn->error;
        }
    }
}
?>

<style>
.form-container {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 30px;
    max-width: 1000px;
    margin: 0 auto;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group.full-width {
    grid-column: span 2;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    color: var(--text-primary);
    font-weight: 500;
    font-size: 0.9rem;
}

.form-group label i {
    color: var(--accent);
    margin-right: 5px;
}

.form-control {
    width: 100%;
    padding: 12px 15px;
    border: 1px solid var(--border);
    border-radius: 8px;
    background: var(--bg-secondary);
    color: var(--text-primary);
    font-size: 0.95rem;
    transition: all 0.2s ease;
}

.form-control:focus {
    outline: none;
    border-color: var(--accent);
    box-shadow: 0 0 0 3px var(--accent-light);
}

textarea.form-control {
    min-height: 100px;
    resize: vertical;
}

.form-check {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 15px;
}

.form-check input[type="checkbox"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
}

.form-check label {
    margin-bottom: 0;
    cursor: pointer;
}

.caracteristicas-container {
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 20px;
    background: var(--bg-secondary);
    margin-bottom: 20px;
}

.caracteristica-row {
    display: grid;
    grid-template-columns: 1fr auto auto;
    gap: 10px;
    margin-bottom: 10px;
    align-items: center;
}

.caracteristica-row input[type="text"] {
    padding: 8px 12px;
    border: 1px solid var(--border);
    border-radius: 6px;
    background: var(--bg-primary);
    color: var(--text-primary);
}

.btn-add {
    background: var(--accent-light);
    color: var(--accent);
    border: 1px dashed var(--accent);
    padding: 10px;
    border-radius: 8px;
    width: 100%;
    cursor: pointer;
    font-size: 0.9rem;
    margin-top: 10px;
    transition: all 0.2s ease;
}

.btn-add:hover {
    background: var(--accent);
    color: white;
}

.btn-remove {
    background: #ef444420;
    color: #ef4444;
    border: none;
    padding: 8px 12px;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-remove:hover {
    background: #ef4444;
    color: white;
}

.alert {
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.alert-success {
    background: #22c55e20;
    color: #22c55e;
    border: 1px solid #22c55e;
}

.alert-danger {
    background: #ef444420;
    color: #ef4444;
    border: 1px solid #ef4444;
}

.form-actions {
    display: flex;
    gap: 15px;
    justify-content: flex-end;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid var(--border);
}

.btn {
    padding: 12px 30px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    border: none;
    font-size: 0.95rem;
    text-decoration: none;
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

small {
    color: var(--text-muted);
    font-size: 0.8rem;
    display: block;
    margin-top: 5px;
}

.icone-sugestoes {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
    margin-top: 5px;
}

.icone-sugestao {
    padding: 4px 8px;
    background: var(--hover);
    border: 1px solid var(--border);
    border-radius: 4px;
    font-size: 0.8rem;
    cursor: pointer;
    transition: all 0.2s ease;
}

.icone-sugestao:hover {
    background: var(--accent);
    color: white;
    border-color: var(--accent);
}
</style>

<div class="main-content" id="mainContent">
    <div class="top-bar">
        <h1 class="page-title">
            <i class="fas fa-plus-circle" style="margin-right: 10px;"></i>
            <?php echo $page_title; ?>
        </h1>
    </div>

    <div class="content-area">
        <div class="form-container">
            <?php if(!empty($erros)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php foreach($erros as $erro): ?>
                        <div><?php echo $erro; ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <?php if($sucesso): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $sucesso; ?> Redirecionando...
                </div>
            <?php endif; ?>
            
            <form method="POST" id="formPlano">
                <div class="form-grid">
                    <!-- Nome -->
                    <div class="form-group">
                        <label><i class="fas fa-tag"></i> Nome do Plano *</label>
                        <input type="text" name="nome" class="form-control" required value="<?php echo $_POST['nome'] ?? ''; ?>">
                    </div>
                    
                    <!-- Categoria -->
                    <div class="form-group">
                        <label><i class="fas fa-folder"></i> Categoria</label>
                        <select name="categoria_id" class="form-control">
                            <option value="">Selecione uma categoria</option>
                            <?php foreach($categorias as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo ($_POST['categoria_id'] ?? '') == $cat['id'] ? 'selected' : ''; ?>>
                                <i class="fas <?php echo $cat['icone']; ?>"></i> <?php echo $cat['nome']; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Preço -->
                    <div class="form-group">
                        <label><i class="fas fa-dollar-sign"></i> Preço *</label>
                        <input type="number" step="0.01" name="preco" class="form-control" required value="<?php echo $_POST['preco'] ?? ''; ?>">
                    </div>
                    
                    <!-- Período -->
                    <div class="form-group">
                        <label><i class="fas fa-clock"></i> Período</label>
                        <select name="periodo" class="form-control">
                            <?php foreach($periodos as $key => $nome): ?>
                            <option value="<?php echo $key; ?>" <?php echo ($_POST['periodo'] ?? 'permanente') == $key ? 'selected' : ''; ?>>
                                <?php echo $nome; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Perfil -->
                    <div class="form-group">
                        <label><i class="fas fa-user-tie"></i> Perfil</label>
                        <select name="perfil" class="form-control">
                            <?php foreach($perfis as $key => $nome): ?>
                            <option value="<?php echo $key; ?>" <?php echo ($_POST['perfil'] ?? 'ambos') == $key ? 'selected' : ''; ?>>
                                <?php echo $nome; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Ordem -->
                    <div class="form-group">
                        <label><i class="fas fa-sort"></i> Ordem</label>
                        <input type="number" name="ordem" class="form-control" value="<?php echo $_POST['ordem'] ?? 0; ?>">
                        <small>Menor número aparece primeiro</small>
                    </div>
                    
                    <!-- Prazo de entrega -->
                    <div class="form-group">
                        <label><i class="fas fa-truck"></i> Prazo de entrega</label>
                        <input type="text" name="prazo_entrega" class="form-control" value="<?php echo $_POST['prazo_entrega'] ?? ''; ?>" placeholder="Ex: 5 a 7 dias úteis">
                    </div>
                    
                    <!-- Badge Text -->
                    <div class="form-group">
                        <label><i class="fas fa-tag"></i> Texto do badge</label>
                        <input type="text" name="badge_text" class="form-control" value="<?php echo $_POST['badge_text'] ?? ''; ?>" placeholder="Ex: Mais Popular">
                    </div>
                    
                    <!-- Destaque -->
                    <div class="form-group">
                        <div class="form-check">
                            <input type="checkbox" name="destaque" id="destaque" value="1" <?php echo isset($_POST['destaque']) ? 'checked' : ''; ?>>
                            <label for="destaque">Marcar como destaque ⭐</label>
                        </div>
                    </div>
                    
                    <!-- Ativo -->
                    <div class="form-group">
                        <div class="form-check">
                            <input type="checkbox" name="ativo" id="ativo" value="1" <?php echo !isset($_POST['ativo']) || $_POST['ativo'] ? 'checked' : ''; ?>>
                            <label for="ativo">Plano ativo</label>
                        </div>
                    </div>
                </div>
                
                <!-- Descrição Curta -->
                <div class="form-group full-width">
                    <label><i class="fas fa-align-left"></i> Descrição Curta</label>
                    <input type="text" name="descricao_curta" class="form-control" value="<?php echo $_POST['descricao_curta'] ?? ''; ?>" placeholder="Breve descrição do plano">
                </div>
                
                <!-- Descrição Completa -->
                <div class="form-group full-width">
                    <label><i class="fas fa-align-justify"></i> Descrição Completa</label>
                    <textarea name="descricao_completa" class="form-control" placeholder="Descrição detalhada do plano"><?php echo $_POST['descricao_completa'] ?? ''; ?></textarea>
                </div>
                
                <!-- Observação -->
                <div class="form-group full-width">
                    <label><i class="fas fa-info-circle"></i> Observação</label>
                    <textarea name="observacao" class="form-control" placeholder="Observações importantes sobre o plano"><?php echo $_POST['observacao'] ?? ''; ?></textarea>
                </div>
                
                <!-- WhatsApp -->
                <div class="form-grid">
                    <div class="form-group">
                        <label><i class="fab fa-whatsapp"></i> Link WhatsApp (opcional)</label>
                        <input type="text" name="link_whatsapp" class="form-control" value="<?php echo $_POST['link_whatsapp'] ?? ''; ?>" placeholder="https://wa.me/5519999999999">
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fab fa-whatsapp"></i> Mensagem WhatsApp (opcional)</label>
                        <input type="text" name="mensagem_whatsapp" class="form-control" value="<?php echo $_POST['mensagem_whatsapp'] ?? ''; ?>" placeholder="Use {plano_nome} para substituir">
                    </div>
                </div>
                
                <!-- Características -->
                <div class="form-group full-width">
                    <label><i class="fas fa-list"></i> Características do Plano</label>
                    <div class="caracteristicas-container" id="caracteristicas-container">
                        <div id="caracteristicas-list">
                            <!-- Características serão adicionadas aqui via JS -->
                        </div>
                        <button type="button" class="btn-add" onclick="adicionarCaracteristica()">
                            <i class="fas fa-plus"></i> Adicionar Característica
                        </button>
                    </div>
                    <small>Adicione os benefícios e funcionalidades do plano</small>
                </div>
                
                <div class="form-actions">
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Salvar Plano
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let caracCount = 0;

function adicionarCaracteristica(texto = '', icone = 'fa-check-circle') {
    const container = document.getElementById('caracteristicas-list');
    const div = document.createElement('div');
    div.className = 'caracteristica-row';
    div.innerHTML = `
        <input type="text" name="caracteristicas[${caracCount}][texto]" value="${texto}" placeholder="Ex: Layout responsivo" required>
        <input type="text" name="caracteristicas[${caracCount}][icone]" value="${icone}" placeholder="Ícone" style="width: 80px;">
        <button type="button" class="btn-remove" onclick="this.parentElement.remove()">
            <i class="fas fa-trash"></i>
        </button>
    `;
    container.appendChild(div);
    caracCount++;
}

// Adiciona 3 características padrão
adicionarCaracteristica('', 'fa-check-circle');
adicionarCaracteristica('', 'fa-check-circle');
adicionarCaracteristica('', 'fa-check-circle');

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