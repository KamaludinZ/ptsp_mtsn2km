{{-- Shared by Lupa Password and Atur Ulang Password. --}}
@push('styles')
<style>
    .forgot-container {
        min-height: 100vh;
        display: flex;
        align-items: center;
        padding: 40px 16px;
        background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 50%, #bbf7d0 100%);
    }

    [data-theme="dark"] .forgot-container {
        background: linear-gradient(135deg, #1a2e1a 0%, #0f1e0f 50%, #071207 100%);
    }

    /* Dark Mode Toggle Button */
    .theme-toggle {
        position: fixed;
        bottom: 24px;
        right: 24px;
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: var(--bs-primary);
        color: white;
        border: none;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        transition: all 0.3s;
        z-index: 1000;
    }

    .theme-toggle:hover {
        transform: scale(1.1);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
    }

    .forgot-card {
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }

    /* Desktop Layout - Header di sebelah kiri */
    @media (min-width: 768px) {
        .forgot-card {
            flex-direction: row;
            min-height: 550px;
        }
    }

    [data-theme="dark"] .forgot-card {
        background: #111827;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
    }

    .forgot-header {
        background: var(--bs-primary);
        color: white;
        padding: 40px 32px;
        text-align: center;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }

    /* Desktop - Header occupy 40% width */
    @media (min-width: 768px) {
        .forgot-header {
            width: 40%;
            min-height: 550px;
            padding: 60px 40px;
        }
    }

    .forgot-icon {
        width: 80px;
        height: 80px;
        margin: 0 auto 16px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 40px;
    }

    /* Desktop - Larger icon */
    @media (min-width: 768px) {
        .forgot-icon {
            width: 100px;
            height: 100px;
            font-size: 50px;
            margin-bottom: 24px;
        }
    }

    .forgot-header h1 {
        font-size: 24px;
        margin-bottom: 12px;
    }

    @media (min-width: 768px) {
        .forgot-header h1 {
            font-size: 28px;
        }
    }

    .forgot-features {
        margin-top: 32px;
        text-align: left;
    }

    .forgot-features .feature-item {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 16px;
        opacity: 0.95;
    }

    .forgot-features .feature-icon {
        width: 36px;
        height: 36px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .forgot-features .feature-text {
        font-size: 14px;
        line-height: 1.4;
    }

    .form-body {
        padding: 40px 32px;
        flex: 1;
        background: #ffffff;
    }

    [data-theme="dark"] .form-body {
        background: #111827;
    }

    /* Desktop - Form body occupy 60% width */
    @media (min-width: 768px) {
        .form-body {
            width: 60%;
            padding: 60px 50px;
        }
    }

    .form-title {
        font-size: 24px;
        font-weight: 700;
        color: var(--bs-text);
        margin-bottom: 8px;
    }

    .form-subtitle {
        font-size: 14px;
        color: var(--bs-secondary-text);
        margin-bottom: 24px;
    }

    .form-group {
        margin-bottom: 24px;
    }

    .form-label {
        display: block;
        font-size: 14px;
        font-weight: 600;
        color: var(--bs-text);
        margin-bottom: 8px;
    }

    .form-control {
        width: 100%;
        padding: 12px 16px;
        font-size: 15px;
        border: 1px solid var(--bs-border-color);
        border-radius: 8px;
        background: #ffffff;
        color: var(--bs-text);
        transition: all 0.2s;
    }

    [data-theme="dark"] .form-control {
        background: #1f2937;
        border-color: #374151;
    }

    .form-control:focus {
        outline: none;
        border-color: var(--bs-primary);
        box-shadow: 0 0 0 3px rgba(20, 83, 45, 0.1);
    }

    .btn {
        padding: 12px 24px;
        font-size: 15px;
        font-weight: 600;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s;
        border: none;
        text-decoration: none;
        display: inline-block;
    }

    .btn-primary {
        background: var(--bs-primary);
        color: #ffffff !important;
        width: 100%;
    }

    .btn-primary:hover {
        background: var(--bs-primary-dark);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(20, 83, 45, 0.3);
    }

    .btn-outline {
        background: transparent;
        border: 1px solid var(--bs-primary);
        color: var(--bs-primary);
    }

    .btn-outline:hover {
        background: var(--bs-primary);
        color: #ffffff;
    }

    .alert-success {
        background: #d1fae5;
        border: 1px solid #6ee7b7;
        color: #065f46;
        padding: 16px;
        border-radius: 8px;
        margin-bottom: 24px;
    }

    [data-theme="dark"] .alert-success {
        background: #064e3b;
        border-color: #047857;
        color: #d1fae5;
    }

    .alert-danger {
        background: #fee;
        border: 1px solid #fcc;
        color: #c33;
        padding: 16px;
        border-radius: 8px;
        margin-bottom: 24px;
    }

    .info-box {
        background: rgba(59, 130, 246, 0.1);
        border-left: 4px solid #3b82f6;
        padding: 16px;
        border-radius: 8px;
        margin-bottom: 24px;
    }

    [data-theme="dark"] .info-box {
        background: rgba(59, 130, 246, 0.2);
    }

    .divider {
        display: flex;
        align-items: center;
        margin: 24px 0;
        color: var(--bs-secondary-text);
        font-size: 14px;
    }

    .divider::before,
    .divider::after {
        content: '';
        flex: 1;
        height: 1px;
        background: var(--bs-border-color);
    }

    .divider span {
        padding: 0 16px;
    }

    .bottom-links {
        display: flex;
        gap: 12px;
        margin-top: 24px;
    }

    .bottom-links a,
    .bottom-links button {
        flex: 1;
        text-align: center;
    }
</style>
@endpush
