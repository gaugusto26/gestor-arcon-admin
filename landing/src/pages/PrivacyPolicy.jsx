import { Link } from 'react-router-dom'
import { ArrowLeft } from 'lucide-react'

const sections = [
  {
    title: '1. Quem somos',
    content: `A DIGITAL FIVE é uma plataforma dedicada ao desenvolvimento e disponibilização de soluções SaaS (Software as a Service), automações, integrações e sistemas de gestão empresarial.

Atualmente, a DIGITAL FIVE é representada por seu responsável legal:

Responsável: Guilherme Augusto Luiz de Oliveira
E-mail para contato: guilhermeoliveira.ov@gmail.com

Enquanto a empresa estiver em processo de formalização, os serviços são prestados por seu responsável legal identificado acima. Após a constituição da pessoa jurídica, esta Política de Privacidade será atualizada para refletir os dados da empresa.`,
  },
  {
    title: '2. Quais dados coletamos',
    subsections: [
      {
        title: '2.1 Dados fornecidos pelo usuário',
        items: ['Nome completo', 'Empresa', 'CNPJ (quando aplicável)', 'CPF (quando necessário)', 'E-mail', 'Telefone', 'Cargo', 'Senha criptografada', 'Endereço', 'Informações enviadas em formulários', 'Informações cadastradas durante o uso da plataforma'],
      },
      {
        title: '2.2 Dados coletados automaticamente',
        items: ['Endereço IP', 'Localização aproximada', 'Navegador utilizado', 'Sistema operacional', 'Modelo do dispositivo', 'Identificadores técnicos', 'Data e horário de acesso', 'Logs de utilização', 'Cookies', 'Dados de desempenho', 'Informações de segurança'],
      },
      {
        title: '2.3 Dados provenientes de integrações',
        content: 'Quando autorizado pelo usuário, poderemos receber informações provenientes de serviços como: Google, Microsoft, Meta (Facebook e Instagram), WhatsApp, OpenAI, Stripe, Mercado Pago, Supabase, Kommo, Google Agenda, Google Drive, APIs públicas e privadas utilizadas pela plataforma. Cada integração acessará apenas as permissões autorizadas pelo usuário.',
      },
    ],
  },
  {
    title: '3. Login com Google',
    content: `Quando o usuário optar por autenticar-se utilizando sua conta Google, poderemos receber informações como nome, endereço de e-mail, foto de perfil (quando disponível) e identificador único da conta.

Esses dados são utilizados exclusivamente para autenticação, identificação da conta e segurança da plataforma. A DIGITAL FIVE não acessa conteúdos privados da conta Google além das permissões expressamente autorizadas pelo usuário.`,
  },
  {
    title: '4. Como utilizamos os dados',
    items: ['Criar contas de usuários', 'Autenticar acessos', 'Operar os sistemas da plataforma', 'Prestar suporte técnico', 'Processar pagamentos', 'Emitir documentos', 'Gerenciar assinaturas', 'Executar integrações', 'Melhorar nossos serviços', 'Enviar notificações', 'Cumprir obrigações legais', 'Detectar fraudes', 'Garantir segurança', 'Produzir estatísticas de uso', 'Desenvolver novos recursos'],
  },
  {
    title: '5. Dados armazenados pelos clientes da plataforma',
    content: `Os usuários da plataforma poderão cadastrar informações de terceiros para execução de suas atividades profissionais, como: clientes, empresas, equipamentos, endereços, telefones, ordens de serviço, fotografias, assinaturas digitais, documentos, orçamentos e contratos.

Nessas situações, o cliente contratante atua como Controlador dos Dados e a DIGITAL FIVE atua como Operadora dos Dados, realizando o tratamento conforme instruções do cliente, nos termos da Lei Geral de Proteção de Dados.`,
  },
  {
    title: '6. Compartilhamento de dados',
    content: 'A DIGITAL FIVE não vende dados pessoais. Os dados poderão ser compartilhados apenas quando necessário com provedores de hospedagem, processadores de pagamento, serviços de autenticação, provedores de e-mail, serviços de armazenamento, plataformas de automação, ferramentas de análise, fornecedores de infraestrutura e autoridades públicas, quando exigido por lei. Sempre que possível, o compartilhamento ocorrerá de forma limitada ao mínimo necessário.',
  },
  {
    title: '7. Base legal para tratamento',
    content: 'O tratamento de dados ocorre com fundamento nas hipóteses previstas na Lei nº 13.709/2018 (LGPD), incluindo: execução de contrato, cumprimento de obrigação legal, legítimo interesse, proteção do crédito, exercício regular de direitos e consentimento do titular, quando necessário.',
  },
  {
    title: '8. Segurança das informações',
    items: ['Conexão segura (HTTPS)', 'Criptografia', 'Autenticação de usuários', 'Controle de permissões', 'Backups periódicos', 'Monitoramento de acessos', 'Registros de auditoria (logs)', 'Proteção contra acessos não autorizados'],
    suffix: 'Apesar de nossos esforços, nenhum sistema é completamente imune a riscos decorrentes da própria natureza da internet.',
  },
  {
    title: '9. Armazenamento e retenção',
    content: 'Os dados serão armazenados enquanto houver relação contratual, durante o período necessário para prestação dos serviços, pelo prazo exigido pela legislação e enquanto necessário para defesa de direitos da DIGITAL FIVE. Após esse período, os dados poderão ser eliminados ou anonimizados.',
  },
  {
    title: '10. Direitos do titular',
    content: 'Nos termos da LGPD, o titular poderá solicitar:',
    items: ['Confirmação da existência de tratamento', 'Acesso aos dados', 'Correção de dados incorretos', 'Anonimização', 'Bloqueio', 'Eliminação', 'Portabilidade', 'Informação sobre compartilhamentos', 'Revogação do consentimento', 'Revisão de decisões automatizadas, quando aplicável'],
    suffix: 'As solicitações poderão ser realizadas pelos canais oficiais da DIGITAL FIVE.',
  },
  {
    title: '11. Cookies',
    content: 'Utilizamos cookies para autenticação, segurança, funcionamento da plataforma, personalização, análise estatística e melhoria da experiência do usuário. O usuário poderá gerenciar os cookies diretamente em seu navegador, ciente de que algumas funcionalidades poderão deixar de funcionar corretamente.',
  },
  {
    title: '12. Transferência internacional',
    content: 'Alguns serviços utilizados pela DIGITAL FIVE poderão processar dados em servidores localizados fora do Brasil. Sempre que isso ocorrer, adotaremos medidas compatíveis com a legislação brasileira para garantir a proteção dos dados pessoais.',
  },
  {
    title: '13. Responsabilidade do usuário',
    content: 'O usuário declara que possui autorização legal para inserir na plataforma dados pessoais de terceiros necessários ao desenvolvimento de suas atividades. O usuário é responsável pelas informações cadastradas em sua conta.',
  },
  {
    title: '14. Menores de idade',
    content: 'Os serviços da DIGITAL FIVE não são destinados a menores de 18 anos. Caso seja identificado tratamento indevido de dados de menores, adotaremos as medidas cabíveis para sua exclusão.',
  },
  {
    title: '15. Disponibilidade dos serviços',
    content: 'Embora sejam adotadas medidas para garantir alta disponibilidade, poderão ocorrer interrupções temporárias decorrentes de manutenção, atualizações, falhas técnicas, eventos de força maior ou indisponibilidade de provedores terceiros.',
  },
  {
    title: '16. Alterações desta Política',
    content: 'Esta Política poderá ser alterada a qualquer momento para refletir melhorias, mudanças legais ou novas funcionalidades da plataforma. A versão mais recente permanecerá sempre disponível nos canais oficiais da DIGITAL FIVE. O uso continuado da plataforma após a publicação das alterações constitui concordância com a versão vigente.',
  },
  {
    title: '17. Contato',
    content: 'Dúvidas, solicitações ou assuntos relacionados à privacidade poderão ser encaminhados para:',
    contact: { email: 'guilhermeoliveira.ov@gmail.com' },
  },
]

