import { Routes, Route } from 'react-router-dom'
import ScrollToTop from './components/ScrollToTop'
import Header from './components/Header'
import HeroSection from './components/HeroSection'
import ProductEcosystem from './components/ProductEcosystem'
import ArconSection from './components/ArconSection'
import VideoSection from './components/VideoSection'
import IntegrationsSection from './components/IntegrationsSection'
import WorkflowSection from './components/WorkflowSection'
import StatsSection from './components/StatsSection'
import TestimonialsSection from './components/TestimonialsSection'
import FounderSection from './components/FounderSection'
import PricingSection from './components/PricingSection'
import BlogSection from './components/BlogSection'
import FinalCTA from './components/FinalCTA'
import Footer from './components/Footer'
import PrivacyPolicy from './pages/PrivacyPolicy'
import TermsOfUse from './pages/TermsOfUse'

function LandingPage() {
  return (
    <div className="min-h-screen bg-surface font-sans">
      <Header />
      <main>
        <HeroSection />
        <ProductEcosystem />
        <ArconSection />
        <VideoSection />
        <IntegrationsSection />
        <WorkflowSection />
        <StatsSection />
        <TestimonialsSection />
        <FounderSection />
        <PricingSection />
        <BlogSection />
        <FinalCTA />
      </main>
      <Footer />
    </div>
  )
}

export default function App() {
  return (
    <>
      <ScrollToTop />
      <Routes>
      <Route path="/" element={<LandingPage />} />
      <Route path="/privacidade" element={<PrivacyPolicy />} />
      <Route path="/termos" element={<TermsOfUse />} />
    </Routes>
    </>
  )
}
