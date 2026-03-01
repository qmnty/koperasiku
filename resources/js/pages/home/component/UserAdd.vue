<template>
  <transition name="fade-slide">
    <div v-if="modals.user" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
      <div @click="modals.user = false" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>

      <div class="relative bg-white w-full max-w-md rounded-[2.5rem] shadow-2xl p-8 overflow-hidden animate-in zoom-in-95 duration-200">
        <div class="absolute -right-6 -top-6 w-24 h-24 bg-emerald-50 rounded-full"></div>
        
        <div class="relative">
          <div class="flex justify-between items-start mb-6">
            <div>
              <h2 class="text-xl font-black text-slate-800">Tambah Pengguna</h2>
              <p class="text-xs text-slate-400 font-medium">Berikan akses sistem ke tim baru</p>
            </div>
            <button @click="modals.user = false" class="cursor-pointer text-slate-300 hover:text-slate-600 transition p-2">
              <i class="fa-solid fa-xmark text-lg"></i>
            </button>
          </div>

          <form @submit.prevent="handleAddUser" class="space-y-4">
            <div>
              <label class="block text-[10px] font-black text-slate-400 uppercase ml-2 mb-1 tracking-widest">Nama Lengkap</label>
              <input v-model="form.name" type="text" required placeholder="Contoh: Budi Santoso"
                class="w-full px-5 py-3 bg-slate-50 border border-slate-100 rounded-2xl text-sm font-bold focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 outline-none transition">
            </div>

            <div>
              <label class="block text-[10px] font-black text-slate-400 uppercase ml-2 mb-1 tracking-widest">Alamat Email</label>
              <input v-model="form.email" type="email" required placeholder="budi@koperasi.com"
                class="w-full px-5 py-3 bg-slate-50 border border-slate-100 rounded-2xl text-sm font-bold focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 outline-none transition">
            </div>

            <div>
              <label class="block text-[10px] font-black text-slate-400 uppercase ml-2 mb-1 tracking-widest">Password</label>
              <div class="relative">
                <input :type="showPassword ? 'text' : 'password'" v-model="form.password" required placeholder="••••••••"
                  class="w-full px-5 py-3 bg-slate-50 border border-slate-100 rounded-2xl text-sm font-bold focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 outline-none transition">
                <button type="button" @click="showPassword = !showPassword" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-emerald-600 transition">
                  <i :class="['fa-solid', showPassword ? 'fa-eye-slash' : 'fa-eye']"></i>
                </button>
              </div>
            </div>

            <div>
              <label class="block text-[10px] font-black text-slate-400 uppercase ml-2 mb-1 tracking-widest">Level Akses</label>
              <div class="grid grid-cols-3 gap-2">
                <button v-for="r in ['admin', 'manager', 'staff']" :key="r" type="button"
                  @click="form.role = r"
                  :class="['py-2.5 rounded-xl text-[10px] font-black uppercase transition border-2', 
                    form.role === r ? 'bg-emerald-600 border-emerald-600 text-white shadow-lg shadow-emerald-100' : 'bg-white border-slate-100 text-slate-400 hover:border-emerald-200']">
                  {{ r }}
                </button>
              </div>
            </div>

            <button type="submit" :disabled="loading"
              class="w-full mt-4 py-4 bg-emerald-600 text-white rounded-[1.5rem] font-black shadow-xl shadow-emerald-200 hover:bg-emerald-700 disabled:opacity-50 transition-all flex items-center justify-center gap-2">
              <i v-if="loading" class="fa-solid fa-circle-notch animate-spin"></i>
              {{ loading ? 'MENYIMPAN...' : 'SIMPAN PENGGUNA' }}
            </button>
          </form>
        </div>
      </div>
    </div>
  </transition>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import api from '@/lib/api';

const props = defineProps({
  modals: Object,
  data: Object
});

const emit = defineEmits(['success']);

const loading = ref(false);
const showPassword = ref(false);
const form = reactive({
  name: '',
  email: '',
  password: '',
  role: 'staff'
});

const handleAddUser = async () => {
  loading.value = true;
  try {
    const res = await api.post('users', form);
    if (res.status === 201 || res.status === 200) {
      props.modals.user = false;
      emit('success'); // Memberitahu parent untuk refresh list user
    }
  } catch (e) {
    alert(e.response?.data?.message || 'Gagal menyimpan user');
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  if (props.data) {
    form.name = props.data.name;
    form.email = props.data.email;
  }
});
</script>

<style scoped>
.fade-slide-enter-active, .fade-slide-leave-active {
  transition: all 0.3s ease;
}
.fade-slide-enter-from, .fade-slide-leave-to {
  opacity: 0;
  transform: translateY(20px);
}
</style>