<?php
$page_title = 'Visualizar Contrato';
require_once '../../includes/header.php';
require_once '../../includes/menu.php';
require_once 'config.php';

if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id = $_GET['id'];

// Fuso horário de Brasília
date_default_timezone_set('America/Sao_Paulo');

// Busca contrato com dados do cliente, plano e assinatura
$stmt = $conn->prepare("
    SELECT c.*, 
           cl.nome as cliente_nome, cl.email, cl.telefone, cl.cpf_cnpj,
           cl.empresa, cl.endereco, cl.cidade, cl.estado,
           pc.nome_plano, pc.valor_plano, pc.valor_mensal, pc.descricao as plano_descricao,
           cac.data_assinatura as data_assinatura_cliente,
           cac.ip as ip_assinatura,
           cac.user_agent,
           ca.assinatura_base64
    FROM contratos c
    LEFT JOIN clientes cl ON c.cliente_id = cl.id
    LEFT JOIN planos_contratados pc ON c.plano_contratado_id = pc.id
    LEFT JOIN cliente_assinaturas_contratos cac ON cac.contrato_id = c.id
    LEFT JOIN cliente_assinaturas ca ON cac.assinatura_id = ca.id
    WHERE c.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$contrato = $stmt->get_result()->fetch_assoc();

if(!$contrato) {
    header('Location: index.php');
    exit;
}

// Remove bloco de assinaturas já embutido no conteúdo salvo (gerado pelo JS)
$conteudo_limpo = $contrato['conteudo'];
$conteudo_limpo = preg_replace('/<div class=["\']assinaturas["\'][^>]*>.*?<\/div>\s*<\/div>/si', '', $conteudo_limpo);
$conteudo_limpo = preg_replace('/<div class=["\']data-local["\'][^>]*>.*?<\/div>/si', '', $conteudo_limpo);
// Remove o bloco inline de assinaturas que o JS gera com style="margin-top: 60px"
$conteudo_limpo = preg_replace('/<div style="margin-top:\s*60px[^"]*">.*?<\/div>\s*<\/div>/si', '', $conteudo_limpo);

// Busca histórico do contrato
$historico = $conn->query("
    SELECT h.*, a.nome_completo as admin_nome
    FROM contrato_historico h
    LEFT JOIN admin_users a ON h.created_by = a.id
    WHERE h.contrato_id = $id
    ORDER BY h.created_at DESC
");

$meses = ['janeiro','fevereiro','março','abril','maio','junho','julho','agosto','setembro','outubro','novembro','dezembro'];
?>

<style>
.view-container {
    max-width: 1000px;
    margin: 0 auto;
}

.view-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    flex-wrap: wrap;
    gap: 20px;
}

.contrato-status-bar {
    background: linear-gradient(135deg, #f8faff 0%, #ffffff 100%);
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: 25px;
    margin-bottom: 30px;
    display: flex;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
}

.status-icon {
    width: 60px;
    height: 60px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
}

.status-icon.rascunho { background: rgba(249,115,22,0.1); color: #f97316; }
.status-icon.enviado  { background: rgba(67,97,238,0.1);  color: #4361ee; }
.status-icon.assinado { background: rgba(16,185,129,0.1); color: #10b981; }

.status-info { flex: 1; }

.status-info h2 {
    font-size: 1.5rem;
    margin-bottom: 5px;
    color: var(--text-primary);
}

.status-info .numero-contrato {
    color: var(--text-muted);
    font-size: 0.95rem;
}

.status-badge {
    padding: 8px 20px;
    border-radius: 50px;
    font-size: 0.9rem;
    font-weight: 600;
    display: inline-block;
}

.status-badge.rascunho { background: rgba(249,115,22,0.1); color: #f97316; }
.status-badge.enviado  { background: rgba(67,97,238,0.1);  color: #4361ee; }
.status-badge.assinado { background: rgba(16,185,129,0.1); color: #10b981; }
.status-badge.cancelado{ background: rgba(239,68,68,0.1);  color: #ef4444; }

.action-buttons {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
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
}

.btn-success { background: #10b981; color: white; }
.btn-success:hover { background: #0d9488; }

.btn-icon {
    padding: 8px 12px;
    border-radius: 8px;
    border: 1px solid var(--border);
    background: #ffffff;
    color: var(--text-secondary);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s ease;
}

.btn-icon:hover {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-color: transparent;
}

.info-card {
    background: #ffffff;
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.02);
}

.info-card h3 {
    font-size: 1.2rem;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f0f0f0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.info-card h3 i { color: #4361ee; }

.info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}

.info-item {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.info-label {
    font-size: 0.85rem;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.info-value {
    font-size: 1.1rem;
    color: var(--text-primary);
    font-weight: 500;
}

.contrato-conteudo {
    background: #ffffff;
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: 40px;
    margin-bottom: 30px;
    font-family: 'Times New Roman', Times, serif;
    line-height: 1.8;
    box-shadow: 0 10px 30px rgba(0,0,0,0.02);
}

.contrato-conteudo h1 {
    font-size: 24px;
    text-align: center;
    margin-bottom: 30px;
    color: #1e293b;
    border-bottom: 2px solid #4361ee;
    padding-bottom: 15px;
}

.contrato-conteudo h2 {
    font-size: 18px;
    margin: 30px 0 15px;
    color: #1e293b;
    border-left: 4px solid #4361ee;
    padding-left: 15px;
}

.contrato-conteudo p {
    margin-bottom: 15px;
    color: #334155;
    text-align: justify;
}

/* Bloco de assinaturas dentro do contrato */
.assinaturas-bloco {
    margin-top: 60px;
    padding-top: 30px;
    border-top: 1px dashed #cbd5e1;
}

.assinaturas-cols {
    display: flex;
    justify-content: space-between;
    gap: 40px;
    margin: 40px 0 0;
}

.assinatura-col {
    flex: 1;
    text-align: center;
}

.assinatura-col img {
    max-width: 220px;
    max-height: 80px;
    object-fit: contain;
    display: block;
    margin: 0 auto 10px;
}

.linha-ass {
    border-top: 1px solid #000;
    width: 100%;
    margin: 10px 0 8px;
}

.ass-nome {
    font-weight: bold;
    font-family: sans-serif;
    font-size: 14px;
    margin: 0;
    color: #1e293b;
}

.ass-sub {
    font-family: sans-serif;
    font-size: 13px;
    margin: 4px 0 0;
    color: #334155;
}

.ass-data {
    font-size: 11px;
    color: #64748b;
    font-family: sans-serif;
    margin: 4px 0 0;
}

.ass-badge {
    display: inline-block;
    background: rgba(16,185,129,0.1);
    color: #10b981;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-family: sans-serif;
    font-weight: 600;
    margin-top: 5px;
}

.ass-pendente {
    font-size: 11px;
    color: #f97316;
    font-family: sans-serif;
    margin: 4px 0 0;
}

.ass-rodape {
    text-align: center;
    margin-top: 30px;
    font-style: italic;
    color: #64748b;
    font-size: 12px;
    font-family: sans-serif;
}

/* Histórico */
.table-container {
    background: #ffffff;
    border: 1px solid var(--border);
    border-radius: 20px;
    overflow: hidden;
    margin-bottom: 30px;
}

.table-header {
    padding: 20px 25px;
    border-bottom: 1px solid var(--border);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.table-title {
    font-weight: 600;
    color: var(--text-primary);
    font-size: 1.1rem;
    display: flex;
    align-items: center;
    gap: 10px;
}

.table-title i { color: #4361ee; }

.historico-item {
    padding: 15px 20px;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    gap: 15px;
}

.historico-item:last-child { border-bottom: none; }

.historico-icon {
    width: 40px;
    height: 40px;
    background: #f8faff;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #4361ee;
    flex-shrink: 0;
}

.historico-content { flex: 1; }

.historico-acao {
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 3px;
}

.historico-data {
    font-size: 0.85rem;
    color: var(--text-muted);
}
</style>

<div class="main-content" id="mainContent">
    <div class="top-bar">
        <h1 class="page-title">
            <i class="fas fa-file-contract" style="color: #4361ee; margin-right: 10px;"></i>
            Visualizar Contrato
        </h1>
        <div class="top-bar-actions">
            <div class="theme-toggle" id="themeToggle">
                <i class="fas <?php echo $tema == 'dark' ? 'fa-moon' : 'fa-sun'; ?>" id="themeIcon"></i>
            </div>
        </div>
    </div>

    <div class="content-area">
        <div class="view-container">

            <!-- Header com ações -->
            <div class="view-header">
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
                <div class="action-buttons">
                    <?php if($contrato['status'] == 'rascunho'): ?>
                    <a href="editar.php?id=<?php echo $id; ?>" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                    <a href="enviar.php?id=<?php echo $id; ?>" class="btn btn-success">
                        <i class="fas fa-paper-plane"></i> Enviar para Cliente
                    </a>
                    <?php endif; ?>

                    <?php if($contrato['status'] == 'assinado'): ?>
                    <a href="pdf.php?id=<?php echo $id; ?>" class="btn btn-primary" target="_blank">
                        <i class="fas fa-file-pdf"></i> Baixar PDF
                    </a>
                    <?php endif; ?>

                    <a href="historico.php?id=<?php echo $id; ?>" class="btn btn-secondary">
                        <i class="fas fa-history"></i> Histórico
                    </a>
                </div>
            </div>

            <!-- Status Bar -->
            <div class="contrato-status-bar">
                <div class="status-icon <?php echo $contrato['status']; ?>">
                    <?php
                    switch($contrato['status']) {
                        case 'rascunho': echo '<i class="fas fa-pen"></i>'; break;
                        case 'enviado':  echo '<i class="fas fa-paper-plane"></i>'; break;
                        case 'assinado': echo '<i class="fas fa-check-circle"></i>'; break;
                        case 'cancelado':echo '<i class="fas fa-ban"></i>'; break;
                        default:         echo '<i class="fas fa-file"></i>';
                    }
                    ?>
                </div>
                <div class="status-info">
                    <h2><?php echo htmlspecialchars($contrato['titulo']); ?></h2>
                    <div class="numero-contrato">
                        <i class="fas fa-hashtag"></i> <?php echo $contrato['numero_contrato']; ?> | 
                        Versão <?php echo $contrato['versao'] ?? '1.0'; ?>
                    </div>
                </div>
                <div>
                    <span class="status-badge <?php echo $contrato['status']; ?>">
                        <?php echo ucfirst($contrato['status']); ?>
                    </span>
                </div>
            </div>

            <!-- Dados do Cliente -->
            <div class="info-card">
                <h3><i class="fas fa-user"></i> Dados do Cliente</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Nome</span>
                        <span class="info-value"><?php echo htmlspecialchars($contrato['cliente_nome']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Empresa</span>
                        <span class="info-value"><?php echo htmlspecialchars($contrato['empresa'] ?: '—'); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">E-mail</span>
                        <span class="info-value"><?php echo htmlspecialchars($contrato['email']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Telefone</span>
                        <span class="info-value"><?php echo htmlspecialchars($contrato['telefone'] ?: '—'); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">CPF/CNPJ</span>
                        <span class="info-value"><?php echo htmlspecialchars($contrato['cpf_cnpj'] ?: '—'); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Endereço</span>
                        <span class="info-value"><?php echo htmlspecialchars($contrato['endereco'] ?: '—'); ?></span>
                    </div>
                </div>
            </div>

            <!-- Detalhes do Contrato -->
            <div class="info-card">
                <h3><i class="fas fa-file-signature"></i> Detalhes do Contrato</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Tipo de Contrato</span>
                        <span class="info-value"><?php echo ucfirst($contrato['tipo_contrato']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Plano</span>
                        <span class="info-value"><?php echo htmlspecialchars($contrato['nome_plano'] ?: 'Personalizado'); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Valor do Desenvolvimento</span>
                        <span class="info-value">R$ <?php echo number_format($contrato['valor_total'] ?? 0, 2, ',', '.'); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Valor Mensal</span>
                        <span class="info-value">R$ <?php echo number_format($contrato['valor_mensal'] ?? 0, 2, ',', '.'); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Multa Cancelamento</span>
                        <span class="info-value">R$ <?php echo number_format($contrato['multa_cancelamento'] ?? 0, 2, ',', '.'); ?> (<?php echo $contrato['percentual_multa']; ?>%)</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Fidelidade</span>
                        <span class="info-value"><?php echo $contrato['prazo_fidelidade']; ?> meses</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Data de Assinatura</span>
                        <span class="info-value"><?php echo $contrato['data_assinatura'] ? date('d/m/Y', strtotime($contrato['data_assinatura'])) : '—'; ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Data de Criação</span>
                        <span class="info-value"><?php echo date('d/m/Y H:i', strtotime($contrato['created_at'])); ?></span>
                    </div>
                </div>

                <?php if($contrato['observacoes']): ?>
                <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--border);">
                    <strong>Observações:</strong>
                    <p style="color: var(--text-secondary); margin-top: 10px;"><?php echo nl2br(htmlspecialchars($contrato['observacoes'])); ?></p>
                </div>
                <?php endif; ?>
            </div>

            <!-- Conteúdo do Contrato + Assinaturas -->
            <div class="contrato-conteudo">
                <?php echo $conteudo_limpo; ?>

                <!-- Bloco de assinaturas controlado pelo PHP -->
                <div class="assinaturas-bloco">
                    <div class="assinaturas-cols">

                        <!-- CONTRATANTE -->
                        <div class="assinatura-col">
                            <?php if(!empty($contrato['assinatura_base64'])): ?>
                            <img src="<?php echo $contrato['assinatura_base64']; ?>" alt="Assinatura">
                            <?php endif; ?>
                            <div class="linha-ass"></div>
                            <p class="ass-nome">CONTRATANTE</p>
                            <p class="ass-sub"><?php echo htmlspecialchars($contrato['cliente_nome']); ?></p>
                            <?php if($contrato['data_assinatura_cliente']): ?>
                            <p class="ass-data">
                                Assinado em: <?php echo date('d/m/Y \à\s H:i', strtotime($contrato['data_assinatura_cliente'])); ?>
                            </p>
                            <span class="ass-badge">✓ Assinado digitalmente</span>
                            <?php else: ?>
                            <p class="ass-pendente">Aguardando assinatura</p>
                            <?php endif; ?>
                        </div>

                        <!-- CONTRATADA -->
                        <div class="assinatura-col">
                            <div class="linha-ass" style="margin-top: <?php echo !empty($contrato['assinatura_base64']) ? '100px' : '10px'; ?>;"></div>
                            <p class="ass-nome">CONTRATADA</p>
                            <p class="ass-sub">GESTOR ARCON ADMIN</p>
                            <p class="ass-data">Documento gerado eletronicamente</p>
                        </div>

                    </div>

                    <p class="ass-rodape">
                        © - GESTOR ARCON ADMIN, <?php 
                            echo date('d') . ' de ' . $meses[date('n')-1] . ' de ' . date('Y'); 
                        ?>
                    </p>
                </div>
            </div>

            <!-- Histórico Recente -->
            <?php if($historico && $historico->num_rows > 0): ?>
            <div class="table-container">
                <div class="table-header">
                    <h3 class="table-title">
                        <i class="fas fa-history"></i> Últimas Atividades
                    </h3>
                    <a href="historico.php?id=<?php echo $id; ?>" class="btn-icon">
                        Ver todos <i class="fas fa-arrow-right"></i>
                    </a>
                </div>

                <?php 
                $count = 0;
                while($hist = $historico->fetch_assoc()): 
                    if($count++ >= 5) break;
                ?>
                <div class="historico-item">
                    <div class="historico-icon">
                        <i class="fas fa-circle"></i>
                    </div>
                    <div class="historico-content">
                        <div class="historico-acao"><?php echo htmlspecialchars($hist['acao']); ?></div>
                        <div class="historico-data">
                            <?php echo date('d/m/Y H:i', strtotime($hist['created_at'])); ?>
                            por <?php echo htmlspecialchars($hist['admin_nome'] ?: 'Sistema'); ?>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<script>
const themeToggle = document.getElementById('themeToggle');
const themeIcon   = document.getElementById('themeIcon');
const body        = document.body;

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
