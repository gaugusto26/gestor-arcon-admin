import { Link } from 'react-router-dom'
import { ArrowLeft } from 'lucide-react'

const sections = [
  {
    title: '1. Sobre a Plataforma',
    content: 'A DIGITAL FIVE é uma plataforma de software disponibilizada no modelo SaaS (Software as a Service), destinada à gestão empresarial, automação de processos e integração de sistemas. Os serviços poderão ser disponibilizados por meio de diferentes aplicativos, módulos e soluções desenvolvidas pela DIGITAL FIVE.',
  },
  {
    title: '2. Cadastro',
    content: 'Para utilizar determinadas funcionalidades poderá ser necessário criar uma conta. O usuário declara que:',
    items: ['Fornecerá informações verdadeiras e atualizadas', 'Manterá seus dados sempre corretos', 'É responsável pela confidencialidade de sua senha', 'É responsável por todas as atividades realizadas em sua conta'],
    suffix: 'A DIGITAL FIVE poderá suspender ou cancelar contas que contenham informações falsas ou que violem estes Termos.',
  },
  {
    title: '3. Licença de Uso',
    content: 'A DIGITAL FIVE concede ao usuário uma licença limitada, pessoal, não exclusiva, intransferível e revogável para utilização da plataforma. Esta licença não transfere qualquer direito de propriedade intelectual sobre o software.',
  },
  {
    title: '4. Utilização da Plataforma',
    content: 'O usuário compromete-se a utilizar a plataforma de forma ética, legal e compatível com sua finalidade. É proibido:',
    items: ['Utilizar o sistema para atividades ilícitas', 'Tentar acessar áreas restritas sem autorização', 'Realizar engenharia reversa', 'Copiar, reproduzir ou redistribuir o software sem autorização', 'Utilizar robôs ou scripts para sobrecarregar a plataforma', 'Inserir códigos maliciosos', 'Compartilhar credenciais de acesso de forma indevida', 'Praticar qualquer ato que comprometa a segurança da plataforma'],
  },
  {
    title: '5. Dados Inseridos na Plataforma',
    content: 'O usuário é exclusivamente responsável pelos dados cadastrados em sua conta. Ao inserir informações de terceiros, o usuário declara possuir autorização legal para realizar o respectivo tratamento, nos termos da legislação aplicável. A DIGITAL FIVE atua apenas como fornecedora da tecnologia, não realizando qualquer verificação prévia da veracidade das informações inseridas pelos usuários.',
  },
  {
    title: '6. Disponibilidade',
    content: 'A DIGITAL FIVE busca manter seus serviços disponíveis continuamente. Entretanto, poderão ocorrer interrupções temporárias decorrentes de:',
    items: ['Manutenção preventiva', 'Manutenção corretiva', 'Atualizações', 'Falhas de infraestrutura', 'Indisponibilidade de provedores terceiros', 'Eventos de força maior'],
    suffix: 'Essas interrupções não caracterizam falha contratual quando necessárias para garantir estabilidade, segurança ou evolução da plataforma.',
  },
  {
    title: '7. Integrações',
    content: 'A plataforma poderá integrar-se com serviços de terceiros, incluindo: Google, Meta, WhatsApp, OpenAI, Stripe, Mercado Pago, PicPay, Supabase, Kommo, Google Drive e Google Agenda. O funcionamento dessas integrações depende da disponibilidade e das políticas adotadas pelos respectivos fornecedores. A DIGITAL FIVE não se responsabiliza por alterações, limitações ou interrupções causadas por terceiros.',
  },
  {
    title: '8. Pagamentos e Assinaturas',
    content: 'Algumas funcionalidades poderão estar disponíveis apenas mediante contratação de planos pagos. As condições comerciais, preços, formas de pagamento e regras de renovação serão informadas no momento da contratação. O não pagamento poderá resultar na suspensão ou cancelamento do acesso aos serviços contratados.',
  },
  {
    title: '9. Propriedade Intelectual',
    content: 'Todo o conteúdo da plataforma, incluindo software, código-fonte, interface, identidade visual, logotipos, marcas, layouts, textos, imagens, ícones, bancos de dados e documentação, é protegido pela legislação de propriedade intelectual e pertence à DIGITAL FIVE ou a seus respectivos licenciantes. É proibida qualquer reprodução, modificação ou distribuição sem autorização expressa.',
  },
  {
    title: '10. Limitação de Responsabilidade',
    content: 'A DIGITAL FIVE não será responsável por:',
    items: ['Decisões tomadas pelos usuários com base nas informações da plataforma', 'Informações cadastradas pelos usuários', 'Indisponibilidade causada por terceiros', 'Falhas decorrentes de conexão com a internet', 'Perda de dados causada por ações do próprio usuário', 'Danos decorrentes de uso inadequado da plataforma'],
    suffix: 'Em qualquer hipótese, a responsabilidade da DIGITAL FIVE limita-se aos valores efetivamente pagos pelo usuário nos 12 (doze) meses anteriores ao evento que originou eventual reclamação, quando permitido pela legislação aplicável.',
  },
  {
    title: '11. Suspensão ou Encerramento da Conta',
    content: 'A DIGITAL FIVE poderá suspender ou cancelar contas que:',
    items: ['Violem estes Termos', 'Pratiquem atividades ilícitas', 'Coloquem em risco a segurança da plataforma', 'Utilizem o sistema de forma abusiva ou fraudulenta'],
    suffix: 'Sempre que possível, o usuário será previamente comunicado.',
  },
  {
    title: '12. Atualizações da Plataforma',
    content: 'A DIGITAL FIVE poderá adicionar, alterar ou remover funcionalidades visando melhorias, segurança, evolução tecnológica ou adequação legal. Essas alterações não geram direito adquirido à manutenção de funcionalidades específicas.',
  },
  {
    title: '13. Alterações destes Termos',
    content: 'Os presentes Termos poderão ser atualizados periodicamente. A versão mais recente permanecerá disponível na plataforma. O uso continuado dos serviços após a publicação das alterações representa concordância com a versão vigente.',
  },
  {
    title: '14. Lei Aplicável',
    content: 'Os presentes Termos são regidos pelas leis da República Federativa do Brasil, especialmente pelo Código Civil, Marco Civil da Internet e Lei Geral de Proteção de Dados (LGPD).',
  },
  {
    title: '15. Contato',
    content: 'Em caso de dúvidas, sugestões ou solicitações relacionadas a estes Termos de Uso, entre em contato:',
    contact: { name: 'Guilherme Augusto Luiz de Oliveira', email: 'guilhermeoliveira.ov@gmail.com' },
  },
]

