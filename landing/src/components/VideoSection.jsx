export default function VideoSection() {
  return (
    <section className="py-24 px-4 sm:px-6 lg:px-8 bg-white" id="recursos">
      <div className="max-w-5xl mx-auto">
        <div className="text-center mb-12">
          <div className="inline-flex items-center gap-2 bg-primary/10 text-primary px-4 py-2 rounded-full text-xs font-bold mb-4 tracking-wide">
            NO CAMPO E NO PAINEL
          </div>
          <h2 className="text-3xl sm:text-4xl font-extrabold text-navy mb-4 tracking-tight">
            Do técnico em campo ao{' '}
            <span className="bg-gradient-to-r from-primary to-purple-brand bg-clip-text text-transparent">
              gestor em tempo real.
            </span>
          </h2>
          <p className="text-muted text-lg max-w-xl mx-auto">
            O ARCON acompanha cada etapa do serviço — da abertura da OS até o fechamento com assinatura digital.
          </p>
        </div>

        <div className="grid md:grid-cols-2 gap-6">
          {/* Foto técnico 1 */}
          <div className="relative rounded-3xl overflow-hidden shadow-xl border border-gray-100 aspect-square">
            <img
              src="/images/tecnico1.jpg"
              alt="Técnico realizando manutenção de ar-condicionado"
              className="w-full h-full object-cover"
            />
            <div className="absolute inset-0 bg-gradient-to-t from-navy/60 to-transparent" />
            <div className="absolute bottom-5 left-5 right-5">
              <div className="text-white font-bold text-base mb-1">Manutenção preventiva</div>
              <p className="text-white/75 text-xs">OS aberta, técnico alocado e cliente notificado — tudo automático.</p>
            </div>
          </div>

          {/* Foto técnico 2 */}
          <div className="relative rounded-3xl overflow-hidden shadow-xl border border-gray-100 aspect-square">
            <img
              src="/images/tecnico2.jpg"
              alt="Técnico em serviço externo de ar-condicionado"
              className="w-full h-full object-cover"
            />
            <div className="absolute inset-0 bg-gradient-to-t from-navy/60 to-transparent" />
            <div className="absolute bottom-5 left-5 right-5">
              <div className="text-white font-bold text-base mb-1">Check-in com GPS</div>
              <p className="text-white/75 text-xs">Gestor acompanha localização e status do técnico em tempo real.</p>
            </div>
          </div>
        </div>
      </div>
    </section>
  )
}
