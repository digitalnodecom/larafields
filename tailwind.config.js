/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/views/**/*.blade.php",
    "./resources/views/*.blade.php",
    "./resources/views/components/*.blade.php",
    "./resources/views/livewire/*.blade.php",
  ],
  theme: {
    extend: {
      colors: {
        // Add your custom colors here if needed
      },
      spacing: {
        // Add custom spacing if needed
      },
    },
  },
  plugins: [
    // Add forms plugin for better form styling
    require('@tailwindcss/forms')({
      strategy: 'class', // only generate classes when explicitly used
    }),
  ],
  // Production optimizations
  future: {
    removeDeprecatedGapUtilities: true,
    purgeLayersByDefault: true,
  },
  // Disable variants that are rarely used to reduce CSS size
  variants: {
    extend: {},
  },
};
