<?php
$page_title = 'Minha Assinatura';
require_once '../../includes/header.php';
require_once '../../includes/menu.php';

$cid = (int)$_SESSION['cliente_id'];

// Busca assinatura existente
$assinatura = getAssinaturaCliente($cid);
$tem_assinatura = !empty($assinatura);
$total_contratos_assinados = countContratosAssinados($cid);
?>

<style>
/* ===== ESTILOS DA PÁGINA DE ASSINATURA ===== */
.assinatura-container {
    max-width: 800px;
    margin: 0 auto;
}

/* Cards de Status */
.status-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    margin-bottom: 30px;
}

.status-card {
    background: var(--surf);
    border: 1px solid var(--bdr);
    border-radius: 20px;
    padding: 25px;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.status-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--sh2);
    border-color: var(--ac);
}

.status-icon {
    width: 50px;
    height: 50px;
    border-radius: 14px;
    background: rgba(102, 126, 234, 0.1);
    color: var(--ac);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
    margin-bottom: 15px;
}

.status-value {
    font-size: 2rem;
    font-weight: 700;
    color: var(--tx);
    margin-bottom: 5px;
    font-family: var(--mono);
}

.status-label {
    color: var(--tx3);
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Card Principal */
.assinatura-card {
    background: var(--surf);
    border: 1px solid var(--bdr);
    border-radius: 24px;
    padding: 40px;
    margin-bottom: 30px;
    text-align: center;
    transition: all 0.3s ease;
}

.assinatura-card.tem-assinatura {
    border-color: var(--ac);
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(124, 58, 237, 0.05) 100%);
}

.assinatura-icon {
    font-size: 5rem;
    color: var(--ac);
    margin-bottom: 20px;
}

.assinatura-icon i {
    filter: drop-shadow(0 10px 15px rgba(102, 126, 234, 0.2));
}

.assinatura-titulo {
    font-size: 1.8rem;
    font-weight: 700;
    color: var(--tx);
    margin-bottom: 10px;
}

.assinatura-subtitulo {
    color: var(--tx3);
    font-size: 1rem;
    margin-bottom: 30px;
    max-width: 500px;
    margin-left: auto;
    margin-right: auto;
}

/* Preview da Assinatura */
.assinatura-preview {
    background: var(--surf2);
    border: 2px dashed var(--bdr2);
    border-radius: 16px;
    padding: 30px;
    margin: 30px 0;
    position: relative;
}

.assinatura-preview::before {
    content: 'Prévia da Assinatura';
    position: absolute;
    top: -12px;
    left: 20px;
    background: var(--surf2);
    padding: 0 10px;
    color: var(--tx3);
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.assinatura-imagem {
    max-width: 300px;
    max-height: 100px;
    margin: 10px auto;
    padding: 15px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.assinatura-nome {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--tx);
    margin: 15px 0 5px;
    font-family: 'Dancing Script', cursive;
}

.assinatura-data {
    color: var(--tx3);
    font-size: 0.85rem;
}

/* Botões */
.btn {
    padding: 14px 32px;
    border-radius: 50px;
    font-weight: 600;
    font-size: 1rem;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
}

.btn-primary {
    background: linear-gradient(135deg, var(--ac) 0%, var(--ac2) 100%);
    color: white;
    box-shadow: 0 10px 20px rgba(102, 126, 234, 0.2);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 15px 30px rgba(102, 126, 234, 0.3);
}

.btn-outline {
    background: transparent;
    border: 2px solid var(--ac);
    color: var(--ac);
}

.btn-outline:hover {
    background: var(--ac);
    color: white;
    transform: translateY(-2px);
}

.btn-secondary {
    background: var(--surf2);
    border: 1px solid var(--bdr2);
    color: var(--tx2);
}

.btn-secondary:hover {
    background: var(--surf3);
    transform: translateY(-2px);
}

/* Canvas para assinatura */
.canvas-container {
    background: var(--surf2);
    border: 2px dashed var(--bdr2);
    border-radius: 16px;
    padding: 20px;
    margin: 20px 0;
}

canvas {
    background: white;
    border-radius: 12px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    cursor: crosshair;
    width: 100%;
    height: auto;
    touch-action: none;
}

.assinatura-actions {
    display: flex;
    gap: 10px;
    justify-content: center;
    flex-wrap: wrap;
    margin: 20px 0;
}

.btn-sm {
    padding: 10px 20px;
    border-radius: 30px;
    font-size: 0.85rem;
    font-weight: 500;
    border: 1px solid var(--bdr2);
    background: var(--surf2);
    color: var(--tx2);
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s ease;
}

.btn-sm:hover {
    background: var(--surf3);
    border-color: var(--ac);
    color: var(--ac);
}

.btn-sm.danger:hover {
    background: #ef4444;
    border-color: #ef4444;
    color: white;
}

/* Modal */
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
    backdrop-filter: blur(5px);
}

