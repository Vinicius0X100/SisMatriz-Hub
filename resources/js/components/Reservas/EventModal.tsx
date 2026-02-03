import React, { useEffect, useState } from 'react';
import { format } from 'date-fns';
import { Reserva, Local } from './types';

interface EventModalProps {
    isOpen: boolean;
    onClose: () => void;
    onSave: (event: Partial<Reserva>) => void;
    onDelete: (id: number) => void;
    selectedEvent: Partial<Reserva> | null;
    locais: Local[];
    mode: 'create' | 'edit';
}

export default function EventModal({
    isOpen,
    onClose,
    onSave,
    onDelete,
    selectedEvent,
    locais,
    mode
}: EventModalProps) {
    const [formData, setFormData] = useState<Partial<Reserva>>({
        title: '',
        start: new Date(),
        end: new Date(),
        local: undefined,
        responsavel: '',
        observacoes: '',
        color: '#0d6efd'
    });
    
    // Estados independentes para inputs de data/hora
    const [dateStr, setDateStr] = useState('');
    const [startTimeStr, setStartTimeStr] = useState('');
    const [endTimeStr, setEndTimeStr] = useState('');

    useEffect(() => {
        if (isOpen && selectedEvent) {
            const start = selectedEvent.start || new Date();
            const end = selectedEvent.end || new Date();

            setFormData({
                ...selectedEvent,
                start,
                end
            });

            // Inicializar strings dos inputs
            setDateStr(format(start, 'yyyy-MM-dd'));
            setStartTimeStr(format(start, 'HH:mm'));
            setEndTimeStr(format(end, 'HH:mm'));
        }
    }, [isOpen, selectedEvent]);

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        
        // Reconstruir datas completas
        const start = new Date(`${dateStr}T${startTimeStr}`);
        const end = new Date(`${dateStr}T${endTimeStr}`);

        if (end <= start) {
            alert("A hora final deve ser maior que a inicial.");
            return;
        }

        onSave({
            ...formData,
            start,
            end
        });
    };

    if (!isOpen) return null;

    return (
        <>
            <div className="modal-backdrop fade show"></div>
            <div className="modal fade show d-block" tabIndex={-1} role="dialog">
                <div className="modal-dialog modal-dialog-centered" role="document">
                    <div className="modal-content shadow rounded-4 border-0">
                        <div className="modal-header border-bottom-0 pb-0">
                            <h5 className="modal-title fw-bold">
                                {mode === 'edit' ? 'Editar Reserva' : 'Nova Reserva'}
                            </h5>
                            <button type="button" className="btn-close" onClick={onClose} aria-label="Close"></button>
                        </div>
                        <form onSubmit={handleSubmit}>
                            <div className="modal-body pt-4">
                                <div className="mb-4">
                                    <label className="form-label small fw-bold text-uppercase text-muted">Título do Evento</label>
                                    <input
                                        type="text"
                                        className="form-control form-control-lg bg-light border-0"
                                        value={formData.title}
                                        onChange={e => setFormData({ ...formData, title: e.target.value })}
                                        placeholder="Ex: Reunião de Pastoral"
                                        required
                                    />
                                </div>

                                <div className="row g-3 mb-4">
                                    <div className="col-md-6">
                                        <label className="form-label small fw-bold text-uppercase text-muted">Data</label>
                                        <div className="input-group">
                                            <span className="input-group-text bg-light border-end-0">
                                                <i className="bi bi-calendar-event"></i>
                                            </span>
                                            <input
                                                type="date"
                                                className="form-control border-start-0"
                                                value={dateStr}
                                                onChange={e => setDateStr(e.target.value)}
                                                required
                                            />
                                        </div>
                                    </div>
                                    <div className="col-md-3">
                                        <label className="form-label small fw-bold text-uppercase text-muted">Início</label>
                                        <input
                                            type="time"
                                            className="form-control"
                                            value={startTimeStr}
                                            onChange={e => setStartTimeStr(e.target.value)}
                                            required
                                        />
                                    </div>
                                    <div className="col-md-3">
                                        <label className="form-label small fw-bold text-uppercase text-muted">Fim</label>
                                        <input
                                            type="time"
                                            className="form-control"
                                            value={endTimeStr}
                                            onChange={e => setEndTimeStr(e.target.value)}
                                            required
                                        />
                                    </div>
                                </div>

                                <div className="mb-4">
                                    <label className="form-label small fw-bold text-uppercase text-muted">Local</label>
                                    <div className="input-group">
                                        <span className="input-group-text bg-light border-end-0">
                                            <i className="bi bi-geo-alt"></i>
                                        </span>
                                        <select
                                            className="form-select border-start-0"
                                            value={(typeof formData.local === 'object' ? formData.local.id : formData.local) || ''}
                                            onChange={e => {
                                                const local = locais.find(l => l.id === Number(e.target.value));
                                                setFormData({ ...formData, local });
                                            }}
                                        >
                                            <option value="">Selecione um local...</option>
                                            {locais.map(local => (
                                                <option key={local.id} value={local.id}>{local.name}</option>
                                            ))}
                                        </select>
                                    </div>
                                </div>

                                <div className="mb-4">
                                    <label className="form-label small fw-bold text-uppercase text-muted">Responsável</label>
                                    <input
                                        type="text"
                                        className="form-control"
                                        value={formData.responsavel || ''}
                                        onChange={e => setFormData({ ...formData, responsavel: e.target.value })}
                                        placeholder="Nome do responsável"
                                    />
                                </div>

                                <div className="mb-4">
                                    <label className="form-label small fw-bold text-uppercase text-muted">Cor</label>
                                    <div className="d-flex gap-2">
                                        {['#0d6efd', '#198754', '#dc3545', '#ffc107', '#0dcaf0', '#6610f2'].map(color => (
                                            <button
                                                key={color}
                                                type="button"
                                                onClick={() => setFormData({ ...formData, color })}
                                                style={{
                                                    backgroundColor: color,
                                                    width: '32px',
                                                    height: '32px',
                                                }}
                                                className={`btn rounded-circle p-0 border-2 ${formData.color === color ? 'border-dark' : 'border-white shadow-sm'}`}
                                            />
                                        ))}
                                    </div>
                                </div>

                                <div className="mb-3">
                                    <label className="form-label small fw-bold text-uppercase text-muted">Observações</label>
                                    <textarea
                                        className="form-control"
                                        rows={3}
                                        value={formData.observacoes || ''}
                                        onChange={e => setFormData({ ...formData, observacoes: e.target.value })}
                                    ></textarea>
                                </div>
                            </div>
                            <div className="modal-footer border-top-0 pt-0 pb-4 px-4">
                                {mode === 'edit' && selectedEvent?.id && (
                                    <button
                                        type="button"
                                        className="btn btn-outline-danger rounded-pill px-4 me-auto"
                                        onClick={() => onDelete(selectedEvent.id!)}
                                    >
                                        <i className="bi bi-trash me-2"></i> Excluir
                                    </button>
                                )}
                                <button type="button" className="btn btn-light rounded-pill px-4" onClick={onClose}>
                                    Cancelar
                                </button>
                                <button type="submit" className="btn btn-primary rounded-pill px-4 shadow-sm">
                                    <i className="bi bi-check-lg me-2"></i> Salvar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </>
    );
}
