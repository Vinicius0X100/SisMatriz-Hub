import React from 'react';
import ReactDOM from 'react-dom/client';
import CalendarMatrimonioApp from './components/Matrimonio/CalendarMatrimonioApp';
import 'react-big-calendar/lib/css/react-big-calendar.css';
import './bootstrap';

const rootEl = document.getElementById('calendar-matrimonio-root');
if (rootEl) {
    try {
        const root = ReactDOM.createRoot(rootEl as HTMLElement);
        root.render(
            <React.StrictMode>
                <CalendarMatrimonioApp />
            </React.StrictMode>
        );
    } catch (e) {
        console.error('Error mounting React:', e);
    }
}
