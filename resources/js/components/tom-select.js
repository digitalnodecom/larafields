/**
 * TomSelect initialization and utilities
 * 
 * This file contains the JavaScript code for initializing and managing TomSelect instances.
 * It's extracted from the tom-select.blade.php component to avoid code duplication
 * when the component is rendered multiple times.
 */

/**
 * Initialize a TomSelect instance
 * 
 * @param {HTMLElement} element - The select element to initialize TomSelect on
 * @param {Object} options - The options for the select
 * @param {Array} items - The current value(s) of the select
 * @param {Function} getCleanOptions - Function to get cleaned options
 * @param {boolean} canCreate - Whether new options can be created
 * @param {Function} onChange - Function to call when the value changes
 * @returns {Object} - The TomSelect instance
 */
function initializeTomSelect(element, options, items, getCleanOptions, canCreate = false, onChange) {
  // Initialize TomSelect
  const tomSelect = new TomSelect(element, {
    options: getCleanOptions(),
    create: canCreate,
    items: items || [],
    valueField: 'value',
    labelField: 'label',
    searchField: 'label',
    plugins: ['remove_button'],
    onChange: onChange,
    onFocus: function() {
      this.addOptions(getCleanOptions());
    }
  });

  return tomSelect;
}

/**
 * Handle changes to the selectValue
 * 
 * @param {Object} tomSelect - The TomSelect instance
 * @param {string|Array} newValue - The new value
 */
function handleSelectValueChange(tomSelect, newValue) {
  if (!tomSelect) return;

  if (newValue === null) {
    tomSelect.clear(true);
  } else if (newValue !== tomSelect.getValue()) {
    if (Array.isArray(newValue)) {
      newValue.forEach(val => {
        const existingOption = tomSelect.options[val];
        if (!existingOption) {
          tomSelect.addOption({value: val, label: val});
        }
      });
    }

    tomSelect.setValue(newValue);
  }
}

/**
 * Handle changes to the options
 * 
 * @param {Object} tomSelect - The TomSelect instance
 * @param {Function} getCleanOptions - Function to get cleaned options
 */
function handleOptionsChange(tomSelect, getCleanOptions) {
  if (!tomSelect) return;

  const currentValues = tomSelect.getValue();
  tomSelect.clearOptions();
  tomSelect.addOptions(getCleanOptions());
  tomSelect.setValue(currentValues);
}

// Export the functions for use in other files
export { 
  initializeTomSelect,
  handleSelectValueChange,
  handleOptionsChange
};
