# 📋 Form Validator - Guía de Uso

## 🚀 Inicialización

```javascript
const formValidator = initFormValidator('#miFormulario', {
    validateOnBlur: true,        // Validar cuando pierde el foco
    validateOnInput: false,      // Validar mientras escribe
    scrollToFirstError: true,    // Scroll automático al error
    preventSubmitOnError: true   // Prevenir submit si hay errores
});
```

---

## ⚡ Detección Automática

El validador **detecta automáticamente**:

1. ✅ Campos con `data-validate` (validación explícita)
2. ✅ Campos con `required` HTML (validación implícita)
3. ✅ **Skip validación** en campos opcionales vacíos (solo valida si el usuario escribe algo)

```html
<!-- Validación explícita -->
<input data-validate="required|email">

<!-- Validación implícita (detecta required automáticamente) -->
<input type="text" required>

<!-- Campo opcional: solo valida si el usuario escribe -->
<input data-validate="email">  <!-- ✅ OK si está vacío, valida formato si escribe -->
```

---

## 📝 Uso en HTML con `data-validate`

### Sintaxis Básica
```html
<input 
    type="text" 
    name="email" 
    data-validate="required|email|max:100"
    data-validate-messages='{"required":"El correo es obligatorio","email":"Formato inválido"}'
>
```

### Reglas Disponibles

#### ✅ **Obligatorio**
```html
data-validate="required"
```

#### 📧 **Email**
```html
data-validate="email"
```

#### 📏 **Longitud**
```html
<!-- Mínimo 3 caracteres -->
data-validate="min:3"

<!-- Máximo 50 caracteres -->
data-validate="max:50"

<!-- Exactamente 8 caracteres -->
data-validate="length:8"
```

#### 🔢 **Numéricos**
```html
<!-- Solo números -->
data-validate="numeric"

<!-- Valor mínimo -->
data-validate="minValue:18"

<!-- Valor máximo -->
data-validate="maxValue:100"
```

#### 🔤 **Texto**
```html
<!-- Solo letras (incluye acentos y ñ) -->
data-validate="alpha"

<!-- Alfanumérico -->
data-validate="alphanumeric"
```

#### 🆔 **Documentos Peruanos**
```html
<!-- DNI (8 dígitos) -->
data-validate="dni"

<!-- RUC (11 dígitos) -->
data-validate="ruc"

<!-- Teléfono (9 dígitos, empieza con 9) -->
data-validate="phone"
```

#### 🌐 **URL**
```html
data-validate="url"
```

#### 📋 **Select Obligatorio**
```html
<select data-validate="required|selected">
    <option value="" disabled selected>Seleccione...</option>
    <option value="1">Opción 1</option>
</select>
```

#### 🔄 **Confirmar Campo**
```html
<!-- Campo original -->
<input type="password" id="password" name="password">

<!-- Campo de confirmación -->
<input 
    type="password" 
    name="password_confirmation" 
    data-validate="confirmed:password"
    data-validate-messages='{"confirmed":"Las contraseñas no coinciden"}'
>
```

#### 🔗 **Campos Dependientes (requiredWith)**

Hace que **este campo** sea obligatorio cuando alguno de los campos relacionados tiene valor. Útil para grupos de campos que dependen entre sí.

```html
<!-- Campos del botón CTA: si se llena uno, todos son requeridos -->
<input type="text" name="button_text" id="button_text"
    data-validate="max:100|requiredWith:button_link,button_style">

<input type="url" name="button_link" id="button_link"
    data-validate="url|requiredWith:button_text,button_style">

<select name="button_style" id="button_style"
    data-validate="requiredWith:button_text,button_link">
    <option value="primary">Principal</option>
    <option value="secondary">Secundario</option>
</select>
```

**Parámetro:** Lista de IDs de campos separados por coma.

**Comportamiento (por campo):**
- Si ninguno de los campos relacionados tiene valor → este campo sigue siendo opcional.
- Si alguno de los campos relacionados tiene valor → este campo no puede ir vacío.