export default function TermsOfUse() {
  return (
    <div className="min-h-screen bg-white font-sans">
      {/* Header */}
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
          <Link to="/" className="inline-flex items-center gap-1.5 text-sm text-muted hover:text-primary transition-colors">
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
            Termos de Uso
          </h1>
          <p className="text-muted text-sm">Última atualização: 20 de junho de 2026</p>
          <p className="text-muted mt-4 leading-relaxed">
            Bem-vindo à <strong className="text-navy">DIGITAL FIVE</strong>. Os presentes Termos de Uso
            regulam o acesso e a utilização da plataforma Digital Five, incluindo todos os seus aplicativos,
            sistemas, módulos, APIs, integrações e serviços, atuais e futuros, como o{' '}
            <strong className="text-navy">ARCON</strong>.
          </p>
          <p className="text-muted mt-3 leading-relaxed">
            Ao criar uma conta ou utilizar qualquer funcionalidade da plataforma, o usuário declara que leu,
            compreendeu e concorda integralmente com estes Termos.
          </p>
        </div>

        {/* Seções */}
        <div className="divide-y divide-gray-50">
          {sections.map((section, i) => (
            <div key={i} className="py-8">
              <h2 className="text-xl font-bold text-navy mb-4">{section.title}</h2>

              {section.content && (
                <p className="text-muted leading-relaxed mb-3">{section.content}</p>
              )}

              {section.items && (
                <ul className="list-disc list-inside space-y-1.5 text-muted mb-3 pl-2">
                  {section.items.map((item, j) => <li key={j}>{item}</li>)}
                </ul>
              )}

              {section.suffix && (
                <p className="text-muted leading-relaxed mt-3">{section.suffix}</p>
              )}

              {section.contact && (
                <div className="mt-2 text-muted space-y-1">
                  <p><strong className="text-navy">DIGITAL FIVE</strong></p>
                  <p>Responsável: {section.contact.name}</p>
                  <p>
                    E-mail:{' '}
                    <a href={`mailto:${section.contact.email}`} className="text-primary hover:underline">
                      {section.contact.email}
                    </a>
                  </p>
                </div>
              )}
            </div>
          ))}
        </div>

        {/* Considerações finais */}
        <div className="mt-12 pt-8 border-t border-gray-100 bg-surface rounded-2xl p-6">
          <p className="text-muted leading-relaxed text-sm">
            Ao utilizar a plataforma <strong className="text-navy">DIGITAL FIVE</strong> e seus aplicativos,
            incluindo o <strong className="text-navy">ARCON</strong>, o usuário declara ter lido, compreendido
            e aceitado integralmente estes Termos de Uso.
          </p>
        </div>
      </div>

      {/* Footer simples */}
      <div className="border-t border-gray-100 py-6 px-4 text-center">
        <p className="text-xs text-muted">© 2025 Digital Five. Todos os direitos reservados.</p>
        <div className="flex items-center justify-center gap-4 mt-2">
          <Link to="/privacidade" className="text-xs text-muted hover:text-primary transition-colors">Política de Privacidade</Link>
          <Link to="/termos" className="text-xs text-primary">Termos de Uso</Link>
          <Link to="/" className="text-xs text-muted hover:text-primary transition-colors">Voltar ao site</Link>
        </div>
      </div>
    </div>
  )
}
