import React from 'react';
import { ToolbarProps } from 'react-big-calendar';

const CustomToolbar: React.FC<ToolbarProps> = (props) => {
    const { label, onNavigate, onView, view } = props;

    const navigate = (action: 'PREV' | 'NEXT' | 'TODAY') => {
        onNavigate(action);
    };

    const changeView = (viewName: any) => {
        onView(viewName);
    };

    return (
        <div className="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 pb-3 border-bottom">
            <div className="d-flex align-items-center gap-3">
                <div className="d-flex align-items-center">
                    <h2 className="mb-0 fw-bold text-dark text-capitalize fs-3">{label}</h2>
                </div>
                <div className="d-flex gap-2 ms-4">
                    <button
                        type="button"
                        className="btn btn-outline-secondary btn-sm rounded-circle d-flex align-items-center justify-content-center"
                        style={{ width: '32px', height: '32px' }}
                        onClick={() => navigate('PREV')}
                        title="Anterior"
                    >
                        <i className="bi bi-chevron-left"></i>
                    </button>
                    <button
                        type="button"
                        className="btn btn-outline-secondary btn-sm rounded-circle d-flex align-items-center justify-content-center"
                        style={{ width: '32px', height: '32px' }}
                        onClick={() => navigate('NEXT')}
                        title="Próximo"
                    >
                        <i className="bi bi-chevron-right"></i>
                    </button>
                    <button
                        type="button"
                        className="btn btn-outline-primary btn-sm rounded-pill px-3 ms-2"
                        onClick={() => navigate('TODAY')}
                    >
                        Hoje
                    </button>
                </div>
            </div>

            <div className="bg-light p-1 rounded-pill d-inline-flex">
                <button
                    type="button"
                    className={`btn btn-sm rounded-pill px-3 ${view === 'month' ? 'btn-white shadow-sm fw-bold text-primary' : 'text-muted border-0'}`}
                    onClick={() => changeView('month')}
                >
                    Mês
                </button>
                <button
                    type="button"
                    className={`btn btn-sm rounded-pill px-3 ${view === 'week' ? 'btn-white shadow-sm fw-bold text-primary' : 'text-muted border-0'}`}
                    onClick={() => changeView('week')}
                >
                    Semana
                </button>
                <button
                    type="button"
                    className={`btn btn-sm rounded-pill px-3 ${view === 'day' ? 'btn-white shadow-sm fw-bold text-primary' : 'text-muted border-0'}`}
                    onClick={() => changeView('day')}
                >
                    Dia
                </button>
                <button
                    type="button"
                    className={`btn btn-sm rounded-pill px-3 ${view === 'agenda' ? 'btn-white shadow-sm fw-bold text-primary' : 'text-muted border-0'}`}
                    onClick={() => changeView('agenda')}
                >
                    Agenda
                </button>
            </div>
        </div>
    );
};

export default CustomToolbar;
