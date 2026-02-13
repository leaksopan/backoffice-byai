<?php

namespace App\Livewire;

use App\Models\ModuleForm;
use Illuminate\Support\Arr;
use Livewire\Component;

class ModuleFormRenderer extends Component
{
    public ModuleForm $moduleForm;

    public array $schema = [];

    public array $formState = [];

    public int $currentStep = 0;

    public function mount(ModuleForm $moduleForm): void
    {
        $this->moduleForm = $moduleForm;
        $this->schema = $moduleForm->schema_json ?? [];
        $this->initializeState();
    }

    public function render()
    {
        return view('livewire.module-form-renderer');
    }

    public function nextStep(): void
    {
        $stepsCount = count($this->getSteps());
        $this->currentStep = min($this->currentStep + 1, max($stepsCount - 1, 0));
    }

    public function previousStep(): void
    {
        $this->currentStep = max($this->currentStep - 1, 0);
    }

    public function addRepeaterItem(string $name): void
    {
        $schema = $this->getRepeaterSchema($name);
        $item = [];

        foreach ($schema as $field) {
            $item[$field['name']] = $field['default'] ?? null;
        }

        $this->formState[$name][] = $item;
    }

    public function removeRepeaterItem(string $name, int $index): void
    {
        if (! isset($this->formState[$name][$index])) {
            return;
        }

        unset($this->formState[$name][$index]);
        $this->formState[$name] = array_values($this->formState[$name]);
    }

    public function submit(): void
    {
        session()->flash('status', 'Form submitted successfully.');
    }

    public function isFieldVisible(array $field): bool
    {
        if (! isset($field['visibleWhen'])) {
            return true;
        }

        $rule = $field['visibleWhen'];
        $actual = Arr::get($this->formState, $rule['field'] ?? '');
        $expected = $rule['value'] ?? null;
        $operator = $rule['operator'] ?? 'equals';

        return match ($operator) {
            'equals', '==' => $actual == $expected,
            'not_equals', '!=' => $actual != $expected,
            '>' => $actual > $expected,
            '<' => $actual < $expected,
            'contains' => is_array($actual) ? in_array($expected, $actual, true) : str_contains((string) $actual, (string) $expected),
            default => $actual == $expected,
        };
    }

    public function getSteps(): array
    {
        if (($this->schema['type'] ?? null) !== 'wizard') {
            return [];
        }

        return $this->schema['steps'] ?? [];
    }

    private function initializeState(): void
    {
        $this->formState = [];

        foreach ($this->getAllFields() as $field) {
            if (($field['type'] ?? null) === 'repeater') {
                $this->formState[$field['name']] = [];
            } else {
                $this->formState[$field['name']] = $field['default'] ?? null;
            }
        }
    }

    private function getAllFields(): array
    {
        $fields = [];
        $steps = $this->getSteps();

        foreach ($steps as $step) {
            foreach ($step['fields'] ?? [] as $field) {
                $fields[] = $field;
            }
        }

        return $fields;
    }

    private function getRepeaterSchema(string $name): array
    {
        foreach ($this->getAllFields() as $field) {
            if (($field['type'] ?? null) === 'repeater' && ($field['name'] ?? null) === $name) {
                return $field['itemSchema'] ?? [];
            }
        }

        return [];
    }
}
