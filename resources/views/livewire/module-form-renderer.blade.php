<div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    @if (session()->has('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    @php
        $steps = $this->getSteps();
        $totalSteps = count($steps);
    @endphp

    @if ($totalSteps === 0)
        <div class="text-sm text-slate-600">No form schema configured.</div>
    @else
        <div class="flex flex-wrap gap-2">
            @foreach ($steps as $index => $step)
                <div class="{{ $index === $currentStep ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600' }} rounded-full px-3 py-1 text-xs font-semibold">
                    Step {{ $index + 1 }}
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            @foreach ($steps as $index => $step)
                @if ($index === $currentStep)
                    <div class="space-y-4">
                        <div class="text-lg font-semibold text-slate-900">{{ $step['title'] ?? ('Step '.($index + 1)) }}</div>

                        @foreach ($step['fields'] ?? [] as $field)
                            @if (! $this->isFieldVisible($field))
                                @continue
                            @endif

                            @switch($field['type'] ?? 'text')
                                @case('textarea')
                                    <label class="block text-sm font-medium text-slate-700">
                                        {{ $field['label'] ?? $field['name'] }}
                                        <textarea
                                            class="mt-2 w-full rounded-lg border-slate-300 shadow-sm focus:border-slate-500 focus:ring-slate-500"
                                            rows="4"
                                            wire:model="formState.{{ $field['name'] }}"
                                        ></textarea>
                                    </label>
                                    @break
                                @case('select')
                                    <label class="block text-sm font-medium text-slate-700">
                                        {{ $field['label'] ?? $field['name'] }}
                                        <select
                                            class="mt-2 w-full rounded-lg border-slate-300 shadow-sm focus:border-slate-500 focus:ring-slate-500"
                                            wire:model="formState.{{ $field['name'] }}"
                                        >
                                            <option value="">Select...</option>
                                            @foreach ($field['options'] ?? [] as $optionValue => $optionLabel)
                                                <option value="{{ $optionValue }}">{{ $optionLabel }}</option>
                                            @endforeach
                                        </select>
                                    </label>
                                    @break
                                @case('checkbox')
                                    <label class="flex items-center gap-2 text-sm font-medium text-slate-700">
                                        <input
                                            type="checkbox"
                                            class="rounded border-slate-300 text-slate-900 focus:ring-slate-500"
                                            wire:model="formState.{{ $field['name'] }}"
                                        >
                                        {{ $field['label'] ?? $field['name'] }}
                                    </label>
                                    @break
                                @case('repeater')
                                    <div class="space-y-3">
                                        <div class="flex items-center justify-between">
                                            <div class="text-sm font-medium text-slate-700">{{ $field['label'] ?? $field['name'] }}</div>
                                            <button
                                                class="rounded-lg border border-slate-200 px-3 py-1 text-xs font-semibold text-slate-600 hover:bg-slate-100"
                                                type="button"
                                                wire:click="addRepeaterItem('{{ $field['name'] }}')"
                                            >
                                                Add item
                                            </button>
                                        </div>
                                        @forelse ($formState[$field['name']] ?? [] as $itemIndex => $item)
                                            <div class="rounded-lg border border-slate-200 p-4">
                                                <div class="space-y-3">
                                                    @foreach ($field['itemSchema'] ?? [] as $itemField)
                                                        @switch($itemField['type'] ?? 'text')
                                                            @case('textarea')
                                                                <label class="block text-sm font-medium text-slate-700">
                                                                    {{ $itemField['label'] ?? $itemField['name'] }}
                                                                    <textarea
                                                                        class="mt-2 w-full rounded-lg border-slate-300 shadow-sm focus:border-slate-500 focus:ring-slate-500"
                                                                        rows="3"
                                                                        wire:model="formState.{{ $field['name'] }}.{{ $itemIndex }}.{{ $itemField['name'] }}"
                                                                    ></textarea>
                                                                </label>
                                                                @break
                                                            @case('select')
                                                                <label class="block text-sm font-medium text-slate-700">
                                                                    {{ $itemField['label'] ?? $itemField['name'] }}
                                                                    <select
                                                                        class="mt-2 w-full rounded-lg border-slate-300 shadow-sm focus:border-slate-500 focus:ring-slate-500"
                                                                        wire:model="formState.{{ $field['name'] }}.{{ $itemIndex }}.{{ $itemField['name'] }}"
                                                                    >
                                                                        <option value="">Select...</option>
                                                                        @foreach ($itemField['options'] ?? [] as $optionValue => $optionLabel)
                                                                            <option value="{{ $optionValue }}">{{ $optionLabel }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </label>
                                                                @break
                                                            @default
                                                                <label class="block text-sm font-medium text-slate-700">
                                                                    {{ $itemField['label'] ?? $itemField['name'] }}
                                                                    <input
                                                                        class="mt-2 w-full rounded-lg border-slate-300 shadow-sm focus:border-slate-500 focus:ring-slate-500"
                                                                        type="{{ $itemField['type'] ?? 'text' }}"
                                                                        wire:model="formState.{{ $field['name'] }}.{{ $itemIndex }}.{{ $itemField['name'] }}"
                                                                    >
                                                                </label>
                                                        @endswitch
                                                    @endforeach
                                                </div>
                                                <button
                                                    class="mt-3 text-xs font-semibold text-rose-600 hover:text-rose-700"
                                                    type="button"
                                                    wire:click="removeRepeaterItem('{{ $field['name'] }}', {{ $itemIndex }})"
                                                >
                                                    Remove item
                                                </button>
                                            </div>
                                        @empty
                                            <div class="rounded-lg border border-dashed border-slate-200 p-4 text-sm text-slate-500">
                                                No items yet. Add one to continue.
                                            </div>
                                        @endforelse
                                    </div>
                                    @break
                                @default
                                    <label class="block text-sm font-medium text-slate-700">
                                        {{ $field['label'] ?? $field['name'] }}
                                        <input
                                            class="mt-2 w-full rounded-lg border-slate-300 shadow-sm focus:border-slate-500 focus:ring-slate-500"
                                            type="{{ $field['type'] ?? 'text' }}"
                                            wire:model="formState.{{ $field['name'] }}"
                                        >
                                    </label>
                            @endswitch
                        @endforeach
                    </div>
                @endif
            @endforeach
        </div>

        <div class="mt-6 flex items-center justify-between">
            <button
                class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100"
                type="button"
                wire:click="previousStep"
                @if ($currentStep === 0) disabled @endif
            >
                Back
            </button>
            @if ($currentStep < ($totalSteps - 1))
                <button
                    class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800"
                    type="button"
                    wire:click="nextStep"
                >
                    Next
                </button>
            @else
                <button
                    class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-500"
                    type="button"
                    wire:click="submit"
                >
                    Submit
                </button>
            @endif
        </div>
    @endif
</div>
