import { Link } from 'react-router-dom'

export default function Footer() {
  return (
    <footer className="bg-navy pt-16 pb-8 px-4 sm:px-6 lg:px-8">
      <div className="max-w-7xl mx-auto">
        <div className="pb-12 border-b border-white/10">
          <div className="max-w-md">
            <div className="flex items-center gap-3 mb-4">
              <div className="w-11 h-11 rounded-xl overflow-hidden flex-shrink-0 bg-white">
                <img src="/images/logo_quadrada.png" alt="" className="w-full h-full object-cover" />
              </div>
              <span className="font-extrabold text-xl text-white tracking-tight leading-tight">
                DIGITAL <span className="text-primary">FIVE</span>
              </span>
            </div>
            <p className="text-gray-400 text-sm leading-relaxed mb-5">
              O ecossistema SaaS completo para a sua empresa crescer sem bagunça.
            </p>
          </div>
        </div>

        <div className="pt-8 flex flex-col sm:flex-row justify-between items-center gap-3">
          <p className="text-gray-500 text-sm">© 2025 Digital Five. Todos os direitos reservados.</p>
          <div className="flex items-center gap-5">
            <Link to="/privacidade" className="text-gray-500 hover:text-gray-300 text-sm transition-colors">
              Política de Privacidade
            </Link>
            <Link to="/termos" className="text-gray-500 hover:text-gray-300 text-sm transition-colors">
              Termos de Uso
            </Link>
          </div>
        </div>
      </div>
    </footer>
  )
}
