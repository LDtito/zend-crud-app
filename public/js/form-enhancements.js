/**
 * Mejoras JavaScript para formularios modernos
 * Añade funcionalidad interactiva y validación visual
 */

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar mejoras de formulario
    initFormEnhancements();
    initFileInputs();
    initFormValidation();
    initAnimations();
});

/**
 * Inicializar mejoras generales de formulario
 */
function initFormEnhancements() {
    // Añadir efectos de focus y blur a todos los inputs
    const inputs = document.querySelectorAll('.form-control-modern, .form-select-modern, .form-textarea-modern');
    
    inputs.forEach(input => {
        // Efecto de focus
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('input-focused');
            
            // Añadir clase de validación si es válido
            if (this.validity.valid && this.value.trim() !== '') {
                this.classList.add('is-valid');
                this.classList.remove('is-invalid');
            }
        });
        
        // Efecto de blur (perder focus)
        input.addEventListener('blur', function() {
            this.parentElement.classList.remove('input-focused');
            
            // Validar el campo al perder el focus
            validateField(this);
        });
        
        // Validar mientras el usuario escribe (con debounce)
        let timeoutId;
        input.addEventListener('input', function() {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(() => {
                validateField(this);
            }, 500);
        });
    });
}

/**
 * Mejorar inputs de archivo
 */
function initFileInputs() {
    const fileInputs = document.querySelectorAll('input[type="file"]');
    
    fileInputs.forEach(input => {
        input.addEventListener('change', function() {
            const fileInputContainer = this.closest('.file-input-modern');
            const fileText = fileInputContainer.querySelector('.file-input-text');
            const fileSubtext = fileInputContainer.querySelector('.file-input-subtext');
            
            if (this.files && this.files.length > 0) {
                const file = this.files[0];
                fileText.textContent = `Archivo seleccionado: ${file.name}`;
                fileSubtext.textContent = `Tamaño: ${formatFileSize(file.size)}`;
                fileInputContainer.classList.add('file-selected');
                
                // Validar tipo y tamaño
                if (validateFileInput(file)) {
                    fileInputContainer.classList.add('file-valid');
                    fileInputContainer.classList.remove('file-invalid');
                } else {
                    fileInputContainer.classList.add('file-invalid');
                    fileInputContainer.classList.remove('file-valid');
                }
                
                // Previsualizar imagen si es posible
                if (file.type.startsWith('image/')) {
                    previewImage(file, fileInputContainer);
                }
            } else {
                fileText.textContent = 'Seleccionar imagen del producto';
                fileSubtext.textContent = 'JPG, PNG, GIF (máximo 2MB)';
                fileInputContainer.classList.remove('file-selected', 'file-valid', 'file-invalid');
                
                // Remover preview si existe
                const preview = fileInputContainer.querySelector('.image-preview');
                if (preview) {
                    preview.remove();
                }
            }
        });
    });
}

/**
 * Validar campo individual
 */
function validateField(field) {
    const value = field.value.trim();
    const isRequired = field.hasAttribute('required');
    const fieldType = field.type;
    let isValid = true;
    let errorMessage = '';
    
    // Validaciones básicas
    if (isRequired && value === '') {
        isValid = false;
        errorMessage = 'Este campo es obligatorio';
    } else if (value !== '') {
        // Validaciones específicas por tipo
        switch (fieldType) {
            case 'email':
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(value)) {
                    isValid = false;
                    errorMessage = 'Formato de email inválido';
                }
                break;
            case 'tel':
                const phoneRegex = /^[\+]?[0-9\-\(\)\s]+$/;
                if (!phoneRegex.test(value)) {
                    isValid = false;
                    errorMessage = 'Formato de teléfono inválido';
                }
                break;
            case 'number':
                const min = field.getAttribute('min');
                const max = field.getAttribute('max');
                const numValue = parseFloat(value);
                
                if (isNaN(numValue)) {
                    isValid = false;
                    errorMessage = 'Debe ser un número válido';
                } else {
                    if (min !== null && numValue < parseFloat(min)) {
                        isValid = false;
                        errorMessage = `El valor mínimo es ${min}`;
                    }
                    if (max !== null && numValue > parseFloat(max)) {
                        isValid = false;
                        errorMessage = `El valor máximo es ${max}`;
                    }
                }
                break;
        }
        
        // Validación de patrón
        const pattern = field.getAttribute('pattern');
        if (pattern && !new RegExp(pattern).test(value)) {
            isValid = false;
            errorMessage = 'El formato no es válido';
        }
    }
    
    // Aplicar estilos de validación
    if (isValid) {
        field.classList.add('is-valid');
        field.classList.remove('is-invalid');
        removeFieldError(field);
    } else {
        field.classList.add('is-invalid');
        field.classList.remove('is-valid');
        showFieldError(field, errorMessage);
    }
    
    return isValid;
}

