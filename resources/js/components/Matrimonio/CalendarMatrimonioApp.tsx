import React, { useState, useEffect, useCallback, useMemo } from 'react';
import { Calendar, dateFnsLocalizer, Views, View } from 'react-big-calendar';
import format from 'date-fns/format';
import parse from 'date-fns/parse';
import startOfWeek from 'date-fns/startOfWeek';
import getDay from 'date-fns/getDay';
import ptBR from 'date-fns/locale/pt-BR';
import addMonths from 'date-fns/addMonths';
import subMonths from 'date-fns/subMonths';
import startOfMonth from 'date-fns/startOfMonth';
import endOfMonth from 'date-fns/endOfMonth';
import eachDayOfInterval from 'date-fns/eachDayOfInterval';
import isSameMonth from 'date-fns/isSameMonth';
import isSameDay from 'date-fns/isSameDay';
import isToday from 'date-fns/isToday';
import endOfWeek from 'date-fns/endOfWeek';
import 'react-big-calendar/lib/css/react-big-calendar.css';
import axios from 'axios';

import { ReservaMatrimonio, Local, RegraMatrimonio } from './types';
import RulesModal from './RulesModal';
import EventModalMatrimonio from './EventModalMatrimonio';
import ConfirmationModal from './ConfirmationModal';

const locales = {
    'pt-BR': ptBR,
};

const localizer = dateFnsLocalizer({
    format,
    parse,
    startOfWeek,
    getDay,
    locales,
});

interface MiniCalendarProps {
    currentDate: Date;
    onDateSelect: (date: Date) => void;
}

const MiniCalendar: React.FC<MiniCalendarProps> = ({ currentDate, onDateSelect }) => {
    const [viewDate, setViewDate] = useState(currentDate);

    useEffect(() => {
        setViewDate(currentDate);
    }, [currentDate]);

    const days = useMemo(() => {
        const start = startOfWeek(startOfMonth(viewDate), { locale: ptBR });
        const end = endOfWeek(endOfMonth(viewDate), { locale: ptBR });
        return eachDayOfInterval({ start, end });
    }, [viewDate]);

    const weekDays = ['D', 'S', 'T', 'Q', 'Q', 'S', 'S'];

    return (
        <div className="mini-calendar mb-4 px-2">
            <div className="d-flex justify-content-between align-items-center mb-3">
                <span className="fw-bold text-capitalize text-dark" style={{ fontSize: '0.9rem' }}>
                    {format(viewDate, 'MMMM yyyy', { locale: ptBR })}
                </span>
                <div className="d-flex gap-1">
                    <button className="btn btn-sm btn-light border-0 rounded-circle p-1" onClick={() => setViewDate(subMonths(viewDate, 1))}>
                        <i className="bi bi-chevron-left text-muted" style={{ fontSize: '0.8rem' }}></i>
                    </button>
                    <button className="btn btn-sm btn-light border-0 rounded-circle p-1" onClick={() => setViewDate(addMonths(viewDate, 1))}>
                        <i className="bi bi-chevron-right text-muted" style={{ fontSize: '0.8rem' }}></i>
                    </button>
                </div>
            </div>
            <div className="d-grid text-center mb-2" style={{ gridTemplateColumns: 'repeat(7, 1fr)' }}>
                {weekDays.map((day, idx) => (
                    <small key={idx} className="text-muted fw-bold" style={{ fontSize: '0.7rem' }}>{day}</small>
                ))}
            </div>
            <div className="d-grid gap-1" style={{ gridTemplateColumns: 'repeat(7, 1fr)' }}>
                {days.map(day => {
                    const isSelected = isSameDay(day, currentDate);
                    const isCurrentMonth = isSameMonth(day, viewDate);
                    const isTodayDate = isToday(day);
                    
                    return (
                        <button
                            key={day.toISOString()}
                            className={`btn btn-sm p-0 d-flex align-items-center justify-content-center rounded-circle border-0 position-relative ${
                                isSelected ? 'bg-primary text-white shadow-sm' : 
                                isTodayDate ? 'bg-primary-subtle text-primary fw-bold' : 
                                'text-dark hover-bg-light'
                            }`}
                            style={{ 
                                width: '28px', 
                                height: '28px', 
                                fontSize: '0.75rem',
                                color: !isCurrentMonth ? '#dee2e6' : undefined,
                                cursor: 'pointer',
                                transition: 'all 0.2s'
                            }}
                            onClick={() => onDateSelect(day)}
                        >
                            {format(day, 'd')}
                        </button>
                    );
                })}
            </div>
        </div>
    );
};

