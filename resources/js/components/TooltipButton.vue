<template>
  <div 
    :class="[
      'relative flex flex-col items-center group w-fit',
      baseClass
    ]">
    <button 
      v-bind="$attrs" 
      class="flex flex-col items-center justify-center transition-all duration-200"
    >
      <slot />
    </button>

    <div 
      class="absolute bottom-full mb-2 hidden group-hover:flex items-center justify-center px-2 py-1 bg-gray-800 text-white text-[10px] rounded shadow-lg whitespace-nowrap z-50 pointer-events-none"
    >
      {{ text }}
      <div class="absolute top-full left-1/2 -ml-1 border-4 border-transparent border-t-gray-800"></div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  text: {
    type: String,
    required: true
  },
  baseClass: {
    type: String,
    default: ''
  }
});
</script>