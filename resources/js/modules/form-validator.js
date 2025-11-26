// ========================================
// 📋 FORM VALIDATOR - Sistema de Validación Global
// Validación escalable con reglas reutilizables
// ========================================

class FormValidator {
    constructor(formSelector, options = {}) {
        this.form = document.querySelector(formSelector);
        if (!this.form) {
            console.warn(`❌ Formulario "${formSelector}" no encontrado`);
            return;
        }

        this.options = {
            validateOnBlur: true,
            validateOnInput: false,
            showErrorsInline: true,
            showSuccessIndicators: true, // ✅ NUEVO: Mostrar indicadores de éxito
            preventSubmitOnError: true,
            scrollToFirstError: true,
            errorClass: 'input-error',
            successClass: 'input-success', // ✅ NUEVO: Clase de éxito
            errorMessageClass: 'input-error-message',
            ...options
        };

        this.fields = new Map();
        this.errors = new Map();
        
        this.init();
    }

    // ========================================
    // 🚀 INICIALIZACIÓN
    // ========================================
    init() {
        this.scanFields();
        this.attachEventListeners();
        console.log('✅ FormValidator inicializado:', this.fields.size, 'campos');
    }

    // ========================================
    // 🔍 ESCANEAR CAMPOS CON data-validate
    // ========================================
    scanFields() {
        // Buscar campos con data-validate explícito
        const explicitInputs = this.form.querySelectorAll('[data-validate]');
        
        // Buscar campos con required HTML (que no tengan data-validate)
        const requiredInputs = this.form.querySelectorAll('[required]:not([data-validate])');
        
        // Combinar ambos
        const allInputs = [...explicitInputs, ...requiredInputs];
        
        allInputs.forEach(input => {
            let rules = [];
            
            // Si tiene data-validate, parsear
            if (input.dataset.validate) {
                rules = this.parseRules(input.dataset.validate);
            }
            // Si solo tiene required HTML, agregar regla required automática
            else if (input.hasAttribute('required')) {
                rules.push({ name: 'required', param: null });
            }
            
            const customMessages = input.dataset.validateMessages ? 
                JSON.parse(input.dataset.validateMessages) : {};
            
            // Detectar tipo de campo para getValue correcto
            const getValue = () => {
                if (input.type === 'file') {
                    return input.files;
                }
                return input.value.trim();
            };
            
            this.fields.set(input, {
                rules,
                customMessages,
                value: getValue,
                isRequired: rules.some(r => r.name === 'required') || input.hasAttribute('required')
            });
        });
    }

    // ========================================
    // 📝 PARSEAR REGLAS DESDE data-validate
    // ========================================
    parseRules(rulesString) {
        // Formato: "required|email|min:3|max:50"
        return rulesString.split('|').map(rule => {
            const [name, param] = rule.split(':');
            return { name: name.trim(), param: param ? param.trim() : null };
        });
    }

    // ========================================
    // 🎯 ADJUNTAR EVENT LISTENERS
    // ========================================
    attachEventListeners() {
        // Validar en blur (cuando pierde el foco)
        if (this.options.validateOnBlur) {
            this.fields.forEach((config, field) => {
                field.addEventListener('blur', () => this.validateField(field));
            });
        }

        // Validar mientras escribe (opcional)
        if (this.options.validateOnInput) {
            this.fields.forEach((config, field) => {
                field.addEventListener('input', () => this.validateField(field));
            });
        }

        // Prevenir submit si hay errores
        if (this.options.preventSubmitOnError) {
            this.form.addEventListener('submit', (e) => {
                const isValid = this.validateAll();
                
                if (!isValid) {
                    e.preventDefault();
                    e.stopImmediatePropagation(); // Evitar que otros listeners se ejecuten
                    
                    // Resetear submit loader si existe
                    if (window.submitLoaderInstance) {
                        window.submitLoaderInstance.resetButton();
                    }
                    
                    if (this.options.scrollToFirstError) {
                        this.scrollToFirstError();
                    }
                }
                // Si es válido, dejar que el submit continúe normalmente
            });
        }
    }

