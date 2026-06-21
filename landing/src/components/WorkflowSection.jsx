import {
  MessageCircle,
  ClipboardList,
  Calendar,
  MapPin,
  Wrench,
  FileText,
  CreditCard,
  Star,
  BarChart2,
} from 'lucide-react'

const steps = [
  { icon: MessageCircle, label: 'Cliente solicita atendimento' },
  { icon: ClipboardList, label: 'ARCON cria ordem' },
  { icon: Calendar, label: 'Agenda técnico' },
  { icon: MapPin, label: 'Técnico faz check-in' },
  { icon: Wrench, label: 'Serviço executado' },
  { icon: FileText, label: 'Assinatura coletada' },
  { icon: CreditCard, label: 'Pagamento recebido' },
  { icon: Star, label: 'Pesquisa satisfação' },
  { icon: BarChart2, label: 'Relatório gerado' },
]

export default function WorkflowSection() {
  return (
    <section id="solucoes" className="py-24 px-4 sm:px-6 lg:px-8 bg-surface">
      <div className="max-w-7xl mx-auto">
        {/* Header */}
        <div className="text-center max-w-2xl mx-auto mb-16">
          <div className="inline-flex items-center gap-2 bg-primary/10 text-primary px-4 py-2 rounded-full text-xs font-bold mb-4 tracking-wide">
            FLUXO DE TRABALHO
          </div>
          <h2 className="text-3xl sm:text-4xl font-extrabold text-navy mb-4 tracking-tight">
            Do atendimento à satisfação,{' '}
            <span className="bg-gradient-to-r from-primary to-purple-brand bg-clip-text text-transparent">
              tudo integrado.
            </span>
          </h2>
          <p className="text-muted text-lg">
            Uma jornada completa, do primeiro contato ao relatório final, sem lacunas.
          </p>
        </div>

        {/* Desktop: horizontal */}
        <div className="hidden lg:flex items-start justify-center gap-0">
          {steps.map((step, i) => {
            const Icon = step.icon
            return (
              <div key={i} className="flex items-start">
                <div className="flex flex-col items-center gap-3 w-28">
                  <div className="w-12 h-12 rounded-2xl bg-white border border-gray-100 shadow-sm flex items-center justify-center hover:shadow-md hover:border-primary/20 transition-all group">
                    <Icon size={20} className="text-primary group-hover:scale-110 transition-transform" />
                  </div>
                  <div className="text-[11px] font-medium text-navy text-center leading-snug px-1">
                    {step.label}
                  </div>
                  <div className="w-6 h-6 rounded-full bg-primary/10 flex items-center justify-center">
                    <span className="text-[10px] font-bold text-primary">{i + 1}</span>
                  </div>
                </div>
                {i < steps.length - 1 && (
                  <div className="w-6 h-px bg-gradient-to-r from-primary/30 to-purple-brand/30 mt-6 flex-shrink-0" />
                )}
              </div>
            )
          })}
        </div>

        {/* Mobile: vertical */}
        <div className="lg:hidden space-y-2">
          {steps.map((step, i) => {
            const Icon = step.icon
            return (
              <div key={i} className="flex items-center gap-4 bg-white rounded-2xl p-4 border border-gray-100 shadow-sm">
                <div className="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center flex-shrink-0">
                  <Icon size={18} className="text-primary" />
                </div>
                <div>
                  <div className="text-xs font-bold text-primary mb-0.5">Passo {i + 1}</div>
                  <div className="text-sm font-medium text-navy">{step.label}</div>
                </div>
              </div>
            )
          })}
        </div>
      </div>
    </section>
  )
}
