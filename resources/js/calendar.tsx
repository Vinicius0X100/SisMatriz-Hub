import React from 'react';
import ReactDOM from 'react-dom/client';
import CalendarApp from './components/Reservas/CalendarApp';
import 'react-big-calendar/lib/css/react-big-calendar.css';
import './bootstrap';

console.log('Calendar script loaded');

class ErrorBoundary extends React.Component<{children: React.ReactNode}, {hasError: boolean, error: any}> {
    constructor(props: any) {
        super(props);
        this.state = { hasError: false, error: null };
    }

    static getDerivedStateFromError(error: any) {
        return { hasError: true, error };
    }

    componentDidCatch(error: any, errorInfo: any) {
        console.error("React Error Boundary Caught:", error, errorInfo);
    }

    render() {
        if (this.state.hasError) {
            return (
                <div className="p-4 bg-danger bg-opacity-10 text-danger rounded border border-danger border-opacity-25">
                    <h3 className="fw-bold">Algo deu errado no calendário.</h3>
                    <p className="small mt-2 mb-0">{this.state.error?.toString()}</p>
                </div>
            );
        }

        return this.props.children;
    }
}

const rootEl = document.getElementById('calendar-root');
if (rootEl) {
    console.log('Found calendar-root, mounting React...');
    try {
        const root = ReactDOM.createRoot(rootEl as HTMLElement);
        root.render(
            <React.StrictMode>
                <ErrorBoundary>
                    <CalendarApp />
                </ErrorBoundary>
            </React.StrictMode>
        );
        console.log('React mounted successfully');
    } catch (e) {
        console.error('Error mounting React:', e);
    }
} else {
    console.error('calendar-root element not found!');
}