    // ========================================
    // ✅ VALIDAR UN CAMPO INDIVIDUAL
    // ========================================
    validateField(field) {
        const config = this.fields.get(field);
        if (!config) return true;

        const value = config.value();
        let isValid = true;
        let errorMessage = null;
        
        // Si el campo es opcional (no required) y está vacío, skip validación
        const isEmpty = field.type === 'file' ? value.length === 0 : value === '';
        if (!config.isRequired && isEmpty) {
            this.clearError(field);
            return true;
        }

        // Ejecutar cada regla
        for (const rule of config.rules) {
            const validationResult = this.executeRule(rule, value, field);
            
            if (!validationResult.valid) {
                isValid = false;
                errorMessage = config.customMessages[rule.name] || validationResult.message;
                break; // Detener en el primer error
            }
        }

        // Actualizar UI
        if (isValid) {
            this.clearError(field);
            this.showSuccess(field); // ✅ NUEVO: Mostrar indicador de éxito
        } else {
            this.clearSuccess(field); // ✅ NUEVO: Limpiar éxito antes de mostrar error
            this.showError(field, errorMessage);
        }

        return isValid;
    }

    // ========================================
    // 🔄 VALIDAR TODOS LOS CAMPOS
    // ========================================
    validateAll() {
        let allValid = true;

        this.fields.forEach((config, field) => {
            const isValid = this.validateField(field);
            if (!isValid) allValid = false;
        });

        return allValid;
    }

    // ========================================
    // ⚙️ EJECUTAR REGLA DE VALIDACIÓN
    // ========================================
    executeRule(rule, value, field) {
        const ruleName = rule.name;
        const param = rule.param;

        // Buscar la regla en el registro
        if (!this.validationRules[ruleName]) {
            console.warn(`⚠️ Regla "${ruleName}" no encontrada`);
            return { valid: true };
        }

        return this.validationRules[ruleName](value, param, field);
    }

    // ========================================
    // 📚 REGLAS DE VALIDACIÓN PREDEFINIDAS
    // ========================================
    validationRules = {
        // === OBLIGATORIO ===
        required: (value) => ({
            valid: value.length > 0,
            message: 'Este campo es obligatorio'
        }),

        // === EMAIL ===
        email: (value) => {
            if (!value) return { valid: true }; // Skip si está vacío (usar required para obligar)
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return {
                valid: emailRegex.test(value),
                message: 'Ingrese un correo electrónico válido'
            };
        },

        // === LONGITUD MÍNIMA ===
        min: (value, param) => ({
            valid: value.length >= parseInt(param),
            message: `Debe tener al menos ${param} caracteres`
        }),

        // === LONGITUD MÁXIMA ===
        max: (value, param) => ({
            valid: value.length <= parseInt(param),
            message: `No puede exceder ${param} caracteres`
        }),

        // === LONGITUD EXACTA ===
        length: (value, param) => ({
            valid: value.length === parseInt(param),
            message: `Debe tener exactamente ${param} caracteres`
        }),

        // === SOLO NÚMEROS ===
        numeric: (value) => {
            if (!value) return { valid: true };
            return {
                valid: /^\d+$/.test(value),
                message: 'Solo se permiten números'
            };
        },

        // === SOLO LETRAS ===
        alpha: (value) => {
            if (!value) return { valid: true };
            return {
                valid: /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/.test(value),
                message: 'Solo se permiten letras'
            };
        },

        // === ALFANUMÉRICO (debe contener al menos una letra) ===
        alphanumeric: (value) => {
            if (!value) return { valid: true };
            // Primero valida que solo tenga letras, números y espacios
            const onlyValidChars = /^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\s]+$/.test(value);
            // Luego valida que contenga al menos una letra
            const hasLetter = /[a-zA-ZáéíóúÁÉÍÓÚñÑ]/.test(value);
            return {
                valid: onlyValidChars && hasLetter,
                message: 'Debe contener al menos una letra'
            };
        },

        // === DNI PERUANO (8 dígitos) ===
        dni: (value) => {
            if (!value) return { valid: true };
            return {
                valid: /^\d{8}$/.test(value),
                message: 'El DNI debe tener 8 dígitos'
            };
        },

        // === RUC PERUANO (11 dígitos) ===
        ruc: (value) => {
            if (!value) return { valid: true };
            return {
                valid: /^\d{11}$/.test(value),
                message: 'El RUC debe tener 11 dígitos'
            };
        },

        // === TELÉFONO PERUANO (9 dígitos, empieza con 9) ===
        phone: (value) => {
            if (!value) return { valid: true };
            return {
                valid: /^9\d{8}$/.test(value),
                message: 'Ingrese un número de teléfono válido (9 dígitos, inicia con 9)'
            };
        },

        // === URL ===
        url: (value) => {
            if (!value) return { valid: true };
            try {
                new URL(value);
                return { valid: true };
            } catch {
                return {
                    valid: false,
                    message: 'Ingrese una URL válida'
                };
            }
        },

        // === VALOR MÍNIMO (NUMÉRICO) ===
        minValue: (value, param) => {
            const numValue = parseFloat(value);
            return {
                valid: !isNaN(numValue) && numValue >= parseFloat(param),
                message: `El valor mínimo es ${param}`
            };
        },

