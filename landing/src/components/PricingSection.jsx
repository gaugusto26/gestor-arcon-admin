import { useEffect, useState } from 'react'
import { Check, ArrowRight } from 'lucide-react'

const ADMIN_URL = import.meta.env.VITE_ADMIN_URL || 'https://sistemas.digitalfive.com.br'

const fallbackPlans = [
  {
    id: 'fallback-basic',
    nome: 'Autônomo Básico',
    preco_formatado: 'R$ 69,90',
    periodo: 'mensal',
    descricao: 'Ideal para autônomos que querem começar com o essencial.',
    destaque: false,
    caracteristicas: [
      { texto: '1 usuário' },
      { texto: '20 ordens de serviço/mês' },
      { texto: 'App mobile' },
      { texto: 'Relatórios básicos' },
      { texto: 'Suporte via WhatsApp' },
    ],
  },
  {
    id: 'fallback-pro',
    nome: 'Autônomo Profissional',
    preco_formatado: 'R$ 99,90',
    periodo: 'mensal',
    descricao: 'Para autônomos que precisam de mais controle e recursos.',
    destaque: false,
    caracteristicas: [
      { texto: '1 usuário' },
      { texto: '50 ordens de serviço/mês' },
      { texto: 'Financeiro integrado' },
      { texto: 'Relatórios avançados' },
    ],
  },
  {
    id: 'fallback-business',
    nome: 'Business Basic',
    preco_formatado: 'R$ 179,90',
    periodo: 'mensal',
    descricao: 'Para pequenas empresas com equipe de técnicos.',
    destaque: true,
    badge_text: 'Mais popular',
    caracteristicas: [
      { texto: '1 administrador' },
      { texto: '5 técnicos' },
      { texto: '150 ordens de serviço/mês' },
      { texto: 'Financeiro integrado' },
      { texto: 'Suporte via WhatsApp' },
    ],
  },
]

function periodLabel(period) {
  const labels = {
    mensal: '/mês',
    anual: '/ano',
    permanente: '',
    unico: '',
  }
  return labels[period] ?? ''
}

function PlanCard({ plan }) {
  const featured = Boolean(plan.destaque)
  const features = plan.caracteristicas?.length ? plan.caracteristicas : []

  return (
    <div
      className={`relative rounded-3xl p-6 border transition-all duration-200 hover:-translate-y-1 flex flex-col ${
        featured
          ? 'bg-gradient-to-br from-primary via-[#1a6bff] to-purple-brand border-transparent shadow-2xl shadow-primary/25'
          : 'bg-white border-gray-100 hover:shadow-xl hover:border-gray-200'
      }`}
    >
      {(featured || plan.badge_text) && (
        <div className="absolute -top-4 left-1/2 -translate-x-1/2 whitespace-nowrap">
          <span className="bg-white text-primary text-xs font-bold px-4 py-1.5 rounded-full shadow-lg border border-primary/10">
            {plan.badge_text || 'Mais popular'}
          </span>
        </div>
      )}

      <div className="mb-5">
        <div className={`text-xs font-bold mb-1 tracking-wide ${featured ? 'text-white/70' : 'text-muted'}`}>
          {plan.nome?.toUpperCase()}
        </div>
        <div className="flex items-baseline gap-1 mb-2">
          <span className={`text-3xl font-extrabold tracking-tight ${featured ? 'text-white' : 'text-navy'}`}>
            {plan.preco_formatado}
          </span>
          {periodLabel(plan.periodo) && (
            <span className={`text-sm ${featured ? 'text-white/60' : 'text-muted'}`}>
              {periodLabel(plan.periodo)}
            </span>
          )}
        </div>
        {plan.descricao && (
          <p className={`text-xs leading-relaxed ${featured ? 'text-white/70' : 'text-muted'}`}>
            {plan.descricao}
          </p>
        )}
      </div>

      <ul className="space-y-2.5 mb-7 flex-1">
        {features.slice(0, 7).map((feature, index) => (
          <li key={`${plan.id}-${index}`} className="flex items-start gap-2 text-sm">
            <Check size={14} className={`mt-0.5 flex-shrink-0 ${featured ? 'text-white' : 'text-primary'}`} />
            <span className={featured ? 'text-white/90' : 'text-muted'}>{feature.texto}</span>
          </li>
        ))}
      </ul>

      <a
        href="#contato"
        className={`flex items-center justify-center gap-2 w-full py-3 rounded-2xl font-semibold text-sm transition-all ${
          featured
            ? 'bg-white text-primary hover:bg-white/90 hover:shadow-md'
            : 'bg-gradient-to-r from-primary to-purple-brand text-white hover:shadow-lg hover:shadow-primary/25'
        }`}
      >
        Começar agora
        <ArrowRight size={15} />
      </a>
    </div>
  )
}

export default function PricingSection() {
  const [plans, setPlans] = useState([])
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    fetch(`${ADMIN_URL}/planos-api.php?limit=4`)
      .then((response) => response.json())
      .then((data) => {
        setPlans(data.plans?.length ? data.plans : fallbackPlans)
        setLoading(false)
      })
      .catch(() => {
        setPlans(fallbackPlans)
        setLoading(false)
      })
  }, [])

  const displayPlans = plans.length ? plans : fallbackPlans

  return (
    <section id="precos" className="py-24 px-4 sm:px-6 lg:px-8 bg-surface">
      <div className="max-w-7xl mx-auto">
        <div className="text-center max-w-2xl mx-auto mb-16">
          <div className="inline-flex items-center gap-2 bg-primary/10 text-primary px-4 py-2 rounded-full text-xs font-bold mb-4 tracking-wide">
            PLANOS
          </div>
          <h2 className="text-3xl sm:text-4xl font-extrabold text-navy mb-4 tracking-tight">
            Planos que crescem{' '}
            <span className="bg-gradient-to-r from-primary to-purple-brand bg-clip-text text-transparent">
              com você
            </span>
          </h2>
          <p className="text-muted text-lg">
            Do autônomo à empresa com equipe, escolha o plano certo para sua operação.
          </p>
        </div>

        {loading ? (
          <div className="grid md:grid-cols-2 lg:grid-cols-4 gap-5">
            {[1, 2, 3, 4].map((item) => (
              <div key={item} className="h-[430px] rounded-3xl bg-white border border-gray-100 animate-pulse" />
            ))}
          </div>
        ) : (
          <div className="grid md:grid-cols-2 lg:grid-cols-4 gap-5">
            {displayPlans.map((plan) => (
              <PlanCard key={plan.id} plan={plan} />
            ))}
          </div>
        )}

        <div className="mt-8 flex flex-col items-center gap-4 text-center">
          <p className="text-sm text-muted">
            Exibimos até 4 planos na página inicial. Os demais ficam na página completa de preços.
          </p>
          <a
            href={`${ADMIN_URL}/planos.php`}
            className="inline-flex items-center gap-2 rounded-full border border-primary/20 bg-white px-5 py-3 text-sm font-semibold text-primary shadow-sm transition-all hover:-translate-y-px hover:border-primary/40 hover:shadow-md"
          >
            Ver todos os planos
            <ArrowRight className="h-4 w-4" />
          </a>
        </div>
      </div>
    </section>
  )
}
