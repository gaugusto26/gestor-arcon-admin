import { ArrowRight, Mail, Phone, Instagram } from 'lucide-react'

export default function FinalCTA() {
  return (
    <section id="contato" className="py-16 px-4 sm:px-6 lg:px-8">
      <div className="max-w-4xl mx-auto">
        <div className="relative bg-gradient-to-br from-primary via-[#1a6bff] to-purple-brand rounded-3xl p-12 sm:p-16 text-center overflow-hidden">
          {/* Decorative blobs */}
          <div className="absolute top-0 right-0 w-72 h-72 bg-white/5 rounded-full -translate-y-36 translate-x-36" />
          <div className="absolute bottom-0 left-0 w-56 h-56 bg-white/5 rounded-full translate-y-28 -translate-x-28" />
          <div className="absolute top-1/2 left-1/2 w-40 h-40 bg-white/5 rounded-full -translate-x-1/2 -translate-y-1/2" />

          <div className="relative z-10">
            <div className="inline-flex items-center gap-2 bg-white/10 text-white/90 px-4 py-2 rounded-full text-xs font-bold mb-6 tracking-wide">
              COMECE HOJE
            </div>
            <h2 className="text-3xl sm:text-4xl font-extrabold text-white mb-4 tracking-tight">
              Pronto para transformar sua empresa?
            </h2>
            <p className="text-white/75 text-lg mb-9 max-w-lg mx-auto leading-relaxed">
              Comece gratuitamente e descubra o poder da plataforma Digital Five.
            </p>
            <a
              href="https://wa.me/5517992347622?text=Ol%C3%A1%2C%20quero%20come%C3%A7ar%20a%20usar%20o%20ARCON!"
              target="_blank"
              rel="noopener noreferrer"
              className="inline-flex items-center gap-2.5 bg-white text-primary font-bold px-9 py-4 rounded-full hover:shadow-2xl hover:shadow-black/20 transition-all hover:-translate-y-0.5 text-base"
            >
              Começar gratuitamente
              <ArrowRight size={19} />
            </a>
            <p className="text-white/50 text-xs mt-5">
              Sem cartão de crédito. Cancele quando quiser.
            </p>

            <div className="mt-10 pt-8 border-t border-white/15 flex flex-col sm:flex-row items-center justify-center gap-5">
              <a
                href="https://wa.me/5517992347622"
                target="_blank"
                rel="noopener noreferrer"
                className="flex items-center gap-2 text-white/80 hover:text-white text-sm transition-colors"
              >
                <Phone size={15} />
                (17) 9923476-7622
              </a>
              <span className="hidden sm:block text-white/20">|</span>
              <a
                href="mailto:guilhermeoliveira.ov@gmail.com"
                className="flex items-center gap-2 text-white/80 hover:text-white text-sm transition-colors"
              >
                <Mail size={15} />
                guilhermeoliveira.ov@gmail.com
              </a>
              <span className="hidden sm:block text-white/20">|</span>
              <a
                href="https://instagram.com/gguiaaugusto"
                target="_blank"
                rel="noopener noreferrer"
                className="flex items-center gap-2 text-white/80 hover:text-white text-sm transition-colors"
              >
                <Instagram size={15} />
                @gguiaaugusto
              </a>
            </div>
          </div>
        </div>
      </div>
    </section>
  )
}