        // === VALOR MÁXIMO (NUMÉRICO) ===
        maxValue: (value, param) => {
            const numValue = parseFloat(value);
            return {
                valid: !isNaN(numValue) && numValue <= parseFloat(param),
                message: `El valor máximo es ${param}`
            };
        },

        // === SELECT OBLIGATORIO (excluye value="") ===
        selected: (value, param, field) => {
            // Para <select>, verifica que no sea la opción disabled
            return {
                valid: value !== '' && value !== null,
                message: 'Debe seleccionar una opción'
            };
        },

        // === CONFIRMAR (COMPARAR CON OTRO CAMPO) ===
        confirmed: (value, param, field) => {
            const confirmField = document.querySelector(`#${param}`);
            if (!confirmField) {
                console.warn(`⚠️ Campo de confirmación "#${param}" no encontrado`);
                return { valid: true };
            }
            return {
                valid: value === confirmField.value.trim(),
                message: 'Los campos no coinciden'
            };
        },

        // === PATRÓN REGEX PERSONALIZADO ===
        pattern: (value, param) => {
            if (!value) return { valid: true };
            const regex = new RegExp(param);
            return {
                valid: regex.test(value),
                message: 'El formato no es válido'
            };
        },

        // ========================================
        // 📁 VALIDACIONES DE ARCHIVOS/IMÁGENES
        // ========================================
        
        // === ARCHIVO REQUERIDO ===
        fileRequired: (files) => ({
            valid: files && files.length > 0,
            message: 'Debe seleccionar un archivo'
        }),

        // === TAMAÑO MÁXIMO (en KB) ===
        maxSize: (files, param) => {
            if (!files || files.length === 0) return { valid: true };
            const file = files[0];
            const maxSizeKB = parseInt(param);
            const fileSizeKB = file.size / 1024;
            
            return {
                valid: fileSizeKB <= maxSizeKB,
                message: `El archivo no debe exceder ${maxSizeKB}KB (actual: ${Math.round(fileSizeKB)}KB)`
            };
        },

        // === TAMAÑO MÁXIMO (en MB) - MÁS INTUITIVO ===
        maxSizeMB: (files, param) => {
            if (!files || files.length === 0) return { valid: true };
            const file = files[0];
            const maxSizeMB = parseFloat(param);
            const fileSizeMB = file.size / (1024 * 1024);
            
            return {
                valid: fileSizeMB <= maxSizeMB,
                message: `El archivo no debe exceder ${maxSizeMB}MB (actual: ${fileSizeMB.toFixed(2)}MB)`
            };
        },

        // === TIPOS DE ARCHIVO PERMITIDOS ===
        fileTypes: (files, param) => {
            if (!files || files.length === 0) return { valid: true };
            const file = files[0];
            const allowedTypes = param.split(',').map(t => t.trim().toLowerCase());
            const fileExtension = file.name.split('.').pop().toLowerCase();
            
            return {
                valid: allowedTypes.includes(fileExtension),
                message: `Solo se permiten archivos: ${allowedTypes.join(', ')}`
            };
        },

        // === MIME TYPES PERMITIDOS ===
        mimeTypes: (files, param) => {
            if (!files || files.length === 0) return { valid: true };
            const file = files[0];
            const allowedMimes = param.split(',').map(m => m.trim().toLowerCase());
            
            return {
                valid: allowedMimes.includes(file.type.toLowerCase()),
                message: `Tipo de archivo no permitido. Solo: ${allowedMimes.join(', ')}`
            };
        },

        // === SOLO IMÁGENES ===
        image: (files) => {
            if (!files || files.length === 0) return { valid: true };
            const file = files[0];
            const imageTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            
            return {
                valid: imageTypes.includes(file.type.toLowerCase()),
                message: 'Solo se permiten imágenes (JPG, PNG, GIF, WebP)'
            };
        },

