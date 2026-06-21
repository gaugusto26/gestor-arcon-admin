import {
  SiMeta,
  SiWhatsapp,
  SiGoogle,
  SiSupabase,
  SiStripe,
  SiMercadopago,
  SiN8N,
  SiOpenai,
  SiGooglecalendar,
  SiGoogledrive,
} from 'react-icons/si'
import { Users } from 'lucide-react'

const integrations = [
  { name: 'WhatsApp', Icon: SiWhatsapp, color: 'text-green-500', bg: 'bg-green-50 border-green-100' },
  { name: 'Meta Ads', Icon: SiMeta, color: 'text-blue-600', bg: 'bg-blue-50 border-blue-100' },
  { name: 'Google', Icon: SiGoogle, color: 'text-red-500', bg: 'bg-red-50 border-red-100' },
  { name: 'Supabase', Icon: SiSupabase, color: 'text-emerald-600', bg: 'bg-emerald-50 border-emerald-100' },
  { name: 'Stripe', Icon: SiStripe, color: 'text-indigo-600', bg: 'bg-indigo-50 border-indigo-100' },
  { name: 'Mercado Pago', Icon: SiMercadopago, color: 'text-sky-500', bg: 'bg-sky-50 border-sky-100' },
  { name: 'N8N', Icon: SiN8N, color: 'text-orange-500', bg: 'bg-orange-50 border-orange-100' },
  { name: 'Kommo CRM', Icon: Users, color: 'text-pink-500', bg: 'bg-pink-50 border-pink-100' },
  { name: 'OpenAI', Icon: SiOpenai, color: 'text-gray-700', bg: 'bg-gray-50 border-gray-100' },
  { name: 'Google Agenda', Icon: SiGooglecalendar, color: 'text-blue-500', bg: 'bg-blue-50 border-blue-100' },
  { name: 'Google Drive', Icon: SiGoogledrive, color: 'text-yellow-500', bg: 'bg-yellow-50 border-yellow-100' },
]

export default function IntegrationsSection() {
  return (
    <section id="integracoes" className="py-24 px-4 sm:px-6 lg:px-8 bg-white">
      <div className="max-w-7xl mx-auto">
        {/* Header */}
        <div className="text-center max-w-2xl mx-auto mb-16">
          <div className="inline-flex items-center gap-2 bg-primary/10 text-primary px-4 py-2 rounded-full text-xs font-bold mb-4 tracking-wide">
            INTEGRAÇÕES
          </div>
          <h2 className="text-3xl sm:text-4xl font-extrabold text-navy mb-4 tracking-tight">
            Integrações que{' '}
            <span className="bg-gradient-to-r from-primary to-purple-brand bg-clip-text text-transparent">
              conectam seu negócio
            </span>
          </h2>
          <p className="text-muted text-lg">
            A Digital Five se integra com as ferramentas que você já usa, garantindo uma operação fluida.
          </p>
        </div>

        {/* Cards */}
        <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
          {integrations.map(({ name, Icon, color, bg }) => (
            <div
              key={name}
              className={`group flex flex-col items-center gap-3 p-5 rounded-2xl border hover:shadow-lg transition-all duration-200 hover:-translate-y-1 bg-white cursor-default ${bg}`}
            >
              <Icon size={28} className={`${color} transition-transform group-hover:scale-110`} />
              <span className="text-xs font-medium text-muted text-center leading-tight">{name}</span>
            </div>
          ))}
        </div>

        <p className="text-center text-sm text-muted mt-10">
          E muito mais integrações chegando via API e marketplace.
        </p>
      </div>
    </section>
  )
}
