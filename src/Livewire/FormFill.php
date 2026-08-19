<?php

namespace BlackpigCreatif\Confessionnal\Livewire;

use BlackpigCreatif\Confessionnal\Enums\FieldType;
use BlackpigCreatif\Confessionnal\Enums\FormMode;
use BlackpigCreatif\Confessionnal\Models\Form;
use BlackpigCreatif\Confessionnal\Models\FormField;
use BlackpigCreatif\Confessionnal\Models\Submission;
use BlackpigCreatif\Confessionnal\Support\ConditionEvaluator;
use BlackpigCreatif\Confessionnal\Support\TargetModelMapper;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('confessionnal::layouts.fill')]
class FormFill extends Component
{
    use WithFileUploads;

    public Form $form;

    public string $locale;

    /** Flattened navigation steps: section intros + pages */
    public array $steps = [];

    /** Current step index */
    public int $currentStep = 0;

    /** All answers keyed by field key */
    public array $answers = [];

    /** Section order (for randomised groups) */
    public array $sectionOrder = [];

    /** Validation errors for the current step */
    public array $stepErrors = [];

    /** Whether the form has been submitted */
    public bool $completed = false;

    /** Captured query params */
    public array $capturedParams = [];

    /** Redirect URL after submission (if set) */
    public ?string $redirectUrl = null;

    public function mount(string $slug, ?string $locale = null): void
    {
        $this->locale = $locale ?? app()->getLocale();
        app()->setLocale($this->locale);

        $this->form = Form::where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        $this->captureQueryParams();
        $this->buildSteps();
    }

    public function render(): View
    {
        $step = $this->steps[$this->currentStep] ?? null;

        // Filter fields by conditional logic
        if ($step && $step['type'] === 'question') {
            $step['fields'] = array_values(array_filter(
                $step['fields'],
                fn (array $field) => ConditionEvaluator::isMet($field['conditional_logic'] ?? null, $this->answers),
            ));
        }

        return view('confessionnal::livewire.form-fill', [
            'step' => $step,
            'totalSteps' => count($this->steps),
            'progress' => $this->calculateProgress(),
            'sectionProgress' => $this->calculateSectionProgress(),
            'isConversational' => $this->form->mode === FormMode::CONVERSATIONAL,
        ]);
    }

    public function next(): void
    {
        $step = $this->steps[$this->currentStep] ?? null;

        if (! $step) {
            return;
        }

        // Section intros have no fields to validate
        if ($step['type'] !== 'intro' && ! $this->validateCurrentStep()) {
            return;
        }

        if ($this->currentStep < count($this->steps) - 1) {
            $this->currentStep++;
            $this->stepErrors = [];
            $this->skipHiddenSteps(direction: 'forward');
        } else {
            $this->submit();
        }
    }

    public function previous(): void
    {
        if ($this->currentStep > 0) {
            $this->currentStep--;
            $this->stepErrors = [];
            $this->skipHiddenSteps(direction: 'backward');
        }
    }

    protected function skipHiddenSteps(string $direction): void
    {
        $delta = $direction === 'forward' ? 1 : -1;

        while (
            $this->currentStep >= 0
            && $this->currentStep < count($this->steps)
            && $this->isStepFullyHidden($this->steps[$this->currentStep])
        ) {
            $this->currentStep += $delta;
        }

        // Clamp to valid range
        $this->currentStep = max(0, min($this->currentStep, count($this->steps) - 1));
    }

    protected function isStepFullyHidden(array $step): bool
    {
        if ($step['type'] === 'intro') {
            return false;
        }

        // A step is fully hidden if ALL its fields have unmet conditions
        foreach ($step['fields'] as $field) {
            if (ConditionEvaluator::isMet($field['conditional_logic'] ?? null, $this->answers)) {
                return false;
            }
        }

        return true;
    }

    public function submit(): void
    {
        // Validate all remaining fields on the current step
        $step = $this->steps[$this->currentStep] ?? null;

        if ($step && $step['type'] !== 'intro' && ! $this->validateCurrentStep()) {
            return;
        }

        $submission = Submission::create([
            'form_id' => $this->form->id,
            'answers' => $this->answers,
            'locale' => $this->locale,
            'section_order' => $this->sectionOrder,
            'meta' => array_filter([
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'referrer' => request()->header('referer'),
                'query_params' => $this->capturedParams ?: null,
            ]),
            'completed_at' => now(),
        ]);

        TargetModelMapper::handle($this->form, $submission);

        $redirectUrl = $this->buildRedirectUrl();

        if ($redirectUrl) {
            $this->redirectUrl = $redirectUrl;
        }

        $this->completed = true;
    }

    protected function validateCurrentStep(): bool
    {
        $step = $this->steps[$this->currentStep] ?? null;

        if (! $step || $step['type'] === 'intro') {
            return true;
        }

        $rules = [];
        $attributes = [];

        foreach ($step['fields'] as $field) {
            // Skip validation for conditionally hidden fields
            if (! ConditionEvaluator::isMet($field['conditional_logic'] ?? null, $this->answers)) {
                continue;
            }

            $fieldRules = $this->buildValidationRules($field);

            if (! empty($fieldRules)) {
                $rules["answers.{$field['key']}"] = $fieldRules;
                $attributes["answers.{$field['key']}"] = $field['label'];
            }
        }

        if (empty($rules)) {
            return true;
        }

        $validator = Validator::make(
            ['answers' => $this->answers],
            $rules,
            [],
            $attributes,
        );

        if ($validator->fails()) {
            $this->stepErrors = $validator->errors()->toArray();

            return false;
        }

        $this->stepErrors = [];

        return true;
    }

