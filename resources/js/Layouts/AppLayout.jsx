import { Link } from '@inertiajs/react';

export default function AppLayout({ children }) {
    return (
        <>


            <main>
                {children}
            </main>
        </>
    );
}