Aplicando la regla de forma simétrica en todos los campos del grupo (como en el ejemplo del botón CTA), el efecto práctico es: si uno se rellena, el resto también debe completarse.

**Mensaje de error:** `Este campo es requerido cuando se completa: [nombres de campos relacionados que tienen valor]`

**Ejemplo con tipo / número de documento:**

```html
<select id="document_type" name="document_type" class="select-form"
    data-validate="selected">
    <option value="">Seleccione una opción</option>
    <option value="DNI">DNI</option>
    <option value="RUC">RUC</option>
    <option value="CE">Carné de extranjería</option>
    <option value="PASAPORTE">Pasaporte</option>
</select>

<input type="text" id="document_number" name="document_number" class="input-form"
    placeholder="Ingresa tu número de documento"
    data-validate="document_number|max:30|requiredWith:document_type">
```

- Si no se selecciona tipo de documento → `document_number` es opcional.
- Si se selecciona un tipo de documento → `document_number` no puede ir vacío.

#### 🎨 **Patrón Regex Personalizado**
```html
<!-- Solo letras mayúsculas -->
data-validate="pattern:^[A-Z]+$"
```

---

## 📁 Validación de Archivos e Imágenes

### Archivo Requerido
```html
<input 
    type="file" 
    name="documento" 
    data-validate="fileRequired"
    data-validate-messages='{"fileRequired":"Debe seleccionar un archivo"}'
>
```

### Tamaño Máximo (en KB)
```html
<!-- Máximo 2MB (2048 KB) -->
<input 
    type="file" 
    data-validate="maxSize:2048"
    data-validate-messages='{"maxSize":"El archivo no debe superar 2MB"}'
>
```

### Tipos de Archivo Permitidos
```html
<!-- Solo PDF, Word y Excel -->
<input 
    type="file" 
    data-validate="fileTypes:pdf,doc,docx,xls,xlsx"
    data-validate-messages='{"fileTypes":"Solo se permiten documentos PDF, Word o Excel"}'
>
```

### Solo Imágenes
```html
<input 
    type="file" 
    data-validate="image|maxSize:5120"
    data-validate-messages='{
        "image":"Solo se permiten imágenes",
        "maxSize":"La imagen no debe superar 5MB"
    }'
>
```

### MIME Types Específicos
```html
<input 
    type="file" 
    data-validate="mimeTypes:image/jpeg,image/png"
    data-validate-messages='{"mimeTypes":"Solo JPG o PNG"}'
>
```

### Ejemplo Completo: Upload de Imagen
```html
<input 
    type="file" 
    name="photo" 
    accept="image/*"
    data-validate="image|maxSize:3072"
    data-validate-messages='{
        "image":"Solo imágenes JPG, PNG, GIF o WebP",
        "maxSize":"La imagen no debe exceder 3MB"
    }'
>
```

---

## 🎯 Ejemplos Completos

### Formulario de Registro
```html
<!-- Nombre -->
<input 
    type="text" 
    name="name" 
    data-validate="required|alpha|min:3|max:50"
    data-validate-messages='{
        "required":"El nombre es obligatorio",
        "alpha":"Solo se permiten letras",
        "min":"Mínimo 3 caracteres",
        "max":"Máximo 50 caracteres"
    }'
>

<!-- DNI -->
<input 
    type="text" 
    name="dni" 
    data-validate="required|dni"
    data-validate-messages='{
        "required":"El DNI es obligatorio",
        "dni":"Debe tener 8 dígitos"
    }'
>

<!-- Email -->
<input 
    type="email" 
    name="email" 
    data-validate="required|email|max:100"
    data-validate-messages='{
        "required":"El email es obligatorio",
        "email":"Ingrese un email válido"
    }'
>

<!-- Teléfono -->
<input 
    type="tel" 
    name="phone" 
    data-validate="phone"
    data-validate-messages='{"phone":"Debe ser un número válido (9 dígitos)"}'
>

<!-- Edad -->
<input 
    type="number" 
    name="age" 
    data-validate="required|minValue:18|maxValue:100"
    data-validate-messages='{
        "required":"La edad es obligatoria",
        "minValue":"Debe ser mayor de 18",
        "maxValue":"Edad máxima 100"
    }'
>

<!-- Departamento -->
<select 
    name="department" 
    data-validate="required|selected"
    data-validate-messages='{
        "required":"Seleccione un departamento",
        "selected":"Debe elegir una opción válida"
    }'
>
    <option value="" disabled selected>Seleccione...</option>
    <option value="lima">Lima</option>
    <option value="arequipa">Arequipa</option>
</select>
```

