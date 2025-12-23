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

function buildOptionsIndex(options) {
  const byId = new Map();
  const featureToOption = new Map();

  options.forEach((opt) => {
    const option = {
      id: opt.id,
      name: opt.name,
      features: Array.isArray(opt.features) ? opt.features : [],
    };
    byId.set(option.id, option);
    option.features.forEach((feat) => {
      featureToOption.set(feat.id, {
        optionId: option.id,
        optionName: option.name,
        featureId: feat.id,
        featureValue: feat.value,
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
  const byOption = new Map();

  featuresMeta.forEach((meta) => {
    if (!byOption.has(meta.optionName)) {
      byOption.set(meta.optionName, []);
    }
    byOption.get(meta.optionName).push(meta.featureValue);
  });

  const parts = [];
  byOption.forEach((values, optionName) => {
    parts.push(`${optionName}: ${values.join(', ')}`);
  });

  return parts.join(' / ');
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

function buildVariantRowFromTemplate({
  templateHtml,
  index,
  variant,
  container,
  emptyState,
}) {
  let html = templateHtml
    .replace(/__INDEX__/g, String(index))
    .replace(/__ID__/g, String(variant.id || ''))
    .replace(/__SKU__/g, String(variant.sku || ''))
    .replace(/__PRICE__/g, variant.price != null ? String(variant.price) : '')
    .replace(/__STOCK__/g, variant.stock != null ? String(variant.stock) : '')
    .replace(/__STATUS_CHECKED__/g, variant.status ? 'checked' : '')
    .replace(/__OPTIONS_LABEL__/g, buildVariantLabel(variant.featuresMeta || []));

  const wrapper = document.createElement('div');
  wrapper.innerHTML = html.trim();
  const row = wrapper.firstElementChild;
  if (!row) return null;

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

  container.appendChild(row);
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

  const emptyState = emptyStateId ? document.getElementById(emptyStateId) : null;
  const addButton = addButtonId ? document.getElementById(addButtonId) : null;
  const optionsContainer = optionsContainerId ? document.getElementById(optionsContainerId) : null;
  const generateButton = generateButtonId ? document.getElementById(generateButtonId) : null;
  const baseSkuInput = baseSkuInputId ? document.getElementById(baseSkuInputId) : null;
  const templateNode = templateId ? document.getElementById(templateId) : null;

  const templateHtml = templateNode ? templateNode.textContent.trim() : '';
  if (!templateHtml) {
    console.warn('[product-variants-manager] Plantilla de variante no encontrada.');
  }

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

      opt.features.forEach((feat) => {
        const pill = document.createElement('label');
        pill.className = 'product-option-feature-pill';

        const cb = document.createElement('input');
        cb.type = 'checkbox';
        cb.className = 'feature-toggle';
        cb.value = String(feat.id);
        cb.checked = hasAnyFeature ? usedSet.has(feat.id) : false;

        const span = document.createElement('span');
        span.textContent = feat.value;

        pill.appendChild(cb);
        pill.appendChild(span);
        featuresWrapper.appendChild(pill);
      });

      card.appendChild(featuresWrapper);
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
        }
      });

      featuresWrapper.addEventListener('change', (event) => {
        const target = event.target;
        if (!(target instanceof HTMLInputElement) || !target.classList.contains('feature-toggle')) return;

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
    container.innerHTML = '';
    reindexVariantRows(container, emptyState);
  }

  function generateVariants() {
    const sets = collectOptionFeatureSets();
    const combos = buildCartesianProduct(sets, index);

    clearVariants();

    if (!combos.length) {
      return;
    }

    const baseSku = baseSkuInput ? baseSkuInput.value : '';

    combos.forEach((combo, idx) => {
      const featuresMeta = combo.features;
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
        templateHtml,
        index: idx,
        variant,
        container,
        emptyState,
      });
    });
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
      templateHtml,
      index: nextIndex,
      variant,
      container,
      emptyState,
    });
  }

  function hydrateInitialVariants() {
    if (!initialVariants.length || !templateHtml) return;

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
