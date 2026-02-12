import React from 'react';

interface ConfirmationModalProps {
    isOpen: boolean;
    onClose: () => void;
    onConfirm: () => void;
    title: string;
    message: string;
    confirmText?: string;
    cancelText?: string;
    variant?: 'primary' | 'danger' | 'warning';
}

const ConfirmationModal: React.FC<ConfirmationModalProps> = ({
    isOpen,
    onClose,
    onConfirm,
    title,
    message,
    confirmText = 'Confirmar',
    cancelText = 'Cancelar',
    variant = 'primary'
}) => {
    if (!isOpen) return null;

    return (
        <div className="modal fade show d-block" style={{ backgroundColor: 'rgba(0,0,0,0.5)', zIndex: 1060 }} tabIndex={-1}>
            <div className="modal-dialog modal-dialog-centered modal-sm">
                <div className="modal-content rounded-4 border-0 shadow-lg">
                    <div className="modal-body p-4 text-center">
                        <div className={`mb-3 text-${variant} bg-${variant}-subtle d-inline-flex align-items-center justify-content-center rounded-circle`} style={{ width: '64px', height: '64px' }}>
                            <i className={`bi ${variant === 'danger' ? 'bi-exclamation-triangle-fill' : 'bi-info-circle-fill'} fs-3`}></i>
                        </div>
                        <h5 className="fw-bold mb-2 text-dark">{title}</h5>
                        <p className="text-muted mb-4">{message}</p>
                        <div className="d-grid gap-2">
                            <button
                                type="button"
                                className={`btn btn-${variant} rounded-pill fw-bold py-2`}
                                onClick={() => {
                                    onConfirm();
                                    onClose();
                                }}
                            >
                                {confirmText}
                            </button>
                            <button
                                type="button"
                                className="btn btn-light rounded-pill fw-bold py-2 text-muted"
                                onClick={onClose}
                            >
                                {cancelText}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default ConfirmationModal;
