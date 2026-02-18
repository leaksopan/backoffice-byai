<div class="glass-card p-6">
    <form wire:submit="save">
        {{ $this->form }}

        <div class="flex items-center justify-end gap-3 mt-6 pt-6 border-t border-slate-200 dark:border-slate-700">
            <a href="{{ route('ccm.cost-centers.index') }}" class="btn-ghost">
                Cancel
            </a>
            <button type="submit" class="btn-primary">
                {{ $costCenter ? 'Update' : 'Create' }} Cost Center
            </button>
        </div>
    </form>
</div>