const CustomToolbar = (toolbar: any) => {
    const goToBack = () => {
        toolbar.onNavigate('PREV');
    };

    const goToNext = () => {
        toolbar.onNavigate('NEXT');
    };

    const goToCurrent = () => {
        toolbar.onNavigate('TODAY');
    };

    const label = () => {
        const date = toolbar.date;
        return (
            <span className="text-capitalize fw-bold fs-4 text-dark ms-3">
                {format(date, 'MMMM yyyy', { locale: ptBR })}
            </span>
        );
    };

    return (
        <div className="d-flex justify-content-between align-items-center mb-4 px-2 py-2">
            <div className="d-flex align-items-center">
                 <div className="d-flex align-items-center gap-1">
                    <button className="btn btn-light border rounded-pill px-3 fw-bold" onClick={goToCurrent}>
                        Hoje
                    </button>
                    <div className="d-flex gap-1 ms-2">
                        <button className="btn btn-light border-0 rounded-circle p-2 d-flex align-items-center justify-content-center" onClick={goToBack} title="Anterior" style={{ width: '32px', height: '32px' }}>
                            <i className="bi bi-chevron-left"></i>
                        </button>
                        <button className="btn btn-light border-0 rounded-circle p-2 d-flex align-items-center justify-content-center" onClick={goToNext} title="Próximo" style={{ width: '32px', height: '32px' }}>
                            <i className="bi bi-chevron-right"></i>
                        </button>
                    </div>
                </div>
                {label()}
            </div>

            <div className="btn-group shadow-sm rounded-pill" role="group">
                {['month', 'week', 'day', 'agenda'].map(view => (
                    <button
                        key={view}
                        type="button"
                        className={`btn btn-sm px-3 ${toolbar.view === view ? 'btn-primary' : 'btn-light text-muted'}`}
                        onClick={() => toolbar.onView(view)}
                    >
                        {view === 'month' ? 'Mês' : 
                         view === 'week' ? 'Semana' : 
                         view === 'day' ? 'Dia' : 'Agenda'}
                    </button>
                ))}
            </div>
        </div>
    );
};

