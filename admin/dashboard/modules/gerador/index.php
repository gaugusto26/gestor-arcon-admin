<?php
$page_title = 'Gerador de Contratos';
require_once '../../includes/header.php';
require_once '../../includes/menu.php';
require_once '../contratos/config.php';

// Busca clientes para select
$clientes = $conn->query("SELECT id, nome, email, empresa, cpf_cnpj, endereco, cidade, estado, cep, telefone, celular FROM clientes WHERE status = 'ativo' ORDER BY nome");

// Busca planos padrão
$planos_padrao = $conn->query("SELECT id, nome, preco FROM planos WHERE ativo = 1 ORDER BY nome");

// Busca contratos ativos para renovação/cancelamento
$contratos_ativos = $conn->query("
    SELECT c.id, c.numero_contrato, c.valor_total, c.valor_mensal, c.percentual_multa, 
           c.prazo_fidelidade, c.data_assinatura, cl.nome as cliente_nome, cl.id as cliente_id
    FROM contratos c
    JOIN clientes cl ON c.cliente_id = cl.id
    WHERE c.status = 'assinado' AND c.tipo_contrato = 'adesao'
    ORDER BY c.created_at DESC
");

// Busca cláusulas para visualização
$clausulas_adesao = getClausulasPorTipo('adesao');
$clausulas_renovacao = getClausulasPorTipo('renovacao');
$clausulas_cancelamento = getClausulasPorTipo('cancelamento');
?>

<style>
.gerador-container {
    max-width: 1200px;
    margin: 0 auto;
}

.gerador-tabs {
    display: flex;
    gap: 10px;
    margin-bottom: 30px;
    border-bottom: 1px solid var(--border);
    padding-bottom: 15px;
    flex-wrap: wrap;
}

.tab-btn {
    padding: 12px 25px;
    border-radius: 50px;
    border: 1px solid var(--border);
    background: #ffffff;
    color: var(--text-secondary);
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
}

.tab-btn i {
    color: #4361ee;
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

.form-card {
    background: #ffffff;
    border: 1px solid var(--border);
    border-radius: 24px;
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.05);
}

.form-card h2 {
    font-size: 1.3rem;
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f0f0f0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.form-card h2 i {
    color: #4361ee;
}

.form-row {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
    margin-bottom: 20px;
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
    width: 20px;
}

.form-control {
    width: 100%;
    padding: 12px 18px;
    border: 1px solid var(--border);
    border-radius: 12px;
    background: #f8faff;
    color: var(--text-primary);
    font-size: 0.95rem;
    transition: all 0.2s ease;
}

.form-control:focus {
    outline: none;
    border-color: #4361ee;
    background: #ffffff;
    box-shadow: 0 0 0 3px rgba(67,97,238,0.1);
}

textarea.form-control {
    min-height: 100px;
    resize: vertical;
}

.contrato-preview {
    background: #ffffff;
    border: 2px dashed var(--border);
    border-radius: 24px;
    padding: 40px;
    margin-top: 30px;
    font-family: 'Times New Roman', Times, serif;
    line-height: 1.8;
    max-height: 600px;
    overflow-y: auto;
    box-shadow: inset 0 0 20px rgba(0,0,0,0.02);
}

.contrato-preview h1 {
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

.contrato-preview h2 {
    font-size: 18px;
    font-weight: bold;
    margin: 30px 0 15px;
    color: #1e293b;
    border-left: 4px solid #4361ee;
    padding-left: 15px;
}

.contrato-preview p {
    font-size: 14px;
    line-height: 1.8;
    margin-bottom: 15px;
    color: #334155;
    text-align: justify;
}

.contrato-preview strong {
    color: #0b5cff;
    font-weight: 700;
}

.contrato-preview .destaque {
    background: #f8faff;
    padding: 20px;
    border-radius: 8px;
    margin: 20px 0;
    border-left: 4px solid #4361ee;
}

.contrato-preview ul, .contrato-preview ol {
    margin: 15px 0 15px 30px;
    line-height: 1.8;
    color: #334155;
}

.valor-destaque {
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

.assinaturas {
    display: flex;
    justify-content: space-between;
    margin-top: 60px;
    padding-top: 30px;
    border-top: 1px dashed #cbd5e1;
}

.assinatura-bloco {
    text-align: center;
    width: 45%;
}

.linha-assinatura {
    width: 100%;
    border-top: 1px solid #000;
    margin: 30px 0 10px;
}

.data-local {
    text-align: center;
    margin-top: 40px;
    font-style: italic;
    color: #64748b;
}

.clausula-item {
    margin-bottom: 20px;
    padding: 15px;
    background: #f8faff;
    border-radius: 8px;
}

.clausula-titulo {
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: bold;
    color: #1e293b;
    margin-bottom: 10px;
}

.clausula-titulo i {
    color: #4361ee;
}

.btn {
    padding: 14px 30px;
    border-radius: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    border: none;
    font-size: 1rem;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
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

.btn-warning:hover {
    background: #ea580c;
}

.action-buttons {
    display: flex;
    gap: 15px;
    justify-content: flex-end;
    margin-top: 30px;
    flex-wrap: wrap;
}

.info-box {
    background: #f8faff;
    border-left: 4px solid #4361ee;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 25px;
}

.info-box h4 {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 10px;
    color: var(--text-primary);
}

.info-box ul {
    padding-left: 20px;
    color: var(--text-secondary);
}

.loading {
    display: inline-block;
    width: 20px;
    height: 20px;
    border: 3px solid rgba(255,255,255,.3);
    border-radius: 50%;
    border-top-color: #fff;
    animation: spin 1s ease-in-out infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.multa-calculada {
    background: #f8faff;
    padding: 15px;
    border-radius: 12px;
    margin: 15px 0;
    font-size: 1.1rem;
    border-left: 4px solid #f97316;
}

.multa-valor {
    font-size: 1.5rem;
    font-weight: 700;
    color: #f97316;
}
</style>

<div class="main-content" id="mainContent">
    <div class="top-bar">
        <h1 class="page-title">
            <i class="fas fa-file-alt" style="color: #4361ee; margin-right: 10px;"></i>
            Gerador de Contratos
        </h1>
        
        <div class="top-bar-actions">
            <div class="theme-toggle" id="themeToggle">
                <i class="fas <?php echo $tema == 'dark' ? 'fa-moon' : 'fa-sun'; ?>" id="themeIcon"></i>
            </div>
        </div>
    </div>

    <div class="content-area">
        <div class="gerador-container">
            <!-- Tabs -->
            <div class="gerador-tabs">
                <button class="tab-btn active" onclick="mudarTab('adesao')">
                    <i class="fas fa-file-signature"></i> Contrato de Adesão
                </button>
                <button class="tab-btn" onclick="mudarTab('renovacao')">
                    <i class="fas fa-sync-alt"></i> Renovação
                </button>
                <button class="tab-btn" onclick="mudarTab('cancelamento')">
                    <i class="fas fa-ban"></i> Cancelamento
                </button>
            </div>

            <!-- ===== TAB 1: CONTRATO DE ADESÃO ===== -->
            <div id="tab-adesao" class="tab-content active">
                <form id="formAdesao">
                    <div class="form-card">
                        <h2><i class="fas fa-user"></i> Dados do Cliente</h2>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label><i class="fas fa-user"></i> Cliente *</label>
                                <select class="form-control" id="adesao_cliente_id" required onchange="carregarClienteAdesao()">
                                    <option value="">Selecione um cliente</option>
                                    <?php 
                                    $clientes->data_seek(0);
                                    while($cli = $clientes->fetch_assoc()): 
                                    ?>
                                    <option value="<?php echo $cli['id']; ?>" 
                                            data-nome="<?php echo htmlspecialchars($cli['nome']); ?>" 
                                            data-email="<?php echo $cli['email']; ?>" 
                                            data-empresa="<?php echo htmlspecialchars($cli['empresa']); ?>"
                                            data-cpf="<?php echo $cli['cpf_cnpj']; ?>"
                                            data-endereco="<?php echo htmlspecialchars($cli['endereco']); ?>"
                                            data-cidade="<?php echo $cli['cidade']; ?>"
                                            data-estado="<?php echo $cli['estado']; ?>"
                                            data-cep="<?php echo $cli['cep']; ?>"
                                            data-telefone="<?php echo $cli['telefone'] ?: $cli['celular']; ?>">
                                        <?php echo $cli['nome']; ?> - <?php echo $cli['empresa'] ?: $cli['email']; ?>
                                    </option>
                                    <?php endwhile; ?>
                                    <option value="novo">+ Criar novo cliente</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label><i class="fas fa-building"></i> Empresa</label>
                                <input type="text" class="form-control" id="adesao_empresa" readonly>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label><i class="fas fa-envelope"></i> E-mail</label>
                                <input type="email" class="form-control" id="adesao_email" readonly>
                            </div>
                            
                            <div class="form-group">
                                <label><i class="fas fa-phone"></i> Telefone</label>
                                <input type="text" class="form-control" id="adesao_telefone" readonly>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label><i class="fas fa-id-card"></i> CPF/CNPJ</label>
                                <input type="text" class="form-control" id="adesao_cpf" readonly>
                            </div>
                            
                            <div class="form-group">
                                <label><i class="fas fa-map-marker-alt"></i> Endereço</label>
                                <input type="text" class="form-control" id="adesao_endereco" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="form-card">
                        <h2><i class="fas fa-crown"></i> Dados do Plano</h2>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label><i class="fas fa-tag"></i> Tipo de Plano</label>
                                <select class="form-control" id="adesao_tipo_plano" onchange="mudarTipoPlanoAdesao()">
                                    <option value="padrao">Plano Padrão</option>
                                    <option value="outros">Plano Personalizado (Outros)</option>
                                </select>
                            </div>
                            
                            <div class="form-group" id="adesao_plano_padrao_group">
                                <label><i class="fas fa-list"></i> Selecione o Plano</label>
                                <select class="form-control" id="adesao_plano_padrao" onchange="carregarValorPlanoAdesao()">
                                    <option value="">Selecione</option>
                                    <?php 
                                    $planos_padrao->data_seek(0);
                                    while($plano = $planos_padrao->fetch_assoc()): 
                                    ?>
                                    <option value="<?php echo $plano['id']; ?>" 
                                            data-preco="<?php echo $plano['preco']; ?>"
                                            data-nome="<?php echo htmlspecialchars($plano['nome']); ?>">
                                        <?php echo $plano['nome']; ?> - R$ <?php echo number_format($plano['preco'], 2, ',', '.'); ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="form-group" id="adesao_plano_outros_group" style="display: none;">
                                <label><i class="fas fa-pen"></i> Nome do Plano Personalizado</label>
                                <input type="text" class="form-control" id="adesao_plano_outros_nome" placeholder="Ex: Plano ARCON Personalizado">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label><i class="fas fa-dollar-sign"></i> Valor de Implantação/Licenciamento (R$) *</label>
                                <input type="number" step="0.01" class="form-control" id="adesao_valor_plano" required value="0.00">
                            </div>
                            
                            <div class="form-group">
                                <label><i class="fas fa-calendar-alt"></i> Valor da Mensalidade (R$) *</label>
                                <input type="number" step="0.01" class="form-control" id="adesao_valor_mensal" required value="0.00">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label><i class="fas fa-credit-card"></i> Forma de Pagamento</label>
                                <select class="form-control" id="adesao_forma_pagamento">
                                    <option value="vista">À Vista</option>
                                    <option value="parcelado">Parcelado</option>
                                    <option value="recorrente">Recorrente (Mensal)</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label><i class="fas fa-sort-numeric-up"></i> Número de Parcelas</label>
                                <input type="number" class="form-control" id="adesao_numero_parcelas" value="1" min="1">
                            </div>
                        </div>

                        <div class="form-row">
    <div class="form-group">
        <label><i class="fas fa-calendar-day"></i> Dia de Vencimento das Mensalidades *</label>
        <select class="form-control" id="adesao_dia_vencimento" required>
            <?php for($dia = 1; $dia <= 31; $dia++): ?>
            <option value="<?php echo $dia; ?>" <?php echo $dia == 10 ? 'selected' : ''; ?>><?php echo $dia; ?></option>
            <?php endfor; ?>
        </select>
        <small style="color: var(--text-muted);">Dia do mês para pagamento das mensalidades</small>
    </div>
    
    <div class="form-group">
        <label><i class="fas fa-clock"></i> Prazo para Primeira Parcela (dias)</label>
        <input type="number" class="form-control" id="adesao_prazo_primeira_parcela" value="30" min="1">
        <small style="color: var(--text-muted);">Dias após assinatura para vencimento da primeira parcela</small>
    </div>
</div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label><i class="fas fa-play"></i> Data de Início</label>
                                <input type="date" class="form-control" id="adesao_data_inicio" value="<?php echo date('Y-m-d'); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label><i class="fas fa-hourglass-half"></i> Prazo de Ativação/Implantação (dias)</label>
                                <input type="number" class="form-control" id="adesao_prazo_desenvolvimento" value="30">
                            </div>
                        </div>
                    </div>

                    <div class="form-card">
                        <h2><i class="fas fa-gavel"></i> Cláusulas Contratuais</h2>
                        
                        <div class="info-box">
                            <h4><i class="fas fa-info-circle"></i> Informações importantes</h4>
                            <ul>
                                <li><strong>Multa de cancelamento:</strong> 20% do valor total do contrato, proporcional ao tempo de fidelidade não cumprido</li>
                                <li><strong>Período de fidelidade:</strong> 12 meses</li>
                                <li><strong>Mensalidade:</strong> Inclui acesso ao ARCON, atualizações, suporte e manutenção da plataforma</li>
                                <li><strong>Serviços extras:</strong> Implantações, integrações ou demandas fora do plano serão orçadas separadamente</li>
                            </ul>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label><i class="fas fa-percent"></i> Percentual de Multa (%)</label>
                                <input type="number" step="0.01" class="form-control" id="adesao_percentual_multa" value="20">
                            </div>
                            
                            <div class="form-group">
                                <label><i class="fas fa-calendar-week"></i> Fidelidade (meses)</label>
                                <input type="number" class="form-control" id="adesao_fidelidade" value="12">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-align-left"></i> Observações Adicionais</label>
                            <textarea class="form-control" id="adesao_observacoes" rows="3" placeholder="Informações complementares..."></textarea>
                        </div>
                    </div>

                    <!-- Preview do Contrato de Adesão -->
                    <div class="form-card">
                        <h2><i class="fas fa-eye"></i> Pré-visualização do Contrato</h2>
                        
                        <div class="contrato-preview" id="preview-adesao">
                            <!-- Conteúdo será inserido via JavaScript -->
                        </div>
                        
                        <div class="action-buttons">
                            <button type="button" class="btn btn-secondary" onclick="gerarPreviewAdesao()">
                                <i class="fas fa-sync-alt"></i> Atualizar Preview
                            </button>
                            <button type="button" class="btn btn-success" onclick="gerarPDF('preview-adesao')">
                                <i class="fas fa-file-pdf"></i> Gerar PDF
                            </button>
                            <button type="button" class="btn btn-primary" onclick="salvarContratoAdesao()">
                                <i class="fas fa-save"></i> Salvar Contrato
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- ===== TAB 2: RENOVAÇÃO ===== -->
            <div id="tab-renovacao" class="tab-content">
                <form id="formRenovacao">
                    <div class="form-card">
                        <h2><i class="fas fa-file-contract"></i> Contrato Original</h2>
                        
                        <div class="form-group">
                            <label><i class="fas fa-file-signature"></i> Selecione o Contrato a Renovar *</label>
                            <select class="form-control" id="renovacao_contrato_id" required onchange="carregarContratoRenovacao()">
                                <option value="">Selecione um contrato</option>
                                <?php 
                                $contratos_ativos->data_seek(0);
                                while($cont = $contratos_ativos->fetch_assoc()): 
                                ?>
                                <option value="<?php echo $cont['id']; ?>" 
                                        data-cliente-id="<?php echo $cont['cliente_id']; ?>"
                                        data-cliente-nome="<?php echo htmlspecialchars($cont['cliente_nome']); ?>"
                                        data-valor="<?php echo $cont['valor_total']; ?>"
                                        data-mensal="<?php echo $cont['valor_mensal']; ?>"
                                        data-multa="<?php echo $cont['percentual_multa']; ?>"
                                        data-fidelidade="<?php echo $cont['prazo_fidelidade']; ?>"
                                        data-data="<?php echo $cont['data_assinatura']; ?>">
                                    <?php echo $cont['numero_contrato']; ?> - <?php echo $cont['cliente_nome']; ?> 
                                    (R$ <?php echo number_format($cont['valor_mensal'], 2, ',', '.'); ?>/mês)
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-card">
                        <h2><i class="fas fa-user"></i> Dados do Cliente</h2>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label><i class="fas fa-user"></i> Cliente</label>
                                <input type="text" class="form-control" id="renovacao_cliente_nome" readonly>
                            </div>
                            
                            <div class="form-group">
                                <label><i class="fas fa-building"></i> Contrato Original</label>
                                <input type="text" class="form-control" id="renovacao_contrato_original" readonly>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label><i class="fas fa-calendar-alt"></i> Data da Assinatura Original</label>
                                <input type="text" class="form-control" id="renovacao_data_original" readonly>
                            </div>
                            
                            <div class="form-group">
                                <label><i class="fas fa-dollar-sign"></i> Valor Mensal Atual</label>
                                <input type="text" class="form-control" id="renovacao_valor_atual" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="form-card">
                        <h2><i class="fas fa-sync-alt"></i> Dados da Renovação</h2>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label><i class="fas fa-calendar-plus"></i> Nova Data de Início *</label>
                                <input type="date" class="form-control" id="renovacao_nova_data" value="<?php echo date('Y-m-d'); ?>" onchange="gerarPreviewRenovacao()">
                            </div>
                            
                            <div class="form-group">
                                <label><i class="fas fa-percent"></i> Percentual de Reajuste (%)</label>
                                <input type="number" step="0.1" class="form-control" id="renovacao_reajuste" value="5.5" oninput="calcularNovoValorRenovacao()">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label><i class="fas fa-dollar-sign"></i> Novo Valor Mensal</label>
                                <input type="text" class="form-control" id="renovacao_novo_valor" readonly>
                            </div>
                            
                            <div class="form-group">
                                <label><i class="fas fa-hourglass-half"></i> Novo Prazo (meses)</label>
                                <select class="form-control" id="renovacao_novo_prazo" onchange="gerarPreviewRenovacao()">
                                    <option value="12">12 meses</option>
                                    <option value="24">24 meses</option>
                                    <option value="36">36 meses</option>
                                    <option value="0">Indeterminado</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-align-left"></i> Observações da Renovação</label>
                            <textarea class="form-control" id="renovacao_observacoes" rows="3" placeholder="Motivo da renovação, melhorias, etc..." oninput="gerarPreviewRenovacao()"></textarea>
                        </div>
                    </div>

                    <!-- Preview do Termo de Renovação -->
                    <div class="form-card">
                        <h2><i class="fas fa-eye"></i> Pré-visualização do Termo de Renovação</h2>
                        
                        <div class="contrato-preview" id="preview-renovacao">
                            <!-- Conteúdo será inserido via JavaScript -->
                        </div>
                        
                        <div class="action-buttons">
                            <button type="button" class="btn btn-secondary" onclick="gerarPreviewRenovacao()">
                                <i class="fas fa-sync-alt"></i> Atualizar Preview
                            </button>
                            <button type="button" class="btn btn-success" onclick="gerarPDF('preview-renovacao')">
                                <i class="fas fa-file-pdf"></i> Gerar PDF
                            </button>
                            <button type="button" class="btn btn-primary" onclick="salvarContratoRenovacao()">
                                <i class="fas fa-save"></i> Salvar Termo
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- ===== TAB 3: CANCELAMENTO ===== -->
            <div id="tab-cancelamento" class="tab-content">
                <form id="formCancelamento">
                    <div class="form-card">
                        <h2><i class="fas fa-file-contract"></i> Contrato a Cancelar</h2>
                        
                        <div class="form-group">
                            <label><i class="fas fa-file-signature"></i> Selecione o Contrato a Cancelar *</label>
                            <select class="form-control" id="cancelamento_contrato_id" required onchange="carregarContratoCancelamento()">
                                <option value="">Selecione um contrato</option>
                                <?php 
                                $contratos_ativos->data_seek(0);
                                while($cont = $contratos_ativos->fetch_assoc()): 
                                ?>
                                <option value="<?php echo $cont['id']; ?>" 
                                        data-cliente-id="<?php echo $cont['cliente_id']; ?>"
                                        data-cliente-nome="<?php echo htmlspecialchars($cont['cliente_nome']); ?>"
                                        data-valor="<?php echo $cont['valor_total']; ?>"
                                        data-mensal="<?php echo $cont['valor_mensal']; ?>"
                                        data-multa="<?php echo $cont['percentual_multa']; ?>"
                                        data-fidelidade="<?php echo $cont['prazo_fidelidade']; ?>"
                                        data-data="<?php echo $cont['data_assinatura']; ?>">
                                    <?php echo $cont['numero_contrato']; ?> - <?php echo $cont['cliente_nome']; ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-card">
                        <h2><i class="fas fa-user"></i> Dados do Contrato</h2>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label><i class="fas fa-user"></i> Cliente</label>
                                <input type="text" class="form-control" id="cancelamento_cliente_nome" readonly>
                            </div>
                            
                            <div class="form-group">
                                <label><i class="fas fa-calendar-alt"></i> Data de Assinatura</label>
                                <input type="text" class="form-control" id="cancelamento_data_original" readonly>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label><i class="fas fa-dollar-sign"></i> Valor Total</label>
                                <input type="text" class="form-control" id="cancelamento_valor_total" readonly>
                            </div>
                            
                            <div class="form-group">
                                <label><i class="fas fa-dollar-sign"></i> Valor Mensal</label>
                                <input type="text" class="form-control" id="cancelamento_valor_mensal" readonly>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label><i class="fas fa-percent"></i> Percentual de Multa</label>
                                <input type="text" class="form-control" id="cancelamento_percentual_multa" readonly>
                            </div>
                            
                            <div class="form-group">
                                <label><i class="fas fa-calendar-week"></i> Fidelidade (meses)</label>
                                <input type="text" class="form-control" id="cancelamento_fidelidade" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="form-card">
                        <h2><i class="fas fa-ban"></i> Dados do Cancelamento</h2>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label><i class="fas fa-calendar-times"></i> Data do Cancelamento *</label>
                                <input type="date" class="form-control" id="cancelamento_data" value="<?php echo date('Y-m-d'); ?>" onchange="calcularMultaCancelamento()">
                            </div>
                            
                            <div class="form-group">
                                <label><i class="fas fa-user"></i> Responsável pelo Cancelamento</label>
                                <select class="form-control" id="cancelamento_responsavel" onchange="calcularMultaCancelamento()">
                                    <option value="cliente">Cliente</option>
                                    <option value="digitalfive">Digital Five</option>
                                    <option value="mutuo">Mútuo Acordo</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-align-left"></i> Motivo do Cancelamento *</label>
                            <textarea class="form-control" id="cancelamento_motivo" rows="3" placeholder="Descreva o motivo do cancelamento..." oninput="gerarPreviewCancelamento()"></textarea>
                        </div>
                        
                        <div class="multa-calculada" id="multa_container">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span><i class="fas fa-exclamation-triangle"></i> <strong>Multa a ser paga:</strong></span>
                                <span class="multa-valor" id="cancelamento_multa_valor">R$ 0,00</span>
                            </div>
                            <div style="margin-top: 10px; font-size: 0.9rem;" id="multa_detalhe"></div>
                        </div>
                    </div>

                    <!-- Preview do Termo de Cancelamento -->
                    <div class="form-card">
                        <h2><i class="fas fa-eye"></i> Pré-visualização do Termo de Cancelamento</h2>
                        
                        <div class="contrato-preview" id="preview-cancelamento">
                            <!-- Conteúdo será inserido via JavaScript -->
                        </div>
                        
                        <div class="action-buttons">
                            <button type="button" class="btn btn-secondary" onclick="gerarPreviewCancelamento()">
                                <i class="fas fa-sync-alt"></i> Atualizar Preview
                            </button>
                            <button type="button" class="btn btn-success" onclick="gerarPDF('preview-cancelamento')">
                                <i class="fas fa-file-pdf"></i> Gerar PDF
                            </button>
                            <button type="button" class="btn btn-warning" onclick="salvarContratoCancelamento()">
                                <i class="fas fa-save"></i> Salvar Termo
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// ===== DADOS DAS CLÁUSULAS =====
let clausulasAdesao = <?php 
    $clausulas = [];
    while($c = $clausulas_adesao->fetch_assoc()) {
        $clausulas[] = $c;
    }
    echo json_encode($clausulas, JSON_UNESCAPED_UNICODE);
?>;

let clausulasRenovacao = <?php 
    $clausulas = [];
    while($c = $clausulas_renovacao->fetch_assoc()) {
        $clausulas[] = $c;
    }
    echo json_encode($clausulas, JSON_UNESCAPED_UNICODE);
?>;

let clausulasCancelamento = <?php 
    $clausulas = [];
    while($c = $clausulas_cancelamento->fetch_assoc()) {
        $clausulas[] = $c;
    }
    echo json_encode($clausulas, JSON_UNESCAPED_UNICODE);
?>;

// ===== FUNÇÕES GERAIS =====
function mudarTab(tab) {
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
    
    event.currentTarget.classList.add('active');
    document.getElementById('tab-' + tab).classList.add('active');
    
    // Gerar preview da tab ativa se houver cliente selecionado
    if(tab == 'adesao' && document.getElementById('adesao_cliente_id').value) {
        setTimeout(gerarPreviewAdesao, 100);
    } else if(tab == 'renovacao' && document.getElementById('renovacao_contrato_id').value) {
        setTimeout(gerarPreviewRenovacao, 100);
    } else if(tab == 'cancelamento' && document.getElementById('cancelamento_contrato_id').value) {
        setTimeout(gerarPreviewCancelamento, 100);
    }
}

function gerarPDF(previewId) {
    const conteudo = document.getElementById(previewId).innerHTML;
    
    const estiloPDF = `
        <style>
            body {
                font-family: 'Times New Roman', Times, serif;
                margin: 40px;
                line-height: 1.6;
            }
            h1 {
                font-size: 24px;
                text-align: center;
                margin-bottom: 30px;
                text-transform: uppercase;
                border-bottom: 2px solid #4361ee;
                padding-bottom: 15px;
            }
            h2 {
                font-size: 18px;
                margin: 30px 0 15px;
                border-left: 4px solid #4361ee;
                padding-left: 15px;
            }
            p {
                margin-bottom: 15px;
                text-align: justify;
                font-size: 14px;
            }
            .clausula-item {
                margin-bottom: 20px;
            }
            .clausula-titulo {
                font-weight: bold;
                margin-bottom: 10px;
                color: #1e293b;
            }
            .assinaturas {
                display: flex;
                justify-content: space-between;
                margin-top: 80px;
            }
            .assinatura-bloco {
                text-align: center;
                width: 45%;
            }
            .linha-assinatura {
                border-top: 1px solid #000;
                margin: 30px 0 10px;
            }
            .data-local {
                text-align: center;
                margin-top: 50px;
                font-style: italic;
            }
            strong {
                color: #0b5cff;
            }
            .multa-destaque {
                background: #fff7ed;
                border-left: 4px solid #f97316;
                padding: 15px;
                margin: 20px 0;
            }
        </style>
    `;
    
    const htmlCompleto = `
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Contrato Digital Five | ARCON</title>
            ${estiloPDF}
        </head>
        <body>
            ${conteudo}
        </body>
        </html>
    `;
    
    const iframe = document.createElement('iframe');
    iframe.style.display = 'none';
    document.body.appendChild(iframe);
    
    const doc = iframe.contentWindow.document;
    doc.open();
    doc.write(htmlCompleto);
    doc.close();
    
    iframe.onload = function() {
        setTimeout(() => {
            iframe.contentWindow.focus();
            iframe.contentWindow.print();
            setTimeout(() => {
                document.body.removeChild(iframe);
            }, 1000);
        }, 500);
    };
}

// ===== FUNÇÕES DA TAB ADESÃO =====
function mudarTipoPlanoAdesao() {
    const tipo = document.getElementById('adesao_tipo_plano').value;
    if(tipo == 'padrao') {
        document.getElementById('adesao_plano_padrao_group').style.display = 'block';
        document.getElementById('adesao_plano_outros_group').style.display = 'none';
    } else {
        document.getElementById('adesao_plano_padrao_group').style.display = 'none';
        document.getElementById('adesao_plano_outros_group').style.display = 'block';
    }
}

function carregarValorPlanoAdesao() {
    const select = document.getElementById('adesao_plano_padrao');
    const selected = select.options[select.selectedIndex];
    if(selected && selected.dataset.preco) {
        document.getElementById('adesao_valor_plano').value = selected.dataset.preco;
        gerarPreviewAdesao();
    }
}

function carregarClienteAdesao() {
    const select = document.getElementById('adesao_cliente_id');
    const selected = select.options[select.selectedIndex];
    
    if(select.value == 'novo') {
        window.open('../clientes/criar.php', '_blank');
        select.value = '';
        return;
    }
    
    if(selected && select.value) {
        document.getElementById('adesao_empresa').value = selected.dataset.empresa || '';
        document.getElementById('adesao_email').value = selected.dataset.email || '';
        document.getElementById('adesao_telefone').value = selected.dataset.telefone || '';
        document.getElementById('adesao_cpf').value = selected.dataset.cpf || '';
        
        const enderecoCompleto = selected.dataset.endereco ? 
            `${selected.dataset.endereco}${selected.dataset.cidade ? ', ' + selected.dataset.cidade : ''}${selected.dataset.estado ? ' - ' + selected.dataset.estado : ''}${selected.dataset.cep ? ' - CEP ' + selected.dataset.cep : ''}` 
            : '';
        document.getElementById('adesao_endereco').value = enderecoCompleto;
        
        gerarPreviewAdesao();
    }
}

function gerarPreviewAdesao() {
    const clienteSelect = document.getElementById('adesao_cliente_id');
    if(!clienteSelect.value) return;
    
    const selected = clienteSelect.options[clienteSelect.selectedIndex];
    const clienteNome = selected.dataset.nome || '[NOME DO CLIENTE]';
    const documento = document.getElementById('adesao_cpf').value || '[DOCUMENTO]';
    const endereco = document.getElementById('adesao_endereco').value || '[ENDEREÇO]';
    
    const valorPlano = parseFloat(document.getElementById('adesao_valor_plano').value) || 0;
    const valorMensal = parseFloat(document.getElementById('adesao_valor_mensal').value) || 0;
    const percentualMulta = parseFloat(document.getElementById('adesao_percentual_multa').value) || 20;
    const fidelidade = parseInt(document.getElementById('adesao_fidelidade').value) || 12;
    const prazo = parseInt(document.getElementById('adesao_prazo_desenvolvimento').value) || 30;
    const numeroParcelas = parseInt(document.getElementById('adesao_numero_parcelas').value) || 1;
    const diaVencimento = parseInt(document.getElementById('adesao_dia_vencimento').value) || 10;
    const prazoPrimeiraParcela = parseInt(document.getElementById('adesao_prazo_primeira_parcela').value) || 30;
    
    const multa = (valorPlano * percentualMulta) / 100;
    const numeroContrato = 'Arcon-' + new Date().getFullYear() + ('0' + (new Date().getMonth()+1)).slice(-2) + '-' + Math.floor(1000 + Math.random() * 9000);
    
    // Calcula datas
    const dataInicio = new Date(document.getElementById('adesao_data_inicio').value);
    const dataPrimeiraParcela = new Date(dataInicio);
    dataPrimeiraParcela.setDate(dataPrimeiraParcela.getDate() + prazoPrimeiraParcela);
    
    const dataPrimeiraMensalidade = new Date(dataInicio);
    dataPrimeiraMensalidade.setDate(dataPrimeiraMensalidade.getDate() + prazo);
    
    const valorPlanoFormatado = valorPlano.toFixed(2).replace('.', ',');
    const valorMensalFormatado = valorMensal.toFixed(2).replace('.', ',');
    const multaFormatada = multa.toFixed(2).replace('.', ',');
    
    const meses = ['janeiro', 'fevereiro', 'março', 'abril', 'maio', 'junho', 'julho', 'agosto', 'setembro', 'outubro', 'novembro', 'dezembro'];
    
    let clausulasHTML = '';
    clausulasAdesao.forEach(clausula => {
        let conteudo = clausula.conteudo
            .replace(/{valor_plano}/g, '' + valorPlanoFormatado)
            .replace(/{valor_plano_extenso}/g, valorPlanoFormatado + ' reais')
            .replace(/{valor_mensal}/g, '' + valorMensalFormatado)
            .replace(/{valor_mensal_extenso}/g, valorMensalFormatado + ' reais')
            .replace(/{multa_cancelamento}/g, '' + multaFormatada)
            .replace(/{multa_extenso}/g, multaFormatada + ' reais')
            .replace(/{percentual_multa}/g, percentualMulta)
            .replace(/{fidelidade}/g, fidelidade)
            .replace(/{prazo_desenvolvimento}/g, prazo)
            .replace(/{numero_parcelas}/g, numeroParcelas)
            .replace(/{data_primeira_parcela}/g, dataPrimeiraParcela.getDate() + ' de ' + meses[dataPrimeiraParcela.getMonth()] + ' de ' + dataPrimeiraParcela.getFullYear())
            .replace(/{dia_vencimento}/g, diaVencimento)
            .replace(/{data_primeira_mensalidade}/g, dataPrimeiraMensalidade.getDate() + ' de ' + meses[dataPrimeiraMensalidade.getMonth()] + ' de ' + dataPrimeiraMensalidade.getFullYear());
        
        clausulasHTML += `
            <div class="clausula-item">
                <div class="clausula-titulo">
                    <i class="fas fa-gavel"></i> ${clausula.titulo}
                </div>
                <div class="clausula-conteudo">
                    ${conteudo}
                </div>
            </div>
        `;
    });
    
    const dataAtual = new Date();
    
    const previewHTML = `
        <h1>CONTRATO DE LICENÇA DE USO E SERVIÇOS DO ARCON</h1>
        
        <p style="text-align: right;">Contrato nº: ${numeroContrato}</p>
        
        <p><strong>CONTRATANTE:</strong> ${clienteNome}, inscrito no CPF/CNPJ sob nº ${documento}, com endereço ${endereco}.</p>
        
        <p><strong>CONTRATADA:</strong> DIGITAL FIVE, responsável pelo produto ARCON, inscrita no CNPJ sob nº [CNPJ DA DIGITAL FIVE], com sede em [ENDEREÇO DA DIGITAL FIVE].</p>
        
        <p>As partes acima identificadas têm, entre si, justo e acordado o presente Contrato de Licença de Uso do ARCON e Serviços de Implantação, Suporte e Manutenção, que se regerá pelas cláusulas seguintes e pelas condições descritas neste instrumento.</p>
        
        ${clausulasHTML}
        
        <div class="assinaturas">
            <div class="assinatura-bloco">
                <div class="linha-assinatura"></div>
                <p><strong>CONTRATANTE</strong><br>${clienteNome}</p>
            </div>
            <div class="assinatura-bloco">
                <div class="linha-assinatura"></div>
                <p><strong>CONTRATADA</strong><br>DIGITAL FIVE<br>Produto: ARCON</p>
            </div>
        </div>
        
        <div class="data-local">
            DIGITAL FIVE | ARCON, ${dataAtual.getDate()} de ${meses[dataAtual.getMonth()]} de ${dataAtual.getFullYear()}
        </div>
    `;
    
    document.getElementById('preview-adesao').innerHTML = previewHTML;
}

function salvarContratoAdesao() {
    const clienteId = document.getElementById('adesao_cliente_id').value;
    if(!clienteId || clienteId === 'novo') {
        alert('❌ Selecione um cliente válido');
        return;
    }
    
    const valorPlano = parseFloat(document.getElementById('adesao_valor_plano').value) || 0;
    if(valorPlano <= 0) {
        alert('❌ Informe o valor de implantação/licenciamento');
        return;
    }

    // ── Captura dados do plano ──
    const tipoPlano = document.getElementById('adesao_tipo_plano').value;
    const planoPadraoSelect = document.getElementById('adesao_plano_padrao');
    const planoSelecionado = planoPadraoSelect.options[planoPadraoSelect.selectedIndex];
    
    const plano_id = tipoPlano === 'padrao' ? (planoPadraoSelect.value || null) : null;
    const nome_plano = tipoPlano === 'padrao' 
        ? (planoSelecionado.dataset.nome || 'Plano Padrão') 
        : (document.getElementById('adesao_plano_outros_nome').value || 'Plano Personalizado');

    const dados = {
        cliente_id: clienteId,
        tipo_contrato: 'adesao',
        titulo: 'Contrato de Licença de Uso e Serviços do ARCON',
        conteudo: document.getElementById('preview-adesao').innerHTML,
        valor_plano: valorPlano,
        valor_mensal: parseFloat(document.getElementById('adesao_valor_mensal').value) || 0,
        percentual_multa: parseFloat(document.getElementById('adesao_percentual_multa').value) || 20,
        fidelidade: parseInt(document.getElementById('adesao_fidelidade').value) || 12,
        prazo_desenvolvimento: parseInt(document.getElementById('adesao_prazo_desenvolvimento').value) || 30,
        forma_pagamento: document.getElementById('adesao_forma_pagamento').value,
        numero_parcelas: parseInt(document.getElementById('adesao_numero_parcelas').value) || 1,
        data_inicio: document.getElementById('adesao_data_inicio').value,
        observacoes: document.getElementById('adesao_observacoes').value,
        dia_vencimento: parseInt(document.getElementById('adesao_dia_vencimento').value) || 10,
        prazo_primeira_parcela: parseInt(document.getElementById('adesao_prazo_primeira_parcela').value) || 30,
        plano_id: plano_id,
        nome_plano: nome_plano,
        tipo_plano: tipoPlano
    };
    
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<span class="loading"></span> Salvando...';
    btn.disabled = true;
    
    fetch('salvar_contrato.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(dados)
    })
    .then(response => response.json())
    .then(data => {
        if(data.sucesso) {
            alert('✅ Contrato salvo com sucesso!');
            window.location.href = '../contratos/visualizar.php?id=' + data.id;
        } else {
            alert('❌ Erro ao salvar contrato: ' + data.erro);
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    })
    .catch(error => {
        alert('❌ Erro na requisição: ' + error);
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}


// ===== FUNÇÕES DA TAB RENOVAÇÃO =====
function carregarContratoRenovacao() {
    const select = document.getElementById('renovacao_contrato_id');
    const selected = select.options[select.selectedIndex];
    
    if(selected && select.value) {
        document.getElementById('renovacao_cliente_nome').value = selected.dataset.clienteNome || '';
        document.getElementById('renovacao_contrato_original').value = select.options[select.selectedIndex].text.split(' - ')[0];
        
        const dataOriginal = selected.dataset.data ? new Date(selected.dataset.data) : new Date();
        document.getElementById('renovacao_data_original').value = dataOriginal.toLocaleDateString('pt-BR');
        
        const valorAtual = parseFloat(selected.dataset.mensal) || 0;
        document.getElementById('renovacao_valor_atual').value = '' + valorAtual.toFixed(2).replace('.', ',');
        
        calcularNovoValorRenovacao();
    }
}

function calcularNovoValorRenovacao() {
    const select = document.getElementById('renovacao_contrato_id');
    const selected = select.options[select.selectedIndex];
    
    if(selected && select.value) {
        const valorAtual = parseFloat(selected.dataset.mensal) || 0;
        const reajuste = parseFloat(document.getElementById('renovacao_reajuste').value) || 0;
        const novoValor = valorAtual * (1 + reajuste/100);
        
        document.getElementById('renovacao_novo_valor').value = '' + novoValor.toFixed(2).replace('.', ',');
        gerarPreviewRenovacao();
    }
}

function gerarPreviewRenovacao() {
    const select = document.getElementById('renovacao_contrato_id');
    if(!select.value) return;
    
    const selected = select.options[select.selectedIndex];
    const clienteNome = selected.dataset.clienteNome || '[NOME DO CLIENTE]';
    const contratoOriginal = select.options[select.selectedIndex].text.split(' - ')[0];
    const dataOriginal = selected.dataset.data ? new Date(selected.dataset.data) : new Date();
    const valorAtual = parseFloat(selected.dataset.mensal) || 0;
    const reajuste = parseFloat(document.getElementById('renovacao_reajuste').value) || 0;
    const novoValor = valorAtual * (1 + reajuste/100);
    const novoPrazo = document.getElementById('renovacao_novo_prazo').value;
    const novaData = document.getElementById('renovacao_nova_data').value;
    const observacoes = document.getElementById('renovacao_observacoes').value;
    
    const dataObj = new Date(novaData);
    const meses = ['janeiro', 'fevereiro', 'março', 'abril', 'maio', 'junho', 'julho', 'agosto', 'setembro', 'outubro', 'novembro', 'dezembro'];
    
    let clausulasHTML = '';
    clausulasRenovacao.forEach(clausula => {
        let conteudo = clausula.conteudo
            .replace(/{contrato_original}/g, contratoOriginal)
            .replace(/{data_original}/g, dataOriginal.toLocaleDateString('pt-BR'))
            .replace(/{nova_data_inicio}/g, dataObj.getDate() + ' de ' + meses[dataObj.getMonth()] + ' de ' + dataObj.getFullYear())
            .replace(/{reajuste}/g, reajuste.toFixed(1).replace('.', ','))
            .replace(/{novo_valor_mensal}/g, novoValor.toFixed(2).replace('.', ','))
            .replace(/{novo_prazo}/g, novoPrazo == '0' ? 'indeterminado' : novoPrazo + ' meses');
        
        clausulasHTML += `
            <div class="clausula-item">
                <div class="clausula-titulo">
                    <i class="fas fa-gavel"></i> ${clausula.titulo}
                </div>
                <div class="clausula-conteudo">
                    ${conteudo}
                </div>
            </div>
        `;
    });
    
    const previewHTML = `
        <h1>TERMO DE RENOVAÇÃO CONTRATUAL</h1>
        
        <p style="text-align: right;">Contrato Original nº: ${contratoOriginal}</p>
        
        <p>Pelo presente instrumento particular, as partes a seguir qualificadas:</p>
        
        <p><strong>CONTRATANTE:</strong> ${clienteNome}, já qualificado no contrato original firmado em ${dataOriginal.toLocaleDateString('pt-BR')}.</p>
        
        <p><strong>CONTRATADA:</strong> DIGITAL FIVE, responsável pelo produto ARCON, inscrita no CNPJ sob nº [CNPJ DA DIGITAL FIVE], com sede em [ENDEREÇO DA DIGITAL FIVE].</p>
        
        <p>As partes acima identificadas resolvem, de comum acordo, RENOVAR o contrato originalmente firmado, mediante as seguintes condições:</p>
        
        ${clausulasHTML}
        
        ${observacoes ? `<p><strong>Observações:</strong> ${observacoes}</p>` : ''}
        
        <div class="assinaturas">
            <div class="assinatura-bloco">
                <div class="linha-assinatura"></div>
                <p><strong>CONTRATANTE</strong><br>${clienteNome}</p>
            </div>
            <div class="assinatura-bloco">
                <div class="linha-assinatura"></div>
                <p><strong>CONTRATADA</strong><br>DIGITAL FIVE<br>Produto: ARCON</p>
            </div>
        </div>
        
        <div class="data-local">
            DIGITAL FIVE | ARCON, ${dataObj.getDate()} de ${meses[dataObj.getMonth()]} de ${dataObj.getFullYear()}
        </div>
    `;
    
    document.getElementById('preview-renovacao').innerHTML = previewHTML;
}

function salvarContratoRenovacao() {
    alert('✅ Funcionalidade de salvar termo de renovação será implementada em breve!');
}

// ===== FUNÇÕES DA TAB CANCELAMENTO =====
function carregarContratoCancelamento() {
    const select = document.getElementById('cancelamento_contrato_id');
    const selected = select.options[select.selectedIndex];
    
    if(selected && select.value) {
        document.getElementById('cancelamento_cliente_nome').value = selected.dataset.clienteNome || '';
        
        const dataOriginal = selected.dataset.data ? new Date(selected.dataset.data) : new Date();
        document.getElementById('cancelamento_data_original').value = dataOriginal.toLocaleDateString('pt-BR');
        
        const valorTotal = parseFloat(selected.dataset.valor) || 0;
        const valorMensal = parseFloat(selected.dataset.mensal) || 0;
        const percentualMulta = parseFloat(selected.dataset.multa) || 20;
        const fidelidade = parseInt(selected.dataset.fidelidade) || 12;
        
        document.getElementById('cancelamento_valor_total').value = '' + valorTotal.toFixed(2).replace('.', ',');
        document.getElementById('cancelamento_valor_mensal').value = '' + valorMensal.toFixed(2).replace('.', ',');
        document.getElementById('cancelamento_percentual_multa').value = percentualMulta + '%';
        document.getElementById('cancelamento_fidelidade').value = fidelidade + ' meses';
        
        calcularMultaCancelamento();
    }
}

function calcularMultaCancelamento() {
    const select = document.getElementById('cancelamento_contrato_id');
    if(!select.value) return;
    
    const selected = select.options[select.selectedIndex];
    const valorTotal = parseFloat(selected.dataset.valor) || 0;
    const percentualMulta = parseFloat(selected.dataset.multa) || 20;
    const fidelidade = parseInt(selected.dataset.fidelidade) || 12;
    const dataAssinatura = selected.dataset.data ? new Date(selected.dataset.data) : new Date();
    const dataCancelamento = new Date(document.getElementById('cancelamento_data').value);
    const responsavel = document.getElementById('cancelamento_responsavel').value;
    
    // Calcula meses cumpridos
    const diffTime = Math.abs(dataCancelamento - dataAssinatura);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    const mesesCumpridos = Math.floor(diffDays / 30);
    
    const mesesRestantes = Math.max(0, fidelidade - mesesCumpridos);
    const multaTotal = (valorTotal * percentualMulta) / 100;
    const multaProporcional = (multaTotal * mesesRestantes) / fidelidade;
    
    let multaFinal = 0;
    let detalhe = '';
    
    if(responsavel == 'cliente' && mesesCumpridos < fidelidade) {
        multaFinal = multaProporcional;
        detalhe = `Cliente cumpriu ${mesesCumpridos} meses de ${fidelidade}. Multa proporcional: ${multaProporcional.toFixed(2).replace('.', ',')}`;
    } else if(responsavel == 'cliente' && mesesCumpridos >= fidelidade) {
        multaFinal = 0;
        detalhe = `Período de fidelidade já cumprido (${mesesCumpridos} meses). Isento de multa.`;
    } else if(responsavel == 'digitalfive') {
        multaFinal = 0;
        detalhe = 'Cancelamento por iniciativa da Digital Five. Isento de multa.';
    } else if(responsavel == 'mutuo') {
        multaFinal = 0;
        detalhe = 'Cancelamento por mútuo acordo. Isento de multa.';
    }
    
    document.getElementById('cancelamento_multa_valor').innerHTML = '' + multaFinal.toFixed(2).replace('.', ',');
    document.getElementById('multa_detalhe').innerHTML = detalhe;
    
    gerarPreviewCancelamento();
}

function gerarPreviewCancelamento() {
    const select = document.getElementById('cancelamento_contrato_id');
    if(!select.value) return;
    
    const selected = select.options[select.selectedIndex];
    const clienteNome = selected.dataset.clienteNome || '[NOME DO CLIENTE]';
    const contratoOriginal = select.options[select.selectedIndex].text.split(' - ')[0];
    const dataOriginal = selected.dataset.data ? new Date(selected.dataset.data) : new Date();
    const motivo = document.getElementById('cancelamento_motivo').value || '[MOTIVO DO CANCELAMENTO]';
    const dataCancelamento = new Date(document.getElementById('cancelamento_data').value);
    const responsavel = document.getElementById('cancelamento_responsavel').value;
    const multaTexto = document.getElementById('cancelamento_multa_valor').innerHTML;
    
    const meses = ['janeiro', 'fevereiro', 'março', 'abril', 'maio', 'junho', 'julho', 'agosto', 'setembro', 'outubro', 'novembro', 'dezembro'];
    
    let responsavelTexto = '';
    if(responsavel == 'cliente') responsavelTexto = 'por iniciativa do CONTRATANTE';
    else if(responsavel == 'digitalfive') responsavelTexto = 'por iniciativa da CONTRATADA';
    else responsavelTexto = 'por mútuo acordo entre as partes';
    
    let clausulasHTML = '';
    clausulasCancelamento.forEach(clausula => {
        let conteudo = clausula.conteudo
            .replace(/{contrato_original}/g, contratoOriginal)
            .replace(/{data_original}/g, dataOriginal.toLocaleDateString('pt-BR'))
            .replace(/{motivo}/g, motivo)
            .replace(/{multa_calculada}/g, multaTexto)
            .replace(/{data_cancelamento}/g, dataCancelamento.getDate() + ' de ' + meses[dataCancelamento.getMonth()] + ' de ' + dataCancelamento.getFullYear())
            .replace(/{responsavel}/g, responsavelTexto);
        
        clausulasHTML += `
            <div class="clausula-item">
                <div class="clausula-titulo">
                    <i class="fas fa-gavel"></i> ${clausula.titulo}
                </div>
                <div class="clausula-conteudo">
                    ${conteudo}
                </div>
            </div>
        `;
    });
    
    const previewHTML = `
        <h1>TERMO DE CANCELAMENTO CONTRATUAL</h1>
        
        <p style="text-align: right;">Contrato Original nº: ${contratoOriginal}</p>
        
        <p>Pelo presente instrumento particular, as partes a seguir qualificadas:</p>
        
        <p><strong>CONTRATANTE:</strong> ${clienteNome}, já qualificado no contrato original firmado em ${dataOriginal.toLocaleDateString('pt-BR')}.</p>
        
        <p><strong>CONTRATADA:</strong> DIGITAL FIVE, responsável pelo produto ARCON, inscrita no CNPJ sob nº [CNPJ DA DIGITAL FIVE], com sede em [ENDEREÇO DA DIGITAL FIVE].</p>
        
        <p>As partes acima identificadas resolvem, de comum acordo, CANCELAR o contrato originalmente firmado ${responsavelTexto}, mediante as seguintes condições:</p>
        
        ${clausulasHTML}
        
        <div class="assinaturas">
            <div class="assinatura-bloco">
                <div class="linha-assinatura"></div>
                <p><strong>CONTRATANTE</strong><br>${clienteNome}</p>
            </div>
            <div class="assinatura-bloco">
                <div class="linha-assinatura"></div>
                <p><strong>CONTRATADA</strong><br>DIGITAL FIVE<br>Produto: ARCON</p>
            </div>
        </div>
        
        <div class="data-local">
            DIGITAL FIVE | ARCON, ${dataCancelamento.getDate()} de ${meses[dataCancelamento.getMonth()]} de ${dataCancelamento.getFullYear()}
        </div>
    `;
    
    document.getElementById('preview-cancelamento').innerHTML = previewHTML;
}

function salvarContratoCancelamento() {
    alert('✅ Funcionalidade de salvar termo de cancelamento será implementada em breve!');
}

// ===== EVENT LISTENERS PARA ATUALIZAÇÃO EM TEMPO REAL =====
function adicionarEventListeners() {
    // Adesão
    const camposAdesao = [
    'adesao_valor_plano',
    'adesao_valor_mensal',
    'adesao_percentual_multa',
    'adesao_fidelidade',
    'adesao_prazo_desenvolvimento',
    'adesao_cliente_id',
    'adesao_numero_parcelas',
    'adesao_dia_vencimento',
    'adesao_prazo_primeira_parcela'
];
    
    camposAdesao.forEach(campoId => {
        const campo = document.getElementById(campoId);
        if(campo) {
            campo.addEventListener('input', function() {
                if(document.getElementById('adesao_cliente_id').value) {
                    gerarPreviewAdesao();
                }
            });
            campo.addEventListener('change', function() {
                if(document.getElementById('adesao_cliente_id').value) {
                    gerarPreviewAdesao();
                }
            });
        }
    });
    
    // Renovação
    const camposRenovacao = [
        'renovacao_reajuste',
        'renovacao_nova_data',
        'renovacao_novo_prazo',
        'renovacao_observacoes'
    ];
    
    camposRenovacao.forEach(campoId => {
        const campo = document.getElementById(campoId);
        if(campo) {
            campo.addEventListener('input', gerarPreviewRenovacao);
            campo.addEventListener('change', gerarPreviewRenovacao);
        }
    });
    
    // Cancelamento
    const camposCancelamento = [
        'cancelamento_data',
        'cancelamento_responsavel',
        'cancelamento_motivo'
    ];
    
    camposCancelamento.forEach(campoId => {
        const campo = document.getElementById(campoId);
        if(campo) {
            campo.addEventListener('input', gerarPreviewCancelamento);
            campo.addEventListener('change', gerarPreviewCancelamento);
        }
    });
}

// ===== THEME TOGGLE =====
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

// Inicialização
setTimeout(() => {
    adicionarEventListeners();
}, 1000);
</script>

<?php require_once '../../includes/footer.php'; ?>
