import { ArrowRight } from "lucide-react";
import Button from "@/Components/UI/Button";

export default function CtaSection() {
    return (
        <section className="py-24 bg-gray-50 dark:bg-gray-900">
            <div className="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h2 className="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white mb-5">
                    Ready to modernize attendance?
                </h2>
                <p className="text-gray-500 dark:text-gray-400 mb-8 text-lg">
                    Set up takes under 15 minutes. Your first room is always
                    free.
                </p>
                <div className="flex flex-wrap justify-center gap-4">
                    <a href="/register">
                        <Button size="lg">
                            Start for Free <ArrowRight size={18} />
                        </Button>
                    </a>
                    <a href="/demo">
                        <Button size="lg" variant="outline">
                            Book a Demo
                        </Button>
                    </a>
                </div>
            </div>
        </section>
    );
}
