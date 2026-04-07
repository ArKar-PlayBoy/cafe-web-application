@extends('layouts.app')

@section('title', 'Connected Accounts')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Connected Accounts</h1>
        <a href="{{ route('profile.edit') }}" class="text-green-600 hover:underline">
            ← Back to Profile
        </a>
    </div>

    @if(session('success'))
    <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-lg text-green-700 dark:text-green-300">
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-lg text-red-700 dark:text-red-300">
        {{ session('error') }}
    </div>
    @endif

    @if($errors->has('unlink'))
    <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-lg text-red-700 dark:text-red-300">
        {{ $errors->first('unlink') }}
    </div>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 mb-6">
        <p class="text-gray-600 dark:text-gray-400 mb-4">
            Manage your linked social login accounts. You can connect multiple providers to your account.
        </p>

        @if($socialAccounts->isEmpty())
        <p class="text-gray-500 dark:text-gray-400">No connected accounts.</p>
        @else
        <div class="space-y-4">
            @foreach($socialAccounts as $account)
            <div class="flex items-center justify-between p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-600" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-medium text-gray-900 dark:text-white">{{ ucfirst($account->provider) }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $account->provider_email }}</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('user.social.unlink', $account->provider) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="text-red-600 hover:text-red-700 text-sm font-medium"
                            onclick="return confirm('Are you sure you want to unlink this account?');">
                        Unlink
                    </button>
                </form>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    @if(!$hasCustomPassword)
    <div class="bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-xl p-6">
        <div class="flex items-start gap-4">
            <svg class="w-6 h-6 text-blue-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <h3 class="font-medium text-blue-900 dark:text-blue-100">Set a Password</h3>
                <p class="text-sm text-blue-700 dark:text-blue-300 mt-1">
                    You signed up with Google and don't have a password set. 
                    Set a password to ensure you can always access your account.
                </p>
                <a href="{{ route('profile.edit') }}#password-section" 
                   class="inline-block mt-3 text-sm font-medium text-blue-600 hover:text-blue-700 hover:underline">
                    Set Password →
                </a>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
