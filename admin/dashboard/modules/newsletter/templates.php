<?php
$page_title = 'Templates de E-mail';
require_once '../../includes/header.php';
require_once '../../includes/menu.php';
require_once 'config.php';

$config = getNewsletterConfig();

// Templates pré-definidos (agora com HTML completo)
$templates = [
    'padrao' => [
        'nome' => 'Template Elegante',
        'descricao' => 'Template clean e profissional para comunicações gerais',
        'icone' => 'fa-envelope-open-text',
        'cor' => '#4361ee',
        'conteudo' => '
<div style="max-width: 500px; margin: 0 auto; background: #ffffff; border-radius: 24px; overflow: hidden; box-shadow: 0 20px 40px rgba(0,0,0,0.05);">
    <!-- Header com gradiente suave -->
    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 40px 30px; text-align: center;">
        <h1 style="color: #ffffff; font-size: 32px; font-weight: 700; margin: 0; letter-spacing: -0.5px;">Olá, {nome}!</h1>
        <p style="color: rgba(255,255,255,0.9); font-size: 16px; margin-top: 10px;">Fique por dentro das novidades</p>
    </div>
    
    <!-- Conteúdo principal -->
    <div style="padding: 40px 30px;">
        <div style="background: #f8faff; border-radius: 20px; padding: 25px; margin-bottom: 30px;">
            <h2 style="color: #333; font-size: 20px; margin-bottom: 15px;">✨ Novidades da semana</h2>
            <p style="color: #666; line-height: 1.6; margin: 0;">Confira as últimas atualizações e novidades que preparamos para você. Estamos sempre trabalhando para oferecer o melhor em tecnologia.</p>
        </div>
        
        <!-- Cards de destaque -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 30px;">
            <div style="background: #ffffff; border: 1px solid #eef2f6; border-radius: 16px; padding: 20px; text-align: center;">
                <div style="width: 48px; height: 48px; background: #e6ecfe; border-radius: 16px; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px;">
                    <span style="font-size: 24px;">🚀</span>
                </div>
                <h3 style="color: #333; font-size: 16px; margin-bottom: 5px;">Sites</h3>
                <p style="color: #999; font-size: 14px; margin: 0;">Responsivos</p>
            </div>
            <div style="background: #ffffff; border: 1px solid #eef2f6; border-radius: 16px; padding: 20px; text-align: center;">
                <div style="width: 48px; height: 48px; background: #e6ecfe; border-radius: 16px; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px;">
                    <span style="font-size: 24px;">🤖</span>
                </div>
                <h3 style="color: #333; font-size: 16px; margin-bottom: 5px;">Bots com IA</h3>
                <p style="color: #999; font-size: 14px; margin: 0;">Inteligentes</p>
            </div>
        </div>
        
        <!-- Call to Action -->
        <div style="text-align: center;">
            <a href="{site_url}" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #ffffff; text-decoration: none; padding: 16px 40px; border-radius: 50px; font-weight: 600; display: inline-block; box-shadow: 0 10px 20px rgba(102,126,234,0.2);">Conheça nossos planos</a>
        </div>
    </div>
    
    <!-- Footer minimalista -->
    <div style="background: #f8faff; padding: 30px; text-align: center; border-top: 1px solid #eef2f6;">
        <p style="color: #999; font-size: 13px; margin: 0;">© {ano} NTW - New Software. Todos os direitos reservados.</p>
        <p style="margin-top: 15px;">
            <a href="{desinscrever_link}" style="color: #999; font-size: 13px; text-decoration: underline;">Cancelar inscrição</a>
        </p>
    </div>
</div>'
    ],
    
    'blog' => [
        'nome' => 'Blog Moderno',
        'descricao' => 'Template clean para divulgar artigos do blog',
        'icone' => 'fa-pen-fancy',
        'cor' => '#f97316',
        'conteudo' => '
<div style="max-width: 550px; margin: 0 auto; background: #ffffff; border-radius: 24px; overflow: hidden; box-shadow: 0 20px 40px rgba(0,0,0,0.05);">
    <!-- Header suave -->
    <div style="background: linear-gradient(135deg, #f97316 0%, #fbbf24 100%); padding: 40px 30px; text-align: center;">
        <h1 style="color: #ffffff; font-size: 28px; font-weight: 700; margin: 0;">Novidades do Blog</h1>
        <p style="color: rgba(255,255,255,0.9); font-size: 16px; margin-top: 10px;">Olá, {nome}! Confira nossos novos artigos</p>
    </div>
    
    <!-- Lista de posts -->
    <div style="padding: 40px 30px;">
        <!-- Post 1 -->
        <div style="margin-bottom: 30px; padding-bottom: 30px; border-bottom: 1px solid #eef2f6;">
            <div style="display: flex; gap: 15px; align-items: center; margin-bottom: 10px;">
                <span style="background: #fff7ed; color: #f97316; padding: 4px 12px; border-radius: 50px; font-size: 12px; font-weight: 600;">TECNOLOGIA</span>
                <span style="color: #999; font-size: 12px;">5 min de leitura</span>
            </div>
            <h3 style="color: #333; font-size: 18px; margin-bottom: 10px;">Como a IA está revolucionando o desenvolvimento</h3>
            <p style="color: #666; line-height: 1.5; margin-bottom: 15px;">Descubra como a inteligência artificial pode aumentar sua produtividade em até 40%.</p>
            <a href="{blog_url}/ia-revolucao" style="color: #f97316; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center;">Ler artigo →</a>
        </div>
        
        <!-- Post 2 -->
        <div style="margin-bottom: 30px; padding-bottom: 30px; border-bottom: 1px solid #eef2f6;">
            <div style="display: flex; gap: 15px; align-items: center; margin-bottom: 10px;">
                <span style="background: #f0fdf4; color: #22c55e; padding: 4px 12px; border-radius: 50px; font-size: 12px; font-weight: 600;">MARKETING</span>
                <span style="color: #999; font-size: 12px;">4 min de leitura</span>
            </div>
            <h3 style="color: #333; font-size: 18px; margin-bottom: 10px;">5 estratégias de marketing para pequenas empresas</h3>
            <p style="color: #666; line-height: 1.5; margin-bottom: 15px;">Estratégias práticas e de baixo custo para alavancar seu negócio.</p>
            <a href="{blog_url}/marketing" style="color: #f97316; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center;">Ler artigo →</a>
        </div>
        
        <!-- Post 3 -->
        <div>
            <div style="display: flex; gap: 15px; align-items: center; margin-bottom: 10px;">
                <span style="background: #eef2ff; color: #4361ee; padding: 4px 12px; border-radius: 50px; font-size: 12px; font-weight: 600;">PROGRAMAÇÃO</span>
                <span style="color: #999; font-size: 12px;">3 min de leitura</span>
            </div>
            <h3 style="color: #333; font-size: 18px; margin-bottom: 10px;">PHP 8.4: O que há de novo</h3>
            <p style="color: #666; line-height: 1.5; margin-bottom: 15px;">Principais novidades e melhorias da nova versão.</p>
            <a href="{blog_url}/php-84" style="color: #f97316; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center;">Ler artigo →</a>
        </div>
        
        <!-- Botão para ver todos -->
        <div style="text-align: center; margin-top: 40px;">
            <a href="{blog_url}" style="background: #f8faff; color: #4361ee; text-decoration: none; padding: 14px 30px; border-radius: 50px; font-weight: 600; display: inline-block; border: 1px solid #eef2f6;">Ver todos os artigos</a>
        </div>
    </div>
    
    <!-- Footer -->
    <div style="background: #f8faff; padding: 30px; text-align: center; border-top: 1px solid #eef2f6;">
        <p style="color: #999; font-size: 13px;">Recebeu este e-mail porque se inscreveu no blog da NTW</p>
        <p style="margin-top: 10px;">
            <a href="{desinscrever_link}" style="color: #999; font-size: 13px; text-decoration: underline;">Cancelar inscrição</a>
        </p>
    </div>
</div>'
    ],
    
    'promocao' => [
        'nome' => 'Oferta Especial',
        'descricao' => 'Template moderno para promoções e descontos',
        'icone' => 'fa-gift',
        'cor' => '#10b981',
        'conteudo' => '
<div style="max-width: 500px; margin: 0 auto; background: #ffffff; border-radius: 24px; overflow: hidden; box-shadow: 0 20px 40px rgba(0,0,0,0.05);">
    <!-- Header com gradiente -->
    <div style="background: linear-gradient(135deg, #10b981 0%, #34d399 100%); padding: 40px 30px; text-align: center;">
        <span style="font-size: 48px; margin-bottom: 20px; display: block;">🎁</span>
        <h1 style="color: #ffffff; font-size: 32px; font-weight: 700; margin: 0;">Oferta Especial</h1>
        <p style="color: rgba(255,255,255,0.9); font-size: 16px; margin-top: 10px;">Para: {nome}</p>
    </div>
    
    <!-- Conteúdo -->
    <div style="padding: 40px 30px; text-align: center;">
        <h2 style="color: #333; font-size: 24px; margin-bottom: 15px;">20% OFF em todos os planos</h2>
        <p style="color: #666; line-height: 1.6; margin-bottom: 30px;">Aproveite esta oportunidade única para transformar seu negócio com nossas soluções tecnológicas.</p>
        
        <!-- Card do cupom -->
        <div style="background: linear-gradient(135deg, #f8faff 0%, #ffffff 100%); border: 2px dashed #10b981; border-radius: 16px; padding: 25px; margin-bottom: 30px;">
            <p style="color: #666; font-size: 14px; margin-bottom: 10px;">Use o cupom</p>
            <div style="background: #ffffff; border: 1px solid #eef2f6; border-radius: 12px; padding: 15px;">
                <span style="font-size: 28px; font-weight: 700; color: #10b981; letter-spacing: 2px;">NTW20</span>
            </div>
            <p style="color: #999; font-size: 13px; margin-top: 15px;">Válido até 30/03/2026</p>
        </div>
        
        <!-- Benefícios -->
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin-bottom: 30px;">
            <div style="text-align: left;">
                <span style="color: #10b981; margin-right: 8px;">✓</span>
                <span style="color: #666; font-size: 14px;">Sites profissionais</span>
            </div>
            <div style="text-align: left;">
                <span style="color: #10b981; margin-right: 8px;">✓</span>
                <span style="color: #666; font-size: 14px;">Sistemas personalizados</span>
            </div>
            <div style="text-align: left;">
                <span style="color: #10b981; margin-right: 8px;">✓</span>
                <span style="color: #666; font-size: 14px;">Bots com IA</span>
            </div>
            <div style="text-align: left;">
                <span style="color: #10b981; margin-right: 8px;">✓</span>
                <span style="color: #666; font-size: 14px;">Suporte prioritário</span>
            </div>
        </div>
        
        <!-- Botão CTA -->
        <a href="{site_url}/planos" style="background: #10b981; color: #ffffff; text-decoration: none; padding: 16px 40px; border-radius: 50px; font-weight: 600; display: inline-block; box-shadow: 0 10px 20px rgba(16,185,129,0.2);">APROVEITAR OFERTA</a>
    </div>
    
    <!-- Footer -->
    <div style="background: #f8faff; padding: 30px; text-align: center; border-top: 1px solid #eef2f6;">
        <p style="color: #999; font-size: 13px;">© {ano} NTW - New Software</p>
        <p style="margin-top: 5px;">
            <a href="{desinscrever_link}" style="color: #999; font-size: 13px;">Cancelar inscrição</a>
        </p>
    </div>
</div>'
    ],
    
    'newsletter' => [
        'nome' => 'Newsletter Mensal',
        'descricao' => 'Template clean com resumo do mês',
        'icone' => 'fa-calendar-check',
        'cor' => '#8b5cf6',
        'conteudo' => '
<div style="max-width: 550px; margin: 0 auto; background: #ffffff; border-radius: 24px; overflow: hidden; box-shadow: 0 20px 40px rgba(0,0,0,0.05);">
    <!-- Header minimalista -->
    <div style="padding: 40px 30px; text-align: center; border-bottom: 1px solid #eef2f6;">
        <img src="{site_url}/assets/image/logo.png" style="width: 60px; height: 60px; border-radius: 50%; margin-bottom: 15px;">
        <h1 style="color: #333; font-size: 24px; font-weight: 600; margin: 0;">Resumo do Mês</h1>
        <p style="color: #8b5cf6; font-size: 14px; margin-top: 5px;">Fevereiro 2026</p>
    </div>
    
    <!-- Conteúdo -->
    <div style="padding: 40px 30px;">
        <p style="color: #666; font-size: 16px; margin-bottom: 30px;">Olá, {nome}! Preparamos um resumo com as principais novidades deste mês.</p>
        
        <!-- Estatísticas -->
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 40px;">
            <div style="text-align: center;">
                <div style="font-size: 28px; font-weight: 700; color: #8b5cf6;">12</div>
                <div style="color: #999; font-size: 12px;">Novos artigos</div>
            </div>
            <div style="text-align: center;">
                <div style="font-size: 28px; font-weight: 700; color: #8b5cf6;">8</div>
                <div style="color: #999; font-size: 12px;">Projetos entregues</div>
            </div>
            <div style="text-align: center;">
                <div style="font-size: 28px; font-weight: 700; color: #8b5cf6;">156</div>
                <div style="color: #999; font-size: 12px;">Novos clientes</div>
            </div>
        </div>
        
        <!-- Destaques -->
        <h2 style="color: #333; font-size: 18px; margin-bottom: 20px;">✨ Destaques do mês</h2>
        
        <div style="margin-bottom: 20px;">
            <div style="display: flex; gap: 15px; align-items: flex-start; margin-bottom: 20px;">
                <div style="width: 32px; height: 32px; background: #f3e8ff; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #8b5cf6;">✓</div>
                <div>
                    <h3 style="color: #333; font-size: 16px; margin-bottom: 5px;">Nova plataforma de cursos</h3>
                    <p style="color: #666; font-size: 14px;">Lançamos nossa plataforma de cursos online com conteúdo exclusivo.</p>
                </div>
            </div>
            
            <div style="display: flex; gap: 15px; align-items: flex-start; margin-bottom: 20px;">
                <div style="width: 32px; height: 32px; background: #f3e8ff; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #8b5cf6;">✓</div>
                <div>
                    <h3 style="color: #333; font-size: 16px; margin-bottom: 5px;">Promoção de aniversário</h3>
                    <p style="color: #666; font-size: 14px;">30% de desconto em todos os planos durante o mês de aniversário.</p>
                </div>
            </div>
        </div>
        
        <!-- Botão -->
        <div style="text-align: center; margin-top: 30px;">
            <a href="{site_url}" style="background: #8b5cf6; color: #ffffff; text-decoration: none; padding: 14px 30px; border-radius: 50px; font-weight: 600; display: inline-block;">VER TODAS AS NOVIDADES</a>
        </div>
    </div>
    
    <!-- Footer -->
    <div style="background: #f8faff; padding: 30px; text-align: center;">
        <div style="display: flex; justify-content: center; gap: 20px; margin-bottom: 20px;">
            <a href="#" style="color: #999; text-decoration: none;">Instagram</a>
            <a href="#" style="color: #999; text-decoration: none;">LinkedIn</a>
            <a href="#" style="color: #999; text-decoration: none;">Facebook</a>
        </div>
        <p style="color: #999; font-size: 12px;">Se não quiser mais receber nossos e-mails, <a href="{desinscrever_link}" style="color: #999;">clique aqui</a>.</p>
    </div>
</div>'
    ],
    
    'aviso' => [
        'nome' => 'Aviso Importante',
        'descricao' => 'Template clean para comunicados',
        'icone' => 'fa-bell',
        'cor' => '#ef4444',
        'conteudo' => '
<div style="max-width: 500px; margin: 0 auto; background: #ffffff; border-radius: 24px; overflow: hidden; box-shadow: 0 20px 40px rgba(0,0,0,0.05);">
    <!-- Header -->
    <div style="background: linear-gradient(135deg, #ef4444 0%, #f87171 100%); padding: 40px 30px; text-align: center;">
        <span style="font-size: 48px; margin-bottom: 20px; display: block;">📢</span>
        <h1 style="color: #ffffff; font-size: 28px; font-weight: 700; margin: 0;">Aviso Importante</h1>
    </div>
    
    <!-- Conteúdo -->
    <div style="padding: 40px 30px;">
        <p style="color: #666; font-size: 16px; margin-bottom: 25px;">Prezado(a) <strong>{nome}</strong>,</p>
        
        <div style="background: #fef2f2; border-left: 4px solid #ef4444; padding: 20px; border-radius: 12px; margin-bottom: 30px;">
            <p style="color: #ef4444; margin: 0; font-weight: 600;">🕐 Manutenção Programada</p>
            <p style="color: #666; margin-top: 10px;">Nossos sistemas estarão em manutenção no dia 25/02 das 23h às 02h para melhorias de performance.</p>
        </div>
        
        <p style="color: #666; line-height: 1.6; margin-bottom: 20px;">Durante este período, alguns serviços podem ficar temporariamente indisponíveis. Pedimos desculpas pelos transtornos e agradecemos a compreensão.</p>
        
        <div style="background: #f8faff; padding: 20px; border-radius: 12px;">
            <p style="color: #333; margin: 0 0 10px 0;"><strong>📞 Precisa de ajuda?</strong></p>
            <p style="color: #666; margin: 0;">Nossa equipe de suporte está disponível para qualquer dúvida.</p>
            <a href="https://wa.me/5519987111656" style="color: #25D366; text-decoration: none; display: inline-block; margin-top: 10px;">WhatsApp: (19) 98711-1656</a>
        </div>
    </div>
    
    <!-- Footer -->
    <div style="background: #f8faff; padding: 30px; text-align: center; border-top: 1px solid #eef2f6;">
        <p style="color: #999; font-size: 13px;">Agradecemos sua compreensão</p>
        <p style="margin-top: 10px;">
            <a href="{desinscrever_link}" style="color: #999; font-size: 12px;">Cancelar inscrição</a>
        </p>
    </div>
</div>'
    ],
    
    'convite' => [
        'nome' => 'Convite Especial',
        'descricao' => 'Template para convites e eventos',
        'icone' => 'fa-envelope-open-text',
        'cor' => '#ec4899',
        'conteudo' => '
<div style="max-width: 550px; margin: 0 auto; background: #ffffff; border-radius: 24px; overflow: hidden; box-shadow: 0 20px 40px rgba(0,0,0,0.05);">
    <!-- Header elegante -->
    <div style="background: linear-gradient(135deg, #ec4899 0%, #f472b6 100%); padding: 60px 30px; text-align: center;">
        <span style="font-size: 48px; margin-bottom: 20px; display: block;">🎉</span>
        <h1 style="color: #ffffff; font-size: 32px; font-weight: 700; margin: 0;">Você está convidado!</h1>
        <p style="color: rgba(255,255,255,0.9); font-size: 18px; margin-top: 15px;">{nome}, não perca esta oportunidade</p>
    </div>
    
    <!-- Detalhes do evento -->
    <div style="padding: 50px 30px; text-align: center;">
        <h2 style="color: #333; font-size: 28px; margin-bottom: 20px;">Workshop de Tecnologia</h2>
        
        <div style="display: flex; justify-content: center; gap: 30px; margin: 30px 0; flex-wrap: wrap;">
            <div style="text-align: center;">
                <div style="font-size: 24px; color: #ec4899; margin-bottom: 5px;">📅</div>
                <div style="color: #333; font-weight: 600;">15 de Março</div>
                <div style="color: #999; font-size: 14px;">2026</div>
            </div>
            <div style="text-align: center;">
                <div style="font-size: 24px; color: #ec4899; margin-bottom: 5px;">⏰</div>
                <div style="color: #333; font-weight: 600;">19:30</div>
                <div style="color: #999; font-size: 14px;">Horário de Brasília</div>
            </div>
            <div style="text-align: center;">
                <div style="font-size: 24px; color: #ec4899; margin-bottom: 5px;">📍</div>
                <div style="color: #333; font-weight: 600;">Online</div>
                <div style="color: #999; font-size: 14px;">Zoom</div>
            </div>
        </div>
        
        <p style="color: #666; line-height: 1.6; max-width: 400px; margin: 30px auto;">Um evento exclusivo para você aprender sobre as últimas tendências em desenvolvimento web e IA.</p>
        
        <!-- Botão de confirmação -->
        <a href="#" style="background: #ec4899; color: #ffffff; text-decoration: none; padding: 18px 50px; border-radius: 50px; font-weight: 600; display: inline-block; box-shadow: 0 10px 20px rgba(236,72,153,0.2);">Confirmar Presença</a>
        
        <p style="color: #999; font-size: 14px; margin-top: 25px;">Vagas limitadas! Confirme sua presença o quanto antes.</p>
    </div>
    
    <!-- Footer -->
    <div style="background: #f8faff; padding: 30px; text-align: center;">
        <p style="color: #999; font-size: 12px;">Realização: NTW - New Software</p>
        <p style="margin-top: 10px;">
            <a href="{desinscrever_link}" style="color: #999; font-size: 12px;">Cancelar inscrição</a>
        </p>
    </div>
</div>'
    ]
];