        // === DIMENSIONES DE IMAGEN (requiere carga asíncrona) ===
        // Nota: Esta validación es síncrona, para dimensiones usar un método especial
        maxDimensions: (files, param) => {
            // Esta regla necesita validación asíncrona
            // Se implementa por separado si es necesario
            return { valid: true };
        }
    };

    // ========================================
    // ✅ MOSTRAR ÉXITO
    // ========================================
    showSuccess(field) {
            if (!this.options.showSuccessIndicators) return;

            // Agregar clase de éxito al input
            field.classList.add(this.options.successClass);
            field.classList.remove(this.options.errorClass);

            console.log(`✅ Success aplicado a:`, field.tagName, field.name || field.id, `Clases:`, field.className);

            // Agregar icono de check en inputs, selects y textareas (no en files)
            if (field.type !== 'file') {
                const parent = field.closest('.input-icon-container');
                if (parent && !parent.querySelector('.validation-check-icon')) {
                    const checkIcon = document.createElement('i');
                    checkIcon.className = 'ri-checkbox-circle-fill validation-check-icon';
                    parent.appendChild(checkIcon);
                    console.log(`✅ Check icon agregado`);
                }
            } else {
                console.log(`⚠️ No se agrega check (es FILE)`);
            }
    }

    // ========================================
    // ❌ LIMPIAR ÉXITO
    // ========================================
    clearSuccess(field) {
        field.classList.remove(this.options.successClass);
        
        const parent = field.closest('.input-icon-container');
        if (parent) {
            const checkIcon = parent.querySelector('.validation-check-icon');
            if (checkIcon) {
                checkIcon.remove();
            }
        }
    }

    // ========================================
    // ❌ MOSTRAR ERROR
    // ========================================
    showError(field, message) {
        this.errors.set(field, message);

        // Agregar clase de error al input
        field.classList.add(this.options.errorClass);
        field.classList.remove(this.options.successClass); // ✅ NUEVO: Quitar clase de éxito

        if (!this.options.showErrorsInline) return;

        // Buscar o crear mensaje de error
        const parent = field.closest('.input-group') || field.parentElement;
        let errorElement = parent.querySelector(`.${this.options.errorMessageClass}`);

        if (!errorElement) {
            errorElement = document.createElement('span');
            errorElement.className = this.options.errorMessageClass;
            errorElement.innerHTML = `<i class="ri-error-warning-line"></i> <span class="error-text"></span>`;
            
            // Insertar después del input-icon-container o directamente después del field
            const container = field.closest('.input-icon-container');
            if (container) {
                container.after(errorElement);
            } else {
                field.after(errorElement);
            }
        }

        errorElement.querySelector('.error-text').textContent = message;
        errorElement.style.display = 'flex';
    }

    // ========================================
    // ✅ LIMPIAR ERROR
    // ========================================
    clearError(field) {
        this.errors.delete(field);
        field.classList.remove(this.options.errorClass);

        const parent = field.closest('.input-group') || field.parentElement;
        const errorElement = parent.querySelector(`.${this.options.errorMessageClass}`);
        
        if (errorElement) {
            errorElement.style.display = 'none';
        }
    }

    // ========================================
    // 📍 SCROLL AL PRIMER ERROR
    // ========================================
    scrollToFirstError() {
        const firstErrorField = Array.from(this.errors.keys())[0];
        if (firstErrorField) {
            firstErrorField.scrollIntoView({ behavior: 'smooth', block: 'center' });
            firstErrorField.focus();
        }
    }

    // ========================================
    // 🔧 AGREGAR REGLA PERSONALIZADA
    // ========================================
    addRule(name, validationFunction) {
        this.validationRules[name] = validationFunction;
        console.log(`✅ Regla personalizada "${name}" agregada`);
    }

    // ========================================
    // 🔄 RESETEAR FORMULARIO
    // ========================================
    reset() {
        this.errors.clear();
        this.fields.forEach((config, field) => {
            this.clearError(field);
            this.clearSuccess(field); // ✅ NUEVO: Limpiar también éxitos
        });
    }
}

// ========================================
// 📤 EXPORTAR CLASE Y FUNCIÓN HELPER
// ========================================
export default FormValidator;

// Función helper para inicialización rápida
export function initFormValidator(formSelector, options = {}) {
    const validator = new FormValidator(formSelector, options);

    // === Habilitar botón submit solo si hay cambios en formularios de edición ===
    const form = document.querySelector(formSelector);
    if (!form) return validator;

    // Detectar si es formulario de edición por id o atributo personalizado
    const isEditForm = form.hasAttribute('data-edit-form') || /Form$/.test(form.id);
    if (isEditForm) {
        const submitBtn = form.querySelector('button[type="submit"],input[type="submit"]');
        if (submitBtn) {
            // Guardar estado inicial
            const initialData = new FormData(form);
            let edited = false;

            function checkChanges() {
                const currentData = new FormData(form);
                edited = false;
                for (let [key, value] of currentData.entries()) {
                    if (initialData.get(key) !== value) {
                        edited = true;
                        break;
                    }
                }
                submitBtn.disabled = !edited;
            }

            form.addEventListener('input', checkChanges);
            form.addEventListener('change', checkChanges);
        }
    }
    return validator;
}
