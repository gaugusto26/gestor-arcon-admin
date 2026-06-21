// Fácil de editar — apenas altere os valores em `stats`
const stats = [
  { value: '+2.500', label: 'empresas confiam', sub: 'na plataforma' },
  { value: '+25.000', label: 'usuários ativos', sub: 'todos os dias' },
  { value: '+180.000', label: 'ordens concluídas', sub: 'em todo o Brasil' },
  { value: '98%', label: 'satisfação', sub: 'dos clientes' },
]

export default function StatsSection() {
  return (
    <section className="py-24 px-4 sm:px-6 lg:px-8 bg-gradient-to-br from-navy via-[#0d2040] to-navy">
      <div className="max-w-7xl mx-auto">
        <div className="text-center mb-14">
          <h2 className="text-3xl sm:text-4xl font-extrabold text-white mb-3 tracking-tight">
            Números que falam por si
          </h2>
          <p className="text-gray-400 text-lg">
            A Digital Five cresce junto com as empresas que confiam na plataforma.
          </p>
        </div>

        <div className="grid grid-cols-2 lg:grid-cols-4 gap-5">
          {stats.map((s) => (
            <div
              key={s.label}
              className="text-center p-7 rounded-3xl bg-white/5 border border-white/10 backdrop-blur-sm hover:bg-white/8 transition-all hover:-translate-y-0.5"
            >
              <div className="text-4xl sm:text-5xl font-extrabold text-white mb-2 tracking-tight">
                {s.value}
              </div>
              <div className="text-primary font-semibold text-sm mb-1">{s.label}</div>
              <div className="text-gray-500 text-xs">{s.sub}</div>
            </div>
          ))}
        </div>
      </div>
    </section>
  )
}
