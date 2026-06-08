import '../css/app.css';
import React from 'react';
import ReactDOM from 'react-dom/client';


export default function App() {
    return (
        <div className="min-h-screen flex items-center justify-center border-y-gray-100">
            <h1 className="text-4xl font-bold text-blue-400">
                Laravel
            </h1>
        </div>
    )
}

ReactDOM.createRoot(document.getElementById('app')).render(
    <React.StrictMode>
        <App />
    </React.StrictMode>
)