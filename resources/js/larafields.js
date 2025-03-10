/**
 * Larafields JavaScript Entry Point
 * 
 * This file serves as the main entry point for all JavaScript code in the Larafields package.
 * It imports and exports all component-specific JavaScript modules.
 */

import { 
  initializeTomSelect,
  handleSelectValueChange,
  handleOptionsChange
} from './components/tom-select.js';

// Make functions available globally
window.Larafields = window.Larafields || {};
window.Larafields.tomSelect = {
  initialize: initializeTomSelect,
  handleSelectValueChange: handleSelectValueChange,
  handleOptionsChange: handleOptionsChange
};

// Export all functions for module usage
export {
  initializeTomSelect,
  handleSelectValueChange,
  handleOptionsChange
};