/**
 * Mostrar error en campo
 */
function showFieldError(field, message) {
    removeFieldError(field);
    
    const errorDiv = document.createElement('div');
    errorDiv.className = 'invalid-feedback-modern';
    errorDiv.textContent = message;
    
    // Insertar después del campo o su contenedor
    const container = field.closest('.input-group-modern') || field;
    container.parentNode.insertBefore(errorDiv, container.nextSibling);
}

/**
 * Remover error de campo
 */
function removeFieldError(field) {
    const container = field.closest('.form-group-modern');
    const existingError = container.querySelector('.invalid-feedback-modern');
    if (existingError) {
        existingError.remove();
    }
}

/**
 * Validar archivo
 */
function validateFileInput(file) {
    const maxSize = 2 * 1024 * 1024; // 2MB
    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    
    if (file.size > maxSize) {
        return false;
    }
    
    if (!allowedTypes.includes(file.type)) {
        return false;
    }
    
    return true;
}

/**
 * Formatear tamaño de archivo
 */
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

/**
 * Previsualizar imagen
 */
function previewImage(file, container) {
    const reader = new FileReader();
    reader.onload = function(e) {
        // Remover preview anterior si existe
        const existingPreview = container.querySelector('.image-preview');
        if (existingPreview) {
            existingPreview.remove();
        }
        
        // Crear nuevo preview
        const preview = document.createElement('div');
        preview.className = 'image-preview';
        preview.innerHTML = `
            <img src="${e.target.result}" alt="Preview" style="
                max-width: 150px; 
                max-height: 150px; 
                border-radius: 8px; 
                margin-top: 10px;
                box-shadow: 0 4px 8px rgba(0,0,0,0.1);
                cursor: pointer;
            " onclick="openImageModal('${e.target.result}')">
        `;
        
        container.appendChild(preview);
    };
    reader.readAsDataURL(file);
}

/**
 * Inicializar validación de formulario
 */
function initFormValidation() {
    const forms = document.querySelectorAll('.modern-form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            let isFormValid = true;
            const fields = form.querySelectorAll('.form-control-modern, .form-select-modern, .form-textarea-modern');
            
            fields.forEach(field => {
                if (!validateField(field)) {
                    isFormValid = false;
                }
            });
            
            if (!isFormValid) {
                e.preventDefault();
                
                // Hacer scroll al primer campo con error
                const firstError = form.querySelector('.is-invalid');
                if (firstError) {
                    firstError.scrollIntoView({ 
                        behavior: 'smooth', 
                        block: 'center' 
                    });
                    firstError.focus();
                }
                
                // Mostrar mensaje general
                showFormAlert('Por favor, corrija los errores en el formulario', 'error');
            } else {
                // Mostrar indicador de carga
                showFormLoading(form);
            }
        });
    });
}

/**
 * Inicializar animaciones
 */
function initAnimations() {
    // Añadir animación de entrada a los grupos de formulario
    const formGroups = document.querySelectorAll('.form-group-modern');
    
    // Observador de intersección para animar elementos cuando entran en vista
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    });
    
    formGroups.forEach((group, index) => {
        // Configurar estado inicial
        group.style.opacity = '0';
        group.style.transform = 'translateY(20px)';
        group.style.transition = `all 0.6s ease ${index * 0.1}s`;
        
        // Observar elemento
        observer.observe(group);
    });
}

/**
 * Mostrar alerta en formulario
 */
function showFormAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert-modern alert-${type === 'error' ? 'danger' : type}-modern`;
    alertDiv.innerHTML = `
        <div class="alert-content">
            <i class="fas fa-${type === 'error' ? 'exclamation-triangle' : 'info-circle'}"></i>
            ${message}
        </div>
    `;
    
    // Insertar al inicio del contenido
    const contentBody = document.querySelector('.content-body');
    contentBody.insertBefore(alertDiv, contentBody.firstChild);
    
    // Remover después de 5 segundos
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}

/**
 * Mostrar indicador de carga en formulario
 */
function showFormLoading(form) {
    form.classList.add('form-loading');
    
    const submitButton = form.querySelector('button[type="submit"]');
    if (submitButton) {
        const originalText = submitButton.innerHTML;
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
        submitButton.disabled = true;
        
        // Restaurar después de 3 segundos (por si falla)
        setTimeout(() => {
            submitButton.innerHTML = originalText;
            submitButton.disabled = false;
            form.classList.remove('form-loading');
        }, 10000);
    }
}

/**
 * Abrir modal de imagen (si existe la función global)
 */
function openImageModal(imageSrc) {
    if (typeof window.openImageModal === 'function') {
        window.openImageModal(imageSrc);
    }
}