<script setup>
import { ref, computed, reactive, watch, onMounted } from 'vue';
import { 
  Users, Wallet, HandCoins, History, PlusCircle, Search,
  ArrowUpRight, ArrowDownLeft, Info, Calendar, Clock,
  X, AlertCircle, ChevronRight, CheckCircle2
} from 'lucide-vue-next';
import api from '@/lib/api';
import PinjamanAktif from './component/PinjamanAktif.vue';
import AnggotaList from './component/AnggotaList.vue';
import TransaksiList from './component/TransaksiList.vue';
import UserList from './component/UserList.vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
  'user': Object
})
// --- STATE DATA ---
const activeTab = ref('anggota');
const members = ref([]);

// --- MODAL STATES ---
const modals = reactive({
  member: false,
  loan: false,
  saving: false,
  tarik: false,
  user: false,
  installment: false,
  memberDetail: null
});

async function getAnggota() {
    let response
    response = await api.get('anggota');
    return response.data.data;
}
</script>

<template>
  <div class="min-h-screen bg-slate-50 font-sans text-slate-900 pb-20 md:pb-0">
    <nav class="fixed bottom-0 w-full bg-white border-slate-200 border-t md:top-0 md:bottom-auto md:border-b z-40 shadow-sm">
      <div class="max-w-6xl mx-auto px-4 flex justify-around md:justify-start items-center h-16 gap-2 md:gap-8">
        <div class="hidden md:block font-bold text-emerald-600 text-xl mr-8">KoperasiKita</div>
        <template v-for="tab in ['anggota', 'pinjaman', 'transaksi', 'users']" :key="tab">
          <button 
            v-if="tab !== 'users' || $page.props.auth.user.role === 'admin'"
            @click="activeTab = tab"
            :class="[
              'flex cursor-pointer flex-col md:flex-row items-center gap-1 md:gap-2 p-2 transition text-xs capitalize', 
              activeTab === tab ? 'text-emerald-600 border-t-2 md:border-t-0 md:border-b-2 border-emerald-600' : 'text-slate-500'
            ]"
          >
            <component 
              :is="tab === 'anggota' ? Users : tab === 'pinjaman' ? HandCoins : tab === 'users' ? Users : History" 
              :size="20" 
            />
            <span class="font-semibold">{{ tab }}</span>
          </button>

        </template>
        <div class="flex items-center ml-2">
          <Link 
            href="/logout" 
            method="post" 
            as="button" 
            class="flex cursor-pointer items-center gap-2 px-3 py-2 rounded-lg text-slate-500 hover:text-red-600 hover:bg-red-50 transition text-xs font-semibold"
          >
            <span class="hidden md:inline">Keluar</span>
          </Link>
        </div>
      </div>
    </nav>

    <main class="max-w-6xl mx-auto p-4 md:pt-24 pt-8">
      <section v-if="activeTab === 'anggota'" class="fade-in">
        <AnggotaList 
          :modals="modals"
          :user="props.user"
        />
      </section>

      <section v-if="activeTab === 'pinjaman'" class="fade-in">
        <PinjamanAktif
          :members="members"
          :modals="modals"
          :user="props.user"
        />
      </section>

      <section v-if="activeTab === 'transaksi'" class="fade-in">
        <TransaksiList
          v-if="activeTab === 'transaksi'"
        />
      </section>

      <section v-if="activeTab === 'users' && props.user.role === 'admin'" class="fade-in">
        <UserList
          v-if="activeTab === 'users'"
          :modals="modals"
        />
      </section>
    </main>
  </div>
</template>

<style scoped>
.fade-in { animation: fadeIn 0.4s ease-out; }
.scale-in { animation: scaleIn 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
@keyframes scaleIn { from { opacity: 0; transform: scale(0.9); } to { opacity: 1; transform: scale(1); } }
</style>