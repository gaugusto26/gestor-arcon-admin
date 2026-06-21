import { useState, useEffect } from 'react'
import { Menu, X } from 'lucide-react'

const ADMIN_URL = import.meta.env.VITE_ADMIN_URL || 'https://sistemas.digitalfive.com.br'

const navItems = [
  { label: 'Produtos', href: '#produtos' },
  { label: 'Soluções', href: '#solucoes' },
  { label: 'Integrações', href: '#integracoes' },
  { label: 'Preços', href: `${ADMIN_URL}/planos.php` },
  { label: 'Blog', href: '#blog' },
  { label: 'Contato', href: '#contato' },
]

export default function Header() {
  const [scrolled, setScrolled] = useState(false)
  const [mobileOpen, setMobileOpen] = useState(false)

  useEffect(() => {
    const onScroll = () => setScrolled(window.scrollY > 20)
    window.addEventListener('scroll', onScroll)
    return () => window.removeEventListener('scroll', onScroll)
  }, [])

  return (
    <header
      className={`fixed top-0 left-0 right-0 z-50 transition-all duration-300 ${
        scrolled
          ? 'bg-white/95 backdrop-blur-md shadow-sm border-b border-gray-100'
          : 'bg-transparent'
      }`}
    >
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex items-center justify-between h-16">
          {/* Logo */}
          <a href="/" className="flex items-center gap-2.5 flex-shrink-0">
            <div className="w-9 h-9 rounded-xl overflow-hidden flex-shrink-0">
              <img src="/images/logo_quadrada.png" alt="" className="w-full h-full object-cover" />
            </div>
            <span className="font-extrabold text-[1.05rem] text-navy tracking-tight leading-none">
              DIGITAL <span className="text-primary">FIVE</span>
            </span>
          </a>

          {/* Desktop nav */}
          <nav className="hidden md:flex items-center gap-7">
            {navItems.map((item) => (
              <a
                key={item.label}
                href={item.href}
                className="text-sm font-medium text-muted hover:text-primary transition-colors"
              >
                {item.label}
              </a>
            ))}
          </nav>

          {/* Desktop CTAs */}
          <div className="hidden md:flex items-center gap-3">
            <a
              href={`${import.meta.env.VITE_ADMIN_URL ?? ''}/cliente/login.php`}
              className="text-sm font-medium text-muted hover:text-primary transition-colors px-3 py-2"
            >
              Entrar
            </a>
            <a
              href="https://wa.me/5517992347622?text=Ol%C3%A1%2C%20quero%20come%C3%A7ar%20a%20usar%20o%20ARCON!"
              target="_blank"
              rel="noopener noreferrer"
              className="text-sm font-semibold text-white bg-gradient-to-r from-primary to-purple-brand px-5 py-2.5 rounded-full hover:shadow-lg hover:shadow-primary/25 transition-all hover:-translate-y-px"
            >
              Começar gratuitamente
            </a>
          </div>

          {/* Mobile toggle */}
          <button
            className="md:hidden p-2 text-navy rounded-lg hover:bg-gray-100 transition-colors"
            onClick={() => setMobileOpen(!mobileOpen)}
            aria-label="Menu"
          >
            {mobileOpen ? <X size={22} /> : <Menu size={22} />}
          </button>
        </div>
      </div>

      {/* Mobile menu */}
      {mobileOpen && (
        <div className="md:hidden bg-white border-t border-gray-100 px-4 py-4">
          <nav className="flex flex-col gap-1 mb-4">
            {navItems.map((item) => (
              <a
                key={item.label}
                href={item.href}
                className="text-sm font-medium text-muted hover:text-primary transition-colors py-2.5 border-b border-gray-50"
                onClick={() => setMobileOpen(false)}
              >
                {item.label}
              </a>
            ))}
          </nav>
          <div className="flex flex-col gap-2 pt-2">
            <a
              href={`${import.meta.env.VITE_ADMIN_URL ?? ''}/cliente/login.php`}
              className="text-sm font-medium text-muted text-center py-2.5 border border-gray-200 rounded-xl"
            >
              Entrar
            </a>
            <a
              href="https://wa.me/5517992347622?text=Ol%C3%A1%2C%20quero%20come%C3%A7ar%20a%20usar%20o%20ARCON!"
              target="_blank"
              rel="noopener noreferrer"
              className="text-sm font-semibold text-white bg-gradient-to-r from-primary to-purple-brand px-5 py-3 rounded-xl text-center"
              onClick={() => setMobileOpen(false)}
            >
              Começar gratuitamente
            </a>
          </div>
        </div>
      )}
    </header>
  )
}
