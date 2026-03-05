<template>
  <div class="min-h-screen bg-slate-50 flex items-center justify-center p-4 font-sans">
    <div class="fixed top-0 left-0 w-full h-full overflow-hidden pointer-events-none z-0">
      <div class="absolute -top-24 -left-24 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl"></div>
      <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-blue-500/10 rounded-full blur-3xl"></div>
    </div>

    <div class="w-full max-w-[440px] relative z-10">
      <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-20 h-20 bg-emerald-600 rounded-[2.5rem] shadow-xl shadow-emerald-200 mb-4 rotate-3 hover:rotate-0 transition-transform duration-500">
          <i class="fa-solid fa-leaf text-3xl text-white"></i>
        </div>
        <h1 class="text-3xl font-black text-slate-800 tracking-tight">Koperasi <span class="text-emerald-600">Digital</span></h1>
        <p class="text-slate-400 font-medium mt-1">Silakan masuk ke panel manajemen</p>
      </div>

      <div class="bg-white/80 backdrop-blur-xl border border-white rounded-[3rem] p-10 shadow-2xl shadow-slate-200/50">
        <form @submit.prevent="handleLogin" class="space-y-5">
          <div>
            <label class="block text-[10px] font-black text-slate-400 uppercase ml-4 mb-2 tracking-[0.2em]">Alamat Email</label>
            <div class="relative group">
              <i class="fa-solid fa-envelope absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-emerald-500 transition-colors"></i>
              <input 
                v-model="form.email"
                type="email" 
                required 
                placeholder="user@example.com"
                class="w-full pl-14 pr-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl text-sm font-bold text-slate-700 outline-none focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 focus:bg-white transition-all"
              >
            </div>
          </div>

          <div>
            <div class="flex justify-between items-center mb-2 px-4">
              <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Kata Sandi</label>
              <!-- <a href="#" class="text-[10px] font-black text-emerald-600 uppercase hover:underline">Lupa?</a> -->
            </div>
            <div class="relative group">
              <i class="fa-solid fa-lock absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-emerald-500 transition-colors"></i>
              <input 
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'" 
                required 
                placeholder="••••••••"
                class="w-full pl-14 pr-14 py-4 bg-slate-50 border border-slate-100 rounded-2xl text-sm font-bold text-slate-700 outline-none focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 focus:bg-white transition-all"
              >
              <button 
                type="button" 
                @click="showPassword = !showPassword"
                class="absolute right-5 top-1/2 -translate-y-1/2 text-slate-300 hover:text-slate-500 transition"
              >
                <i :class="['fa-solid', showPassword ? 'fa-eye-slash' : 'fa-eye']"></i>
              </button>
            </div>
          </div>

          <label class="flex items-center gap-3 px-2 cursor-pointer group">
            <div class="relative">
              <input type="checkbox" v-model="form.remember" class="sr-only">
              <div :class="['w-5 h-5 rounded-md border-2 transition-all flex items-center justify-center', form.remember ? 'bg-emerald-600 border-emerald-600' : 'bg-white border-slate-200 group-hover:border-emerald-200']">
                <i v-if="form.remember" class="fa-solid fa-check text-[10px] text-white"></i>
              </div>
            </div>
            <span class="text-xs font-bold text-slate-500">Ingat saya di perangkat ini</span>
          </label>

          <button 
            type="submit" 
            :disabled="loading"
            class="w-full py-4 bg-emerald-600 text-white rounded-[1.8rem] font-black shadow-xl shadow-emerald-200 hover:bg-emerald-700 hover:shadow-emerald-300 active:scale-[0.98] disabled:opacity-50 transition-all flex items-center justify-center gap-3"
          >
            <i v-if="loading" class="fa-solid fa-circle-notch animate-spin text-lg"></i>
            <span>{{ loading ? 'MENGECEK AKSES...' : 'MASUK SEKARANG' }}</span>
          </button>
        </form>
      </div>

      <p class="text-center mt-8 text-xs font-bold text-slate-400">
        &copy; 2024 Koperasi Digital v2.0 • Build with <i class="fa-solid fa-heart text-rose-400"></i>
      </p>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue';
import api from '@/lib/api';

const loading = ref(false);
const showPassword = ref(false);

const form = reactive({
  email: '',
  password: '',
  remember: false
});

const handleLogin = async () => {
  loading.value = true;
  try {
    const res = await api.post('/login', form);
    
    // Simpan token (tergantung sistem auth Anda, misal: Sanctum)
    localStorage.setItem('token', res.data.token);
    
    // Redirect ke dashboard
    document.location.href = '/';
  } catch (e) {
    alert(e.response?.data?.message || 'Email atau password salah!');
  } finally {
    loading.value = false;
  }
};
</script>