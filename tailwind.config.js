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
      colors: {},
      spacing: {},
    },
  },
  plugins: [
    require('@tailwindcss/forms')({
      strategy: 'class',
    }),
  ],
  future: {
    removeDeprecatedGapUtilities: true,
    purgeLayersByDefault: true,
  },
  variants: {
    extend: {},
  },
};
