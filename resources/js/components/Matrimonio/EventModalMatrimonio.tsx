import React, { useState, useEffect } from 'react';
import { ReservaMatrimonio, Local, RegraMatrimonio } from './types';

interface EventModalProps {
    isOpen: boolean;
    onClose: () => void;
    onSave: (data: Partial<ReservaMatrimonio>) => Promise<void>;
    onDelete?: (id: number) => Promise<void>;
    selectedEvent: Partial<ReservaMatrimonio> | null;
    locais: Local[];
    rules: RegraMatrimonio[];
    mode: 'create' | 'edit';
}

const EventModalMatrimonio: React.FC<EventModalProps> = ({
    isOpen,
    onClose,
    onSave,
    onDelete,
    selectedEvent,
    locais,
    rules,
    mode
}) => {
    const [formData, setFormData] = useState<Partial<ReservaMatrimonio>>({});
    const [saving, setSaving] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const [locationType, setLocationType] = useState<'community' | 'custom'>('community');

    useEffect(() => {
        if (isOpen && selectedEvent) {
            setFormData({
                ...selectedEvent,
                color: selectedEvent.color || '#3788d8',
                efeito_civil: selectedEvent.efeito_civil || false,
                ent_id: selectedEvent.ent_id,
                local: selectedEvent.local
            });
            
            if (selectedEvent.ent_id) {
                setLocationType('community');
            } else if (selectedEvent.local) {
                setLocationType('custom');
            } else {
                setLocationType('community');
            }
            
            setError(null);
        }
    }, [isOpen, selectedEvent]);

    const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement>) => {
        const { name, value, type } = e.target;
        
        if (type === 'checkbox') {
            const checked = (e.target as HTMLInputElement).checked;
            setFormData(prev => ({ ...prev, [name]: checked }));
        } else {
            setFormData(prev => ({ ...prev, [name]: value }));
        }
    };

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setSaving(true);
        setError(null);

        // Preparar dados finais baseados no tipo de local
        const dataToSave = { ...formData };
        if (locationType === 'community') {
            dataToSave.local = undefined; // Limpa o texto customizado
            // ent_id já está em dataToSave
            if (!dataToSave.ent_id) {
                setError('Selecione uma comunidade.');
                setSaving(false);
                return;
            }
        } else {
            dataToSave.ent_id = undefined; // Limpa o ID da comunidade
            // local já está em dataToSave
            if (!dataToSave.local) {
                setError('Informe o local do casamento.');
                setSaving(false);
                return;
            }
        }

        // Validação de Regras removida daqui e movida para o CalendarMatrimonioApp para permitir confirmação

        try {
            await onSave(dataToSave);
        } catch (err: any) {
            console.error(err);
            setError(err.response?.data?.message || 'Erro ao salvar reserva. Verifique os dados e tente novamente.');
        } finally {
            setSaving(false);
        }
    };

    const handleDeleteClick = async () => {
        if (!formData.id || !onDelete) return;
        
        // Confirmação delegada para o componente pai
        try {
            await onDelete(formData.id);
        } catch (err: any) {
            setError('Erro ao excluir reserva.');
        }
    };

    if (!isOpen) return null;

    return (
        <div className="modal fade show d-block" style={{ backgroundColor: 'rgba(0,0,0,0.5)' }} tabIndex={-1}>
            <div className="modal-dialog modal-lg modal-dialog-centered">
                <div className="modal-content rounded-4 border-0 shadow">
                    <div className="modal-header border-bottom-0 pb-0 pt-4 px-4">
                        <h5 className="modal-title fw-bold text-dark">
                            {mode === 'create' ? 'Nova Reserva' : 'Detalhes da Reserva'}
                        </h5>
                        <button type="button" className="btn-close" onClick={onClose}></button>
                    </div>
                    <form onSubmit={handleSubmit}>
                        <div className="modal-body p-4">
                            {error && (
                                <div className="alert alert-danger rounded-3 mb-3 d-flex align-items-center">
                                    <i className="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                                    <div>{error}</div>
                                </div>
                            )}

                            <div className="mb-4">
                                <label className="form-label fw-bold small text-muted text-uppercase">Informações do Casamento</label>
                                <div className="mb-3">
                                    <label className="form-label fw-medium">Título</label>
                                    <input
                                        type="text"
                                        className="form-control rounded-3 py-2"
                                        name="title"
                                        value={formData.title || ''}
                                        onChange={handleChange}
                                        placeholder="Ex: Casamento de Maria e João"
                                        required
                                    />
                                </div>

                                <div className="row g-3">
                                    <div className="col-md-6">
                                        <label className="form-label fw-medium">Data</label>
                                        <div className="input-group">
                                            <span className="input-group-text bg-light border-end-0 rounded-start-3"><i className="bi bi-calendar-event"></i></span>
                                            <input
                                                type="date"
                                                className="form-control border-start-0 rounded-end-3"
                                                name="start"
                                                value={(() => {
                                                    if (!formData.start) return '';
                                                    const date = new Date(formData.start);
                                                    return !isNaN(date.getTime()) ? date.toISOString().split('T')[0] : '';
                                                })()}
                                                onChange={(e) => {
                                                    if (!e.target.value) return;
                                                    const [year, month, day] = e.target.value.split('-').map(Number);
                                                    const currentStart = formData.start ? new Date(formData.start) : new Date();
                                                    const hours = !isNaN(currentStart.getTime()) ? currentStart.getHours() : 0;
                                                    const minutes = !isNaN(currentStart.getTime()) ? currentStart.getMinutes() : 0;
                                                    const newDate = new Date(year, month - 1, day, hours, minutes);
                                                    setFormData(prev => ({ ...prev, start: newDate }));
                                                }}
                                                required
                                            />
                                        </div>
                                    </div>
                                    <div className="col-md-6">
                                        <label className="form-label fw-medium">Horário</label>
                                        <div className="input-group">
                                            <span className="input-group-text bg-light border-end-0 rounded-start-3"><i className="bi bi-clock"></i></span>
                                            <input
                                                type="time"
                                                className="form-control border-start-0 rounded-end-3"
                                                name="horario"
                                                value={(() => {
                                                    if (!formData.start) return '';
                                                    const date = new Date(formData.start);
                                                    return !isNaN(date.getTime()) ? date.toTimeString().substring(0, 5) : '';
                                                })()}
                                                onChange={(e) => {
                                                    if (!e.target.value) return;
                                                    const [hours, minutes] = e.target.value.split(':').map(Number);
                                                    const newStart = formData.start ? new Date(formData.start) : new Date();
                                                    if (isNaN(newStart.getTime())) {
                                                        const now = new Date();
                                                        newStart.setFullYear(now.getFullYear(), now.getMonth(), now.getDate());
                                                    }
                                                    newStart.setHours(hours, minutes);
                                                    setFormData(prev => ({ ...prev, start: newStart }));
                                                }}
                                                required
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div className="mb-4">
                                <label className="form-label fw-bold small text-muted text-uppercase">Localização</label>
                                <div className="card border-light bg-light rounded-3 p-3">
                                    <div className="d-flex gap-4 mb-3">
                                        <div className="form-check">
                                            <input
                                                className="form-check-input"
                                                type="radio"
                                                name="locationType"
                                                id="locTypeCommunity"
                                                value="community"
                                                checked={locationType === 'community'}
                                                onChange={() => setLocationType('community')}
                                            />
                                            <label className="form-check-label fw-medium" htmlFor="locTypeCommunity">Na Comunidade</label>
                                        </div>
                                        <div className="form-check">
                                            <input
                                                className="form-check-input"
                                                type="radio"
                                                name="locationType"
                                                id="locTypeCustom"
                                                value="custom"
                                                checked={locationType === 'custom'}
                                                onChange={() => setLocationType('custom')}
                                            />
                                            <label className="form-check-label fw-medium" htmlFor="locTypeCustom">Outro Local</label>
                                        </div>
                                    </div>

                                    {locationType === 'community' ? (
                                        <select
                                            className="form-select rounded-3"
                                            name="ent_id"
                                            value={formData.ent_id || ''}
                                            onChange={handleChange}
                                            required={locationType === 'community'}
                                        >
                                            <option value="">Selecione a comunidade...</option>
                                            {locais.map(local => (
                                                <option key={local.id} value={local.id}>{local.nome}</option>
                                            ))}
                                        </select>
                                    ) : (
                                        <input
                                            type="text"
                                            className="form-control rounded-3"
                                            name="local"
                                            value={formData.local || ''}
                                            onChange={handleChange}
                                            placeholder="Digite o nome do local..."
                                            required={locationType === 'custom'}
                                        />
                                    )}
                                </div>
                            </div>

                            <div className="mb-4">
                                <label className="form-label fw-bold small text-muted text-uppercase">Contatos</label>
                                <div className="row g-3">
                                    <div className="col-md-6">
                                        <label className="form-label fw-medium">Telefone Noivo</label>
                                        <input
                                            type="text"
                                            className="form-control rounded-3"
                                            name="telefone_noivo"
                                            value={formData.telefone_noivo || ''}
                                            onChange={handleChange}
                                            placeholder="(00) 00000-0000"
                                        />
                                    </div>
                                    <div className="col-md-6">
                                        <label className="form-label fw-medium">Telefone Noiva</label>
                                        <input
                                            type="text"
                                            className="form-control rounded-3"
                                            name="telefone_noiva"
                                            value={formData.telefone_noiva || ''}
                                            onChange={handleChange}
                                            placeholder="(00) 00000-0000"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div className="d-flex justify-content-between align-items-center mb-4">
                                <div className="d-flex align-items-center gap-2">
                                    <label className="form-label fw-medium mb-0 me-2">Cor:</label>
                                    <input
                                        type="color"
                                        className="form-control form-control-color border-0 p-0 rounded-circle"
                                        style={{ width: '32px', height: '32px' }}
                                        name="color"
                                        value={formData.color || '#3788d8'}
                                        onChange={handleChange}
                                        title="Escolha a cor"
                                    />
                                </div>
                                <div className="form-check form-switch">
                                    <input
                                        className="form-check-input"
                                        type="checkbox"
                                        name="efeito_civil"
                                        id="efeito_civil"
                                        checked={formData.efeito_civil || false}
                                        onChange={handleChange}
                                    />
                                    <label className="form-check-label fw-medium" htmlFor="efeito_civil">
                                        Efeito Civil
                                    </label>
                                </div>
                            </div>

                        </div>
                        <div className="modal-footer border-top-0 px-4 pb-4 pt-0">
                            <div className="d-flex w-100 justify-content-between align-items-center">
                                <div>
                                    {mode === 'edit' && onDelete && (
                                        <button
                                            type="button"
                                            className="btn btn-outline-danger border-0 rounded-pill px-3 fw-bold"
                                            onClick={handleDeleteClick}
                                            disabled={saving}
                                        >
                                            <i className="bi bi-trash me-2"></i> Excluir
                                        </button>
                                    )}
                                </div>
                                <div className="d-flex gap-2">
                                    <button
                                        type="button"
                                        className="btn btn-light rounded-pill px-4 fw-bold text-muted"
                                        onClick={onClose}
                                        disabled={saving}
                                    >
                                        Cancelar
                                    </button>
                                    <button
                                        type="submit"
                                        className="btn btn-primary rounded-pill px-4 fw-bold shadow-sm"
                                        disabled={saving}
                                    >
                                        {saving ? (
                                            <>
                                                <span className="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                                Salvando...
                                            </>
                                        ) : (
                                            <>
                                                <i className="bi bi-check-lg me-1"></i> Salvar
                                            </>
                                        )}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    );
};

export default EventModalMatrimonio;