// Busca templates personalizados salvos no banco (campanhas que foram salvas como template)
$custom_templates = $conn->query("
    SELECT id, titulo, assunto, conteudo, created_at 
    FROM newsletter_campanhas 
    WHERE template = 'custom' OR template IS NULL 
    ORDER BY created_at DESC 
    LIMIT 10
");

// Processa ação de usar template
if(isset($_GET['usar'])) {
    $template_key = $_GET['usar'];
    
    if(isset($templates[$template_key])) {
        // Salva no session pra usar na página de criar campanha
        $_SESSION['template_conteudo'] = $templates[$template_key]['conteudo'];
        $_SESSION['template_nome'] = $templates[$template_key]['nome'];
        header('Location: criar_campanha.php?template=' . $template_key);
        exit;
    }
}

if(isset($_GET['usar_custom'])) {
    $custom_id = (int)$_GET['usar_custom'];
    $stmt = $conn->prepare("SELECT * FROM newsletter_campanhas WHERE id = ?");
    $stmt->bind_param("i", $custom_id);
    $stmt->execute();
    $custom = $stmt->get_result()->fetch_assoc();
    
    if($custom) {
        $_SESSION['template_conteudo'] = $custom['conteudo'];
        $_SESSION['template_nome'] = $custom['titulo'];
        header('Location: criar_campanha.php?custom=' . $custom_id);
        exit;
    }
}

// Processa visualização de template
if(isset($_GET['preview'])) {
    $template_key = $_GET['preview'];
    if(isset($templates[$template_key])) {
        $template = $templates[$template_key];
        
        // Substitui variáveis por exemplos
        $preview = $template['conteudo'];
        $preview = str_replace('{nome}', 'João Silva', $preview);
        $preview = str_replace('{email}', 'joao@email.com', $preview);
        $preview = str_replace('{desinscrever_link}', '#', $preview);
        $preview = str_replace('{site_url}', SITE_URL, $preview);
        $preview = str_replace('{blog_url}', SITE_URL . '/blog.php', $preview);
        $preview = str_replace('{ano}', date('Y'), $preview);
        $preview = str_replace('{data}', date('d/m/Y'), $preview);
        
        // Aplica template base completo
        $html_completo = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>' . $template['nome'] . '</title>
        </head>
        <body style="margin:0; padding:0; background:#f4f4f4; font-family: Arial, sans-serif;">
            <div style="max-width:600px; margin:20px auto; background:white; border-radius:10px; overflow:hidden; box-shadow:0 2px 10px rgba(0,0,0,0.1);">
                <!-- Header -->
                <div style="background:linear-gradient(135deg, #0d47a1 0%, #1976d2 100%); padding:30px; text-align:center;">
                    <img src="' . SITE_URL . '/assets/image/logo.gif" style="max-width:150px; margin-bottom:10px; border-radius:12px;">
                    <h1 style="color:white; margin:0; font-size:24px;">NTW - New Software</h1>
                </div>
                
                <!-- Content -->
                <div style="padding:30px;">
                    ' . $preview . '
                </div>
                
                <!-- Footer -->
                <div style="background:#f8f8f8; padding:20px; text-align:center; font-size:12px; color:#666; border-top:1px solid #eee;">
                    <p style="margin:0 0 10px;">
                        <a href="' . ($config['instagram_url'] ?? '#') . '" style="color:#0d47a1; text-decoration:none; margin:0 10px;">Instagram</a> |
                        <a href="https://wa.me/' . ($config['whatsapp_numero'] ?? '5519987111656') . '" style="color:#0d47a1; text-decoration:none; margin:0 10px;">WhatsApp</a>
                    </p>
                    <p style="margin:0;">&copy; ' . date('Y') . ' NTW - New Software. Todos os direitos reservados.</p>
                    <p style="margin:5px 0 0;">
                        <a href="#" style="color:#999; text-decoration:underline;">Cancelar inscrição</a>
                    </p>
                </div>
            </div>
        </body>
        </html>';
        
        echo $html_completo;
        exit;
    }
}

if(isset($_GET['preview_custom'])) {
    $custom_id = (int)$_GET['preview_custom'];
    $stmt = $conn->prepare("SELECT * FROM newsletter_campanhas WHERE id = ?");
    $stmt->bind_param("i", $custom_id);
    $stmt->execute();
    $custom = $stmt->get_result()->fetch_assoc();
    
    if($custom) {
        $preview = $custom['conteudo'];
        $preview = str_replace('{nome}', 'João Silva', $preview);
        $preview = str_replace('{email}', 'joao@email.com', $preview);
        $preview = str_replace('{desinscrever_link}', '#', $preview);
        $preview = str_replace('{site_url}', SITE_URL, $preview);
        $preview = str_replace('{blog_url}', SITE_URL . '/blog.php', $preview);
        $preview = str_replace('{ano}', date('Y'), $preview);
        
        $html_completo = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>' . $custom['titulo'] . '</title>
        </head>
        <body style="margin:0; padding:0; background:#f4f4f4; font-family: Arial, sans-serif;">
            <div style="max-width:600px; margin:20px auto; background:white; border-radius:10px; overflow:hidden; box-shadow:0 2px 10px rgba(0,0,0,0.1);">
                <div style="background:linear-gradient(135deg, #0d47a1 0%, #1976d2 100%); padding:30px; text-align:center;">
                    <img src="' . SITE_URL . '/assets/image/logo.png" style="max-width:150px; margin-bottom:10px;">
                    <h1 style="color:white; margin:0; font-size:24px;">NTW - New Software</h1>
                </div>
                <div style="padding:30px;">' . $preview . '</div>
                <div style="background:#f8f8f8; padding:20px; text-align:center; font-size:12px; color:#666;">
                    <p>&copy; ' . date('Y') . ' NTW - New Software</p>
                </div>
            </div>
        </body>
        </html>';
        
        echo $html_completo;
        exit;
    }
}
?>

<style>
.templates-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    flex-wrap: wrap;
    gap: 20px;
}

