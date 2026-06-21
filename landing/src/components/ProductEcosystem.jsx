import { ClipboardList, Calendar, Users, Wrench, DollarSign, Package, FileText, BarChart2 } from 'lucide-react'

const modules = [
  { icon: ClipboardList, name: 'Ordens de Serviço', desc: 'Criação, acompanhamento e fechamento de OS com assinatura digital.' },
  { icon: Calendar, name: 'Agenda', desc: 'Agendamento de técnicos com visão de calendário e controle de disponibilidade.' },
  { icon: Users, name: 'Clientes', desc: 'Cadastro completo com histórico de equipamentos, contratos e OS.' },
  { icon: Wrench, name: 'Equipamentos', desc: 'Controle de equipamentos por cliente com ficha técnica e histórico.' },
  { icon: FileText, name: 'PMOC', desc: 'Planos de Manutenção Preventiva em conformidade com as normas técnicas.' },
  { icon: DollarSign, name: 'Financeiro', desc: 'Cobranças, recebimentos e relatórios financeiros integrados às OS.' },
  { icon: Package, name: 'Materiais', desc: 'Controle de peças e materiais utilizados nas ordens de serviço.' },
  { icon: BarChart2, name: 'Relatórios', desc: 'Relatórios profissionais em PDF para clientes e gestão interna.' },
]

export default function ProductEcosystem() {
  return (
    <section id="produtos" className="py-24 px-4 sm:px-6 lg:px-8 bg-white">
      <div className="max-w-7xl mx-auto">
        {/* Header */}
        <div className="text-center max-w-2xl mx-auto mb-16">
          <div className="inline-flex items-center gap-2 bg-primary/10 text-primary px-4 py-2 rounded-full text-xs font-bold mb-4 tracking-wide">
            MÓDULOS DO ARCON
          </div>
          <h2 className="text-3xl sm:text-4xl font-extrabold text-navy mb-4 tracking-tight">
            Tudo que sua empresa de climatização{' '}
            <span className="bg-gradient-to-r from-primary to-purple-brand bg-clip-text text-transparent">
              precisa em um só lugar.
            </span>
          </h2>
          <p className="text-muted text-lg">
            Do primeiro atendimento ao relatório final, o ARCON cobre toda a operação da sua empresa.
          </p>
        </div>

        {/* ARCON logo destaque */}
        <div className="flex justify-center mb-12">
          <div className="flex items-center gap-4 bg-gradient-to-br from-primary/5 to-purple-brand/5 border border-primary/15 rounded-2xl px-8 py-5">
            <div className="w-14 h-14 rounded-2xl overflow-hidden flex-shrink-0">
              <img src="/images/arcon_icon.png" alt="ARCON" className="w-full h-full object-cover" />
            </div>
            <div>
              <div className="text-xl font-extrabold text-navy">ARCON</div>
              <div className="text-sm text-muted">Gestão Inteligente para Climatização</div>
            </div>
            <span className="ml-4 text-xs font-bold bg-success/10 text-success px-3 py-1.5 rounded-full">
              ● Disponível agora
            </span>
          </div>
        </div>

        {/* Módulos */}
        <div className="grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
          {modules.map(({ icon: Icon, name, desc }) => (
            <div
              key={name}
              className="group p-6 rounded-2xl border border-gray-100 bg-white hover:border-primary/20 hover:shadow-lg transition-all duration-200 hover:-translate-y-1"
            >
              <div className="w-11 h-11 rounded-xl bg-primary/10 flex items-center justify-center mb-4 group-hover:bg-primary/15 transition-colors">
                <Icon size={20} className="text-primary" />
              </div>
              <h3 className="text-sm font-bold text-navy mb-2">{name}</h3>
              <p className="text-xs text-muted leading-relaxed">{desc}</p>
            </div>
          ))}
        </div>

        <p className="text-center text-sm text-muted mt-10">
          Novos módulos e produtos chegando em breve — a plataforma Digital Five está em crescimento.
        </p>
      </div>
    </section>
  )
}
