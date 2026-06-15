<?php
$page_title = 'Modelos de Contratos';
require_once '../../includes/header.php';
require_once '../../includes/menu.php';
require_once 'config.php';

// Busca todas as cláusulas organizadas por tipo
$clausulas_adesao = $conn->query("SELECT * FROM contrato_clausulas WHERE (tipo = 'adesao' OR tipo = 'todos') AND ativa = 1 ORDER BY ordem");
$clausulas_renovacao = $conn->query("SELECT * FROM contrato_clausulas WHERE (tipo = 'renovacao' OR tipo = 'todos') AND ativa = 1 ORDER BY ordem");
$clausulas_cancelamento = $conn->query("SELECT * FROM contrato_clausulas WHERE (tipo = 'cancelamento' OR tipo = 'todos') AND ativa = 1 ORDER BY ordem");

// Processa ações de ativar/desativar cláusula
if(isset($_GET['acao']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    if($_GET['acao'] == 'ativar') {
        $conn->query("UPDATE contrato_clausulas SET ativa = 1 WHERE id = $id");
        $_SESSION['mensagem'] = ['tipo' => 'sucesso', 'texto' => 'Cláusula ativada com sucesso!'];
    }
    
    if($_GET['acao'] == 'desativar') {
        $conn->query("UPDATE contrato_clausulas SET ativa = 0 WHERE id = $id");
        $_SESSION['mensagem'] = ['tipo' => 'sucesso', 'texto' => 'Cláusula desativada com sucesso!'];
    }
    
    if($_GET['acao'] == 'excluir') {
        // Verifica se não é obrigatória
        $check = $conn->query("SELECT obrigatoria FROM contrato_clausulas WHERE id = $id")->fetch_assoc();
        if($check['obrigatoria']) {
            $_SESSION['mensagem'] = ['tipo' => 'erro', 'texto' => 'Não é possível excluir uma cláusula obrigatória!'];
        } else {
            $conn->query("DELETE FROM contrato_clausulas WHERE id = $id");
            $_SESSION['mensagem'] = ['tipo' => 'sucesso', 'texto' => 'Cláusula excluída com sucesso!'];
        }
    }
    
    header('Location: modelos.php');
    exit;
}
?>

<style>
.modelos-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    flex-wrap: wrap;
    gap: 20px;
}

.modelos-header h2 {
    font-size: 1.8rem;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 10px;
}

.modelos-header h2 i {
    color: #4361ee;
    width: 50px;
    height: 50px;
    background: #f8faff;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modelos-tabs {
    display: flex;
    gap: 10px;
    margin-bottom: 30px;
    border-bottom: 1px solid var(--border);
    padding-bottom: 15px;
    flex-wrap: wrap;
}

.tab-btn {
    padding: 14px 28px;
    border-radius: 50px;
    border: 1px solid var(--border);
    background: #ffffff;
    color: var(--text-secondary);
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 0.95rem;
}

.tab-btn i {
    color: #4361ee;
    font-size: 1.1rem;
}

.tab-btn:hover {
    background: #f8faff;
    border-color: #4361ee;
    transform: translateY(-2px);
}

.tab-btn.active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-color: transparent;
    box-shadow: 0 10px 20px rgba(102,126,234,0.2);
}

.tab-btn.active i {
    color: white;
}

.tab-content {
    display: none;
    animation: fadeIn 0.3s ease;
}

.tab-content.active {
    display: block;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.modelo-card {
    background: #ffffff;
    border: 1px solid var(--border);
    border-radius: 24px;
    overflow: hidden;
    margin-bottom: 30px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.05);
}