---

## 🔧 Métodos Programáticos

### Validar Campo Individual
```javascript
const emailInput = document.querySelector('#email');
const isValid = formValidator.validateField(emailInput);
```

### Validar Todos los Campos
```javascript
const allValid = formValidator.validateAll();
```

### Resetear Errores
```javascript
formValidator.reset();
```

### Agregar Regla Personalizada
```javascript
formValidator.addRule('codigo_postal', (value) => {
    // Código postal peruano (5 dígitos)
    return {
        valid: /^\d{5}$/.test(value),
        message: 'El código postal debe tener 5 dígitos'
    };
});
```

Luego en HTML:
```html
<input data-validate="codigo_postal">
```

---

## 🎨 Personalización de Mensajes

### Global (para todas las reglas)
```javascript
const formValidator = initFormValidator('#form', {
    errorClass: 'mi-clase-error',
    errorMessageClass: 'mi-mensaje-error'
});
```

### Por Campo (data-validate-messages)
```html
<input 
    data-validate="required|min:3"
    data-validate-messages='{
        "required":"Este campo no puede estar vacío",
        "min":"Necesitas al menos 3 caracteres"
    }'
>
```

---

## 📊 Combinación con Backend (Laravel)

El validador **NO reemplaza** la validación de Laravel, sino que la complementa:

1. **Frontend (FormValidator)**: Validación instantánea, mejor UX
2. **Backend (Laravel)**: Validación robusta, seguridad

Mantén las mismas reglas en ambos lados:

**Blade:**
```html
<input 
    name="email" 
    class="@error('email') input-error @enderror"
    data-validate="required|email|max:100"
>
@error('email')
    <span class="input-error-message">{{ $message }}</span>
@enderror
```

**Controller:**
```php
$request->validate([
    'email' => 'required|email|max:100'
]);
```

---

## ⚡ Performance

- ✅ Solo valida campos con `data-validate`
- ✅ Validación lazy (solo cuando pierde el foco)
- ✅ No bloquea la UI
- ✅ Mensajes inline sin recargar página

---

## 🐛 Debugging

```javascript
// Ver campos registrados
console.log(formValidator.fields);

// Ver errores actuales
console.log(formValidator.errors);
```

---

## 🔗 Integración con Otros Módulos

```javascript
document.addEventListener('DOMContentLoaded', function() {
    // 1. Validación
    const formValidator = initFormValidator('#categoryForm');
    
    // 2. Jerarquía de categorías
    const hierarchyManager = initCategoryHierarchy({...});
    
    // 3. Upload de imagen
    const imageHandler = initImageUpload({...});
    
    // 4. Submit loader
    const submitLoader = initSubmitLoader({...});
});
```

---

## 📚 Reglas por Caso de Uso

### E-commerce
```html
<!-- Precio -->
<input data-validate="required|numeric|minValue:0">

<!-- SKU -->
<input data-validate="required|alphanumeric|length:8">

<!-- Stock -->
<input data-validate="required|numeric|minValue:0|maxValue:9999">
```

### Formularios de Contacto
```html
<!-- Nombre -->
<input data-validate="required|alpha|min:3|max:50">

<!-- Asunto -->
<input data-validate="required|min:5|max:100">

<!-- Mensaje -->
<textarea data-validate="required|min:10|max:500"></textarea>
```

### Datos Personales
```html
<!-- DNI -->
<input data-validate="required|dni">

<!-- RUC (empresas) -->
<input data-validate="ruc">

<!-- Celular -->
<input data-validate="required|phone">
```
