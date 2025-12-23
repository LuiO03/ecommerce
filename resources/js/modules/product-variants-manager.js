function safeParseJson(raw, fallback) {
  if (!raw) return fallback;
  try {
    return JSON.parse(raw);
  } catch (e) {
    console.warn('[product-variants-manager] JSON inválido', e);
    return fallback;
  }
}

function slugifySegment(value) {
  return String(value || '')
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .toUpperCase()
    .trim()
    .replace(/[^A-Z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '');
}

function normalizeHexColor(value) {
  const raw = String(value || '').trim().replace(/^#/, '');
  if (!raw) return null;

  if (!/^([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6})$/.test(raw)) {
    return null;
  }

  const expanded = raw.length === 3
    ? raw.split('').map((ch) => ch + ch).join('')
    : raw;

  return `#${expanded.toUpperCase()}`;
}

function buildOptionsIndex(options) {
  const byId = new Map();
  const featureToOption = new Map();

  options.forEach((opt) => {
    const isColor = !!opt.is_color;
    const option = {
      id: opt.id,
      name: opt.name,
      isColor,
      features: Array.isArray(opt.features) ? opt.features : [],
    };
    byId.set(option.id, option);
    option.features.forEach((feat) => {
      const normalizedColor = isColor ? normalizeHexColor(feat.value) : null;
      let label;
      const rawValue = String(feat.value ?? '').trim();
      const rawDescription = feat.description != null ? String(feat.description).trim() : '';

      if (isColor) {
        // Para colores, mostrar el nombre (description) y caer al HEX si no hay nombre.
        label = rawDescription || rawValue;
      } else {
        // Para otras opciones, usar siempre el value (S, M, L, Masculino, etc.).
        label = rawValue;
      }

      featureToOption.set(feat.id, {
        optionId: option.id,
        optionName: option.name,
        featureId: feat.id,
        rawValue: feat.value,
        label,
        isColor,
        color: normalizedColor,
      });
    });
  });

  return { byId, featureToOption };
}

function getSelectedFeaturesFromInitialVariants(initialVariants) {
  const map = new Map();

  (initialVariants || []).forEach((variant) => {
    (variant.features || []).forEach((feat) => {
      const optionId = feat.option_id;
      const featureId = feat.id;
      if (!optionId || !featureId) return;

      if (!map.has(optionId)) {
        map.set(optionId, new Set());
      }
      map.get(optionId).add(featureId);
    });
  });

  return map;
}

function buildCartesianProduct(optionFeatureSets, optionsIndex) {
  const entries = Object.entries(optionFeatureSets).filter(([, ids]) => Array.isArray(ids) && ids.length);
  if (!entries.length) return [];

  let combos = [{ features: [] }];

  entries.forEach(([optionIdStr, featureIds]) => {
    const optionId = Number(optionIdStr);
    const option = optionsIndex.byId.get(optionId);
    if (!option) return;

    const next = [];
    combos.forEach((combo) => {
      featureIds.forEach((featureId) => {
        const meta = optionsIndex.featureToOption.get(featureId);
        if (!meta) return;
        next.push({
          features: [...combo.features, meta],
        });
      });
    });
    combos = next;
  });

  return combos;
}

function buildVariantLabel(featuresMeta) {
  if (!featuresMeta || !featuresMeta.length) return 'Variante sin opciones';

  // Mostrar solo los valores en orden, sin el nombre de la opción.
  // Para color ya viene meta.label con la description (nombre),
  // y para el resto meta.label es el value (S, M, L, etc.).
  const parts = featuresMeta
    .map((meta) => (meta.label || String(meta.rawValue ?? '').trim()))
    .filter((text) => !!text);

  return parts.length ? parts.join(' / ') : 'Variante sin opciones';
}

function buildSkuSuggestion(baseSku, featuresMeta) {
  const cleanBase = String(baseSku || '').trim();
  const basePart = cleanBase || 'VAR';

  if (!featuresMeta || !featuresMeta.length) {
    return basePart;
  }

  const segments = featuresMeta.map((meta) => slugifySegment(meta.featureValue)).filter(Boolean);
  if (!segments.length) return basePart;

  return `${basePart}-${segments.join('-')}`;
}

function reindexVariantRows(container, emptyState) {
  const rows = Array.from(container.querySelectorAll('.variant-row'));

  if (!rows.length) {
    if (emptyState) emptyState.classList.remove('is-hidden');
    return;
  }

  if (emptyState) emptyState.classList.add('is-hidden');

  rows.forEach((row, index) => {
    row.dataset.index = String(index);
    const inputs = row.querySelectorAll('input, select, textarea');
    inputs.forEach((input) => {
      if (!input.name) return;
      input.name = input.name.replace(/variants\[[0-9]+\]/, `variants[${index}]`);
    });
  });
}

function buildVariantRowDom({ index, variant }) {
  const tr = document.createElement('tr');
  tr.className = 'variant-row';
  tr.dataset.index = String(index);

  const tdOptions = document.createElement('td');
  tdOptions.className = 'column-variant-options';

  const hiddenId = document.createElement('input');
  hiddenId.type = 'hidden';
  hiddenId.name = `variants[${index}][id]`;
  hiddenId.value = String(variant.id || '');
  tdOptions.appendChild(hiddenId);

  const labelText = document.createTextNode(buildVariantLabel(variant.featuresMeta || []));
  tdOptions.appendChild(labelText);

  const featuresDiv = document.createElement('div');
  featuresDiv.className = 'variant-hidden-features';
  featuresDiv.dataset.role = 'features-container';
  tdOptions.appendChild(featuresDiv);

  const tdSku = document.createElement('td');
  tdSku.className = 'column-variant-sku';
  tdSku.innerHTML = `
    <div class="input-group">
      <div class="input-icon-container">
        <i class="ri-hashtag input-icon"></i>
        <input type="text" class="input-form" name="variants[${index}][sku]" value="${variant.sku || ''}"
          placeholder="Ej. PROD-001-RED-M" data-role="variant-sku">
      </div>
    </div>`;

  const tdPrice = document.createElement('td');
  tdPrice.className = 'column-variant-price';
  tdPrice.innerHTML = `
    <div class="input-group">
      <div class="input-icon-container">
        <i class="ri-currency-line input-icon"></i>
        <input type="number" class="input-form" name="variants[${index}][price]" min="0" step="0.01"
          value="${variant.price != null ? String(variant.price) : ''}" placeholder="Opcional" data-role="variant-price">
      </div>
    </div>`;

  const tdStock = document.createElement('td');
  tdStock.className = 'column-variant-stock';
  tdStock.innerHTML = `
    <div class="input-group">
      <div class="input-icon-container">
        <i class="ri-stack-line input-icon"></i>
        <input type="number" class="input-form" name="variants[${index}][stock]" min="0" step="1"
          value="${variant.stock != null ? String(variant.stock) : '0'}" placeholder="0" data-role="variant-stock">
      </div>
    </div>`;

  const tdStatus = document.createElement('td');
  tdStatus.className = 'column-variant-status';
  tdStatus.innerHTML = `
    <div class="input-group">
      <div class="switch-tabla-wrapper">
        <input type="hidden" name="variants[${index}][status]" value="0">
        <label class="switch-tabla" title="Activar o desactivar variante">
          <input type="checkbox" name="variants[${index}][status]" value="1" ${variant.status ? 'checked' : ''}
            data-role="variant-status">
          <span class="slider"></span>
        </label>
      </div>
    </div>`;

  const tdActions = document.createElement('td');
  tdActions.className = 'column-variant-actions';
  tdActions.innerHTML = `
    <button type="button" class="boton boton-danger" data-action="remove-variant" title="Eliminar variante">
        <span class="boton-text">Eliminar</span>
        <span class="boton-icon"><i class="ri-delete-bin-6-fill"></i></span>
    </button>`;

  tr.appendChild(tdOptions);
  tr.appendChild(tdSku);
  tr.appendChild(tdPrice);
  tr.appendChild(tdStock);
  tr.appendChild(tdStatus);
  tr.appendChild(tdActions);

  return tr;
}

function buildVariantRowFromTemplate({
  index,
  variant,
  container,
  emptyState,
}) {
  const row = buildVariantRowDom({ index, variant });

  // Marcar si la variante está ligada a una combinación de opciones
  const isAuto = Array.isArray(variant.featuresMeta) && variant.featuresMeta.length > 0;
  row.dataset.auto = isAuto ? '1' : '0';

  const featuresContainer = row.querySelector('[data-role="features-container"]');
  if (featuresContainer) {
    (variant.featuresMeta || []).forEach((meta) => {
      const hidden = document.createElement('input');
      hidden.type = 'hidden';
      hidden.name = `variants[${index}][features][]`;
      hidden.value = String(meta.featureId);
      featuresContainer.appendChild(hidden);
    });
  }

  const body = container.querySelector('[data-role="variants-body"]') || container;
  body.appendChild(row);
  reindexVariantRows(container, emptyState);
  return row;
}

export function initProductVariantsManager({
  containerId,
  emptyStateId,
  addButtonId,
  templateId,
  optionsContainerId,
  generateButtonId,
  baseSkuInputId,
} = {}) {
  const container = document.getElementById(containerId);
  if (!container) return null;

  // Evitar inicializar dos veces sobre el mismo contenedor
  if (container.dataset.variantsManagerInitialized === '1') {
    return null;
  }
  container.dataset.variantsManagerInitialized = '1';

  const emptyState = emptyStateId ? document.getElementById(emptyStateId) : null;
  const addButton = addButtonId ? document.getElementById(addButtonId) : null;
  const optionsContainer = optionsContainerId ? document.getElementById(optionsContainerId) : null;
  const generateButton = generateButtonId ? document.getElementById(generateButtonId) : null;
  const baseSkuInput = baseSkuInputId ? document.getElementById(baseSkuInputId) : null;
  const templateNode = templateId ? document.getElementById(templateId) : null;

  // Mantener templateHtml definido para compatibilidad, aunque
  // la construcción de filas se hace vía buildVariantRowDom.
  const templateHtml = templateNode ? (templateNode.textContent || '').trim() : '';

  const optionsData = safeParseJson(container.dataset.options, []);
  const initialVariants = safeParseJson(container.dataset.initialVariants, []);
  const index = buildOptionsIndex(optionsData);

  const selectedOptionFeatures = getSelectedFeaturesFromInitialVariants(initialVariants);

  function renderOptionsUi() {
    if (!optionsContainer) return;

    optionsContainer.innerHTML = '';

    if (!optionsData.length) {
      const msg = document.createElement('p');
      msg.className = 'text-muted-td';
      msg.textContent = 'No hay opciones configuradas. Crea opciones desde el módulo "Opciones".';
      optionsContainer.appendChild(msg);
      return;
    }

    const MANY_THRESHOLD = 10;

    optionsData.forEach((opt) => {
      const card = document.createElement('div');
      card.className = 'product-option-card';
      card.dataset.optionId = String(opt.id);

      const header = document.createElement('div');
      header.className = 'product-option-header';

      const label = document.createElement('label');
      label.className = 'checkbox-inline';

      const checkbox = document.createElement('input');
      checkbox.type = 'checkbox';
      checkbox.className = 'option-toggle';
      checkbox.value = String(opt.id);

      const hasAnyFeature = selectedOptionFeatures.has(opt.id) && selectedOptionFeatures.get(opt.id).size > 0;
      checkbox.checked = hasAnyFeature;

      const text = document.createElement('span');
      text.textContent = opt.name;

      label.appendChild(checkbox);
      label.appendChild(text);
      header.appendChild(label);
      card.appendChild(header);

      const featuresWrapper = document.createElement('div');
      featuresWrapper.className = 'product-option-features';

      const usedSet = selectedOptionFeatures.get(opt.id) || new Set();

      const totalFeatures = opt.features.length;
      const showCompact = totalFeatures > MANY_THRESHOLD;

      opt.features.forEach((feat, idx) => {
        const pill = document.createElement('label');
        pill.className = 'product-option-feature-pill';

        if (showCompact && idx >= MANY_THRESHOLD) {
          pill.classList.add('is-extra');
        }

        const cb = document.createElement('input');
        cb.type = 'checkbox';
        cb.className = 'feature-toggle';
        cb.value = String(feat.id);
        cb.checked = hasAnyFeature ? usedSet.has(feat.id) : false;

        const span = document.createElement('span');
        const meta = index.featureToOption.get(feat.id);

        if (meta && meta.isColor && meta.color) {
          const dot = document.createElement('span');
          dot.className = 'product-option-color-dot';
          dot.style.setProperty('--variant-color', meta.color);
          pill.appendChild(dot);
        }

        span.textContent = (meta && meta.label) ? meta.label : feat.value;

        pill.appendChild(cb);
        pill.appendChild(span);

        if (cb.checked) {
          pill.classList.add('is-selected');
        }
        featuresWrapper.appendChild(pill);
      });

      card.appendChild(featuresWrapper);

      if (showCompact) {
        const footer = document.createElement('div');
        footer.className = 'product-option-footer';

        const summary = document.createElement('span');
        summary.className = 'product-option-summary';
        const updateSummary = () => {
          const selectedCount = Array.from(featuresWrapper.querySelectorAll('.feature-toggle'))
            .filter((n) => n.checked).length;
          summary.textContent = `${selectedCount} seleccionados de ${totalFeatures}`;
        };

        const toggleBtn = document.createElement('button');
        toggleBtn.type = 'button';
        toggleBtn.className = 'boton-sm boton-link product-option-toggle';
        toggleBtn.textContent = 'Ver todos';

        toggleBtn.addEventListener('click', () => {
          const collapsed = featuresWrapper.classList.toggle('is-collapsed');
          toggleBtn.textContent = collapsed ? 'Ver todos' : 'Mostrar menos';
        });

        featuresWrapper.classList.add('is-collapsed');

        footer.appendChild(summary);
        footer.appendChild(toggleBtn);
        card.appendChild(footer);

        // inicializar resumen con el estado actual
        setTimeout(updateSummary, 0);

        featuresWrapper.addEventListener('change', updateSummary);
      }

      optionsContainer.appendChild(card);

      const syncVisibility = () => {
        const anyChecked = Array.from(featuresWrapper.querySelectorAll('.feature-toggle')).some((n) => n.checked);
        checkbox.checked = anyChecked;
        featuresWrapper.classList.toggle('is-disabled', !checkbox.checked);
      };

      syncVisibility();

      checkbox.addEventListener('change', () => {
        const checked = checkbox.checked;
        featuresWrapper.classList.toggle('is-disabled', !checked);
        const featureCbs = featuresWrapper.querySelectorAll('.feature-toggle');
        if (!checked) {
          featureCbs.forEach((n) => {
            n.checked = false;
          });

          // Limpiar también el estado interno cuando se deshabilita la opción
          const optionId = Number(opt.id);
          selectedOptionFeatures.delete(optionId);

          // Quitar estilos visuales de selección
          const pills = featuresWrapper.querySelectorAll('.product-option-feature-pill');
          pills.forEach((pill) => pill.classList.remove('is-selected'));
        }
      });

      featuresWrapper.addEventListener('change', (event) => {
        const target = event.target;
        if (!(target instanceof HTMLInputElement) || !target.classList.contains('feature-toggle')) return;

        const pill = target.closest('.product-option-feature-pill');
        if (pill) {
          pill.classList.toggle('is-selected', target.checked);
        }

        const optionId = Number(opt.id);
        if (!selectedOptionFeatures.has(optionId)) {
          selectedOptionFeatures.set(optionId, new Set());
        }
        const set = selectedOptionFeatures.get(optionId);

        const featureId = Number(target.value);
        if (target.checked) {
          set.add(featureId);
        } else {
          set.delete(featureId);
        }

        const cleaned = new Set(Array.from(set).filter((id) => index.featureToOption.has(id)));
        selectedOptionFeatures.set(optionId, cleaned);

        const anyChecked = cleaned.size > 0;
        checkbox.checked = anyChecked;
        featuresWrapper.classList.toggle('is-disabled', !anyChecked);
      });
    });
  }

  function collectOptionFeatureSets() {
    const result = {};
    selectedOptionFeatures.forEach((set, optionId) => {
      const ids = Array.from(set).filter((id) => index.featureToOption.has(id));
      if (ids.length) {
        result[optionId] = ids;
      }
    });
    return result;
  }

  function clearVariants() {
    const body = container.querySelector('[data-role="variants-body"]');
    if (body) {
      const rows = body.querySelectorAll('.variant-row');
      rows.forEach((row) => row.remove());
    }
    reindexVariantRows(container, emptyState);
  }

  function generateVariants() {
    const sets = collectOptionFeatureSets();
    const combos = buildCartesianProduct(sets, index);
    const body = container.querySelector('[data-role="variants-body"]') || container;

    // Indexar variantes automáticas existentes por combinación de features
    const existingByKey = new Map();
    const autoRows = body.querySelectorAll('.variant-row[data-auto="1"]');
    autoRows.forEach((row) => {
      const featureInputs = row.querySelectorAll('input[name$="[features][]"]');
      const ids = Array.from(featureInputs)
        .map((inp) => Number(inp.value))
        .filter((id) => Number.isFinite(id))
        .sort((a, b) => a - b);
      if (!ids.length) return;
      const key = ids.join('-');
      if (!key) return;
      existingByKey.set(key, row);
    });

    if (!combos.length) {
      // Si ya no hay combinaciones, eliminar solo las variantes automáticas
      existingByKey.forEach((row) => row.remove());
      reindexVariantRows(container, emptyState);
      return;
    }

    const baseSku = baseSkuInput ? baseSkuInput.value : '';

    combos.forEach((combo, idx) => {
      const featuresMeta = combo.features;
      const featureIds = featuresMeta
        .map((meta) => Number(meta.featureId))
        .filter((id) => Number.isFinite(id))
        .sort((a, b) => a - b);
      const key = featureIds.join('-');

      let reusedRow = key ? existingByKey.get(key) : null;
      if (reusedRow) {
        // Reutilizar la fila existente para conservar SKU, precio, stock, estado
        existingByKey.delete(key);
        body.appendChild(reusedRow);
        return;
      }

      const suggestionSku = buildSkuSuggestion(baseSku, featuresMeta);

      const variant = {
        id: null,
        sku: suggestionSku,
        price: null,
        stock: 0,
        status: true,
        featuresMeta,
      };

      buildVariantRowFromTemplate({
        index: idx,
        variant,
        container,
        emptyState,
      });
    });

    // Eliminar variantes automáticas que ya no corresponden a ninguna combinación
    existingByKey.forEach((row) => row.remove());

    reindexVariantRows(container, emptyState);
  }

  function addManualVariant() {
    const rows = container.querySelectorAll('.variant-row');
    const nextIndex = rows.length;

    const baseSku = baseSkuInput ? baseSkuInput.value : '';

    const variant = {
      id: null,
      sku: buildSkuSuggestion(baseSku, []),
      price: null,
      stock: 0,
      status: true,
      featuresMeta: [],
    };

    buildVariantRowFromTemplate({
      index: nextIndex,
      variant,
      container,
      emptyState,
    });
  }

  function hydrateInitialVariants() {
    if (!initialVariants.length) return;

    initialVariants.forEach((row, idx) => {
      const featuresMeta = (row.features || []).map((feat) => {
        const meta = index.featureToOption.get(feat.id);
        if (meta) return meta;
        return {
          optionId: feat.option_id,
          optionName: '',
          featureId: feat.id,
          featureValue: feat.value,
        };
      }).filter(Boolean);

      const variant = {
        id: row.id,
        sku: row.sku,
        price: row.price,
        stock: row.stock,
        status: !!row.status,
        featuresMeta,
      };

      buildVariantRowFromTemplate({
        templateHtml,
        index: idx,
        variant,
        container,
        emptyState,
      });
    });
  }

  function handleContainerClick(event) {
    const button = event.target.closest('[data-action="remove-variant"]');
    if (!button) return;

    const row = button.closest('.variant-row');
    if (!row) return;

    row.remove();
    reindexVariantRows(container, emptyState);
  }

  container.addEventListener('click', handleContainerClick);

  if (addButton) {
    addButton.addEventListener('click', (event) => {
      event.preventDefault();
      addManualVariant();
    });
  }

  if (generateButton) {
    generateButton.addEventListener('click', (event) => {
      event.preventDefault();
      generateVariants();
    });
  }

  renderOptionsUi();
  hydrateInitialVariants();
  reindexVariantRows(container, emptyState);

  return {
    regenerate: generateVariants,
    addVariant: addManualVariant,
  };
}

// Inicialización automática de respaldo por si no se llama
// explícitamente a window.initProductVariantsManager desde Blade.
document.addEventListener('DOMContentLoaded', () => {
  const autoContainer = document.getElementById('variantsContainer');
  if (!autoContainer) return;

  if (autoContainer.dataset.variantsManagerInitialized === '1') {
    return;
  }

  initProductVariantsManager({
    containerId: 'variantsContainer',
    emptyStateId: 'variantsEmpty',
    addButtonId: 'addVariantBtn',
    templateId: 'variantRowTemplate',
    optionsContainerId: 'productOptionsContainer',
    generateButtonId: 'generateVariantsBtn',
    baseSkuInputId: 'sku',
  });
});
