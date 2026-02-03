/**
 * MENTTA - Alert System (Minimal)
 * Estilos y funciones auxiliares para alertas
 * NOTA: La lógica principal está en dashboard.js
 */

// Estilos para animaciones de alertas
const alertStyles = document.createElement('style');
alertStyles.textContent = `
    @keyframes slideIn { 
        from { transform: translateX(100%); opacity: 0; } 
        to { transform: translateX(0); opacity: 1; } 
    }
    @keyframes slideOut { 
        from { transform: translateX(0); opacity: 1; } 
        to { transform: translateX(100%); opacity: 0; } 
    }
    @keyframes fadeIn { 
        from { opacity: 0; transform: translateY(-10px); } 
        to { opacity: 1; transform: translateY(0); } 
    }
    @keyframes pulse { 
        0%, 100% { opacity: 1; } 
        50% { opacity: 0.5; } 
    }
    .animate-slideIn { animation: slideIn 0.3s ease-out; }
    .animate-slideOut { animation: slideOut 0.3s ease-in; }
    .animate-fadeIn { animation: fadeIn 0.3s ease-out; }
`;
document.head.appendChild(alertStyles);
