import React, { useState, useEffect } from 'react';
import axios from 'axios';

interface Register {
    id: number;
    name: string;
    address?: string;
    address_number?: string;
}

interface Entidade {
    ent_id: number;
    ent_name: string;
}

interface FormData {
    w_id?: number;
    name: string;
    ent_id: string;
    kind: string; // 0 or 1
    month_entire: string; // 1-12
    address: string;
    address_number: string;
    description: string;
}

interface VicentinoFormProps {
    onClose: () => void;
    onSuccess: () => void;
    initialData?: FormData | null;
}

const VicentinoForm: React.FC<VicentinoFormProps> = ({ onClose, onSuccess, initialData }) => {
    const [formData, setFormData] = useState<FormData>({
        name: '',
        ent_id: '',
        kind: '',
        month_entire: '',
        address: '',
        address_number: '',
        description: ''
    });

    const [entidades, setEntidades] = useState<Entidade[]>([]);
    const [searchQuery, setSearchQuery] = useState('');
    const [searchResults, setSearchResults] = useState<Register[]>([]);
    const [showResults, setShowResults] = useState(false);
    const [selectedRegister, setSelectedRegister] = useState<Register | null>(null);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        fetchEntidades();
        if (initialData) {
            setFormData(initialData);
            if (initialData.name) {
                // If editing, we might not have the register ID linked directly in the table as per schema
                // so we just fill the name.
                setSearchQuery(initialData.name);
                setSelectedRegister({ id: 0, name: initialData.name }); // Dummy register for UI state
            }
        }
    }, [initialData]);

    const fetchEntidades = async () => {
        try {
            const response = await axios.get('/api/vicentinos/entidades');
            setEntidades(response.data);
        } catch (err) {
            console.error('Error fetching entities:', err);
        }
    };

    const handleSearchChange = async (e: React.ChangeEvent<HTMLInputElement>) => {
        const query = e.target.value;
        setSearchQuery(query);
        setFormData(prev => ({ ...prev, name: query }));

        if (query.length < 3) {
            setSearchResults([]);
            setShowResults(false);
            return;
        }

        try {
            const response = await axios.get(`/api/vicentinos/search-registers?query=${query}`);
            setSearchResults(response.data);
            setShowResults(true);
        } catch (err) {
            console.error('Error searching registers:', err);
        }
    };

    const selectRegister = (register: Register) => {
        setFormData(prev => ({
            ...prev,
            name: register.name,
            address: register.address || '',
            address_number: register.address_number || ''
        }));
        setSearchQuery(register.name);
        setSelectedRegister(register);
        setShowResults(false);
    };

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setLoading(true);
        setError(null);

        try {
            if (initialData && initialData.w_id) {
                await axios.put(`/api/vicentinos/${initialData.w_id}`, formData);
            } else {
                await axios.post('/api/vicentinos', formData);
            }
            onSuccess();
        } catch (err: any) {
            console.error('Error saving:', err);
            setError(err.response?.data?.message || 'Erro ao salvar. Verifique os campos.');
        } finally {
            setLoading(false);
        }
    };

    const clearSelection = () => {
        setSelectedRegister(null);
        setSearchQuery('');
        setFormData(prev => ({ ...prev, name: '', address: '', address_number: '' }));
    };

    const months = [
        { value: 1, label: 'Janeiro' },
        { value: 2, label: 'Fevereiro' },
        { value: 3, label: 'Março' },
        { value: 4, label: 'Abril' },
        { value: 5, label: 'Maio' },
        { value: 6, label: 'Junho' },
        { value: 7, label: 'Julho' },
        { value: 8, label: 'Agosto' },
        { value: 9, label: 'Setembro' },
        { value: 10, label: 'Outubro' },
        { value: 11, label: 'Novembro' },
        { value: 12, label: 'Dezembro' },
    ];

    return (
        <div className="modal fade show d-block" style={{ backgroundColor: 'rgba(0,0,0,0.5)' }} tabIndex={-1}>
            <div className="modal-dialog modal-lg modal-dialog-centered">
                <div className="modal-content border-0 shadow-lg rounded-4">
                    <div className="modal-header border-0 pb-0">
                        <h5 className="modal-title fw-bold text-primary">
                            {initialData ? 'Editar Apuração' : 'Nova Apuração'}
                        </h5>
                        <button type="button" className="btn-close" onClick={onClose}></button>
                    </div>
                    <div className="modal-body p-4">
                        {error && <div className="alert alert-danger rounded-4">{error}</div>}

                        <form onSubmit={handleSubmit}>
                            {/* Search Register */}
                            <div className="mb-4">
                                <label className="form-label fw-bold small text-muted">Pessoa (Registro Geral) <span className="text-danger">*</span></label>
                                
                                {!selectedRegister ? (
                                    <div className="position-relative">
                                        <div className="input-group">
                                            <span className="input-group-text bg-light border-0 rounded-start-pill ps-4"><i className="bi bi-search text-muted"></i></span>
                                            <input
                                                type="text"
                                                className="form-control bg-light border-0 rounded-end-pill py-2"
                                                placeholder="Digite o nome para pesquisar..."
                                                value={searchQuery}
                                                onChange={handleSearchChange}
                                                required={!initialData} // Required only for new? Or always?
                                            />
                                        </div>
                                        {showResults && searchResults.length > 0 && (
                                            <div className="position-absolute w-100 bg-white shadow-sm border rounded-4 mt-1 overflow-hidden" style={{ zIndex: 1000, maxHeight: '200px', overflowY: 'auto' }}>
                                                {searchResults.map(reg => (
                                                    <div
                                                        key={reg.id}
                                                        className="p-3 border-bottom cursor-pointer hover-bg-light"
                                                        style={{ cursor: 'pointer' }}
                                                        onClick={() => selectRegister(reg)}
                                                    >
                                                        <div className="fw-bold text-dark">{reg.name}</div>
                                                        <div className="small text-muted">{reg.address}, {reg.address_number}</div>
                                                    </div>
                                                ))}
                                            </div>
                                        )}
                                    </div>
                                ) : (
                                    <div className="alert alert-primary d-flex align-items-center justify-content-between mt-2 mb-0 rounded-pill px-4">
                                        <div>
                                            <i className="bi bi-person-check-fill me-2"></i>
                                            <span className="fw-bold">{selectedRegister.name}</span>
                                        </div>
                                        <button type="button" className="btn btn-sm btn-link text-decoration-none p-0 text-primary" onClick={clearSelection}>
                                            <i className="bi bi-x-circle-fill fs-5"></i>
                                        </button>
                                    </div>
                                )}
                            </div>

                            <div className="row g-3">
                                <div className="col-md-6">
                                    <label className="form-label fw-bold small text-muted">Comunidade <span className="text-danger">*</span></label>
                                    <select
                                        className="form-select rounded-pill bg-light border-0 px-4 py-2"
                                        value={formData.ent_id}
                                        onChange={e => setFormData({ ...formData, ent_id: e.target.value })}
                                        required
                                    >
                                        <option value="">Selecione...</option>
                                        {entidades.map(ent => (
                                            <option key={ent.ent_id} value={ent.ent_id}>{ent.ent_name}</option>
                                        ))}
                                    </select>
                                </div>

                                <div className="col-md-6">
                                    <label className="form-label fw-bold small text-muted">Tipo <span className="text-danger">*</span></label>
                                    <select
                                        className="form-select rounded-pill bg-light border-0 px-4 py-2"
                                        value={formData.kind}
                                        onChange={e => setFormData({ ...formData, kind: e.target.value })}
                                        required
                                    >
                                        <option value="">Selecione...</option>
                                        <option value="0">Não Assistido</option>
                                        <option value="1">Assistido</option>
                                    </select>
                                </div>

                                <div className="col-md-6">
                                    <label className="form-label fw-bold small text-muted">Mês de Referência <span className="text-danger">*</span></label>
                                    <select
                                        className="form-select rounded-pill bg-light border-0 px-4 py-2"
                                        value={formData.month_entire}
                                        onChange={e => setFormData({ ...formData, month_entire: e.target.value })}
                                        required
                                    >
                                        <option value="">Selecione...</option>
                                        {months.map(m => (
                                            <option key={m.value} value={m.value}>{m.label}</option>
                                        ))}
                                    </select>
                                </div>

                                <div className="col-md-8">
                                    <label className="form-label fw-bold small text-muted">Endereço</label>
                                    <input
                                        type="text"
                                        className="form-control rounded-pill bg-light border-0 px-4 py-2"
                                        value={formData.address}
                                        onChange={e => setFormData({ ...formData, address: e.target.value })}
                                    />
                                </div>

                                <div className="col-md-4">
                                    <label className="form-label fw-bold small text-muted">Número</label>
                                    <input
                                        type="text"
                                        className="form-control rounded-pill bg-light border-0 px-4 py-2"
                                        value={formData.address_number}
                                        onChange={e => setFormData({ ...formData, address_number: e.target.value })}
                                    />
                                </div>

                                <div className="col-12">
                                    <label className="form-label fw-bold small text-muted">Descrição (Opcional)</label>
                                    <textarea
                                        className="form-control bg-light border-0 rounded-4 px-4 py-3"
                                        rows={3}
                                        value={formData.description}
                                        onChange={e => setFormData({ ...formData, description: e.target.value })}
                                    ></textarea>
                                </div>
                            </div>

                            <div className="d-flex justify-content-end gap-2 mt-4">
                                <button type="button" className="btn btn-light rounded-pill px-4" onClick={onClose}>Cancelar</button>
                                <button type="submit" className="btn btn-primary rounded-pill px-5" disabled={loading}>
                                    {loading ? 'Salvando...' : 'Salvar'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default VicentinoForm;
