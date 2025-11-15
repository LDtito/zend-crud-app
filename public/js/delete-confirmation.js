/**
 * Sistema de confirmación de eliminación con modal moderno
 * Intercepta clicks en botones de eliminar y muestra confirmación
 */

let deleteUrl = null;

document.addEventListener('DOMContentLoaded', function() {
    initDeleteConfirmation();
});

/**
 * Inicializar sistema de confirmación de eliminación
 */
function initDeleteConfirmation() {
    // Buscar todos los enlaces y formularios de eliminación
    const deleteLinks = document.querySelectorAll('a[href*="/delete/"], a[href*="/eliminar/"], .btn-danger-modern[href*="/delete/"], .btn-danger-modern[href*="/eliminar/"]');
    const deleteForms = document.querySelectorAll('form[action*="/delete/"], form[action*="/eliminar/"]');
    
    // Interceptar clicks en enlaces de eliminación
    deleteLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            deleteUrl = this.href;
            showDeleteModal();
        });
    });
    
    // Interceptar envío de formularios de eliminación
    deleteForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            deleteUrl = this.action;
            showDeleteModal();
        });
    });
    
    // También interceptar clicks en botones con clase específica
    document.addEventListener('click', function(e) {
        const target = e.target.closest('.delete-btn, .btn-delete, [data-action="delete"]');
        if (target) {
            e.preventDefault();
            deleteUrl = target.href || target.getAttribute('data-url') || target.closest('form')?.action;
            const productName = target.getAttribute('data-product-name');
            if (deleteUrl) {
                showDeleteModal(productName);
            }
        }
    });
}

/**
 * Mostrar modal de confirmación
 */
function showDeleteModal(productName = null) {
    const modal = document.getElementById('deleteModal');
    if (modal) {
        // Actualizar contenido del modal si hay nombre de producto
        if (productName) {
            const bodyElement = modal.querySelector('.delete-modal-body');
            bodyElement.innerHTML = `
                <p>¿Está seguro de que desea eliminar el producto:</p>
                <p style="font-weight: 600; color: var(--primary-color); margin: 15px 0;">"${productName}"</p>
                <p style="color: #8c8c8c; font-size: 0.9em;">Esta acción no se puede deshacer.</p>
            `;
        }
        
        modal.style.display = 'flex';
        modal.classList.remove('closing');
        
        // Prevenir scroll del body
        document.body.style.overflow = 'hidden';
        
        // Cerrar modal al hacer click en overlay
        const overlay = modal.querySelector('.delete-modal-overlay');
        overlay.addEventListener('click', closeDeleteModal);
        
        // Cerrar modal con tecla Escape
        document.addEventListener('keydown', handleEscapeKey);
        
        // Enfocar el botón de cancelar por defecto
        setTimeout(() => {
            const cancelBtn = modal.querySelector('.btn-secondary-modern');
            if (cancelBtn) {
                cancelBtn.focus();
            }
        }, 100);
    }
}

/**
 * Cerrar modal de confirmación
 */
function closeDeleteModal() {
    const modal = document.getElementById('deleteModal');
    if (modal) {
        modal.classList.add('closing');
        
        // Restaurar scroll del body
        document.body.style.overflow = '';
        
        setTimeout(() => {
            modal.style.display = 'none';
            modal.classList.remove('closing');
        }, 200);
        
        // Limpiar listeners
        document.removeEventListener('keydown', handleEscapeKey);
        deleteUrl = null;
    }
}

/**
 * Confirmar eliminación
 */
function confirmDelete() {
    if (deleteUrl) {
        // Mostrar indicador de carga
        const modal = document.getElementById('deleteModal');
        const confirmBtn = modal.querySelector('.btn-danger-modern');
        const originalText = confirmBtn.innerHTML;
        
        confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Eliminando...';
        confirmBtn.disabled = true;
        
        // Simular navegación a la URL de eliminación
        window.location.href = deleteUrl;
    }
}

/**
 * Manejar tecla Escape para cerrar modal
 */
function handleEscapeKey(e) {
    if (e.key === 'Escape') {
        closeDeleteModal();
    }
}

/**
 * Función global para inicializar confirmación en elementos dinámicos
 * Útil cuando se añaden elementos vía AJAX
 */
function initDeleteConfirmationForElement(element) {
    const deleteLinks = element.querySelectorAll('a[href*="/delete/"], a[href*="/eliminar/"]');
    const deleteForms = element.querySelectorAll('form[action*="/delete/"], form[action*="/eliminar/"]');
    
    deleteLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            deleteUrl = this.href;
            showDeleteModal();
        });
    });
    
    deleteForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            deleteUrl = this.action;
            showDeleteModal();
        });
    });
}

/**
 * Mostrar notificación después de eliminación exitosa
 */
function showDeleteSuccess(message = 'Producto eliminado correctamente') {
    const notification = document.createElement('div');
    notification.className = 'alert-modern alert-success-modern';
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 10001;
        min-width: 300px;
        animation: slideInRight 0.3s ease;
    `;
    
    notification.innerHTML = `
        <div class="alert-content">
            <i class="fas fa-check-circle"></i>
            ${message}
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Auto-remover después de 3 segundos
    setTimeout(() => {
        notification.style.animation = 'slideOutRight 0.3s ease forwards';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 3000);
}

// CSS adicional para notificaciones
const additionalStyles = document.createElement('style');
additionalStyles.textContent = `
@keyframes slideInRight {
    from {
        opacity: 0;
        transform: translateX(100%);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes slideOutRight {
    from {
        opacity: 1;
        transform: translateX(0);
    }
    to {
        opacity: 0;
        transform: translateX(100%);
    }
}

.delete-modal .btn-danger-modern:hover {
    background: linear-gradient(135deg, #c0392b, #a93226);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(192, 57, 43, 0.4);
}

.delete-modal .btn-secondary-modern:hover {
    background: linear-gradient(135deg, #7f8c8d, #6c757d);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(127, 140, 141, 0.4);
}
`;

document.head.appendChild(additionalStyles);