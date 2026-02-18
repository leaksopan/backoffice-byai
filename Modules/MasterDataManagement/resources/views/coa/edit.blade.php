@extends('layouts.module')

@section('content')
    <div class="space-y-6">
        <h2 class="text-xl font-semibold text-slate-900">Edit Chart of Account</h2>

        @if($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('mdm.coa.update', $account) }}" method="POST" class="space-y-4 rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            @csrf
            @method('PUT')

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Kode (X-XX-XX-XX-XXX)</label>
                    <input type="text" name="code" value="{{ old('code', $account->code) }}" required class="mt-1 block w-full rounded-md border-slate-300 shadow-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Nama</label>
                    <input type="text" name="name" value="{{ old('name', $account->name) }}" required class="mt-1 block w-full rounded-md border-slate-300 shadow-sm">
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Kategori</label>
                    <select name="category" required class="mt-1 block w-full rounded-md border-slate-300 shadow-sm">
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}" {{ old('category', $account->category) == $cat ? 'selected' : '' }}>{{ ucfirst($cat) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Normal Balance</label>
                    <select name="normal_balance" required class="mt-1 block w-full rounded-md border-slate-300 shadow-sm">
                        @foreach($normalBalances as $nb)
                            <option value="{{ $nb }}" {{ old('normal_balance', $account->normal_balance) == $nb ? 'selected' : '' }}>{{ ucfirst($nb) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Parent Akun</label>
                <select name="parent_id" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm">
                    <option value="">— Tanpa Parent —</option>
                    @foreach($parentAccounts as $p)
                        <option value="{{ $p->id }}" {{ old('parent_id', $account->parent_id) == $p->id ? 'selected' : '' }}>{{ $p->code }} - {{ $p->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">External Code</label>
                <input type="text" name="external_code" value="{{ old('external_code', $account->external_code) }}" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Deskripsi</label>
                <textarea name="description" rows="2" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm">{{ old('description', $account->description) }}</textarea>
            </div>

            <div class="flex items-center gap-4">
                <label class="inline-flex items-center">
                    <input type="checkbox" name="is_header" value="1" {{ old('is_header', $account->is_header) ? 'checked' : '' }} class="rounded border-slate-300">
                    <span class="ml-2 text-sm text-slate-700">Header Account</span>
                </label>
                <label class="inline-flex items-center">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $account->is_active) ? 'checked' : '' }} class="rounded border-slate-300">
                    <span class="ml-2 text-sm text-slate-700">Aktif</span>
                </label>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="rounded-md bg-slate-800 px-4 py-2 text-sm text-white hover:bg-slate-700">Simpan</button>
                <a href="{{ route('mdm.coa.index') }}" class="rounded-md border border-slate-300 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Batal</a>
            </div>
        </form>
    </div>
@endsection