.modal-content {
    background: var(--surf);
    border-radius: 24px;
    max-width: 600px;
    width: 90%;
    max-height: 80vh;
    overflow-y: auto;
    box-shadow: 0 20px 40px rgba(0,0,0,0.2);
    animation: modalIn 0.3s ease;
}

@keyframes modalIn {
    from {
        opacity: 0;
        transform: translateY(-30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.modal-header {
    padding: 25px;
    border-bottom: 1px solid var(--bdr);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    font-size: 1.3rem;
    color: var(--tx);
    display: flex;
    align-items: center;
    gap: 10px;
}

.modal-header h3 i {
    color: var(--ac);
}

.modal-close {
    font-size: 1.5rem;
    cursor: pointer;
    color: var(--tx3);
    transition: all 0.2s ease;
}

.modal-close:hover {
    color: #ef4444;
    transform: rotate(90deg);
}

.modal-body {
    padding: 25px;
}

.modal-footer {
    padding: 20px 25px;
    border-top: 1px solid var(--bdr);
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

/* Info Cards */
.info-card {
    background: rgba(102, 126, 234, 0.03);
    border: 1px solid var(--bdr);
    border-radius: 16px;
    padding: 20px;
    margin-top: 20px;
}

.info-card h4 {
    font-size: 0.9rem;
    font-weight: 600;
    color: var(--tx);
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.info-card h4 i {
    color: var(--ac);
}

.info-card p {
    color: var(--tx2);
    font-size: 0.9rem;
    line-height: 1.6;
    margin-bottom: 15px;
}

.info-card ul {
    list-style: none;
    padding: 0;
}

.info-card li {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 0;
    color: var(--tx2);
    font-size: 0.9rem;
    border-bottom: 1px dashed var(--bdr);
}

.info-card li:last-child {
    border-bottom: none;
}

.info-card li i {
    color: #10b981;
    font-size: 0.8rem;
}

/* Responsive */
@media (max-width: 768px) {
    .status-grid {
        grid-template-columns: 1fr;
    }
    
    .assinatura-card {
        padding: 25px;
    }
    
    .assinatura-titulo {
        font-size: 1.5rem;
    }
}
</style>

<!-- ══ MAIN ═══════════════════════════════════════════════════════════ -->
<main class="main" id="main">

    <!-- Topbar -->
    <div class="topbar">
        <div class="top-left">
            <div class="top-pg-ico"><i class="fas fa-pen-fancy"></i></div>
            <span class="top-pg-title">Minha Assinatura</span>
        </div>
        <div class="top-right">
            <div class="ico-btn" id="notifBtn" title="Faturas pendentes">
                <i class="fas fa-bell"></i>
                <?php if ($faturas_pendentes['total'] > 0): ?>
                <span class="ico-dot"></span>
                <?php endif; ?>
            </div>

            <div class="theme-tog" id="themeTog" title="Alternar tema">
                <div class="theme-thumb">
                    <i class="fas <?php echo $tema_atual === 'dark' ? 'fa-sun' : 'fa-moon'; ?>" id="themeIcon"></i>
                </div>
            </div>

            <div class="user-btn" id="userBtn">
                <div class="sb-av"><?php echo strtoupper(substr($cliente['nome'], 0, 1)); ?></div>
                <span class="user-btn-n"><?php echo htmlspecialchars(explode(' ', $cliente['nome'])[0]); ?></span>
                <i class="fas fa-chevron-down user-chevron"></i>
                <div class="ddrop" id="userDrop">
                    <a href="/cliente/modules/perfil/index.php"><i class="fas fa-user-cog"></i> Meu Perfil</a>
                    <a href="/cliente/modules/assinatura/index.php"><i class="fas fa-pen-fancy"></i> Assinatura</a>
                    <hr>
                    <a href="/cliente/logout.php" class="dd-danger"><i class="fas fa-sign-out-alt"></i> Sair</a>
                </div>
            </div>
        </div>
    </div><!-- /topbar -->

    <div class="content">
        <div class="assinatura-container">

            <!-- Welcome Banner -->
            <div class="welcome" style="margin-bottom: 25px;">
                <div class="welcome-copy">
                    <h1>Minha Assinatura Digital</h1>
                    <p>Crie e gerencie sua assinatura para documentos</p>
                </div>
                <div class="welcome-emoji">✍️</div>
            </div>

            <!-- Status Cards -->
            <div class="status-grid">
                <div class="status-card">
                    <div class="status-icon">
                        <i class="fas fa-pen-fancy"></i>
                    </div>
                    <div class="status-value">
                        <?php echo $tem_assinatura ? 'Ativa' : 'Inativa'; ?>
                    </div>
                    <div class="status-label">Status da Assinatura</div>
                </div>

                <div class="status-card">
                    <div class="status-icon">
                        <i class="fas fa-file-contract"></i>
                    </div>
                    <div class="status-value"><?php echo $total_contratos_assinados; ?></div>
                    <div class="status-label">Documentos Assinados</div>
                </div>

                <div class="status-card">
                    <div class="status-icon">
                        <i class="fas fa-calendar"></i>
                    </div>
                    <div class="status-value">
                        <?php echo $assinatura ? date('d/m/Y', strtotime($assinatura['created_at'])) : '—'; ?>
                    </div>
                    <div class="status-label">Criada em</div>
                </div>
            </div>

            <!-- Card Principal -->
            <?php if ($tem_assinatura): ?>
                <!-- Assinatura já existente -->
                <div class="assinatura-card tem-assinatura">
                    <div class="assinatura-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h2 class="assinatura-titulo">Sua assinatura está pronta!</h2>
                    <p class="assinatura-subtitulo">
                        Você já pode assinar documentos utilizando sua assinatura digital.
                    </p>

                    <div class="assinatura-preview">
                        <?php if ($assinatura['assinatura_base64']): ?>
                        <div class="assinatura-imagem">
                            <img src="<?php echo $assinatura['assinatura_base64']; ?>" 
                                 style="max-width: 100%; max-height: 70px; object-fit: contain;">
                        </div>
                        <?php endif; ?>
                        <div class="assinatura-nome"><?php echo htmlspecialchars($assinatura['nome_assinatura']); ?></div>
                        <div class="assinatura-data">
                            Criada em <?php echo date('d/m/Y \à\s H:i', strtotime($assinatura['created_at'])); ?>
                        </div>
                    </div>

                    <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
                        <a href="#" class="btn btn-outline" onclick="abrirModalEditar()">
                            <i class="fas fa-edit"></i> Editar Assinatura
                        </a>
                        <a href="/cliente/modules/contratos/index.php" class="btn btn-primary">
                            <i class="fas fa-file-contract"></i> Assinar Documentos
                        </a>
                    </div>
                </div>

                <!-- Info Card -->
                <div class="info-card">
                    <h4><i class="fas fa-info-circle"></i> Sobre sua assinatura digital</h4>
                    <p>
                        Sua assinatura digital tem validade jurídica e pode ser utilizada para assinar contratos e documentos.
                        Ao assinar um documento, sua assinatura será inserida automaticamente no local indicado.
                    </p>
                    <ul>
                        <li><i class="fas fa-check-circle"></i> Validade jurídica garantida</li>
                        <li><i class="fas fa-check-circle"></i> Assinatura armazenada com segurança</li>
                        <li><i class="fas fa-check-circle"></i> Pode ser usada em múltiplos documentos</li>
                        <li><i class="fas fa-check-circle"></i> Histórico de todas as assinaturas</li>
                    </ul>
                </div>

            <?php else: ?>
                <!-- Criar nova assinatura -->
                <div class="assinatura-card">
                    <div class="assinatura-icon">
                        <i class="fas fa-pen-fancy"></i>
                    </div>
                    <h2 class="assinatura-titulo">Crie sua assinatura digital</h2>
                    <p class="assinatura-subtitulo">
                        Desenhe sua assinatura abaixo para começar a assinar documentos digitalmente.
                        Sua assinatura terá validade jurídica.
                    </p>

                    <button class="btn btn-primary" onclick="abrirModalCriar()">
                        <i class="fas fa-pen"></i> Criar Assinatura Agora
                    </button>
                </div>

                <!-- Info Card -->
                <div class="info-card">
                    <h4><i class="fas fa-info-circle"></i> Por que criar uma assinatura digital?</h4>
                    <ul>
                        <li><i class="fas fa-check-circle"></i> Assine contratos de qualquer lugar</li>
                        <li><i class="fas fa-check-circle"></i> Processo 100% digital e seguro</li>
                        <li><i class="fas fa-check-circle"></i> Validade jurídica garantida</li>
                        <li><i class="fas fa-check-circle"></i> Economize tempo e papel</li>
                    </ul>
                </div>
            <?php endif; ?>

        </div><!-- /assinatura-container -->
    </div><!-- /content -->
</main><!-- /main -->

<!-- Modal para criar assinatura -->
<div id="modalCriar" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-pen-fancy"></i> Criar Assinatura</h3>
            <span class="modal-close" onclick="fecharModalCriar()">&times;</span>
        </div>
        <div class="modal-body">
            <p style="color: var(--tx3); margin-bottom: 20px;">
                Desenhe sua assinatura no quadro abaixo. Use o mouse ou touch para desenhar.
            </p>

            <div class="canvas-container">
                <canvas id="assinaturaCanvas" width="500" height="200" style="width: 100%; height: auto;"></canvas>
            </div>

            <div class="assinatura-actions">
                <button class="btn-sm" onclick="limparCanvas()">
                    <i class="fas fa-undo"></i> Limpar
                </button>
                <button class="btn-sm" onclick="aumentarCanvas()">
                    <i class="fas fa-plus"></i> Aumentar
                </button>
                <button class="btn-sm" onclick="diminuirCanvas()">
                    <i class="fas fa-minus"></i> Diminuir
                </button>
            </div>

            <div style="margin: 15px 0;">
                <label style="display: block; margin-bottom: 8px; color: var(--tx2);">Nome da Assinatura (opcional)</label>
                <input type="text" id="nomeAssinatura" class="filtro-item input" 
                       placeholder="Ex: Assinatura padrão" 
                       style="width: 100%; padding: 12px; border-radius: 12px; border: 1px solid var(--bdr); background: var(--surf2); color: var(--tx);">
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-sm" onclick="fecharModalCriar()">Cancelar</button>
            <button class="btn-sm" style="background: var(--ac); color: white;" onclick="salvarAssinatura()">
                <i class="fas fa-save"></i> Salvar Assinatura
            </button>
        </div>
    </div>
</div>

<!-- Modal para editar assinatura -->
<div id="modalEditar" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-edit"></i> Editar Assinatura</h3>
            <span class="modal-close" onclick="fecharModalEditar()">&times;</span>
        </div>
        <div class="modal-body">
            <p style="color: var(--tx3); margin-bottom: 20px;">
                Desenhe sua nova assinatura abaixo. A anterior será substituída.
            </p>

            <div class="canvas-container">
                <canvas id="assinaturaCanvasEdit" width="500" height="200" style="width: 100%; height: auto;"></canvas>
            </div>

            <div class="assinatura-actions">
                <button class="btn-sm" onclick="limparCanvasEdit()">
                    <i class="fas fa-undo"></i> Limpar
                </button>
                <button class="btn-sm" onclick="aumentarCanvasEdit()">
                    <i class="fas fa-plus"></i> Aumentar
                </button>
                <button class="btn-sm" onclick="diminuirCanvasEdit()">
                    <i class="fas fa-minus"></i> Diminuir
                </button>
            </div>

            <div style="margin: 15px 0;">
                <label style="display: block; margin-bottom: 8px; color: var(--tx2);">Nome da Assinatura</label>
                <input type="text" id="nomeAssinaturaEdit" class="filtro-item input" 
                       value="<?php echo htmlspecialchars($assinatura['nome_assinatura'] ?? ''); ?>"
                       style="width: 100%; padding: 12px; border-radius: 12px; border: 1px solid var(--bdr); background: var(--surf2); color: var(--tx);">
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-sm" onclick="fecharModalEditar()">Cancelar</button>
            <button class="btn-sm" style="background: var(--ac); color: white;" onclick="atualizarAssinatura()">
                <i class="fas fa-save"></i> Atualizar Assinatura
            </button>
        </div>
    </div>
</div>

<script>
// ===== VARIÁVEIS GLOBAIS =====
let canvas, ctx, desenhando = false;
let canvasEdit, ctxEdit, desenhandoEdit = false;
let tamanhoPincel = 2;

// ===== FUNÇÕES DO MODAL CRIAR =====
function abrirModalCriar() {
    document.getElementById('modalCriar').style.display = 'flex';
    setTimeout(iniciarCanvas, 100);
}

function fecharModalCriar() {
    document.getElementById('modalCriar').style.display = 'none';
}

function iniciarCanvas() {
    canvas = document.getElementById('assinaturaCanvas');
    ctx = canvas.getContext('2d');
    
    // Configurações iniciais
    ctx.lineWidth = tamanhoPincel;
    ctx.lineCap = 'round';
    ctx.strokeStyle = '#1e293b';
    ctx.lineJoin = 'round';
    
    // Eventos para mouse
    canvas.addEventListener('mousedown', iniciarDesenho);
    canvas.addEventListener('mousemove', desenhar);
    canvas.addEventListener('mouseup', pararDesenho);
    canvas.addEventListener('mouseout', pararDesenho);
    
    // Eventos para touch (mobile)
    canvas.addEventListener('touchstart', iniciarDesenhoTouch);
    canvas.addEventListener('touchmove', desenharTouch);
    canvas.addEventListener('touchend', pararDesenho);
    
    // Fundo branco
    ctx.fillStyle = '#ffffff';
    ctx.fillRect(0, 0, canvas.width, canvas.height);
}

function iniciarDesenho(e) {
    desenhando = true;
    ctx.beginPath();
    ctx.moveTo(e.offsetX, e.offsetY);
}

function desenhar(e) {
    if (!desenhando) return;
    ctx.lineTo(e.offsetX, e.offsetY);
    ctx.stroke();
    ctx.beginPath();
    ctx.moveTo(e.offsetX, e.offsetY);
}

function iniciarDesenhoTouch(e) {
    e.preventDefault();
    desenhando = true;
    const rect = canvas.getBoundingClientRect();
    const touch = e.touches[0];
    const x = (touch.clientX - rect.left) * (canvas.width / rect.width);
    const y = (touch.clientY - rect.top) * (canvas.height / rect.height);
    ctx.beginPath();
    ctx.moveTo(x, y);
}

function desenharTouch(e) {
    e.preventDefault();
    if (!desenhando) return;
    const rect = canvas.getBoundingClientRect();
    const touch = e.touches[0];
    const x = (touch.clientX - rect.left) * (canvas.width / rect.width);
    const y = (touch.clientY - rect.top) * (canvas.height / rect.height);
    ctx.lineTo(x, y);
    ctx.stroke();
    ctx.beginPath();
    ctx.moveTo(x, y);
}

function pararDesenho() {
    desenhando = false;
}

function limparCanvas() {
    ctx.fillStyle = '#ffffff';
    ctx.fillRect(0, 0, canvas.width, canvas.height);
}

function aumentarCanvas() {
    tamanhoPincel = Math.min(10, tamanhoPincel + 1);
    ctx.lineWidth = tamanhoPincel;
}

function diminuirCanvas() {
    tamanhoPincel = Math.max(1, tamanhoPincel - 1);
    ctx.lineWidth = tamanhoPincel;
}

function salvarAssinatura() {
    const nome = document.getElementById('nomeAssinatura').value || 'Minha Assinatura';
    const assinaturaDataURL = canvas.toDataURL('image/png');
    
    // Enviar para o servidor
    fetch('/cliente/modules/assinatura/salvar.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            nome: nome,
            assinatura: assinaturaDataURL
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.sucesso) {
            alert('✅ Assinatura salva com sucesso!');
            location.reload();
        } else {
            alert('❌ Erro ao salvar: ' + data.erro);
        }
    })
    .catch(error => {
        alert('❌ Erro na requisição: ' + error);
    });
}

// ===== FUNÇÕES DO MODAL EDITAR =====
function abrirModalEditar() {
    document.getElementById('modalEditar').style.display = 'flex';
    setTimeout(iniciarCanvasEdit, 100);
}

function fecharModalEditar() {
    document.getElementById('modalEditar').style.display = 'none';
}

function iniciarCanvasEdit() {
    canvasEdit = document.getElementById('assinaturaCanvasEdit');
    ctxEdit = canvasEdit.getContext('2d');
    
    ctxEdit.lineWidth = tamanhoPincel;
    ctxEdit.lineCap = 'round';
    ctxEdit.strokeStyle = '#1e293b';
    ctxEdit.lineJoin = 'round';
    
    canvasEdit.addEventListener('mousedown', iniciarDesenhoEdit);
    canvasEdit.addEventListener('mousemove', desenharEdit);
    canvasEdit.addEventListener('mouseup', pararDesenhoEdit);
    canvasEdit.addEventListener('mouseout', pararDesenhoEdit);
    
    canvasEdit.addEventListener('touchstart', iniciarDesenhoTouchEdit);
    canvasEdit.addEventListener('touchmove', desenharTouchEdit);
    canvasEdit.addEventListener('touchend', pararDesenhoEdit);
    
    ctxEdit.fillStyle = '#ffffff';
    ctxEdit.fillRect(0, 0, canvasEdit.width, canvasEdit.height);
}

function iniciarDesenhoEdit(e) {
    desenhandoEdit = true;
    ctxEdit.beginPath();
    ctxEdit.moveTo(e.offsetX, e.offsetY);
}

function desenharEdit(e) {
    if (!desenhandoEdit) return;
    ctxEdit.lineTo(e.offsetX, e.offsetY);
    ctxEdit.stroke();
    ctxEdit.beginPath();
    ctxEdit.moveTo(e.offsetX, e.offsetY);
}

function iniciarDesenhoTouchEdit(e) {
    e.preventDefault();
    desenhandoEdit = true;
    const rect = canvasEdit.getBoundingClientRect();
    const touch = e.touches[0];
    const x = (touch.clientX - rect.left) * (canvasEdit.width / rect.width);
    const y = (touch.clientY - rect.top) * (canvasEdit.height / rect.height);
    ctxEdit.beginPath();
    ctxEdit.moveTo(x, y);
}

function desenharTouchEdit(e) {
    e.preventDefault();
    if (!desenhandoEdit) return;
    const rect = canvasEdit.getBoundingClientRect();
    const touch = e.touches[0];
    const x = (touch.clientX - rect.left) * (canvasEdit.width / rect.width);
    const y = (touch.clientY - rect.top) * (canvasEdit.height / rect.height);
    ctxEdit.lineTo(x, y);
    ctxEdit.stroke();
    ctxEdit.beginPath();
    ctxEdit.moveTo(x, y);
}

function pararDesenhoEdit() {
    desenhandoEdit = false;
}

function limparCanvasEdit() {
    ctxEdit.fillStyle = '#ffffff';
    ctxEdit.fillRect(0, 0, canvasEdit.width, canvasEdit.height);
}

function aumentarCanvasEdit() {
    tamanhoPincel = Math.min(10, tamanhoPincel + 1);
    ctxEdit.lineWidth = tamanhoPincel;
}

function diminuirCanvasEdit() {
    tamanhoPincel = Math.max(1, tamanhoPincel - 1);
    ctxEdit.lineWidth = tamanhoPincel;
}

function atualizarAssinatura() {
    const nome = document.getElementById('nomeAssinaturaEdit').value || 'Minha Assinatura';
    const assinaturaDataURL = canvasEdit.toDataURL('image/png');
    
    fetch('/cliente/modules/assinatura/atualizar.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            nome: nome,
            assinatura: assinaturaDataURL
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.sucesso) {
            alert('✅ Assinatura atualizada com sucesso!');
            location.reload();
        } else {
            alert('❌ Erro ao atualizar: ' + data.erro);
        }
    })
    .catch(error => {
        alert('❌ Erro na requisição: ' + error);
    });
}

// ===== FUNÇÕES GLOBAIS =====
(function() {
    const userBtn = document.getElementById('userBtn');
    const userDrop = document.getElementById('userDrop');
    if (userBtn && userDrop) {
        userBtn.addEventListener('click', e => {
            e.stopPropagation();
            userDrop.classList.toggle('open');
            userBtn.classList.toggle('open');
        });
        document.addEventListener('click', () => {
            userDrop.classList.remove('open');
            userBtn.classList.remove('open');
        });
    }

    const notifBtn = document.getElementById('notifBtn');
    if (notifBtn) {
        notifBtn.addEventListener('click', () => {
            window.location.href = '/cliente/modules/faturas/index.php';
        });
    }

    const themeTog = document.getElementById('themeTog');
    if (themeTog) {
        themeTog.addEventListener('click', () => {
            const html = document.documentElement;
            const isDark = html.getAttribute('data-theme') === 'dark';
            const novo = isDark ? 'light' : 'dark';
            html.setAttribute('data-theme', novo);
            
            const icon = document.getElementById('themeIcon');
            if (icon) icon.className = novo === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
            
            const fd = new FormData();
            fd.append('action', 'toggle_tema');
            fd.append('tema', novo);
            fetch(window.location.pathname, { method: 'POST', body: fd })
                .catch(() => {});
        });
    }
})();
</script>

<?php require_once '../../includes/footer.php'; ?>