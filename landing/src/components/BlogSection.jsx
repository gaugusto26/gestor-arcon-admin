import { useState, useEffect } from 'react'
import { ArrowRight, Clock, Calendar } from 'lucide-react'

const ADMIN_URL = import.meta.env.VITE_ADMIN_URL || ''
const BLOG_URL = ADMIN_URL ? `${ADMIN_URL}/blog/blog.php` : '/blog/blog.php'

function formatDate(dateStr) {
  if (!dateStr) return ''
  return new Date(dateStr).toLocaleDateString('pt-BR', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  })
}

const placeholders = [
  {
    titulo: 'Como organizar sua agenda de serviços e nunca mais perder uma visita',
    resumo: 'Descubra como técnicos que usam o ARCON estão aumentando sua capacidade de atendimento em até 40% com uma agenda inteligente.',
    categoria: 'Dicas e Tutoriais',
    categoria_cor: '#f59e0b',
    autor: 'Equipe Digital Five',
    tempo_leitura: 5,
  },
  {
    titulo: 'ARCON v2.0: novas funcionalidades de controle financeiro para técnicos',
    resumo: 'A atualização mais aguardada chegou. Veja tudo que está novo no módulo financeiro e como usar cada recurso para aumentar sua lucratividade.',
    categoria: 'Novidades',
    categoria_cor: '#0b5cff',
    autor: 'Equipe Digital Five',
    tempo_leitura: 4,
  },
  {
    titulo: 'De 0 a 150 ordens de serviço por mês: a história da Climatize SP',
    resumo: 'Como uma empresa de climatização do interior de São Paulo triplicou sua operação em menos de 6 meses usando o ARCON.',
    categoria: 'Cases de Sucesso',
    categoria_cor: '#10b981',
    autor: 'Equipe Digital Five',
    tempo_leitura: 6,
  },
]

function PostCard({ post, isPlaceholder }) {
  return (
    <div className="group bg-white rounded-3xl border border-gray-100 overflow-hidden hover:shadow-xl hover:border-gray-200 transition-all duration-200 hover:-translate-y-1 flex flex-col">
      {/* Imagem */}
      <div className="h-44 bg-gradient-to-br from-gray-100 to-gray-50 flex-shrink-0 overflow-hidden">
        {post.imagem_destaque ? (
          <img
            src={`${ADMIN_URL}/${post.imagem_destaque}`}
            alt={post.titulo}
            className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
          />
        ) : (
          <div className="w-full h-full flex items-center justify-center">
            <div
              className="w-16 h-16 rounded-2xl flex items-center justify-center text-2xl font-black text-white"
              style={{ background: post.categoria_cor || '#0b5cff' }}
            >
              DF
            </div>
          </div>
        )}
      </div>

      <div className="p-6 flex flex-col flex-1">
        {/* Categoria */}
        <div className="mb-3">
          <span
            className="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold"
            style={{
              backgroundColor: `${post.categoria_cor || '#0b5cff'}18`,
              color: post.categoria_cor || '#0b5cff',
            }}
          >
            {post.categoria || 'Blog'}
          </span>
        </div>

        {/* Título */}
        <h3 className="font-extrabold text-navy text-base leading-snug mb-2 line-clamp-2 group-hover:text-primary transition-colors">
          {post.titulo}
        </h3>

        {/* Resumo */}
        <p className="text-muted text-sm leading-relaxed line-clamp-3 flex-1 mb-4">
          {post.resumo}
        </p>

        {/* Meta */}
        <div className="flex items-center gap-3 text-xs text-muted border-t border-gray-50 pt-4">
          {post.data_publicacao && (
            <span className="flex items-center gap-1">
              <Calendar size={12} />
              {formatDate(post.data_publicacao)}
            </span>
          )}
          {post.tempo_leitura > 0 && (
            <span className="flex items-center gap-1">
              <Clock size={12} />
              {post.tempo_leitura} min
            </span>
          )}
          {isPlaceholder && (
            <span className="ml-auto text-xs font-semibold text-primary/60 bg-primary/5 px-2 py-0.5 rounded-full">
              Em breve
            </span>
          )}
        </div>
      </div>
    </div>
  )
}

export default function BlogSection() {
  const [posts, setPosts] = useState([])
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    fetch(`${ADMIN_URL}/blog/api.php?limit=3`)
      .then((r) => r.json())
      .then((data) => {
        setPosts(data.posts || [])
        setLoading(false)
      })
      .catch(() => {
        setPosts([])
        setLoading(false)
      })
  }, [])

  const hasPosts = posts.length > 0
  const displayPosts = hasPosts ? posts : placeholders
  const isPlaceholder = !hasPosts

  return (
    <section id="blog" className="py-24 px-4 sm:px-6 lg:px-8 bg-white">
      <div className="max-w-7xl mx-auto">
        {/* Header */}
        <div className="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-6 mb-14">
          <div className="max-w-xl">
            <div className="inline-flex items-center gap-2 bg-primary/10 text-primary px-4 py-2 rounded-full text-xs font-bold mb-4 tracking-wide">
              BLOG
            </div>
            <h2 className="text-3xl sm:text-4xl font-extrabold text-navy tracking-tight mb-3">
              Conteúdo para{' '}
              <span className="bg-gradient-to-r from-primary to-purple-brand bg-clip-text text-transparent">
                técnicos e gestores
              </span>
            </h2>
            <p className="text-muted text-lg">
              Dicas, novidades e cases reais para você crescer com o ARCON.
            </p>
          </div>

          <a
            href={BLOG_URL}
            className="inline-flex items-center gap-2 text-primary font-semibold text-sm hover:gap-3 transition-all shrink-0"
          >
            Ver todos os artigos
            <ArrowRight size={16} />
          </a>
        </div>

        {/* Grid */}
        {loading ? (
          <div className="grid md:grid-cols-3 gap-6">
            {[1, 2, 3].map((i) => (
              <div key={i} className="bg-white rounded-3xl border border-gray-100 overflow-hidden animate-pulse">
                <div className="h-44 bg-gray-100" />
                <div className="p-6 space-y-3">
                  <div className="h-4 bg-gray-100 rounded-full w-24" />
                  <div className="h-5 bg-gray-100 rounded-full w-full" />
                  <div className="h-5 bg-gray-100 rounded-full w-3/4" />
                  <div className="h-16 bg-gray-50 rounded-xl" />
                </div>
              </div>
            ))}
          </div>
        ) : (
          <div className="grid md:grid-cols-3 gap-6">
            {displayPosts.map((post, i) => (
              <PostCard key={post.slug || i} post={post} isPlaceholder={isPlaceholder} />
            ))}
          </div>
        )}

        {isPlaceholder && !loading && (
          <p className="text-center text-sm text-muted mt-8 opacity-60">
            Em breve os primeiros artigos estarão disponíveis.
          </p>
        )}
      </div>
    </section>
  )
}
