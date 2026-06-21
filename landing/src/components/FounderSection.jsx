import { ArrowRight } from 'lucide-react'

export default function FounderSection() {
  return (
    <section className="py-24 px-4 sm:px-6 lg:px-8 bg-surface">
      <div className="max-w-5xl mx-auto">
        <div className="bg-navy rounded-3xl overflow-hidden shadow-xl">
          <div className="grid md:grid-cols-2 items-stretch">
            {/* Photo */}
            <div className="relative min-h-[340px] md:min-h-0 overflow-hidden">
              <img
                src="/images/criador_fundo.jpg"
                alt="Fundador Digital Five"
                className="w-full h-full object-cover object-top"
              />
              <div className="absolute inset-0 bg-gradient-to-r from-transparent to-navy/60 hidden md:block" />
            </div>

            {/* Text */}
            <div className="p-10 flex flex-col justify-center">
              <div className="inline-flex items-center gap-2 bg-white/10 text-white/80 px-4 py-2 rounded-full text-xs font-bold mb-6 tracking-wide w-fit">
                QUEM CONSTRUIU
              </div>
              <h2 className="text-2xl sm:text-3xl font-extrabold text-white mb-4 tracking-tight">
                Construído por quem entende{' '}
                <span className="bg-gradient-to-r from-primary to-purple-brand bg-clip-text text-transparent">
                  o problema de dentro.
                </span>
              </h2>
              <p className="text-gray-400 leading-relaxed mb-4">
                A Digital Five nasceu da frustração de operar empresas com dezenas de ferramentas
                desconectadas. O ARCON é o sistema que gostaríamos de ter tido desde o início.
              </p>
              <p className="text-gray-400 leading-relaxed mb-8">
                Cada funcionalidade foi construída com quem usa na ponta — o técnico no campo,
                o gestor no escritório, o dono que precisa enxergar o todo.
              </p>
              <a
                href="#contato"
                className="inline-flex items-center gap-2 bg-gradient-to-r from-primary to-purple-brand text-white font-semibold px-6 py-3 rounded-full hover:shadow-lg hover:shadow-primary/30 transition-all hover:-translate-y-0.5 text-sm w-fit"
              >
                Conhecer o ARCON
                <ArrowRight size={15} />
              </a>
            </div>
          </div>
        </div>
      </div>
    </section>
  )
}
