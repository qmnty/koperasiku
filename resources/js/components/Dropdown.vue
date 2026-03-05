<template>
  <div class="relative w-full">
    <label v-if="label" class="block text-xs font-bold text-slate-500 mb-2 ml-2 uppercase tracking-wide">
      {{ label }}
    </label>

    <div class="relative">
      <input 
        type="text"
        v-model="searchQuery"
        @focus="isOpen = true"
        @input="handleInput"
        :placeholder="placeholder"
        :class="[
          'w-full border-2 border-slate-100 rounded-2xl py-2 px-5 font-bold bg-slate-50 outline-none focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition pr-12',
          props.baseClass
        ]"
      >
      
      <div class="absolute right-5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
        <i v-if="modelValue" @click.stop="clearSelection" class="fa-solid fa-circle-xmark cursor-pointer pointer-events-auto hover:text-rose-500"></i>
        <i v-else :class="['fa-solid transition-transform duration-300', isOpen ? 'fa-chevron-up' : 'fa-chevron-down text-xs']"></i>
      </div>
    </div>

    <transition name="fade-slide">
      <div v-if="isOpen && filteredOptions.length > 0" class="absolute z-[60] w-full mt-2 bg-white border border-slate-100 rounded-2xl shadow-xl max-h-60 overflow-y-auto overflow-x-hidden p-2">
        <div 
          v-for="opt in filteredOptions" 
          :key="opt[valueKey]"
          @click="selectOption(opt)"
          class="flex flex-col p-3 hover:bg-emerald-50 rounded-xl cursor-pointer transition mb-1 last:mb-0"
        >
          <span class="font-bold text-slate-800 text-sm">{{ opt[labelKey] }}</span>
          
          <span v-if="subKey && opt[subKey]" class="text-[10px] text-slate-400 font-medium uppercase tracking-tighter">
            {{ subLabelPrefix }}{{ opt[subKey] }}
          </span>
        </div>
      </div>
      
      <div v-else-if="isOpen && searchQuery" class="absolute z-[60] w-full mt-2 bg-white border border-slate-100 rounded-2xl shadow-lg p-6 text-center">
        <p class="text-xs text-slate-400 italic">Data tidak ditemukan</p>
      </div>
    </transition>

    <div v-if="isOpen" @click="isOpen = false" class="fixed inset-0 z-50 bg-transparent"></div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';

const props = defineProps({
  modelValue: [Number, String],
  options: { type: Array, default: () => [] },
  placeholder: { type: String, default: 'Cari...' },
  label: String,
  baseClass: { type: String, default: '' },
  
  // PROPS BARU UNTUK FLEKSIBILITAS
  valueKey: { type: String, default: 'id' },        // Key untuk value (ID)
  labelKey: { type: String, default: 'nama' },      // Key untuk judul item
  subKey: { type: String, default: '' },            // Key untuk sub-judul (optional)
  subLabelPrefix: { type: String, default: '' },    // Prefix tambahan (ex: 'PJ: ')
  searchFields: { type: Array, default: () => ['nama', 'pj'] } // Kolom apa saja yang bisa dicari
});

const emit = defineEmits(['update:modelValue', 'change']);
const isOpen = ref(false);
const searchQuery = ref('');

// Sync input dengan modelValue (untuk Edit Mode)
watch(() => props.modelValue, (newVal) => {
  if (!newVal) {
    searchQuery.value = '';
  } else {
    const selected = props.options.find(o => o[props.valueKey] == newVal);
    if (selected) searchQuery.value = selected[props.labelKey];
  }
}, { immediate: true, deep: true });

const filteredOptions = computed(() => {
  const query = searchQuery.value.toLowerCase();
  if (!query) return props.options;

  return props.options.filter(o => {
    // Mencari di semua fields yang didaftarkan di props searchFields
    return props.searchFields.some(field => {
      const val = o[field];
      return val && val.toString().toLowerCase().includes(query);
    });
  });
});

const selectOption = (opt) => {
  searchQuery.value = opt[props.labelKey];
  emit('update:modelValue', opt[props.valueKey]);
  emit('change', opt);
  isOpen.value = false;
};

const clearSelection = () => {
  searchQuery.value = '';
  emit('update:modelValue', '');
  isOpen.value = false;
};

const handleInput = () => {
  if (searchQuery.value === '') {
    emit('update:modelValue', '');
  }
};
</script>