.modelo-header {
    background: linear-gradient(135deg, #f8faff 0%, #ffffff 100%);
    padding: 30px;
    border-bottom: 1px solid var(--border);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 20px;
}

.modelo-titulo {
    display: flex;
    align-items: center;
    gap: 20px;
}

.modelo-icone {
    width: 70px;
    height: 70px;
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: white;
}

.modelo-icone.adesao {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.modelo-icone.renovacao {
    background: linear-gradient(135deg, #10b981 0%, #34d399 100%);
}

.modelo-icone.cancelamento {
    background: linear-gradient(135deg, #ef4444 0%, #f87171 100%);
}

.modelo-info h3 {
    font-size: 1.8rem;
    margin-bottom: 5px;
    color: var(--text-primary);
}

.modelo-info p {
    color: var(--text-muted);
    font-size: 1rem;
}

.modelo-actions {
    display: flex;
    gap: 10px;
}

.btn {
    padding: 12px 24px;
    border-radius: 12px;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s ease;
    border: none;
    cursor: pointer;
    font-size: 0.95rem;
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
    transform: translateY(-2px);
}

.btn-success {
    background: #10b981;
    color: white;
}

.btn-success:hover {
    background: #0d9488;
}

.btn-warning {
    background: #f97316;
    color: white;
}

.btn-icon {
    padding: 10px 14px;
    border-radius: 10px;
    border: 1px solid var(--border);
    background: #ffffff;
    color: var(--text-secondary);
    text-decoration: none;
    display: inline-block;
    transition: all 0.2s ease;
    margin: 0 2px;
}

.btn-icon:hover {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-color: transparent;
    transform: translateY(-2px);
}

.clausulas-container {
    padding: 30px;
}

.clausula-item {
    background: #f8faff;
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 25px;
    margin-bottom: 20px;
    transition: all 0.3s ease;
    position: relative;
}

.clausula-item:hover {
    transform: translateX(5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    border-color: #4361ee;
}

.clausula-item.inativa {
    opacity: 0.6;
    background: #f5f5f5;
}

.clausula-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    flex-wrap: wrap;
    gap: 15px;
}

.clausula-titulo {
    display: flex;
    align-items: center;
    gap: 12px;
}

.clausula-titulo i {
    width: 40px;
    height: 40px;
    background: rgba(67, 97, 238, 0.1);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #4361ee;
    font-size: 1.2rem;
}

.clausula-titulo h4 {
    font-size: 1.2rem;
    color: var(--text-primary);
}

.clausula-badges {
    display: flex;
    gap: 10px;
}

.badge {
    padding: 4px 12px;
    border-radius: 30px;
    font-size: 0.75rem;
    font-weight: 600;
}

.badge-obrigatoria {
    background: #4361ee;
    color: white;
}

.badge-opcional {
    background: #f8faff;
    color: #4361ee;
    border: 1px solid var(--border);
}

.badge-ativa {
    background: #10b981;
    color: white;
}

.badge-inativa {
    background: #ef4444;
    color: white;
}

.clausula-conteudo {
    color: var(--text-secondary);
    line-height: 1.8;
    margin-bottom: 20px;
    padding-left: 52px;
}

.clausula-conteudo ul {
    margin-top: 10px;
    padding-left: 20px;
}

.clausula-conteudo li {
    margin-bottom: 5px;
}

.clausula-footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding-top: 15px;
    border-top: 1px dashed var(--border);
}

.variables-box {
    background: #ffffff;
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 15px;
    margin-top: 20px;
}

.variables-box h5 {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 10px;
    color: var(--text-primary);
}

.variables-box h5 i {
    color: #4361ee;
}

.variable-tag {
    display: inline-block;
    background: #f8faff;
    color: #4361ee;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 0.75rem;
    margin: 3px;
    border: 1px solid var(--border);
}

.preview-contrato {
    background: #ffffff;
    border: 2px dashed var(--border);
    border-radius: 24px;
    padding: 40px;
    margin-top: 30px;
    font-family: 'Times New Roman', serif;
    line-height: 1.8;
    max-height: 600px;
    overflow-y: auto;
}

.preview-contrato h2 {
    font-size: 1.5rem;
    margin: 30px 0 15px;
    color: var(--text-primary);
}

.preview-contrato p {
    margin-bottom: 15px;
    color: var(--text-secondary);
}

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
    max-width: 800px;
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
    position: sticky;
    top: 0;
    background: #ffffff;
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

.form-control {
    width: 100%;
    padding: 12px 15px;
    border: 1px solid var(--border);
    border-radius: 12px;
    background: #f8faff;
    color: var(--text-primary);
    transition: all 0.2s ease;
}

.form-control:focus {
    outline: none;
    border-color: #4361ee;
    background: #ffffff;
}

textarea.form-control {
    min-height: 150px;
    resize: vertical;
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

.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: #f8faff;
    border-radius: 16px;
}

.empty-state i {
    font-size: 3rem;
    color: var(--text-muted);
    margin-bottom: 15px;
    opacity: 0.5;
}

.empty-state h3 {
    font-size: 1.2rem;
    margin-bottom: 10px;
    color: var(--text-primary);
}

.empty-state p {
    color: var(--text-muted);
    margin-bottom: 20px;
}
</style>

<div class="main-content" id="mainContent">
    <div class="top-bar">
        <h1 class="page-title">
            <i class="fas fa-copy" style="color: #4361ee; margin-right: 10px;"></i>
            Modelos de Contratos
        </h1>
        
        <div class="top-bar-actions">
            <div class="theme-toggle" id="themeToggle">
                <i class="fas <?php echo $tema == 'dark' ? 'fa-moon' : 'fa-sun'; ?>" id="themeIcon"></i>
            </div>
            <button class="btn btn-primary" onclick="abrirModalClausula()">
                <i class="fas fa-plus"></i> Nova Cláusula
            </button>
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

        <!-- Tabs -->
        <div class="modelos-tabs">
            <button class="tab-btn active" onclick="mudarTab('adesao')">
                <i class="fas fa-file-signature"></i> Contrato de Adesão
                <span class="badge" style="background: #4361ee; color: white;"><?php echo $clausulas_adesao->num_rows; ?></span>
            </button>
            <button class="tab-btn" onclick="mudarTab('renovacao')">
                <i class="fas fa-sync-alt"></i> Termo de Renovação
                <span class="badge" style="background: #10b981; color: white;"><?php echo $clausulas_renovacao->num_rows; ?></span>
            </button>
            <button class="tab-btn" onclick="mudarTab('cancelamento')">
                <i class="fas fa-ban"></i> Termo de Cancelamento
                <span class="badge" style="background: #ef4444; color: white;"><?php echo $clausulas_cancelamento->num_rows; ?></span>
            </button>
        </div>

        <!-- Tab Adesão -->
        <div id="tab-adesao" class="tab-content active">
            <div class="modelo-card">
                <div class="modelo-header">
                    <div class="modelo-titulo">
                        <div class="modelo-icone adesao">
                            <i class="fas fa-file-signature"></i>
                        </div>
                        <div class="modelo-info">
                            <h3>Contrato de Adesão</h3>
                            <p>Modelo completo para contratação de serviços de tecnologia</p>
                        </div>
                    </div>
                    <div class="modelo-actions">
                        <button class="btn btn-secondary" onclick="visualizarModelo('adesao')">
                            <i class="fas fa-eye"></i> Visualizar Completo
                        </button>
                        <a href="../gerador/index.php?tipo=adesao" class="btn btn-primary">
                            <i class="fas fa-file-alt"></i> Usar Modelo
                        </a>
                    </div>
                </div>

                <div class="clausulas-container">
                    <h4 style="margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-list"></i> Cláusulas do Contrato
                    </h4>
                    
                    <?php if($clausulas_adesao->num_rows == 0): ?>
                    <div class="empty-state">
                        <i class="fas fa-file-contract"></i>
                        <h3>Nenhuma cláusula cadastrada</h3>
                        <p>Adicione cláusulas para este tipo de contrato</p>
                        <button class="btn btn-primary" onclick="abrirModalClausula('adesao')">
                            <i class="fas fa-plus"></i> Adicionar Cláusula
                        </button>
                    </div>
                    <?php else: ?>
                        <?php while($clausula = $clausulas_adesao->fetch_assoc()): ?>
                        <div class="clausula-item <?php echo !$clausula['ativa'] ? 'inativa' : ''; ?>">
                            <div class="clausula-header">
                                <div class="clausula-titulo">
                                    <i class="fas fa-gavel"></i>
                                    <h4><?php echo $clausula['titulo']; ?></h4>
                                </div>
                                <div class="clausula-badges">
                                    <?php if($clausula['obrigatoria']): ?>
                                    <span class="badge badge-obrigatoria">Obrigatória</span>
                                    <?php else: ?>
                                    <span class="badge badge-opcional">Opcional</span>
                                    <?php endif; ?>
                                    
                                    <?php if($clausula['ativa']): ?>
                                    <span class="badge badge-ativa">Ativa</span>
                                    <?php else: ?>
                                    <span class="badge badge-inativa">Inativa</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="clausula-conteudo">
                                <?php echo substr(strip_tags($clausula['conteudo']), 0, 300) . '...'; ?>
                            </div>
                            
                            <div class="variables-box">
                                <h5><i class="fas fa-code"></i> Variáveis disponíveis nesta cláusula:</h5>
                                <span class="variable-tag">{valor_plano}</span>
                                <span class="variable-tag">{valor_mensal}</span>
                                <span class="variable-tag">{multa_cancelamento}</span>
                                <span class="variable-tag">{percentual_multa}</span>
                                <span class="variable-tag">{fidelidade}</span>
                                <span class="variable-tag">{prazo_desenvolvimento}</span>
                            </div>
                            
                            <div class="clausula-footer">
                                <button class="btn-icon" onclick="editarClausula(<?php echo htmlspecialchars(json_encode($clausula)); ?>)" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <?php if($clausula['ativa']): ?>
                                <a href="?acao=desativar&id=<?php echo $clausula['id']; ?>" class="btn-icon" title="Desativar" style="color: #f97316;">
                                    <i class="fas fa-eye-slash"></i>
                                </a>
                                <?php else: ?>
                                <a href="?acao=ativar&id=<?php echo $clausula['id']; ?>" class="btn-icon" title="Ativar" style="color: #10b981;">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <?php endif; ?>
                                <?php if(!$clausula['obrigatoria']): ?>
                                <a href="?acao=excluir&id=<?php echo $clausula['id']; ?>" class="btn-icon" title="Excluir" onclick="return confirm('Tem certeza que deseja excluir esta cláusula?')" style="color: #ef4444;">
                                    <i class="fas fa-trash"></i>
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Tab Renovação -->
        <div id="tab-renovacao" class="tab-content">
            <div class="modelo-card">
                <div class="modelo-header">
                    <div class="modelo-titulo">
                        <div class="modelo-icone renovacao">
                            <i class="fas fa-sync-alt"></i>
                        </div>
                        <div class="modelo-info">
                            <h3>Termo de Renovação</h3>
                            <p>Modelo para renovação de contratos existentes</p>
                        </div>
                    </div>
                    <div class="modelo-actions">
                        <button class="btn btn-secondary" onclick="visualizarModelo('renovacao')">
                            <i class="fas fa-eye"></i> Visualizar Completo
                        </button>
                        <a href="../gerador/index.php?tipo=renovacao" class="btn btn-success">
                            <i class="fas fa-file-alt"></i> Usar Modelo
                        </a>
                    </div>
                </div>

                <div class="clausulas-container">
                    <h4 style="margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-list"></i> Cláusulas do Termo de Renovação
                    </h4>
                    
                    <?php if($clausulas_renovacao->num_rows == 0): ?>
                    <div class="empty-state">
                        <i class="fas fa-file-contract"></i>
                        <h3>Nenhuma cláusula cadastrada</h3>
                        <p>Adicione cláusulas para este tipo de contrato</p>
                        <button class="btn btn-success" onclick="abrirModalClausula('renovacao')">
                            <i class="fas fa-plus"></i> Adicionar Cláusula
                        </button>
                    </div>
                    <?php else: ?>
                        <?php while($clausula = $clausulas_renovacao->fetch_assoc()): ?>
                        <div class="clausula-item <?php echo !$clausula['ativa'] ? 'inativa' : ''; ?>">
                            <div class="clausula-header">
                                <div class="clausula-titulo">
                                    <i class="fas fa-gavel"></i>
                                    <h4><?php echo $clausula['titulo']; ?></h4>
                                </div>
                                <div class="clausula-badges">
                                    <?php if($clausula['obrigatoria']): ?>
                                    <span class="badge badge-obrigatoria">Obrigatória</span>
                                    <?php else: ?>
                                    <span class="badge badge-opcional">Opcional</span>
                                    <?php endif; ?>
                                    
                                    <?php if($clausula['ativa']): ?>
                                    <span class="badge badge-ativa">Ativa</span>
                                    <?php else: ?>
                                    <span class="badge badge-inativa">Inativa</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="clausula-conteudo">
                                <?php echo substr(strip_tags($clausula['conteudo']), 0, 300) . '...'; ?>
                            </div>
                            
                            <div class="clausula-footer">
                                <button class="btn-icon" onclick="editarClausula(<?php echo htmlspecialchars(json_encode($clausula)); ?>)" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <?php if($clausula['ativa']): ?>
                                <a href="?acao=desativar&id=<?php echo $clausula['id']; ?>" class="btn-icon" title="Desativar" style="color: #f97316;">
                                    <i class="fas fa-eye-slash"></i>
                                </a>
                                <?php else: ?>
                                <a href="?acao=ativar&id=<?php echo $clausula['id']; ?>" class="btn-icon" title="Ativar" style="color: #10b981;">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <?php endif; ?>
                                <?php if(!$clausula['obrigatoria']): ?>
                                <a href="?acao=excluir&id=<?php echo $clausula['id']; ?>" class="btn-icon" title="Excluir" onclick="return confirm('Tem certeza que deseja excluir esta cláusula?')" style="color: #ef4444;">
                                    <i class="fas fa-trash"></i>
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Tab Cancelamento -->
        <div id="tab-cancelamento" class="tab-content">
            <div class="modelo-card">
                <div class="modelo-header">
                    <div class="modelo-titulo">
                        <div class="modelo-icone cancelamento">
                            <i class="fas fa-ban"></i>
                        </div>
                        <div class="modelo-info">
                            <h3>Termo de Cancelamento</h3>
                            <p>Modelo para rescisão contratual</p>
                        </div>
                    </div>
                    <div class="modelo-actions">
                        <button class="btn btn-secondary" onclick="visualizarModelo('cancelamento')">
                            <i class="fas fa-eye"></i> Visualizar Completo
                        </button>
                        <a href="../gerador/index.php?tipo=cancelamento" class="btn btn-warning">
                            <i class="fas fa-file-alt"></i> Usar Modelo
                        </a>
                    </div>
                </div>

                <div class="clausulas-container">
                    <h4 style="margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-list"></i> Cláusulas do Termo de Cancelamento
                    </h4>
                    
                    <?php if($clausulas_cancelamento->num_rows == 0): ?>
                    <div class="empty-state">
                        <i class="fas fa-file-contract"></i>
                        <h3>Nenhuma cláusula cadastrada</h3>
                        <p>Adicione cláusulas para este tipo de contrato</p>
                        <button class="btn btn-warning" onclick="abrirModalClausula('cancelamento')">
                            <i class="fas fa-plus"></i> Adicionar Cláusula
                        </button>
                    </div>
                    <?php else: ?>
                        <?php while($clausula = $clausulas_cancelamento->fetch_assoc()): ?>
                        <div class="clausula-item <?php echo !$clausula['ativa'] ? 'inativa' : ''; ?>">
                            <div class="clausula-header">
                                <div class="clausula-titulo">
                                    <i class="fas fa-gavel"></i>
                                    <h4><?php echo $clausula['titulo']; ?></h4>
                                </div>
                                <div class="clausula-badges">
                                    <?php if($clausula['obrigatoria']): ?>
                                    <span class="badge badge-obrigatoria">Obrigatória</span>
                                    <?php else: ?>
                                    <span class="badge badge-opcional">Opcional</span>
                                    <?php endif; ?>
                                    
                                    <?php if($clausula['ativa']): ?>
                                    <span class="badge badge-ativa">Ativa</span>
                                    <?php else: ?>
                                    <span class="badge badge-inativa">Inativa</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="clausula-conteudo">
                                <?php echo substr(strip_tags($clausula['conteudo']), 0, 300) . '...'; ?>
                            </div>
                            
                            <div class="clausula-footer">
                                <button class="btn-icon" onclick="editarClausula(<?php echo htmlspecialchars(json_encode($clausula)); ?>)" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <?php if($clausula['ativa']): ?>
                                <a href="?acao=desativar&id=<?php echo $clausula['id']; ?>" class="btn-icon" title="Desativar" style="color: #f97316;">
                                    <i class="fas fa-eye-slash"></i>
                                </a>
                                <?php else: ?>
                                <a href="?acao=ativar&id=<?php echo $clausula['id']; ?>" class="btn-icon" title="Ativar" style="color: #10b981;">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <?php endif; ?>
                                <?php if(!$clausula['obrigatoria']): ?>
                                <a href="?acao=excluir&id=<?php echo $clausula['id']; ?>" class="btn-icon" title="Excluir" onclick="return confirm('Tem certeza que deseja excluir esta cláusula?')" style="color: #ef4444;">
                                    <i class="fas fa-trash"></i>
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Preview do Modelo Completo -->
        <div id="previewModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 id="previewTitulo">Modelo Completo</h3>
                    <span class="modal-close" onclick="fecharModal()">&times;</span>
                </div>
                <div class="modal-body" id="previewConteudo">
                    <!-- Conteúdo carregado via JS -->
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" onclick="fecharModal()">Fechar</button>
                    <a href="../gerador/index.php" class="btn btn-primary" id="previewUsarBtn">
                        <i class="fas fa-file-alt"></i> Usar este modelo
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function mudarTab(tab) {
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
    
    event.currentTarget.classList.add('active');
    document.getElementById('tab-' + tab).classList.add('active');
}

function visualizarModelo(tipo) {
    let titulo = '';
    let conteudo = '';
    
    // CSS completo do contrato
    const estiloContrato = `
        <style>
            .contrato-documento {
                font-family: 'Times New Roman', Times, serif;
                max-width: 800px;
                margin: 0 auto;
                background: white;
                padding: 50px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.1);
                border-radius: 8px;
            }
            
            .contrato-documento h1 {
                font-size: 24px;
                font-weight: bold;
                text-align: center;
                text-transform: uppercase;
                margin-bottom: 30px;
                color: #1e293b;
                letter-spacing: 1px;
                padding-bottom: 15px;
                border-bottom: 2px solid #4361ee;
            }
            
            .contrato-documento h2 {
                font-size: 18px;
                font-weight: bold;
                margin: 30px 0 15px;
                color: #1e293b;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                border-left: 4px solid #4361ee;
                padding-left: 15px;
            }
            
            .contrato-documento p {
                font-size: 14px;
                line-height: 1.8;
                margin-bottom: 15px;
                color: #334155;
                text-align: justify;
            }
            
            .contrato-documento strong {
                color: #0d47a1;
                font-weight: 700;
            }
            
            .contrato-documento .destaque {
                background: #f8faff;
                padding: 20px;
                border-radius: 8px;
                margin: 20px 0;
                border-left: 4px solid #4361ee;
                font-style: italic;
            }
            
            .contrato-documento ul, .contrato-documento ol {
                margin: 15px 0 15px 30px;
                line-height: 1.8;
                color: #334155;
            }
            
            .contrato-documento li {
                margin-bottom: 8px;
            }
            
            .contrato-documento .numero-contrato {
                text-align: right;
                font-size: 14px;
                color: #64748b;
                margin-bottom: 30px;
                font-family: 'Courier New', monospace;
            }
            
            .contrato-documento .partes {
                background: #f8faff;
                padding: 20px;
                border-radius: 8px;
                margin: 20px 0;
                border: 1px solid #e2e8f0;
            }
            
            .contrato-documento .partes p {
                margin-bottom: 10px;
            }
            
            .contrato-documento .valor-destaque {
                font-size: 16px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 15px 25px;
                border-radius: 50px;
                display: inline-block;
                font-weight: bold;
                margin: 15px 0;
                box-shadow: 0 10px 20px rgba(102,126,234,0.2);
            }
            
            .contrato-documento .assinaturas {
                display: flex;
                justify-content: space-between;
                margin-top: 60px;
                padding-top: 30px;
                border-top: 1px dashed #cbd5e1;
            }
            
            .contrato-documento .assinatura-bloco {
                text-align: center;
                width: 45%;
            }
            
            .contrato-documento .linha-assinatura {
                width: 100%;
                height: 1px;
                background: #000;
                margin: 30px 0 10px;
            }
            
            .contrato-documento .data-local {
                text-align: center;
                margin-top: 40px;
                font-style: italic;
                color: #64748b;
            }
            
            .contrato-documento .clausula-numero {
                display: inline-block;
                background: #4361ee;
                color: white;
                width: 30px;
                height: 30px;
                border-radius: 50%;
                text-align: center;
                line-height: 30px;
                font-weight: bold;
                margin-right: 10px;
                font-size: 14px;
            }
            
            .contrato-documento .quadro-valores {
                background: white;
                border: 2px solid #e2e8f0;
                border-radius: 12px;
                padding: 20px;
                margin: 20px 0;
                box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            }
            
            .contrato-documento .quadro-valores table {
                width: 100%;
                border-collapse: collapse;
            }
            
            .contrato-documento .quadro-valores td {
                padding: 10px;
                border-bottom: 1px solid #e2e8f0;
            }
            
            .contrato-documento .quadro-valores td:first-child {
                font-weight: bold;
                color: #1e293b;
            }
            
            .contrato-documento .quadro-valores td:last-child {
                text-align: right;
                color: #0d47a1;
                font-weight: bold;
            }
            
            .contrato-documento .quadro-valores .total {
                background: #f8faff;
                font-size: 16px;
            }
            
            .contrato-documento .aviso-legal {
                font-size: 12px;
                color: #94a3b8;
                text-align: center;
                margin-top: 40px;
                padding-top: 20px;
                border-top: 1px solid #e2e8f0;
            }
            
            @media print {
                .contrato-documento {
                    box-shadow: none;
                    padding: 20px;
                }
            }
        </style>
    `;
    
    if(tipo == 'adesao') {
        titulo = 'Contrato de Adesão - Modelo Completo';
        conteudo = `
            <div class="contrato-documento">
                <div class="numero-contrato">
                    <strong>Contrato nº:</strong> Arcon-${new Date().getFullYear()}-${String(new Date().getMonth()+1).padStart(2,'0')}-${Math.floor(1000 + Math.random() * 9000)}
                </div>
                
                <h1>CONTRATO DE PRESTAÇÃO DE SERVIÇOS DE TECNOLOGIA</h1>
                
                <div class="partes">
                    <p><strong>CONTRATANTE:</strong> [NOME DO CLIENTE], inscrito no CPF/CNPJ sob nº [DOCUMENTO], residente e domiciliado à [ENDEREÇO COMPLETO], doravante denominado simplesmente CONTRATANTE.</p>
                    
                    <p><strong>CONTRATADA:</strong> GESTOR ARCON ADMIN, pessoa jurídica de direito privado, inscrita no CNPJ sob nº 00.000.000/0001-00, com sede na [ENDEREÇO DA EMPRESA], neste ato representada por seu representante legal, doravante denominada simplesmente CONTRATADA.</p>
                </div>
                
                <p>As partes acima identificadas têm, entre si, justo e acordado o presente <strong>Contrato de Prestação de Serviços de Tecnologia</strong>, que se regerá pelas cláusulas seguintes e pelas condições descritas no presente instrumento.</p>
                
                <h2><span class="clausula-numero">1</span> CLÁUSULA PRIMEIRA - OBJETO DO CONTRATO</h2>
                <p><strong>1.1.</strong> O presente contrato tem como objeto a prestação de serviços de desenvolvimento, implementação e manutenção de solução tecnológica, conforme especificado no Anexo I (Plano Contratado).</p>
                <p><strong>1.2.</strong> A CONTRATADA desenvolverá o sistema/site conforme as especificações técnicas acordadas entre as partes, podendo incluir: desenvolvimento de sites, sistemas web, aplicações personalizadas, bots com inteligência artificial, integrações com APIs, e demais serviços correlatos.</p>
                <p><strong>1.3.</strong> As funcionalidades, prazos e entregáveis estão detalhados na Proposta Comercial e no Escopo Técnico, que passam a fazer parte integrante deste contrato.</p>
                
                <h2><span class="clausula-numero">2</span> CLÁUSULA SEGUNDA - VALOR E CONDIÇÕES DE PAGAMENTO</h2>
                
                <div class="quadro-valores">
                    <table>
                        <tr>
                            <td>Valor do Desenvolvimento (único):</td>
                            <td><strong>R$ <span id="preview-valor-plano">5.000,00</span></strong></td>
                        </tr>
                        <tr>
                            <td>Valor da Mensalidade (manutenção):</td>
                            <td><strong>R$ <span id="preview-valor-mensal">297,00</span></strong></td>
                        </tr>
                        <tr>
                            <td>Multa por Cancelamento (20%):</td>
                            <td><strong>R$ <span id="preview-multa">1.000,00</span></strong></td>
                        </tr>
                        <tr class="total">
                            <td>Total no período de fidelidade (12 meses):</td>
                            <td><strong>R$ 8.564,00</strong></td>
                        </tr>
                    </table>
                </div>
                
                <p><strong>2.1.</strong> Pelo desenvolvimento do objeto deste contrato, a CONTRATANTE pagará à CONTRATADA o valor único de <strong class="valor-destaque">R$ 5.000,00</strong> (cinco mil reais).</p>
                <p><strong>2.2.</strong> Além do valor do desenvolvimento, a CONTRATANTE pagará uma mensalidade no valor de <strong>R$ 297,00</strong> (duzentos e noventa e sete reais) referente aos serviços de manutenção, atualizações de segurança, correção de bugs, hospedagem (quando aplicável) e suporte técnico.</p>
                <p><strong>2.3.</strong> O pagamento do valor de desenvolvimento poderá ser parcelado em até 12x, com vencimento da primeira parcela em 30 dias após a assinatura.</p>
                <p><strong>2.4.</strong> As mensalidades vencerão todo dia 10 de cada mês, com início no mês subsequente à entrega do projeto.</p>
                <p><strong>2.5.</strong> O atraso no pagamento sujeitará a CONTRATANTE a multa de 2% (dois por cento) sobre o valor devido, mais juros de mora de 1% (um por cento) ao mês, e correção monetária.</p>
                
                <h2><span class="clausula-numero">3</span> CLÁUSULA TERCEIRA - PRAZO E VIGÊNCIA</h2>
                <p><strong>3.1.</strong> O prazo de desenvolvimento será de <strong>30 (trinta) dias úteis</strong>, contados a partir da assinatura deste contrato e do pagamento da entrada (quando aplicável).</p>
                <p><strong>3.2.</strong> Este contrato terá vigência por prazo indeterminado, permanecendo em vigor enquanto houver a prestação dos serviços de manutenção mensal.</p>
                <p><strong>3.3.</strong> O período de fidelidade é de <strong>12 (doze) meses</strong>, contados a partir da data de assinatura deste contrato.</p>
                
                <div class="destaque">
                    <p><strong>⚠️ IMPORTANTE:</strong> Durante o período de fidelidade, o cancelamento por iniciativa da CONTRATANTE implicará no pagamento da multa prevista na Cláusula Quinta.</p>
                </div>
                
                <h2><span class="clausula-numero">4</span> CLÁUSULA QUARTA - MANUTENÇÃO E ATUALIZAÇÕES</h2>
                <p><strong>4.1.</strong> A mensalidade paga pela CONTRATANTE inclui os seguintes serviços de manutenção:</p>
                <ul>
                    <li>Correção de bugs e erros de funcionamento</li>
                    <li>Atualizações de segurança e compatibilidade</li>
                    <li>Backups periódicos dos dados</li>
                    <li>Suporte técnico por e-mail e WhatsApp em horário comercial (segunda a sexta, 9h às 18h)</li>
                    <li>Hospedagem (quando incluída no plano)</li>
                    <li>Pequenas alterações de conteúdo (até 2 horas/mês)</li>
                </ul>
                <p><strong>4.2.</strong> Alterações mais complexas, novas funcionalidades ou modificações substanciais no escopo original serão consideradas serviços extraordinários e serão orçadas separadamente, mediante aprovação prévia da CONTRATANTE.</p>
                
                <h2><span class="clausula-numero">5</span> CLÁUSULA QUINTA - RESCISÃO E MULTA</h2>
                <p><strong>5.1.</strong> Em caso de rescisão contratual por iniciativa da CONTRATANTE antes do término do período de fidelidade de 12 meses, ficará a CONTRATANTE sujeita ao pagamento de multa equivalente a <strong class="valor-destaque">R$ 1.000,00</strong> (hum mil reais), correspondente a 20% do valor total do contrato.</p>
                <p><strong>5.2.</strong> A CONTRATADA poderá rescindir o contrato imediatamente nas seguintes hipóteses:</p>
                <ul>
                    <li>Atraso superior a 30 (trinta) dias no pagamento das mensalidades</li>
                    <li>Uso indevido do sistema ou violação de direitos autorais</li>
                    <li>Descumprimento de obrigações legais ou contratuais</li>
                </ul>
                
                <h2><span class="clausula-numero">6</span> CLÁUSULA SEXTA - PROPRIEDADE INTELECTUAL</h2>
                <p><strong>6.1.</strong> Após a quitação integral dos valores devidos, a CONTRATANTE terá direito de propriedade sobre o código-fonte do sistema/site desenvolvido, podendo utilizá-lo livremente.</p>
                <p><strong>6.2.</strong> A CONTRATADA mantém a propriedade sobre bibliotecas, frameworks e códigos genéricos de sua biblioteca, bem como sobre metodologias e processos de desenvolvimento.</p>
                
                <h2><span class="clausula-numero">7</span> CLÁUSULA SÉTIMA - OBRIGAÇÕES DAS PARTES</h2>
                <p><strong>7.1.</strong> São obrigações da CONTRATADA:</p>
                <ul>
                    <li>Desenvolver o sistema conforme especificações acordadas</li>
                    <li>Entregar o projeto dentro do prazo estipulado</li>
                    <li>Prestar suporte e manutenção conforme cláusula quarta</li>
                    <li>Manter sigilo sobre as informações da CONTRATANTE</li>
                </ul>
                <p><strong>7.2.</strong> São obrigações da CONTRATANTE:</p>
                <ul>
                    <li>Fornecer todas as informações necessárias para o desenvolvimento</li>
                    <li>Aprovar as etapas do projeto dentro dos prazos acordados</li>
                    <li>Efetuar os pagamentos nas datas estipuladas</li>
                    <li>Utilizar o sistema de acordo com a legislação vigente</li>
                </ul>
                
                <div class="assinaturas">
                    <div class="assinatura-bloco">
                        <div class="linha-assinatura"></div>
                        <p><strong>CONTRATANTE</strong><br>Nome: _________________________<br>CPF/CNPJ: _____________________</p>
                    </div>
                    <div class="assinatura-bloco">
                        <div class="linha-assinatura"></div>
                        <p><strong>CONTRATADA</strong><br>GESTOR ARCON ADMIN<br>Representante: _________________</p>
                    </div>
                </div>
                
                <div class="data-local">
                    <p>[CIDADE], ${new Date().getDate()} de ${['janeiro','fevereiro','março','abril','maio','junho','julho','agosto','setembro','outubro','novembro','dezembro'][new Date().getMonth()]} de ${new Date().getFullYear()}</p>
                </div>
                
                <div class="aviso-legal">
                    <p>Este é um modelo de contrato. Os valores e condições podem ser ajustados conforme necessidade do cliente.</p>
                </div>
            </div>
        `;
    } else if(tipo == 'renovacao') {
        titulo = 'Termo de Renovação - Modelo Completo';
        conteudo = `
            <div class="contrato-documento">
                <div class="numero-contrato">
                    <strong>Contrato Original nº:</strong> Arcon-2025-01-1234
                </div>
                
                <h1>TERMO DE RENOVAÇÃO CONTRATUAL</h1>
                
                <div class="partes">
                    <p><strong>CONTRATANTE:</strong> [NOME DO CLIENTE], já qualificado no contrato original firmado em 15 de janeiro de 2025.</p>
                    
                    <p><strong>CONTRATADA:</strong> GESTOR ARCON ADMIN, já qualificada.</p>
                </div>
                
                <p>As partes acima identificadas resolvem, de comum acordo, <strong>RENOVAR</strong> o contrato originalmente firmado em <strong>15 de janeiro de 2025</strong>, mediante as seguintes condições:</p>
                
                <h2><span class="clausula-numero">1</span> CLÁUSULA PRIMEIRA - PRAZO DE RENOVAÇÃO</h2>
                <p><strong>1.1.</strong> O presente termo renova o contrato por mais <strong>12 (doze) meses</strong>, a partir de <strong>15 de janeiro de 2026</strong>, prorrogáveis por igual período se assim acordado entre as partes.</p>
                <p><strong>1.2.</strong> Durante o período de renovação, permanecem válidas todas as cláusulas do contrato original, exceto as alterações expressamente previstas neste termo.</p>
                
                <h2><span class="clausula-numero">2</span> CLÁUSULA SEGUNDA - REAJUSTE DE VALORES</h2>
                
                <div class="quadro-valores">
                    <table>
                        <tr>
                            <td>Valor da Mensalidade (original):</td>
                            <td><strong>R$ 297,00</strong></td>
                        </tr>
                        <tr>
                            <td>Percentual de Reajuste:</td>
                            <td><strong>5,5% (IGPM acumulado)</strong></td>
                        </tr>
                        <tr class="total">
                            <td>Novo Valor da Mensalidade:</td>
                            <td><strong>R$ 313,33</strong></td>
                        </tr>
                    </table>
                </div>
                
                <p><strong>2.1.</strong> Fica estabelecido o reajuste de <strong>5,5% (cinco vírgula cinco por cento)</strong> sobre os valores originalmente contratados, com base na variação do IGPM do período.</p>
                <p><strong>2.2.</strong> A mensalidade passa a ser de <strong class="valor-destaque">R$ 313,33</strong> (trezentos e treze reais e trinta e três centavos).</p>
                <p><strong>2.3.</strong> O primeiro pagamento com o novo valor ocorrerá em <strong>10 de fevereiro de 2026</strong>.</p>
                
                <h2><span class="clausula-numero">3</span> CLÁUSULA TERCEIRA - ATUALIZAÇÕES CONTRATUAIS</h2>
                <p><strong>3.1.</strong> Fica incluída no escopo dos serviços a serem prestados durante o período de renovação:</p>
                <ul>
                    <li>Suporte prioritário com tempo de resposta de até 4 horas</li>
                    <li>Relatórios mensais de performance</li>
                    <li>2 horas extras mensais para alterações</li>
                </ul>
                
                <h2><span class="clausula-numero">4</span> CLÁUSULA QUARTA - DEMAIS CONDIÇÕES</h2>
                <p><strong>4.1.</strong> Permanecem inalteradas as demais cláusulas e condições do contrato original, que não conflitarem com o presente termo.</p>
                <p><strong>4.2.</strong> O período de fidelidade é reiniciado por mais 12 meses a partir da data de início da renovação.</p>
                
                <div class="destaque">
                    <p><strong>📝 NOTA:</strong> Este termo de renovação substitui o contrato original para todos os fins a partir de sua assinatura.</p>
                </div>
                
                <div class="assinaturas">
                    <div class="assinatura-bloco">
                        <div class="linha-assinatura"></div>
                        <p><strong>CONTRATANTE</strong></p>
                    </div>
                    <div class="assinatura-bloco">
                        <div class="linha-assinatura"></div>
                        <p><strong>CONTRATADA</strong></p>
                    </div>
                </div>
                
                <div class="data-local">
                    <p>[CIDADE], ${new Date().getDate()} de ${['janeiro','fevereiro','março','abril','maio','junho','julho','agosto','setembro','outubro','novembro','dezembro'][new Date().getMonth()]} de ${new Date().getFullYear()}</p>
                </div>
            </div>
        `;
    } else if(tipo == 'cancelamento') {
        titulo = 'Termo de Cancelamento - Modelo Completo';
        conteudo = `
            <div class="contrato-documento">
                <div class="numero-contrato">
                    <strong>Contrato Original nº:</strong> Arcon-2025-01-1234
                </div>
                
                <h1>TERMO DE CANCELAMENTO CONTRATUAL</h1>
                
                <div class="partes">
                    <p><strong>CONTRATANTE:</strong> [NOME DO CLIENTE], já qualificado no contrato original.</p>
                    
                    <p><strong>CONTRATADA:</strong> GESTOR ARCON ADMIN.</p>
                </div>
                
                <p>As partes acima identificadas resolvem, de comum acordo, <strong>CANCELAR</strong> o contrato firmado em <strong>15 de janeiro de 2025</strong>, mediante as seguintes condições:</p>
                
                <h2><span class="clausula-numero">1</span> CLÁUSULA PRIMEIRA - MOTIVO DO CANCELAMENTO</h2>
                <p><strong>1.1.</strong> O presente cancelamento ocorre por iniciativa do <strong>CONTRATANTE</strong> / <strong>CONTRATADA</strong> (assinalar conforme o caso).</p>
                <p><strong>1.2.</strong> Motivo declarado: <u>_________________________________________________________________</u></p>
                
                <h2><span class="clausula-numero">2</span> CLÁUSULA SEGUNDA - MULTA CONTRATUAL</h2>
                
                <div class="quadro-valores">
                    <table>
                        <tr>
                            <td>Valor Total do Contrato:</td>
                            <td><strong>R$ 8.564,00</strong></td>
                        </tr>
                        <tr>
                            <td>Percentual de Multa:</td>
                            <td><strong>20%</strong></td>
                        </tr>
                        <tr>
                            <td>Meses de Fidelidade:</td>
                            <td><strong>12 meses</strong></td>
                        </tr>
                        <tr>
                            <td>Meses Cumpridos:</td>
                            <td><strong>8 meses</strong></td>
                        </tr>
                        <tr>
                            <td>Multa Proporcional:</td>
                            <td><strong>R$ 570,93</strong></td>
                        </tr>
                        <tr class="total">
                            <td>Valor da Multa a Pagar:</td>
                            <td><strong>R$ 570,93</strong></td>
                        </tr>
                    </table>
                </div>
                
                <p><strong>2.1.</strong> Em virtude do cancelamento, fica acordado o pagamento de multa no valor de <strong class="valor-destaque">R$ 570,93</strong> (quinhentos e setenta reais e noventa e três centavos).</p>
                <p><strong>2.2.</strong> O cálculo da multa considerou o percentual de 20% sobre o valor total, proporcional aos meses de fidelidade não cumpridos (4 meses restantes).</p>
                <p><strong>2.3.</strong> A multa será paga em até <strong>30 (trinta) dias</strong> corridos a partir da assinatura deste termo.</p>
                
                <h2><span class="clausula-numero">3</span> CLÁUSULA TERCEIRA - DATA DE CANCELAMENTO</h2>
                <p><strong>3.1.</strong> O cancelamento produz efeitos a partir de <strong>${new Date().getDate()} de ${['janeiro','fevereiro','março','abril','maio','junho','julho','agosto','setembro','outubro','novembro','dezembro'][new Date().getMonth()]} de ${new Date().getFullYear()}</strong>.</p>
                <p><strong>3.2.</strong> A partir desta data, a CONTRATADA não tem mais obrigação de manter o sistema online ou fornecer suporte técnico.</p>
                
                <h2><span class="clausula-numero">4</span> CLÁUSULA QUARTA - ENTREGA DE ARQUIVOS</h2>
                <p><strong>4.1.</strong> A CONTRATADA fornecerá uma cópia completa do banco de dados e arquivos do sistema em até <strong>15 (quinze) dias</strong> após o pagamento integral da multa.</p>
                <p><strong>4.2.</strong> A entrega será feita via link de download ou outro meio acordado entre as partes.</p>
                
                <h2><span class="clausula-numero">5</span> CLÁUSULA QUINTA - QUITAÇÃO</h2>
                <p><strong>5.1.</strong> Com o pagamento da multa estipulada, as partes dão plena e geral quitação, nada mais tendo a reclamar uma da outra a respeito do contrato cancelado.</p>
                
                <div class="destaque">
                    <p><strong>⚠️ ATENÇÃO:</strong> O cancelamento do contrato implica na descontinuidade dos serviços de hospedagem, suporte e manutenção.</p>
                </div>
                
                <div class="assinaturas">
                    <div class="assinatura-bloco">
                        <div class="linha-assinatura"></div>
                        <p><strong>CONTRATANTE</strong></p>
                    </div>
                    <div class="assinatura-bloco">
                        <div class="linha-assinatura"></div>
                        <p><strong>CONTRATADA</strong></p>
                    </div>
                </div>
                
                <div class="data-local">
                    <p>[CIDADE], ${new Date().getDate()} de ${['janeiro','fevereiro','março','abril','maio','junho','julho','agosto','setembro','outubro','novembro','dezembro'][new Date().getMonth()]} de ${new Date().getFullYear()}</p>
                </div>
            </div>
        `;
    }
    
    document.getElementById('previewTitulo').textContent = titulo;
    document.getElementById('previewConteudo').innerHTML = estiloContrato + conteudo;
    document.getElementById('previewUsarBtn').href = '../gerador/index.php?tipo=' + tipo;
    document.getElementById('previewModal').style.display = 'flex';
}

function abrirModalClausula(tipo = 'adesao') {
    alert('Funcionalidade de criar/editar cláusulas será implementada no próximo módulo');
}

function editarClausula(clausula) {
    alert('Editar cláusula: ' + clausula.titulo);
}

function fecharModal() {
    document.getElementById('previewModal').style.display = 'none';
}

// Fecha modal ao clicar fora
window.onclick = function(event) {
    const modal = document.getElementById('previewModal');
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