const CalendarMatrimonioApp = () => {
    // Estados Principais
    const [events, setEvents] = useState<ReservaMatrimonio[]>([]);
    const [locais, setLocais] = useState<Local[]>([]);
    const [rules, setRules] = useState<RegraMatrimonio[]>([]);
    const [view, setView] = useState<View>(Views.MONTH);
    const [date, setDate] = useState(new Date());
    const [loading, setLoading] = useState(false);
    const [searchTerm, setSearchTerm] = useState('');

    const filteredEvents = useMemo(() => {
        if (!searchTerm) {
            return events.filter(e => isSameMonth(e.start, date)).sort((a, b) => a.start.getTime() - b.start.getTime());
        }
        return events.filter(e => e.title.toLowerCase().includes(searchTerm.toLowerCase())).sort((a, b) => a.start.getTime() - b.start.getTime());
    }, [events, searchTerm, date]);

    // Estados dos Modais
    const [isRulesModalOpen, setIsRulesModalOpen] = useState(false);
    const [isEventModalOpen, setIsEventModalOpen] = useState(false);
    const [selectedEvent, setSelectedEvent] = useState<Partial<ReservaMatrimonio> | null>(null);
    const [modalMode, setModalMode] = useState<'create' | 'edit'>('create');
    const [confirmModal, setConfirmModal] = useState<{
        isOpen: boolean;
        title: string;
        message: string;
        onConfirm: () => void;
        variant?: 'primary' | 'danger' | 'warning';
        confirmText?: string;
        cancelText?: string;
    }>({
        isOpen: false,
        title: '',
        message: '',
        onConfirm: () => {},
        variant: 'primary',
        confirmText: 'Confirmar',
        cancelText: 'Cancelar'
    });

    // Carregar Locais e Regras
    const fetchLocaisAndRules = async () => {
        try {
            const [locaisRes, rulesRes] = await Promise.all([
                axios.get('/api/calendario-matrimonio/locais'),
                axios.get('/api/calendario-matrimonio/regras')
            ]);
            setLocais(locaisRes.data);
            setRules(rulesRes.data);
        } catch (error) {
            console.error("Erro ao carregar dados iniciais:", error);
        }
    };

    useEffect(() => {
        fetchLocaisAndRules();
    }, []);

    // Carregar Eventos
    const fetchEvents = useCallback(async () => {
        setLoading(true);
        try {
            const response = await axios.get('/api/calendario-matrimonio/reservas');
            console.log("Eventos recebidos:", response.data); // Debug
            
            if (response.data) {
                const mappedEvents = response.data.map((evt: any) => {
                    // Garantir que as datas sejam objetos Date válidos
                    const start = new Date(evt.start);
                    const end = new Date(evt.end);

                    if (isNaN(start.getTime()) || isNaN(end.getTime())) {
                        console.error("Data inválida para o evento:", evt);
                        return null;
                    }

                    return {
                        id: evt.id,
                        title: evt.title,
                        start: start,
                        end: end,
                        ent_id: evt.extendedProps.ent_id,
                        local: evt.extendedProps.local,
                        local_nome: evt.extendedProps.local_nome,
                        telefone_noivo: evt.extendedProps.telefone_noivo,
                        telefone_noiva: evt.extendedProps.telefone_noiva,
                        efeito_civil: Boolean(evt.extendedProps.efeito_civil),
                        color: evt.backgroundColor,
                        paroquia_id: evt.paroquia_id,
                    };
                }).filter((evt: any) => evt !== null); // Remover eventos inválidos

                console.log("Eventos mapeados:", mappedEvents); // Debug
                setEvents(mappedEvents);
            }
        } catch (error) {
            console.error("Erro ao carregar eventos:", error);
        } finally {
            setLoading(false);
        }
    }, []);

    useEffect(() => {
        fetchEvents();
    }, [fetchEvents]);

    // Handlers
    const handleSelectSlot = ({ start }: { start: Date }) => {
        const today = new Date();
        today.setHours(0, 0, 0, 0);

        // Permitir criar em qualquer data, mas focar no horário selecionado
        const selectedDate = new Date(start);
        
        // Se for na visualização de mês, define hora atual + 1
        if (view === Views.MONTH) {
            const now = new Date();
            selectedDate.setHours(now.getHours() + 1, 0, 0, 0);
        }

        setSelectedEvent({
            start: selectedDate,
            end: new Date(selectedDate.getTime() + 60 * 60 * 1000), // 1 hora de duração
            color: '#3788d8',
            efeito_civil: false
        });
        setModalMode('create');
        setIsEventModalOpen(true);
    };

    const handleSelectEvent = (event: ReservaMatrimonio) => {
        setSelectedEvent(event);
        setModalMode('edit');
        setIsEventModalOpen(true);
    };

    const validateEvent = (data: Partial<ReservaMatrimonio>): string | null => {
        if (!data.start || !data.ent_id) return null;

        const communityId = Number(data.ent_id);
        const rule = rules.find(r => r.comunidade_id === communityId);

        if (!rule) return null;

        const dayOfWeek = String(getDay(data.start));

        // Check allowed days
        // Se dias_permitidos for nulo ou vazio, consideramos que há restrição se a regra existe
        const allowedDays = rule.dias_permitidos || [];
        if (allowedDays.length > 0 && !allowedDays.includes(dayOfWeek)) {
            return `A comunidade ${rule.nome} não permite casamentos neste dia da semana.`;
        } else if (allowedDays.length === 0) {
             // Caso não tenha dias definidos, pode ser interpretado como "Sem dias permitidos" ou "Todos os dias"
             // O usuário mencionou "não tem dias definidos ainda" como algo que bloqueava.
             // Vamos assumir que se o array está vazio, é um bloqueio (precisa definir dias).
             return `A comunidade ${rule.nome} não possui dias de casamento definidos.`;
        }

        // Check max events per day
        const eventsOnDay = events.filter(e => 
            isSameDay(e.start, data.start!) && 
            e.ent_id === communityId &&
            e.id !== data.id 
        );

        if (eventsOnDay.length >= rule.max_casamentos_por_dia) {
            return `A comunidade ${rule.nome} já atingiu o limite de ${rule.max_casamentos_por_dia} casamentos para este dia.`;
        }

        return null;
    };

    const processSaveEvent = async (data: Partial<ReservaMatrimonio>, force: boolean = false) => {
        const payload = {
            titulo: data.title,
            data: format(data.start!, 'yyyy-MM-dd'),
            horario: format(data.start!, 'HH:mm'),
            ent_id: data.ent_id ? Number(data.ent_id) : null,
            local: data.local || null,
            telefone_noivo: data.telefone_noivo,
            telefone_noiva: data.telefone_noiva,
            efeito_civil: data.efeito_civil,
            color: data.color,
            force_save: force
        };

        try {
            if (modalMode === 'create') {
                await axios.post('/api/calendario-matrimonio/reservas', payload);
            } else if (modalMode === 'edit' && data.id) {
                await axios.put(`/api/calendario-matrimonio/reservas/${data.id}`, payload);
            }
            
            await fetchEvents();
            setIsEventModalOpen(false); // Close the event modal on success
            setConfirmModal(prev => ({ ...prev, isOpen: false }));
        } catch (error: any) {
            console.error("Erro detalhado ao salvar evento:", error);
            if (error.response) {
                console.error("Dados do erro:", error.response.data);
                throw error;
            } else {
                throw new Error("Erro de conexão com o servidor.");
            }
        }
    };

    const handleSaveEvent = async (data: Partial<ReservaMatrimonio>) => {
        if (!data.start || !data.title || (!data.local && !data.ent_id)) {
            throw new Error("Preencha todos os campos obrigatórios.");
        }

        // Validação de Regras
        const validationError = validateEvent(data);
        if (validationError) {
            setConfirmModal({
                isOpen: true,
                title: 'Atenção às Regras',
                message: validationError,
                variant: 'warning',
                onConfirm: () => processSaveEvent(data, true)
            });
            return;
        }

        await processSaveEvent(data);
    };

    const processDeleteEvent = async (id: number) => {
        try {
            await axios.delete(`/api/calendario-matrimonio/reservas/${id}`);
            await fetchEvents();
            setIsEventModalOpen(false);
            setConfirmModal(prev => ({ ...prev, isOpen: false }));
        } catch (error) {
            console.error("Erro ao excluir reserva:", error);
            throw error;
        }
    };

    const handleDeleteEvent = async (id: number) => {
        setConfirmModal({
            isOpen: true,
            title: 'Excluir Reserva',
            message: 'Tem certeza que deseja excluir esta reserva? Esta ação não pode ser desfeita.',
            variant: 'danger',
            confirmText: 'Excluir',
            onConfirm: () => processDeleteEvent(id)
        });
    };
    
    const handleSaveRules = async (updatedRules: RegraMatrimonio[]) => {
        try {
            await axios.post('/api/calendario-matrimonio/regras', { regras: updatedRules });
            setRules(updatedRules);
        } catch (error) {
            console.error("Erro ao salvar regras:", error);
            throw error;
        }
    };

    const CustomEvent = useCallback(({ event }: { event: ReservaMatrimonio }) => {
        // Formatar horário com segurança
        const timeStr = event.start && !isNaN(event.start.getTime()) 
            ? format(event.start, 'HH:mm') 
            : '--:--';

        // Cor de fundo garantida
        const bgColor = event.color || '#3788d8';

        return (
            <div 
                className="d-flex flex-column justify-content-start p-2 rounded h-100 w-100" 
                title={`${event.title} - ${timeStr}`}
                style={{ 
                    backgroundColor: bgColor, 
                    color: '#fff',
                    boxShadow: '0 1px 2px rgba(0,0,0,0.2)'
                }}
            >
                <div className="fw-bold text-truncate" style={{ lineHeight: '1.2', fontSize: '0.9em' }}>{event.title}</div>
                <div className="d-flex align-items-center gap-1 mt-1" style={{ opacity: 0.9 }}>
                    <i className="bi bi-clock-fill" style={{ fontSize: '0.8em' }}></i>
                    <span style={{ fontSize: '0.85em' }}>{timeStr}</span>
                </div>
                {event.local_nome && (
                    <div className="d-flex align-items-center gap-1 text-truncate mt-1" style={{ opacity: 0.9 }}>
                        <i className="bi bi-geo-alt-fill" style={{ fontSize: '0.8em' }}></i>
                        <span style={{ fontSize: '0.85em' }}>{event.local_nome}</span>
                    </div>
                )}
            </div>
        );
    }, []);

    const eventStyleGetter = useCallback((event: ReservaMatrimonio) => {
        return {
            style: {
                backgroundColor: 'transparent', // Deixa o CustomEvent controlar a cor
                borderRadius: '6px',
                opacity: 1,
                color: 'transparent', // Texto padrão transparente
                border: '0px',
                display: 'block',
                overflow: 'visible',
                padding: '0px', // Sem padding para o CustomEvent preencher tudo
                minHeight: '40px', 
                boxShadow: 'none'
            }
        };
    }, []);

    const components = React.useMemo(() => ({
        event: CustomEvent,
        agenda: {
            event: CustomEvent
        },
        toolbar: CustomToolbar
    }), [CustomEvent]);

    return (
        <div className="d-flex h-100 bg-white rounded-4 overflow-hidden shadow-sm" style={{ minHeight: '800px' }}>
            <style>{`
                .hover-bg-light:hover { background-color: #f8f9fa; }
                .custom-scrollbar::-webkit-scrollbar { width: 6px; }
                .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
                .custom-scrollbar::-webkit-scrollbar-thumb { background-color: rgba(0,0,0,0.1); border-radius: 3px; }
            `}</style>
            {/* Sidebar */}
            <div className="d-flex flex-column p-4 border-end bg-white" style={{ width: '300px', minWidth: '300px' }}>
                <button
                    className="btn btn-primary d-flex align-items-center justify-content-center gap-2 rounded-pill py-2 mb-4 fw-bold shadow-sm w-100"
                    onClick={() => {
                        const now = new Date();
                        now.setMinutes(0, 0, 0);
                        now.setHours(now.getHours() + 1);
                        handleSelectSlot({ start: now });
                    }}
                >
                    <i className="bi bi-plus-lg"></i>
                    Nova Reserva
                </button>

                <MiniCalendar currentDate={date} onDateSelect={setDate} />

                <div className="mb-3">
                    <div className="input-group">
                        <span className="input-group-text bg-light border-end-0 border ps-3 rounded-start-pill">
                            <i className="bi bi-search text-muted"></i>
                        </span>
                        <input 
                            type="text" 
                            className="form-control bg-light border-start-0 border rounded-end-pill" 
                            placeholder="Buscar evento..." 
                            value={searchTerm}
                            onChange={e => setSearchTerm(e.target.value)}
                            style={{ fontSize: '0.9rem' }}
                        />
                    </div>
                </div>

                <div className="flex-grow-1 overflow-auto pe-1 custom-scrollbar">
                    <small className="text-muted fw-bold text-uppercase mb-3 d-block" style={{ fontSize: '0.75rem', letterSpacing: '0.5px' }}>
                        {searchTerm ? 'Resultados' : 'Eventos do Mês'}
                    </small>
                    
                    {filteredEvents.length === 0 ? (
                        <div className="text-center text-muted py-4 small">
                            Nenhum evento encontrado.
                        </div>
                    ) : (
                        <div className="d-flex flex-column gap-2">
                            {filteredEvents.map(evt => (
                                <div 
                                    key={evt.id} 
                                    className="d-flex align-items-center gap-2 p-2 rounded hover-bg-light cursor-pointer transition-all" 
                                    onClick={() => {
                                        setDate(evt.start);
                                        setView(Views.DAY);
                                    }}
                                    style={{ cursor: 'pointer' }}
                                    title={evt.title}
                                >
                                    <div 
                                        className="rounded-circle flex-shrink-0" 
                                        style={{ width: '8px', height: '8px', backgroundColor: evt.color || '#3788d8' }}
                                    ></div>
                                    <div className="flex-grow-1 overflow-hidden">
                                        <div className="text-truncate fw-medium text-dark" style={{ fontSize: '0.85rem' }}>{evt.title}</div>
                                        <div className="d-flex align-items-center gap-2 text-muted" style={{ fontSize: '0.75rem' }}>
                                            <span>{format(evt.start, 'dd/MM', { locale: ptBR })}</span>
                                            <span>•</span>
                                            <span>{format(evt.start, 'HH:mm')}</span>
                                        </div>
                                    </div>
                                </div>
                            ))}
                        </div>
                    )}
                </div>

                <div className="mt-3 pt-3 border-top">
                    <button 
                        className="btn btn-outline-secondary w-100 rounded-pill btn-sm fw-medium"
                        onClick={() => setIsRulesModalOpen(true)}
                    >
                        <i className="bi bi-gear-fill me-2"></i> Configurar Regras
                    </button>
                </div>
            </div>

            {/* Main Content */}
            <div className="flex-grow-1 position-relative d-flex flex-column">
                {loading && (
                    <div className="position-absolute top-0 start-0 w-100 h-100 bg-white bg-opacity-75 d-flex align-items-center justify-content-center" style={{ zIndex: 10 }}>
                        <div className="spinner-border text-primary" role="status"></div>
                    </div>
                )}
                <div className="flex-grow-1 p-4">
                    <Calendar
                        localizer={localizer}
                        events={events}
                        startAccessor="start"
                        endAccessor="end"
                        style={{ height: '100%' }}
                        views={['month', 'week', 'day', 'agenda']}
                        view={view}
                        onView={setView}
                        date={date}
                        onNavigate={setDate}
                        selectable
                        onSelectSlot={handleSelectSlot}
                        onSelectEvent={handleSelectEvent}
                        eventPropGetter={eventStyleGetter}
                        components={components}
                        culture='pt-BR'
                        messages={{
                            next: "Próximo",
                            previous: "Anterior",
                            today: "Hoje",
                            month: "Mês",
                            week: "Semana",
                            day: "Dia",
                            agenda: "Agenda",
                            date: "Data",
                            time: "Hora",
                            event: "Evento",
                            noEventsInRange: "Não há eventos neste período."
                        }}
                    />
                </div>
            </div>

            <RulesModal
                isOpen={isRulesModalOpen}
                onClose={() => setIsRulesModalOpen(false)}
                rules={rules}
                locais={locais}
                onSave={handleSaveRules}
            />

            <EventModalMatrimonio
                isOpen={isEventModalOpen}
                onClose={() => setIsEventModalOpen(false)}
                onSave={handleSaveEvent}
                onDelete={selectedEvent?.id ? handleDeleteEvent : undefined}
                selectedEvent={selectedEvent}
                locais={locais}
                rules={rules}
                mode={modalMode}
            />

            <ConfirmationModal
                isOpen={confirmModal.isOpen}
                onClose={() => setConfirmModal(prev => ({ ...prev, isOpen: false }))}
                onConfirm={confirmModal.onConfirm}
                title={confirmModal.title}
                message={confirmModal.message}
                variant={confirmModal.variant}
                confirmText={confirmModal.confirmText}
                cancelText={confirmModal.cancelText}
            />
        </div>
    );
};

export default CalendarMatrimonioApp;
