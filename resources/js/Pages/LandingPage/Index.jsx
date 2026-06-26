import AppLayout from "@/Layouts/AppLayout";
import { ThemeProvider } from "@/Context/ThemeContext";
import Navbar from "@/Components/Landing/Navbar";
import HeroSection from "@/Components/Landing/HeroSection";
import FeaturesSection from "@/Components/Landing/FeaturesSection";
import HowItWorksSection from "@/Components/Landing/HowItWorksSection";
import AboutSection from "@/Components/Landing/AboutSection";
import Footer from "@/Components/Landing/Footer";

export default function Index() {
    return (
        <ThemeProvider>
            <AppLayout>
                <div className="min-h-screen font-sans antialiased">
                    <Navbar />
                    <main>
                        <HeroSection />
                        <FeaturesSection />
                        <HowItWorksSection />
                        <AboutSection />
                    </main>
                    <Footer />
                </div>
            </AppLayout>
        </ThemeProvider>
    );
}