.templates-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 25px;
    margin-top: 30px;
}

.template-card {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 16px;
    overflow: hidden;
    transition: all 0.3s ease;
    position: relative;
}

.template-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-lg);
    border-color: var(--accent);
}

.template-header {
    padding: 25px;
    color: white;
    position: relative;
    display: flex;
    align-items: center;
    gap: 15px;
}

.template-header i {
    font-size: 2.5rem;
    background: rgba(255,255,255,0.2);
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.template-header h3 {
    font-size: 1.3rem;
    margin: 0;
}

.template-body {
    padding: 25px;
}

.template-descricao {
    color: var(--text-secondary);
    font-size: 0.95rem;
    margin-bottom: 20px;
    line-height: 1.5;
}

.template-preview-mini {
    background: var(--bg-secondary);
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 20px;
    max-height: 150px;
    overflow: hidden;
    position: relative;
}

.template-preview-mini::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 50px;
    background: linear-gradient(transparent, var(--bg-secondary));
    pointer-events: none;
}

.template-preview-mini p {
    margin: 0 0 10px 0;
    color: var(--text-muted);
    font-size: 0.85rem;
    line-height: 1.4;
}

.template-footer {
    padding: 20px;
    border-top: 1px solid var(--border);
    display: flex;
    gap: 10px;
}

