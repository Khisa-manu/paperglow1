<x-layouts.guest title="Sign In">
    <div class="bg-white p-8 rounded-xl border border-slate-200 shadow-sm">
        <h2 class="text-xl font-bold font-heading text-slate-900 mb-6">Sign in to your account</h2>
        
        <form action="{{ route('login') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label for="email" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Email Address</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" placeholder="you@company.com" required
                    class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
            </div>

            <div>
                <div class="flex justify-between items-center mb-1">
                    <label for="password" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">Password</label>
                </div>
                <input type="password" name="password" id="password" placeholder="••••••••" required
                    class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
            </div>

            <div class="flex items-center justify-between text-sm">
                <label class="flex items-center text-slate-600">
                    <input type="checkbox" name="remember" class="rounded border-slate-300 text-red-600 focus:ring-red-500 mr-2">
                    Remember me
                </label>
            </div>

            <button type="submit"
                class="w-full py-2.5 px-4 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg text-sm transition-colors shadow-sm">
                Sign In to Workspace
            </button>
        </form>

        <div class="mt-6 pt-6 border-t border-slate-100 text-center text-sm text-slate-500">
            Don't have an organization?
            <a href="{{ route('register') }}" class="font-semibold text-red-600 hover:text-red-700 ml-1">Create organization</a>
        </div>
    </div>
</x-layouts.guest>
