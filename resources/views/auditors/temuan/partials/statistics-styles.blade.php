<style>
    .stat-card {
        border-left: 4px solid;
        transition: all 0.3s ease;
    }
    
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    .stat-card.revisi { border-left-color: #5e72e4; }
    .stat-card.perakitan { border-left-color: #2dce89; }
    .stat-card.shiyousho { border-left-color: #f5365c; }
    .stat-card.lainlain { border-left-color: #fb6340; }
    
    .stat-number {
        font-size: 2rem;
        font-weight: 700;
        line-height: 1;
    }
    
    .stat-label {
        font-size: 0.875rem;
        color: #8898aa;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .status-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 0.375rem;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .status-badge.belum {
        background: #ffeaa7;
        color: #fdcb6e;
    }
    
    .status-badge.menunggu {
        background: #74b9ff;
        color: #0984e3;
    }
    
    .status-badge.selesai {
        background: #55efc4;
        color: #00b894;
    }

    .missing-stat-item {
        padding: 1rem;
        border-radius: 0.5rem;
        background: #fff3cd;
        border: 1px solid #ffc107;
    }
    
    .missing-stat-item:hover {
        background: #fff3cd;
        border-color: #ff9800;
    }
</style>