    protected function buildValidationRules(array $field): array
    {
        $rules = [];

        if ($field['is_required']) {
            $rules[] = 'required';
        } else {
            $rules[] = 'nullable';
        }

        // Type-specific defaults
        match ($field['type']) {
            FieldType::DATE->value => $rules[] = 'date',
            FieldType::CHECKBOX->value => $rules[] = 'array',
            FieldType::FILE_UPLOAD->value => $rules[] = 'file',
            default => null,
        };

        // Merge custom validation rules from the builder
        if (! empty($field['validation_rules'])) {
            $rules = array_merge($rules, $field['validation_rules']);
        }

        return $rules;
    }

    protected function captureQueryParams(): void
    {
        $settings = $this->form->settings ?? [];
        $mode = $settings['query_capture_mode'] ?? 'none';

        if ($mode === 'none') {
            return;
        }

        $params = request()->query();

        // Remove the route params (slug, locale) from captured params
        unset($params['slug'], $params['locale']);

        if ($mode === 'whitelist') {
            $allowed = $settings['query_capture_whitelist'] ?? [];
            $params = array_intersect_key($params, array_flip($allowed));
        }

        $this->capturedParams = $params;
    }

    protected function buildRedirectUrl(): ?string
    {
        $settings = $this->form->settings ?? [];
        $url = $settings['redirect_url'] ?? null;

        if (! $url) {
            return null;
        }

        if (! empty($settings['passthrough_params']) && ! empty($this->capturedParams)) {
            $separator = str_contains($url, '?') ? '&' : '?';
            $url .= $separator . http_build_query($this->capturedParams);
        }

        return $url;
    }

    protected function buildSteps(): void
    {
        $sections = $this->form->sections()
            ->with(['pages.fields'])
            ->get();

        $sections = $this->applySectionRandomisation($sections);

        $this->sectionOrder = $sections->pluck('id')->toArray();

        $steps = [];

        foreach ($sections as $section) {
            // Section intro step (if section has a title or subtext)
            $title = $section->getTranslation('title', $this->locale);
            $subtext = $section->getTranslation('subtext', $this->locale);

            if ($title || $subtext) {
                $steps[] = [
                    'type' => 'intro',
                    'section_id' => $section->id,
                    'title' => $title,
                    'subtext' => $subtext,
                    'fields' => [],
                ];
            }

            foreach ($section->pages as $page) {
                $fields = $page->fields->map(fn (FormField $field) => [
                    'id' => $field->id,
                    'key' => $field->key,
                    'type' => $field->type->value,
                    'label' => $field->getTranslation('label', $this->locale),
                    'help_text' => $field->getTranslation('help_text', $this->locale),
                    'placeholder' => $field->getTranslation('placeholder', $this->locale),
                    'is_required' => $field->is_required,
                    'options' => $field->options ?? [],
                    'validation_rules' => $field->validation_rules ?? [],
                    'conditional_logic' => $field->conditional_logic,
                ])->toArray();

                $contextImage = $page->getMediaUrl('context_image', 'large');

                if ($this->form->mode === FormMode::CONVERSATIONAL) {
                    // One field per step in conversational mode
                    foreach ($fields as $field) {
                        $steps[] = [
                            'type' => 'question',
                            'section_id' => $section->id,
                            'page_id' => $page->id,
                            'context_image' => $contextImage,
                            'fields' => [$field],
                        ];
                    }
                } else {
                    // All fields on the page in standard mode
                    if (! empty($fields)) {
                        $steps[] = [
                            'type' => 'question',
                            'section_id' => $section->id,
                            'page_id' => $page->id,
                            'context_image' => $contextImage,
                            'fields' => $fields,
                        ];
                    }
                }
            }
        }

        $this->steps = $steps;

        // Initialise answers for all fields
        foreach ($steps as $step) {
            foreach ($step['fields'] as $field) {
                if (! array_key_exists($field['key'], $this->answers)) {
                    $this->answers[$field['key']] = $field['type'] === FieldType::CHECKBOX->value ? [] : null;
                }
            }
        }
    }

    protected function applySectionRandomisation(Collection $sections): Collection
    {
        $groups = $sections->groupBy(fn ($s) => $s->randomisation_group ?? '__ungrouped_' . $s->id);

        $ordered = collect();

        foreach ($groups as $key => $group) {
            if (str_starts_with($key, '__ungrouped_')) {
                $ordered->push(...$group);
            } else {
                $ordered->push(...$group->shuffle());
            }
        }

        return $ordered;
    }

    protected function calculateProgress(): int
    {
        if (count($this->steps) <= 1) {
            return 100;
        }

        return (int) round(($this->currentStep / (count($this->steps) - 1)) * 100);
    }

    protected function calculateSectionProgress(): ?array
    {
        $sectionIds = collect($this->steps)
            ->pluck('section_id')
            ->unique()
            ->values();

        if ($sectionIds->count() <= 1) {
            return null;
        }

        $currentSectionId = $this->steps[$this->currentStep]['section_id'] ?? null;
        $currentIndex = $sectionIds->search($currentSectionId);

        if ($currentIndex === false) {
            return null;
        }

        return [
            'current' => $currentIndex + 1,
            'total' => $sectionIds->count(),
        ];
    }
}