function renderSection(section, i) {
  return (
    <div key={i} className="mb-10">
      <h2 className="text-xl font-bold text-navy mb-4">{section.title}</h2>

      {section.content && (
        <div className="text-muted leading-relaxed whitespace-pre-line mb-3">{section.content}</div>
      )}

      {section.items && (
        <ul className="list-disc list-inside space-y-1.5 text-muted mb-3 pl-2">
          {section.items.map((item, j) => <li key={j}>{item}</li>)}
        </ul>
      )}

      {section.suffix && (
        <p className="text-muted leading-relaxed mt-3">{section.suffix}</p>
      )}

      {section.subsections && section.subsections.map((sub, j) => (
        <div key={j} className="mt-5">
          <h3 className="text-base font-semibold text-navy mb-3">{sub.title}</h3>
          {sub.content && <p className="text-muted leading-relaxed">{sub.content}</p>}
          {sub.items && (
            <ul className="list-disc list-inside space-y-1.5 text-muted pl-2">
              {sub.items.map((item, k) => <li key={k}>{item}</li>)}
            </ul>
          )}
        </div>
      ))}

      {section.contact && (
        <p className="text-muted mt-2">
          E-mail:{' '}
          <a href={`mailto:${section.contact.email}`} className="text-primary hover:underline">
            {section.contact.email}
          </a>
        </p>
      )}
    </div>
  )
}

