<template>
  <div class="p-6 space-y-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
      <div>
        <h1 class="text-2xl font-black text-slate-800 tracking-tight">Manajemen User</h1>
        <p class="text-sm text-slate-500 font-medium">Total {{ filteredUsers.length }} pengguna sistem</p>
      </div>
      
      <button @click="createUser" class="cursor-pointer bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-2xl font-bold text-sm shadow-lg shadow-emerald-200 transition flex items-center gap-2">
        <i class="fa-solid fa-user-plus"></i>
        Tambah User
      </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 bg-white p-4 rounded-3xl border border-slate-200 shadow-sm">
      <div class="relative md:col-span-2">
        <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
        <input 
          v-model="search" 
          type="text" 
          placeholder="Cari nama atau email..." 
          class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-100 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 outline-none transition"
        >
      </div>
      
      <!-- <select v-model="filterRole" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-100 rounded-xl text-sm font-bold text-slate-600 outline-none focus:ring-2 focus:ring-emerald-500 cursor-pointer">
        <option value="">Semua Role</option>
        <option value="admin">Admin</option>
        <option value="staff">Staff</option>
        <option value="manager">Manager</option>
      </select> -->
    </div>

    <div v-if="loading" class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div v-for="n in 3" :key="n" class="h-40 bg-slate-100 animate-pulse rounded-3xl"></div>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div v-for="user in filteredUsers" :key="user.id" class="group bg-white border border-slate-200 p-5 rounded-3xl hover:shadow-xl hover:border-emerald-100 transition-all duration-300 relative overflow-hidden">
        <div class="absolute -right-4 -top-4 w-16 h-16 bg-slate-50 rounded-full group-hover:bg-emerald-50 transition-colors"></div>
        
        <div class="flex items-center gap-4 mb-5 relative">
          <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white text-xl font-black shadow-lg shadow-emerald-100">
            {{ user.name.charAt(0).toUpperCase() }}
          </div>
          <div>
            <h3 class="font-bold text-slate-800 leading-tight">{{ user.name }}</h3>
            <p class="text-xs text-slate-400 font-medium">{{ user.email }}</p>
          </div>
        </div>

        <div class="flex justify-between items-center pt-4 border-t border-slate-50">
          <!-- <span :class="[
            'text-[10px] font-black px-3 py-1 rounded-full uppercase tracking-widest',
            roleColor(user.role)
          ]">
            {{ user.role }}
          </span> -->
          
          <div class="flex gap-2">
            <button @click="editUser(user)" class="cursor-pointer w-8 h-8 rounded-lg bg-slate-100 text-slate-400 hover:bg-amber-100 hover:text-amber-600 transition flex items-center justify-center">
              <i class="fa-solid fa-pen-to-square text-xs"></i>
            </button>
            <button @click="deleteUser(user.id)" class="cursor-pointer w-8 h-8 rounded-lg bg-slate-100 text-slate-400 hover:bg-rose-100 hover:text-rose-600 transition flex items-center justify-center">
              <i class="fa-solid fa-trash text-xs"></i>
            </button>
          </div>
        </div>
      </div>
    </div>

    <div v-if="!loading && filteredUsers.length === 0" class="py-20 text-center">
      <div class="text-slate-200 mb-4"><i class="fa-solid fa-users-slash text-6xl"></i></div>
      <p class="text-slate-400 italic font-medium">User tidak ditemukan</p>
    </div>
  </div>
  <UserAdd 
    v-if="modals.user"
    :modals="modals"
    :data="userData"
    :isEdit="isEdit"
    @success="fetchUsers"
  />
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import api from '@/lib/api';
import UserAdd from './UserAdd.vue';

const users = ref([]);
const loading = ref(true);
const search = ref('');
const filterRole = ref('');
const userData = ref(null);
const isEdit = ref(false)

const props = defineProps({
  modals: Object
})

const fetchUsers = async () => {
  loading.value = true;
  try {
    const res = await api.get('users');
    users.value = res.data;
  } catch (e) {
    console.error(e);
  } finally {
    loading.value = false;
  }
};

const filteredUsers = computed(() => {
  return users.value.filter(u => {
    const matchSearch = u.name.toLowerCase().includes(search.value.toLowerCase()) || 
                        u.email.toLowerCase().includes(search.value.toLowerCase());
    const matchRole = filterRole.value === '' || u.role === filterRole.value;
    return matchSearch && matchRole;
  });
});

const roleColor = (role) => {
  switch(role.toLowerCase()) {
    case 'admin': return 'bg-rose-50 text-rose-600';
    case 'manager': return 'bg-amber-50 text-amber-600';
    default: return 'bg-emerald-50 text-emerald-600';
  }
};

onMounted(fetchUsers);

const createUser = () => {
  props.modals.user = true
  isEdit.value = false
  userData.value = null;
}
const editUser = (user) => {
  userData.value = user;
  isEdit.value = true
  props.modals.user = true;
};

const deleteUser = (id) => {
  if(confirm('Hapus user ini?')) {
    api.delete(`users/${id}`).then(fetchUsers);
  }
};
</script>