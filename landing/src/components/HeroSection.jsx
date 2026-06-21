import { ArrowRight, Shield, Headphones, Clock, Zap, CheckCircle2, TrendingUp } from 'lucide-react'

const benefits = [
  { icon: Clock, label: 'Setup em minutos' },
  { icon: Shield, label: 'Segurança e LGPD' },
  { icon: Headphones, label: 'Suporte especializado' },
]

export default function HeroSection() {
  return (
    <section className="pt-28 pb-20 px-4 sm:px-6 lg:px-8 overflow-hidden bg-gradient-to-b from-white to-surface">
      <div className="max-w-7xl mx-auto">
        <div className="grid lg:grid-cols-2 gap-14 items-center">
          {/* Left */}
          <div>
            <div className="inline-flex items-center gap-2 bg-primary/10 text-primary px-4 py-2 rounded-full text-xs font-bold mb-6 tracking-wide">
              <Zap size={13} />
              POWERED BY DIGITAL FIVE
            </div>

            <h1 className="text-4xl sm:text-5xl lg:text-[3.4rem] font-extrabold text-navy leading-[1.12] tracking-tight mb-6">
              Gestão completa para sua empresa de{' '}
              <span className="bg-gradient-to-r from-primary to-purple-brand bg-clip-text text-transparent">
                climatização.
              </span>
            </h1>

            <p className="text-lg text-muted leading-relaxed mb-8 max-w-lg">
              O ARCON centraliza ordens de serviço, agenda de técnicos, contratos, PMOC e
              financeiro — em um único sistema feito para quem vive de ar-condicionado.
            </p>

            <div className="flex flex-wrap gap-3 mb-10">
              <a
                href="https://wa.me/5517992347622?text=Ol%C3%A1%2C%20quero%20come%C3%A7ar%20a%20usar%20o%20ARCON!"
                target="_blank"
                rel="noopener noreferrer"
                className="inline-flex items-center gap-2 bg-gradient-to-r from-primary to-purple-brand text-white font-semibold px-7 py-3.5 rounded-full hover:shadow-xl hover:shadow-primary/30 transition-all hover:-translate-y-0.5"
              >
                Começar gratuitamente
                <ArrowRight size={17} />
              </a>
              <a
                href="#arcon"
                className="inline-flex items-center gap-2 border border-gray-200 text-navy font-semibold px-7 py-3.5 rounded-full hover:border-primary hover:text-primary transition-all"
              >
                Ver o sistema
              </a>
            </div>

            <div className="flex flex-wrap gap-6">
              {benefits.map(({ icon: Icon, label }) => (
                <div key={label} className="flex items-center gap-2 text-sm text-muted">
                  <Icon size={15} className="text-primary flex-shrink-0" />
                  {label}
                </div>
              ))}
            </div>
          </div>

          {/* Right: real dashboard screenshot */}
          <div className="relative hidden lg:block">
            {/* Floating: ARCON badge */}
            <div className="absolute -top-5 right-4 z-10 bg-white rounded-2xl shadow-xl border border-gray-100 px-4 py-3 flex items-center gap-3">
              <div className="w-9 h-9 rounded-xl overflow-hidden flex-shrink-0">
                <img src="/images/arcon_icon.png" alt="ARCON" className="w-full h-full object-cover" />
              </div>
              <div>
                <div className="text-xs font-bold text-navy">ARCON</div>
                <div className="text-xs text-success font-semibold">● Online</div>
              </div>
            </div>

            {/* Floating: OS notification */}
            <div className="absolute -left-8 top-1/3 z-10 bg-white rounded-2xl shadow-xl border border-gray-100 p-3.5 min-w-[175px]">
              <div className="flex items-center gap-2 mb-1">
                <CheckCircle2 size={13} className="text-success" />
                <span className="text-xs font-semibold text-navy">OS finalizada</span>
              </div>
              <div className="text-xs text-muted">OS #2891 — Manutenção preventiva</div>
            </div>

            {/* Floating: revenue card */}
            <div className="absolute -bottom-5 right-10 z-10 bg-white rounded-2xl shadow-xl border border-gray-100 p-3.5">
              <div className="flex items-center gap-1.5 mb-1">
                <TrendingUp size={13} className="text-success" />
                <span className="text-xs text-muted">Receita do mês</span>
              </div>
              <div className="text-base font-extrabold text-navy">R$ 48.520</div>
              <div className="text-xs text-success font-medium">↑ 12% vs. mês anterior</div>
            </div>

            {/* Dashboard screenshot */}
            <div className="ml-10 rounded-2xl overflow-hidden shadow-2xl border border-gray-200">
              <img
                src="/images/arcon_dashboard2.png"
                alt="ARCON Dashboard"
                className="w-full h-auto block"
              />
            </div>

            {/* Glow */}
            <div className="absolute -z-10 inset-0 bg-gradient-to-br from-primary/10 to-purple-brand/10 rounded-3xl blur-3xl translate-y-4 translate-x-2" />
          </div>
        </div>
      </div>
    </section>
  )
}