.btn-template {
    flex: 1;
    padding: 12px;
    border-radius: 8px;
    border: 1px solid var(--border);
    background: var(--bg-secondary);
    color: var(--text-secondary);
    text-decoration: none;
    font-size: 0.9rem;
    text-align: center;
    transition: all 0.2s ease;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.btn-template:hover {
    background: var(--hover);
    color: var(--accent);
    border-color: var(--accent);
}

.btn-template.primary {
    background: var(--accent);
    color: white;
    border: none;
}

.btn-template.primary:hover {
    background: #2563eb;
    transform: translateY(-2px);
}

.section-title {
    font-size: 1.5rem;
    margin: 40px 0 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid var(--accent);
    display: inline-block;
}

.section-title i {
    color: var(--accent);
    margin-right: 8px;
}

.variables-box {
    background: var(--bg-secondary);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 30px;
}

.variables-box h4 {
    margin-bottom: 15px;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 8px;
}

.variable-tag {
    display: inline-block;
    background: var(--accent-light);
    color: var(--accent);
    padding: 6px 14px;
    border-radius: 30px;
    font-size: 0.85rem;
    margin: 3px;
    cursor: pointer;
    transition: all 0.2s ease;
    border: 1px solid transparent;
}

.variable-tag:hover {
    background: var(--accent);
    color: white;
    transform: translateY(-2px);
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
    background: var(--bg-primary);
    border-radius: 16px;
    max-width: 800px;
    width: 90%;
    max-height: 80vh;
    overflow-y: auto;
    box-shadow: 0 20px 40px rgba(0,0,0,0.2);
}

.modal-header {
    padding: 20px;
    border-bottom: 1px solid var(--border);
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: sticky;
    top: 0;
    background: var(--bg-primary);
    z-index: 10;
}

.modal-header h3 {
    color: var(--text-primary);
    font-size: 1.3rem;
}

.modal-close {
    font-size: 1.5rem;
    cursor: pointer;
    color: var(--text-muted);
    transition: all 0.2s ease;
}

.modal-close:hover {
    color: var(--accent);
    transform: scale(1.1);
}

.modal-body {
    padding: 20px;
    max-height: 60vh;
    overflow-y: auto;
}

.modal-footer {
    padding: 20px;
    border-top: 1px solid var(--border);
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    position: sticky;
    bottom: 0;
    background: var(--bg-primary);
}

.btn {
    padding: 12px 25px;
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

.template-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    background: var(--accent);
    color: white;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
}

.date-badge {
    font-size: 0.8rem;
    color: var(--text-muted);
    margin-top: 10px;
    display: flex;
    align-items: center;
    gap: 5px;
}
</style>

<div class="main-content" id="mainContent">
    <div class="top-bar">
        <h1 class="page-title">
            <i class="fas fa-paint-brush"></i>
            Templates de E-mail
        </h1>
        
        <div class="top-bar-actions">
            <div class="theme-toggle" id="themeToggle">
                <i class="fas <?php echo $tema == 'dark' ? 'fa-moon' : 'fa-sun'; ?>" id="themeIcon"></i>
            </div>
        </div>
    </div>

    <div class="content-area">
        <!-- Botão Criar Novo Template -->
        <div style="margin-bottom: 30px;">
            <a href="criar_template.php" class="btn btn-primary" style="padding: 15px 30px; text-decoration: none;">
                <i class="fas fa-plus"></i> Criar Novo Template Personalizado
            </a>
        </div>

        <!-- Variáveis disponíveis -->
        <div class="variables-box">
            <h4><i class="fas fa-code"></i> Variáveis disponíveis para todos os templates:</h4>
            <span class="variable-tag" onclick="copyToClipboard('{nome}')">{nome}</span>
            <span class="variable-tag" onclick="copyToClipboard('{email}')">{email}</span>
            <span class="variable-tag" onclick="copyToClipboard('{desinscrever_link}')">{desinscrever_link}</span>
            <span class="variable-tag" onclick="copyToClipboard('{site_url}')">{site_url}</span>
            <span class="variable-tag" onclick="copyToClipboard('{blog_url}')">{blog_url}</span>
            <span class="variable-tag" onclick="copyToClipboard('{ano}')">{ano}</span>
            <span class="variable-tag" onclick="copyToClipboard('{data}')">{data}</span>
            <p style="margin-top: 15px; color: var(--text-muted); font-size: 0.85rem;">
                <i class="fas fa-info-circle"></i> Clique em uma variável para copiar. Elas serão substituídas automaticamente no envio.
            </p>
        </div>

        <!-- Templates Pré-definidos -->
        <h2 class="section-title">
            <i class="fas fa-star"></i> Templates Padrão
        </h2>
        
        <div class="templates-grid">
            <?php foreach($templates as $key => $template): ?>
            <div class="template-card">
                <div class="template-header" style="background: <?php echo $template['cor']; ?>;">
                    <i class="fas <?php echo $template['icone']; ?>"></i>
                    <h3><?php echo $template['nome']; ?></h3>
                </div>
                <div class="template-body">
                    <p class="template-descricao"><?php echo $template['descricao']; ?></p>
                    
                    <div class="template-preview-mini">
                        <?php 
                        $mini = strip_tags($template['conteudo']);
                        echo substr($mini, 0, 200) . '...';
                        ?>
                    </div>
                </div>
                <div class="template-footer">
                    <a href="?preview=<?php echo $key; ?>" target="_blank" class="btn-template">
                        <i class="fas fa-eye"></i> Prévia
                    </a>
                    <a href="?usar=<?php echo $key; ?>" class="btn-template primary">
                        <i class="fas fa-copy"></i> Usar Template
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Templates Personalizados Salvos -->
        <?php if($custom_templates->num_rows > 0): ?>
        <h2 class="section-title" style="margin-top: 50px;">
            <i class="fas fa-history"></i> Seus Templates Salvos
        </h2>
        
        <div class="templates-grid">
            <?php while($custom = $custom_templates->fetch_assoc()): ?>
            <div class="template-card">
                <div class="template-header" style="background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);">
                    <i class="fas fa-save"></i>
                    <h3><?php echo $custom['titulo']; ?></h3>
                </div>
                <div class="template-body">
                    <p class="template-descricao">
                        <strong>Assunto:</strong> <?php echo $custom['assunto']; ?>
                    </p>
                    
                    <div class="template-preview-mini">
                        <?php echo substr(strip_tags($custom['conteudo']), 0, 200) . '...'; ?>
                    </div>
                    
                    <div class="date-badge">
                        <i class="fas fa-calendar"></i> Salvo em <?php echo date('d/m/Y H:i', strtotime($custom['created_at'])); ?>
                    </div>
                </div>
                <div class="template-footer">
                    <a href="?preview_custom=<?php echo $custom['id']; ?>" target="_blank" class="btn-template">
                        <i class="fas fa-eye"></i> Prévia
                    </a>
                    <a href="?usar_custom=<?php echo $custom['id']; ?>" class="btn-template primary">
                        <i class="fas fa-copy"></i> Usar
                    </a>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <?php endif; ?>

        <!-- Modal de Pré-visualização (fallback caso a nova aba não funcione) -->
        <div id="previewModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 id="previewTitle">Pré-visualização do Template</h3>
                    <span class="modal-close" onclick="closeModal()">&times;</span>
                </div>
                <div class="modal-body" id="previewBody">
                    Carregando...
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" onclick="closeModal()">Fechar</button>
                    <button class="btn btn-primary" onclick="usarTemplateFromPreview()" id="previewUseBtn">
                        <i class="fas fa-copy"></i> Usar este template
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let currentTemplateKey = '';

function previewTemplate(key) {
    currentTemplateKey = key;
    
    const templates = <?php echo json_encode($templates); ?>;
    const template = templates[key];
    
    document.getElementById('previewTitle').textContent = template.nome;
    
    // Faz requisição AJAX para pegar o preview
    fetch('templates.php?preview=' + key)
        .then(response => response.text())
        .then(html => {
            document.getElementById('previewBody').innerHTML = html;
            document.getElementById('previewModal').style.display = 'flex';
        });
}

function previewCustom(id) {
    fetch('templates.php?preview_custom=' + id)
        .then(response => response.text())
        .then(html => {
            document.getElementById('previewTitle').textContent = 'Template Personalizado';
            document.getElementById('previewBody').innerHTML = html;
            document.getElementById('previewModal').style.display = 'flex';
            document.getElementById('previewUseBtn').onclick = function() {
                window.location.href = '?usar_custom=' + id;
            };
        });
}

function usarTemplate(key) {
    window.location.href = '?usar=' + key;
}

function usarTemplateFromPreview() {
    if(currentTemplateKey) {
        window.location.href = '?usar=' + currentTemplateKey;
    }
}

function closeModal() {
    document.getElementById('previewModal').style.display = 'none';
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        alert('✅ Variável copiada: ' + text);
    }).catch(() => {
        // Fallback para navegadores antigos
        const textarea = document.createElement('textarea');
        textarea.value = text;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        alert('✅ Variável copiada: ' + text);
    });
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