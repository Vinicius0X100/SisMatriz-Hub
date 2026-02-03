import React, { useState, useEffect, useCallback } from 'react';
import { Calendar, dateFnsLocalizer, Views, View, Components } from 'react-big-calendar';
import withDragAndDrop from 'react-big-calendar/lib/addons/dragAndDrop';
import { format, parse, startOfWeek, getDay, addMonths, subMonths } from 'date-fns';
import { ptBR } from 'date-fns/locale';
import 'react-big-calendar/lib/css/react-big-calendar.css';
import 'react-big-calendar/lib/addons/dragAndDrop/styles.css';
import axios from 'axios';
import EventModal from './EventModal';
import CustomToolbar from './CustomToolbar';
import { Reserva, Local, Holiday } from './types';

// Configuração do Localizer (Date-fns)
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

const DnDCalendar = withDragAndDrop(Calendar);

const CalendarApp = () => {
    // Estados Principais
    const [events, setEvents] = useState<Reserva[]>([]);
    const [locais, setLocais] = useState<Local[]>([]);
    const [view, setView] = useState<View>(Views.MONTH);
    const [date, setDate] = useState(new Date());
    const [loading, setLoading] = useState(false);
    
    // Estados do Modal
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [selectedEvent, setSelectedEvent] = useState<Partial<Reserva> | null>(null);
    const [modalMode, setModalMode] = useState<'create' | 'edit'>('create');

    // Filtros
    const [filters, setFilters] = useState({
        local_id: '',
    });

    // Carregar Locais (Apenas uma vez)
    useEffect(() => {
        const fetchLocais = async () => {
            try {
                const response = await axios.get('/api/reservas-calendar/locais');
                setLocais(response.data);
            } catch (error) {
                console.error("Erro ao carregar locais:", error);
            }
        };
        fetchLocais();
    }, []);

    // Carregar Eventos
    const fetchEvents = useCallback(async () => {
        setLoading(true);
        try {
            // Calcular range baseado na view atual (simplificado para mês: mês anterior até próximo mês)
            // Para garantir que pegamos tudo, pegamos -1 mês e +1 mês da data atual
            const start = format(subMonths(date, 1), 'yyyy-MM-dd');
            const end = format(addMonths(date, 2), 'yyyy-MM-dd'); // +2 para garantir fim do mês seguinte

            const response = await axios.get('/api/reservas-calendar', {
                params: {
                    start,
                    end,
                    local_id: filters.local_id
                }
            });

            if (response.data && response.data.events) {
                const mappedEvents = response.data.events.map((evt: any) => ({
                    id: evt.id,
                    title: evt.descricao,
                    start: new Date(`${evt.data}T${evt.hora_inicio}`),
                    end: new Date(`${evt.data}T${evt.hora_fim}`),
                    description: evt.descricao,
                    responsavel: evt.responsavel,
                    observacoes: evt.observacoes,
                    color: evt.color,
                    local: evt.localModel || evt.local,
                    paroquia_id: evt.paroquia_id,
                }));

                const holidayEvents = (response.data.holidays || []).map((h: any) => ({
                    id: `holiday-${h.date}`,
                    title: h.title,
                    start: new Date(`${h.date}T00:00:00`),
                    end: new Date(`${h.date}T23:59:59`),
                    allDay: true,
                    color: h.type === 'feriado' ? '#ef4444' : (h.type === 'catolico' ? '#a855f7' : '#22c55e'),
                    isHoliday: true
                }));

                setEvents([...mappedEvents, ...holidayEvents]);
            }
        } catch (error) {
            console.error("Erro ao carregar eventos:", error);
        } finally {
            setLoading(false);
        }
    }, [date, filters.local_id]);

    useEffect(() => {
        fetchEvents();
    }, [fetchEvents]);

    // Handlers
    const handleSelectSlot = ({ start, end }: { start: Date; end: Date }) => {
        const today = new Date();
        today.setHours(0, 0, 0, 0);

        if (start < today) {
            return;
        }

        setSelectedEvent({
            start,
            end,
            title: '',
            color: '#0d6efd'
        });
        setModalMode('create');
        setIsModalOpen(true);
    };

    const handleSelectEvent = (event: any) => {
        if (event.isHoliday) return;
        setSelectedEvent(event);
        setModalMode('edit');
        setIsModalOpen(true);
    };

    const handleSave = async (formData: Partial<Reserva>) => {
        try {
            const payload = {
                data: format(formData.start!, 'yyyy-MM-dd'),
                hora_inicio: format(formData.start!, 'HH:mm'),
                hora_fim: format(formData.end!, 'HH:mm'),
                descricao: formData.title,
                local: typeof formData.local === 'object' ? formData.local.id : formData.local,
                responsavel: formData.responsavel,
                observacoes: formData.observacoes,
                color: formData.color
            };

            if (modalMode === 'edit' && selectedEvent?.id) {
                await axios.put(`/api/reservas-calendar/${selectedEvent.id}`, payload);
            } else {
                await axios.post('/api/reservas-calendar', payload);
            }

            setIsModalOpen(false);
            fetchEvents(); // Recarregar
        } catch (error) {
            console.error("Erro ao salvar:", error);
            alert("Erro ao salvar reserva. Verifique os dados.");
        }
    };

    const handleDelete = async (id: number) => {
        if (!confirm("Tem certeza que deseja excluir esta reserva?")) return;
        try {
            await axios.delete(`/api/reservas-calendar/${id}`);
            setIsModalOpen(false);
            fetchEvents();
        } catch (error) {
            console.error("Erro ao excluir:", error);
            alert("Erro ao excluir reserva.");
        }
    };

    const handleEventDrop = async ({ event, start, end }: any) => {
        if (event.isHoliday) return;

        const today = new Date();
        today.setHours(0, 0, 0, 0);

        if (start < today) {
            alert("Não é possível mover eventos para datas passadas.");
            return;
        }

        try {
            // Optimistic update
            const updatedEvents = events.map(evt => {
                if (evt.id === event.id) {
                    return { ...evt, start, end };
                }
                return evt;
            });
            setEvents(updatedEvents);

            const payload = {
                data: format(start, 'yyyy-MM-dd'),
                hora_inicio: format(start, 'HH:mm'),
                hora_fim: format(end, 'HH:mm'),
                descricao: event.description || event.title,
                local: event.local?.id || event.local,
                responsavel: event.responsavel,
                observacoes: event.observacoes,
                color: event.color
            };

            await axios.put(`/api/reservas-calendar/${event.id}`, payload);
        } catch (error) {
            console.error("Erro ao mover evento:", error);
            fetchEvents(); // Revert on error
            alert("Erro ao mover evento. Tente novamente.");
        }
    };

    const dayPropGetter = (date: Date) => {
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        if (date < today) {
            return {
                style: {
                    backgroundColor: '#f3f4f6',
                    cursor: 'not-allowed',
                    opacity: 0.6
                }
            };
        }
        return {};
    };

    const slotPropGetter = (date: Date) => {
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        if (date < today) {
            return {
                style: {
                    backgroundColor: '#f3f4f6',
                    cursor: 'not-allowed',
                    opacity: 0.6
                }
            };
        }
        return {};
    };

    // Estilos customizados para eventos
    const eventStyleGetter = (event: any) => {
        // Cores padrão do Google Calendar
        const backgroundColor = event.color || '#3788d8';
        
        return {
            style: {
                backgroundColor: 'transparent', // Vamos controlar o fundo no componente customizado
                border: 'none',
                display: 'block',
                padding: 0,
            }
        };
    };

    // Componente customizado para evento (Mês/Semana)
    const CustomEvent = ({ event }: any) => {
        return (
            <div 
                className="d-flex align-items-center px-2 py-1 h-100 shadow-sm"
                style={{
                    backgroundColor: event.color || '#3788d8',
                    borderRadius: '4px',
                    borderLeft: '3px solid rgba(0,0,0,0.2)',
                    fontSize: '0.85rem',
                    color: '#fff',
                    overflow: 'hidden'
                }}
            >
                <span className="fw-bold me-2 small" style={{ minWidth: '35px', opacity: 0.9 }}>
                    {format(event.start, 'HH:mm')}
                </span>
                <span className="text-truncate fw-medium">{event.title}</span>
            </div>
        );
    };

    return (
        <div className="d-flex flex-column flex-lg-row h-100 bg-white rounded-4 overflow-hidden" style={{ minHeight: '750px' }}>
            {/* Sidebar de Filtros */}
            <div className="p-4 border-end bg-white" style={{ width: '100%', maxWidth: '280px' }}>
                <div className="mb-4">
                    <button 
                        className="btn btn-primary rounded-pill w-100 shadow-sm py-2 fw-bold d-flex align-items-center justify-content-center gap-2"
                        onClick={() => {
                            setSelectedEvent({
                                start: new Date(),
                                end: new Date(),
                                title: '',
                                color: '#0d6efd'
                            });
                            setModalMode('create');
                            setIsModalOpen(true);
                        }}
                    >
                        <i className="bi bi-plus-lg fs-5"></i>
                        <span>Criar Reserva</span>
                    </button>
                </div>

                <div className="mb-4">
                    <label className="form-label small fw-bold text-uppercase text-muted mb-3" style={{ fontSize: '0.75rem', letterSpacing: '0.5px' }}>
                        Filtrar por Local
                    </label>
                    <div className="input-group">
                        <span className="input-group-text bg-light border-end-0 rounded-start-pill ps-3">
                            <i className="bi bi-geo-alt text-muted"></i>
                        </span>
                        <select 
                            className="form-select border-start-0 bg-light rounded-end-pill text-muted fw-medium"
                            value={filters.local_id}
                            onChange={(e) => setFilters({...filters, local_id: e.target.value})}
                            style={{ fontSize: '0.9rem' }}
                        >
                            <option value="">Todos os Locais</option>
                            {locais.map(local => (
                                <option key={local.id} value={local.id}>{local.name}</option>
                            ))}
                        </select>
                    </div>
                </div>

                <div className="mt-5">
                     <label className="form-label small fw-bold text-uppercase text-muted mb-3" style={{ fontSize: '0.75rem', letterSpacing: '0.5px' }}>
                        Minhas Agendas
                    </label>
                    <div className="d-flex flex-column gap-3 ps-1">
                        <div className="d-flex align-items-center gap-3">
                            <div className="rounded-circle" style={{ width: '12px', height: '12px', backgroundColor: '#dc3545' }}></div>
                            <span className="text-secondary fw-medium" style={{ fontSize: '0.9rem' }}>Feriados</span>
                        </div>
                        <div className="d-flex align-items-center gap-3">
                            <div className="rounded-circle" style={{ width: '12px', height: '12px', backgroundColor: '#0d6efd' }}></div>
                            <span className="text-secondary fw-medium" style={{ fontSize: '0.9rem' }}>Reservas</span>
                        </div>
                        <div className="d-flex align-items-center gap-3">
                            <div className="rounded-circle" style={{ width: '12px', height: '12px', backgroundColor: '#a855f7' }}></div>
                            <span className="text-secondary fw-medium" style={{ fontSize: '0.9rem' }}>Liturgia</span>
                        </div>
                    </div>
                </div>
            </div>

            {/* Área do Calendário */}
            <div className="flex-grow-1 p-4">
                <DnDCalendar
                    localizer={localizer}
                    events={events}
                    style={{ height: 750 }}
                    views={['month', 'week', 'day', 'agenda']}
                    view={view}
                    onView={setView}
                    date={date}
                    onNavigate={setDate}
                    selectable
                    onSelectSlot={handleSelectSlot}
                    onSelectEvent={handleSelectEvent}
                    onEventDrop={handleEventDrop}
                    dayPropGetter={dayPropGetter}
                    slotPropGetter={slotPropGetter}
                    eventPropGetter={eventStyleGetter}
                    resizable={false}
                    components={{
                        toolbar: CustomToolbar,
                        event: CustomEvent
                    }}
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
                        noEventsInRange: "Não há eventos."
                    }}
                    culture='pt-BR'
                />
            </div>

            {/* Modal */}
            <EventModal
                isOpen={isModalOpen}
                onClose={() => setIsModalOpen(false)}
                onSave={handleSave}
                onDelete={handleDelete}
                selectedEvent={selectedEvent}
                locais={locais}
                mode={modalMode}
            />
        </div>
    );
};

export default CalendarApp;
