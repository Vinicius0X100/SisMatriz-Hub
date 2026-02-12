import React, { useState, useEffect } from 'react';
import { RegraMatrimonio, Local } from './types';

interface RulesModalProps {
    isOpen: boolean;
    onClose: () => void;
    rules: RegraMatrimonio[];
    locais: Local[];
    onSave: (rules: RegraMatrimonio[]) => Promise<void>;
}

const DIAS_SEMANA = [
    { value: "0", label: "Domingo" },
    { value: "1", label: "Segunda-feira" },
    { value: "2", label: "Terça-feira" },
    { value: "3", label: "Quarta-feira" },
    { value: "4", label: "Quinta-feira" },
    { value: "5", label: "Sexta-feira" },
    { value: "6", label: "Sábado" },
];

const RulesModal: React.FC<RulesModalProps> = ({ isOpen, onClose, rules: initialRules, locais, onSave }) => {
    const [rules, setRules] = useState<RegraMatrimonio[]>([]);
    const [saving, setSaving] = useState(false);

    // Sincronizar estado local com props e garantir que todas as comunidades tenham uma entrada de regra
    useEffect(() => {
        if (isOpen) {
            const mergedRules = locais.map(local => {
                const existingRule = initialRules.find(r => r.comunidade_id === local.id);
                if (existingRule) {
                    return { ...existingRule, nome: local.nome }; // Garantir nome atualizado
                }
                return {
                    comunidade_id: local.id,
                    nome: local.nome,
                    max_casamentos_por_dia: 1, // Default
                    dias_permitidos: [] // Nenhum dia permitido por padrão ou todos? Melhor nenhum pra forçar config.
                };
            });
            setRules(mergedRules);
        }
    }, [isOpen, initialRules, locais]);

    const handleMaxChange = (comunidade_id: number, value: string) => {
        const numValue = parseInt(value);
        if (isNaN(numValue) || numValue < 0) return;
        
        setRules(prev => prev.map(r => 
            r.comunidade_id === comunidade_id ? { ...r, max_casamentos_por_dia: numValue } : r
        ));
    };

    const handleDayToggle = (comunidade_id: number, dayValue: string) => {
        setRules(prev => prev.map(r => {
            if (r.comunidade_id !== comunidade_id) return r;

            const currentDays = r.dias_permitidos || [];
            let newDays;
            if (currentDays.includes(dayValue)) {
                newDays = currentDays.filter(d => d !== dayValue);
            } else {
                newDays = [...currentDays, dayValue];
            }
            return { ...r, dias_permitidos: newDays };
        }));
    };

    const handleSubmit = async () => {
        setSaving(true);
        try {
            await onSave(rules);
            onClose();
        } catch (error) {
            alert("Erro ao salvar regras. Tente novamente.");
        } finally {
            setSaving(false);
        }
    };

    if (!isOpen) return null;

    return (
        <div className="modal fade show d-block" style={{ backgroundColor: 'rgba(0,0,0,0.5)' }} tabIndex={-1}>
            <div className="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                <div className="modal-content rounded-4 border-0 shadow">
                    <div className="modal-header">
                        <h5 className="modal-title fw-bold">Ajustar Regras do Calendário</h5>
                        <button type="button" className="btn-close" onClick={onClose}></button>
                    </div>
                    <div className="modal-body">
                        <div className="alert alert-info rounded-3 mb-4">
                            <i className="bi bi-info-circle-fill me-2"></i>
                            Configure abaixo a quantidade máxima de casamentos por dia e os dias permitidos para cada comunidade.
                        </div>

                        <div className="table-responsive">
                            <table className="table table-hover align-middle">
                                <thead className="table-light">
                                    <tr>
                                        <th style={{ width: '25%' }}>Comunidade</th>
                                        <th style={{ width: '15%' }}>Max. Casamentos/Dia</th>
                                        <th style={{ width: '60%' }}>Dias Permitidos</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {rules.map(rule => (
                                        <tr key={rule.comunidade_id}>
                                            <td className="fw-medium">{rule.nome}</td>
                                            <td>
                                                <input
                                                    type="number"
                                                    className="form-control"
                                                    min="0"
                                                    value={rule.max_casamentos_por_dia}
                                                    onChange={(e) => handleMaxChange(rule.comunidade_id, e.target.value)}
                                                />
                                            </td>
                                            <td>
                                                <div className="d-flex flex-wrap gap-2">
                                                    {DIAS_SEMANA.map(day => {
                                                        const isChecked = (rule.dias_permitidos || []).includes(day.value);
                                                        return (
                                                            <div key={day.value} className="form-check form-check-inline m-0">
                                                                <input
                                                                    className="form-check-input"
                                                                    type="checkbox"
                                                                    id={`check-${rule.comunidade_id}-${day.value}`}
                                                                    checked={isChecked}
                                                                    onChange={() => handleDayToggle(rule.comunidade_id, day.value)}
                                                                />
                                                                <label 
                                                                    className="form-check-label small" 
                                                                    htmlFor={`check-${rule.comunidade_id}-${day.value}`}
                                                                    style={{ cursor: 'pointer' }}
                                                                >
                                                                    {day.label.split('-')[0]}
                                                                </label>
                                                            </div>
                                                        );
                                                    })}
                                                </div>
                                            </td>
                                        </tr>
                                    ))}
                                    {rules.length === 0 && (
                                        <tr>
                                            <td colSpan={3} className="text-center text-muted py-4">
                                                Nenhuma comunidade encontrada nesta paróquia.
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div className="modal-footer">
                        <button 
                            type="button" 
                            className="btn btn-light" 
                            onClick={onClose}
                            disabled={saving}
                        >
                            Cancelar
                        </button>
                        <button 
                            type="button" 
                            className="btn btn-primary" 
                            onClick={handleSubmit}
                            disabled={saving}
                        >
                            {saving ? (
                                <>
                                    <span className="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                    Salvando...
                                </>
                            ) : (
                                <>
                                    <i className="bi bi-check-lg me-1"></i> Salvar Regras
                                </>
                            )}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default RulesModal;
