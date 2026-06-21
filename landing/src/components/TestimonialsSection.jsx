import { Star } from 'lucide-react'

const testimonials = [
  {
    name: 'Carlos Mendes',
    role: 'Diretor',
    company: 'Climatize SP',
    avatar: 'CM',
    avatarClass: 'bg-gradient-to-br from-primary to-purple-brand',
    text: 'O ARCON transformou completamente nossa operação. Hoje controlamos mais de 200 ordens de serviço por mês com muito mais eficiência. A agenda integrada e o check-in dos técnicos nos deram visibilidade que nunca tivemos antes.',
    rating: 5,
  },
  {
    name: 'Juliana Costa',
    role: 'CEO',
    company: 'FrioTech',
    avatar: 'JC',
    avatarClass: 'bg-gradient-to-br from-purple-brand to-indigo-500',
    text: 'A Digital Five entendeu o que nossa empresa precisava. O módulo de PMOC nos salvou de muitas dores de cabeça com conformidade. E o suporte é excepcional — resolvem tudo com agilidade.',
    rating: 5,
  },
  {
    name: 'Roberto Almeida',
    role: 'Sócio',
    company: 'Air Solutions',
    avatar: 'RA',
    avatarClass: 'bg-gradient-to-br from-navy to-primary',
    text: 'Tentei outros sistemas e nenhum entendia a complexidade de uma empresa de climatização como o ARCON. A assinatura digital e os relatórios em PDF foram um divisor de águas para nossos clientes corporativos.',
    rating: 5,
  },
]

function Stars({ count }) {
  return (
    <div className="flex gap-1 mb-4">
      {Array.from({ length: count }).map((_, i) => (
        <Star key={i} size={14} className="text-yellow-400 fill-yellow-400" />
      ))}
    </div>
  )
}

export default function TestimonialsSection() {
  return (
    <section className="py-24 px-4 sm:px-6 lg:px-8 bg-white">
      <div className="max-w-7xl mx-auto">
        {/* Header */}
        <div className="text-center max-w-2xl mx-auto mb-16">
          <div className="inline-flex items-center gap-2 bg-primary/10 text-primary px-4 py-2 rounded-full text-xs font-bold mb-4 tracking-wide">
            DEPOIMENTOS
          </div>
          <h2 className="text-3xl sm:text-4xl font-extrabold text-navy mb-4 tracking-tight">
            O que nossos clientes dizem
          </h2>
          <p className="text-muted text-lg">
            Empresas que já transformaram sua gestão com a Digital Five.
          </p>
        </div>

        {/* Cards */}
        <div className="grid md:grid-cols-3 gap-6">
          {testimonials.map((t) => (
            <div
              key={t.name}
              className="bg-surface rounded-3xl p-7 border border-gray-100 hover:shadow-lg transition-all duration-200 hover:-translate-y-1 flex flex-col"
            >
              <Stars count={t.rating} />
              <p className="text-muted text-sm leading-relaxed mb-6 flex-1">"{t.text}"</p>
              <div className="flex items-center gap-3">
                <div
                  className={`w-10 h-10 rounded-full flex items-center justify-center text-white text-sm font-bold flex-shrink-0 ${t.avatarClass}`}
                >
                  {t.avatar}
                </div>
                <div>
                  <div className="text-sm font-bold text-navy">{t.name}</div>
                  <div className="text-xs text-muted">
                    {t.role}, {t.company}
                  </div>
                </div>
              </div>
            </div>
          ))}
        </div>
      </div>
    </section>
  )
}
