@extends('layouts.admin')

@section('title', 'Users')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
    <div class="flex items-center gap-4">
        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-blue-600 flex items-center justify-center shadow-lg shadow-indigo-500/30 shrink-0">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
        </div>
        <div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white">User Management</h1>
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mt-0.5">Manage staff, admins, and customers</p>
        </div>
    </div>
    @can('users.create')
    <a href="{{ route('admin.users.create') }}" class="px-5 py-2.5 rounded-2xl bg-indigo-600 text-white text-sm font-bold shadow-lg shadow-indigo-600/30 hover:scale-105 active:scale-95 transition-all flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
        Add User
    </a>
    @endcan
</div>

<div class="glass-card rounded-[2rem] border border-slate-200/50 dark:border-slate-700/50 bg-white/40 dark:bg-slate-900/40 shadow-sm flex flex-col min-h-[500px]">
    <div class="flex-1 overflow-x-auto overflow-y-hidden rounded-[2rem]">
        <table class="w-full text-left whitespace-nowrap">
            <thead>
                <tr class="bg-slate-50/80 dark:bg-slate-800/80 border-b border-slate-200/50 dark:border-slate-700/50 backdrop-blur-md">
                    <th class="px-6 py-4 text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest sticky top-0 z-10 w-full sm:w-auto">User</th>
                    <th class="px-6 py-4 text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest sticky top-0 z-10 hidden md:table-cell">Contact</th>
                    <th class="px-6 py-4 text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest sticky top-0 z-10 hidden sm:table-cell">Role & Status</th>
                    <th class="px-6 py-4 text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest sticky top-0 z-10 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100/50 dark:divide-slate-800/50">
                @forelse($users as $user)
                <tr class="hover:bg-white/80 dark:hover:bg-slate-800/50 transition-colors duration-200 group">
                    <td class="px-6 py-5">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-[1.25rem] bg-gradient-to-br from-indigo-100 to-violet-200 dark:from-indigo-900/50 dark:to-violet-900/50 flex items-center justify-center text-lg font-black text-indigo-700 dark:text-indigo-300 shadow-sm shrink-0">
                                {{ substr($user->name, 0, 1) }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-bold text-slate-900 dark:text-white capitalize truncate">{{ $user->name }}</p>
                                <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400 mt-1 truncate md:hidden">{{ $user->email }}</p>
                            </div>
                        </div>
                        
                        <!-- Mobile Info -->
                        <div class="mt-3 flex items-center justify-between sm:hidden">
                            <span class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-widest rounded-lg {{ $user->role && $user->role->name === 'admin' ? 'bg-rose-100 dark:bg-rose-500/20 text-rose-700 dark:text-rose-400' : ($user->role && $user->role->name === 'staff' ? 'bg-blue-100 dark:bg-blue-500/20 text-blue-700 dark:text-blue-400' : 'bg-slate-100 dark:bg-slate-500/20 text-slate-700 dark:text-slate-400') }}">
                                {{ $user->role ? $user->role->name : 'Customer' }}
                            </span>
                            
                            @if($user->isBanned())
                                <span class="px-2.5 py-1 text-[10px] font-bold rounded-lg bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-400 flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>Banned</span>
                            @else
                                <span class="px-2.5 py-1 text-[10px] font-bold rounded-lg bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-400 flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>Active</span>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-5 hidden md:table-cell">
                        <div class="flex flex-col gap-1.5">
                            <span class="text-[13px] font-medium text-slate-700 dark:text-slate-300 flex items-center gap-2">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                {{ $user->email }}
                            </span>
                            @if($user->phone)
                            <span class="text-[12px] font-medium text-slate-500 dark:text-slate-400 flex items-center gap-2">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                {{ $user->phone }}
                            </span>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-5 hidden sm:table-cell">
                        <div class="flex flex-col gap-2 items-start">
                            <span class="px-3 py-1.5 text-[10px] font-bold uppercase tracking-widest rounded-xl {{ $user->role && $user->role->name === 'admin' ? 'bg-rose-100 dark:bg-rose-500/20 text-rose-700 dark:text-rose-400' : ($user->role && $user->role->name === 'staff' ? 'bg-blue-100 dark:bg-blue-500/20 text-blue-700 dark:text-blue-400' : 'bg-slate-100 dark:bg-slate-500/20 text-slate-700 dark:text-slate-400') }}">
                                {{ $user->role ? $user->role->name : 'Customer' }}
                            </span>
                            @if($user->isBanned())
                                <span class="px-2.5 py-1 text-[10px] font-bold rounded-lg bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-400 flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>Banned</span>
                            @else
                                <span class="px-2.5 py-1 text-[10px] font-bold rounded-lg bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-400 flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>Active</span>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-5 text-right">
                        @if(!$user->role || (!$user->isAdmin() || Auth::guard('admin')->user()->isSuperAdmin()))
                        <div class="flex items-center justify-end gap-2">
                            @can('users.edit')
                            <a href="{{ route('admin.users.edit', $user->id) }}" class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-amber-100 hover:text-amber-600 dark:hover:bg-amber-500/20 dark:hover:text-amber-400 flex items-center justify-center transition-colors" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                            </a>
                            @endcan
                            
                            @if($user->isBanned())
                                @can('users.ban')
                                <form action="{{ route('admin.users.unban', $user->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-100 dark:hover:bg-emerald-500/20 flex items-center justify-center transition-colors shadow-inner" title="Unban User">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </button>
                                </form>
                                @endcan
                            @else
                                @can('users.ban')
                                <button type="button" onclick="showBanModal({{ $user->id }}, '{{ addslashes($user->name) }}')" class="w-9 h-9 rounded-xl bg-orange-50 dark:bg-orange-500/10 text-orange-600 dark:text-orange-400 hover:bg-orange-100 dark:hover:bg-orange-500/20 flex items-center justify-center transition-colors shadow-inner" title="Ban User">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                </button>
                                @endcan
                            @endif

                            @can('users.delete')
                            <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-rose-100 hover:text-rose-600 dark:hover:bg-rose-500/20 dark:hover:text-rose-400 flex items-center justify-center transition-colors" title="Delete User" onclick="return confirm('Delete {{ addslashes($user->name) }} forever?')">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                            @endcan
                        </div>
                        @else
                        <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Protected</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-16 text-center">
                        <div class="flex flex-col items-center justify-center text-slate-400 dark:text-slate-500">
                            <div class="w-16 h-16 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center mb-4 text-slate-400 dark:text-slate-500 shadow-inner">
                                <svg class="w-8 h-8 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            </div>
                            <p class="text-sm font-bold text-slate-600 dark:text-slate-400">No users found</p>
                            <p class="text-xs mt-1">There are no users to display matching your criteria.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($users->hasPages())
    <div class="px-6 py-4 border-t border-slate-200/50 dark:border-slate-700/50 bg-slate-50/50 dark:bg-slate-800/30 rounded-b-[2rem]">
        {{ $users->links() }}
    </div>
    @endif
</div>

{{-- Modern Ban Modal --}}
<dialog id="banModal" class="modal m-auto p-0 rounded-[2rem] shadow-2xl bg-transparent backdrop:bg-slate-900/50 backdrop:backdrop-blur-sm border-0 w-full max-w-sm">
    <div class="glass-card p-6 border dark:border-slate-700 text-left bg-white/95 dark:bg-slate-900/95 shadow-2xl">
        <div class="w-12 h-12 rounded-2xl bg-orange-100 dark:bg-orange-500/20 text-orange-600 dark:text-orange-400 flex items-center justify-center mb-4">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        </div>
        <h3 class="text-xl font-black text-slate-900 dark:text-white mb-1">Ban User</h3>
        <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-6">Are you sure you want to ban <span id="banUserName" class="font-bold text-slate-800 dark:text-slate-200"></span>?</p>
        
        <form id="banForm" method="POST">
            @csrf
            <div class="mb-6">
                <label for="ban_reason" class="block text-[10px] font-black uppercase tracking-widest text-slate-600 dark:text-slate-400 mb-2">Reason (Optional)</label>
                <input type="text" name="ban_reason" id="ban_reason" class="w-full bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-orange-500 outline-none transition-all placeholder:text-slate-400" placeholder="E.g., Violating terms of service">
            </div>
            <div class="flex items-center gap-3">
                <button type="submit" class="flex-1 px-4 py-2.5 rounded-xl bg-orange-600 text-white text-sm font-bold shadow-sm hover:bg-orange-700 active:scale-95 transition-all">Ban User</button>
                <button type="button" onclick="document.getElementById('banModal').close()" class="flex-1 px-4 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-sm font-bold shadow-sm hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">Cancel</button>
            </div>
        </form>
    </div>
</dialog>

<script>
function showBanModal(userId, userName) {
    document.getElementById('banUserName').textContent = userName;
    document.getElementById('banForm').action = '/admin/users/' + userId + '/ban';
    document.getElementById('banModal').showModal();
}
</script>
@endsection