export default function PrivacyPolicy() {
  return (
    <div className="min-h-screen bg-white font-sans">
      {/* Header simples */}
      <div className="border-b border-gray-100 px-4 sm:px-6 lg:px-8 py-4">
        <div className="max-w-4xl mx-auto flex items-center justify-between">
          <Link to="/" className="flex items-center gap-2.5">
            <div className="w-8 h-8 rounded-xl overflow-hidden">
              <img src="/images/logo_quadrada.png" alt="Digital Five" className="w-full h-full object-cover" />
            </div>
            <span className="font-extrabold text-base text-navy tracking-tight">
              DIGITAL <span className="text-primary">FIVE</span>
            </span>
          </Link>
          <Link
            to="/"
            className="inline-flex items-center gap-1.5 text-sm text-muted hover:text-primary transition-colors"
          >
            <ArrowLeft size={15} />
            Voltar ao site
          </Link>
        </div>
      </div>

      {/* Conteúdo */}
      <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        {/* Título */}
        <div className="mb-12 pb-8 border-b border-gray-100">
          <div className="inline-flex items-center gap-2 bg-primary/10 text-primary px-3 py-1.5 rounded-full text-xs font-bold mb-4 tracking-wide">
            LEGAL
          </div>
          <h1 className="text-3xl sm:text-4xl font-extrabold text-navy mb-3 tracking-tight">
            Política de Privacidade
          </h1>
          <p className="text-muted text-sm">Última atualização: 20 de junho de 2026</p>
          <p className="text-muted mt-4 leading-relaxed">
            A presente Política de Privacidade descreve como a <strong className="text-navy">DIGITAL FIVE</strong>,
            responsável pelo desenvolvimento e operação da plataforma Digital Five e de seus aplicativos,
            coleta, utiliza, compartilha, armazena e protege os dados pessoais de seus usuários.
          </p>
          <p className="text-muted mt-3 leading-relaxed">
            Esta Política aplica-se à plataforma <strong className="text-navy">Digital Five</strong>, ao{' '}
            <strong className="text-navy">ARCON</strong> e a todos os demais sistemas, aplicativos, APIs,
            integrações, websites, landing pages, automações e serviços disponibilizados pela DIGITAL FIVE,
            atuais ou futuros.
          </p>
          <p className="text-muted mt-3 leading-relaxed">
            Ao utilizar qualquer um de nossos serviços, o usuário declara que leu, compreendeu e concorda
            com esta Política de Privacidade.
          </p>
        </div>

        {/* Seções */}
        <div className="divide-y divide-gray-50">
          {sections.map((section, i) => (
            <div key={i} className="py-8">
              {renderSection(section, i)}
            </div>
          ))}
        </div>

        {/* Considerações finais */}
        <div className="mt-12 pt-8 border-t border-gray-100 bg-surface rounded-2xl p-6">
          <h2 className="text-base font-bold text-navy mb-3">Considerações finais</h2>
          <p className="text-muted leading-relaxed text-sm">
            Esta Política aplica-se à plataforma DIGITAL FIVE e a todos os seus produtos, incluindo o{' '}
            <strong className="text-navy">ARCON</strong>, bem como aos demais sistemas que venham a ser
            disponibilizados futuramente. Ao utilizar nossos serviços, o usuário declara estar ciente desta
            Política de Privacidade e concorda com o tratamento de seus dados pessoais na forma aqui descrita.
          </p>
        </div>
      </div>

      {/* Footer simples */}
      <div className="border-t border-gray-100 py-6 px-4 text-center">
        <p className="text-xs text-muted">© 2025 Digital Five. Todos os direitos reservados.</p>
        <div className="flex items-center justify-center gap-4 mt-2">
          <Link to="/privacidade" className="text-xs text-primary">Política de Privacidade</Link>
          <Link to="/termos" className="text-xs text-muted hover:text-primary transition-colors">Termos de Uso</Link>
          <Link to="/" className="text-xs text-muted hover:text-primary transition-colors">Voltar ao site</Link>
        </div>
      </div>
    </div>
  )
}
