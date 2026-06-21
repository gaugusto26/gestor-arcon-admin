import { Check, ArrowRight } from 'lucide-react'

const features = [
  'Ordens de Serviço completas',
  'Assinatura Digital do cliente',
  'Check-in e Check-out com GPS',
  'Agenda de técnicos',
  'Equipamentos e histórico',
  'PMOC e manutenções',
  'Fotos e evidências em OS',
  'Orçamentos integrados',
  'Financeiro e cobranças',
  'Relatórios em PDF',
  'App mobile para técnicos',
  'Login com Google',
]

export default function ArconSection() {
  return (
    <section id="arcon" className="py-24 px-4 sm:px-6 lg:px-8 bg-surface">
      <div className="max-w-7xl mx-auto">
        <div className="grid lg:grid-cols-2 gap-16 items-center">
          {/* Left: real dashboard screenshot */}
          <div className="relative">
            {/* Login tela flutuante */}
            <div className="absolute -top-6 -right-4 z-10 w-40 rounded-2xl overflow-hidden shadow-2xl border border-gray-200">
              <img src="/images/arcon_login.png" alt="ARCON Login" className="w-full h-auto block" />
            </div>

            <div className="rounded-2xl overflow-hidden shadow-2xl border border-gray-200 relative z-0">
              <img
                src="/images/arcon_dashboard1.png"
                alt="ARCON Dashboard"
                className="w-full h-auto block"
              />
            </div>

            {/* Glow */}
            <div className="absolute -z-10 inset-0 bg-gradient-to-br from-primary/10 to-purple-brand/10 rounded-3xl blur-3xl translate-y-4 translate-x-2" />
          </div>

          {/* Right: features */}
          <div>
            <div className="flex items-center gap-3 mb-6">
              <div className="w-12 h-12 rounded-2xl overflow-hidden flex-shrink-0">
                <img src="/images/arcon_icon.png" alt="ARCON" className="w-full h-full object-cover" />
              </div>
              <div>
                <div className="text-xs font-bold text-primary tracking-widest">DIGITAL FIVE</div>
                <div className="text-lg font-extrabold text-navy">ARCON</div>
              </div>
            </div>

            <h2 className="text-3xl sm:text-4xl font-extrabold text-navy mb-4 tracking-tight">
              O sistema que o técnico usa no campo e o{' '}
              <span className="bg-gradient-to-r from-primary to-purple-brand bg-clip-text text-transparent">
                gestor vê em tempo real.
              </span>
            </h2>

            <p className="text-muted text-lg mb-8 leading-relaxed">
              O ARCON foi construído para empresas de climatização. Cada tela, cada funcionalidade
              foi pensada para a realidade do setor — do técnico em campo ao dono que acompanha tudo pelo painel.
            </p>

            <div className="grid grid-cols-2 gap-3 mb-9">
              {features.map((label) => (
                <div key={label} className="flex items-center gap-2 text-sm text-muted">
                  <Check size={14} className="text-primary flex-shrink-0" />
                  {label}
                </div>
              ))}
            </div>

            <a
              href="#contato"
              className="inline-flex items-center gap-2 bg-gradient-to-r from-primary to-purple-brand text-white font-semibold px-7 py-3.5 rounded-full hover:shadow-xl hover:shadow-primary/30 transition-all hover:-translate-y-0.5"
            >
              Testar o ARCON grátis
              <ArrowRight size={17} />
            </a>
          </div>
        </div>
      </div>
    </section>
  )
}
