import React, { useState, useEffect } from 'react';
import ReactDOM from 'react-dom/client';
import CategoryManager from './components/Campanhas/CategoryManager';
import CampaignDashboard from './components/Campanhas/CampaignDashboard';
import './bootstrap';

// Wrapper for Category Manager
const CategoryManagerWrapper = ({ paroquiaName }: { paroquiaName: string }) => {
    const [isOpen, setIsOpen] = useState(false);

    useEffect(() => {
        const btn = document.getElementById('btnAddCategory');
        if (btn) {
            const handleClick = (e: Event) => {
                e.preventDefault();
                setIsOpen(true);
            };
            btn.addEventListener('click', handleClick);
            return () => btn.removeEventListener('click', handleClick);
        }
    }, []);

    return <CategoryManager isOpen={isOpen} onClose={() => setIsOpen(false)} paroquiaName={paroquiaName} />;
};

// Wrapper for Campaign Dashboard Modal
const CampaignDashboardWrapper = ({ paroquiaName }: { paroquiaName: string }) => {
    const [isOpen, setIsOpen] = useState(false);
    const [campanhaId, setCampanhaId] = useState<number>(0);

    useEffect(() => {
        const handleClick = (e: MouseEvent) => {
            const target = (e.target as HTMLElement).closest('.btn-manage-campaign');
            if (target) {
                e.preventDefault();
                const id = target.getAttribute('data-id');
                if (id) {
                    setCampanhaId(parseInt(id));
                    setIsOpen(true);
                }
            }
        };

        document.addEventListener('click', handleClick);
        return () => document.removeEventListener('click', handleClick);
    }, []);

    return (
        <CampaignDashboard 
            isOpen={isOpen} 
            onClose={() => setIsOpen(false)} 
            campanhaId={campanhaId} 
            paroquiaName={paroquiaName} 
        />
    );
};

// Mount Category Manager
const categoryRootEl = document.getElementById('campanhas-category-manager-root');
if (categoryRootEl) {
    const paroquiaName = categoryRootEl.getAttribute('data-paroquia-name') || '';
    const root = ReactDOM.createRoot(categoryRootEl);
    root.render(
        <React.StrictMode>
            <CategoryManagerWrapper paroquiaName={paroquiaName} />
        </React.StrictMode>
    );
}

// Mount Campaign Dashboard Modal
const dashboardRootEl = document.getElementById('campanhas-dashboard-modal-root');
if (dashboardRootEl) {
    const paroquiaName = dashboardRootEl.getAttribute('data-paroquia-name') || '';
    const root = ReactDOM.createRoot(dashboardRootEl);
    root.render(
        <React.StrictMode>
            <CampaignDashboardWrapper paroquiaName={paroquiaName} />
        </React.StrictMode>
    );
}
