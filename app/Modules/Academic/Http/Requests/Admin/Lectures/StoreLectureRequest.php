<?php

namespace App\Modules\Academic\Http\Requests\Admin\Lectures;

use App\Shared\Enums\ContentKind;
use App\Shared\Enums\LectureAssetKind;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreLectureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'grade_id' => ['required', 'exists:grades,id'],
            'track_id' => ['nullable', 'exists:tracks,id'],
            'curriculum_section_id' => ['nullable', 'exists:curriculum_sections,id'],
            'lecture_section_id' => ['nullable', 'exists:lecture_sections,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:lectures,slug'],
            'short_description' => ['nullable', 'string', 'max:255'],
            'long_description' => ['nullable', 'string'],
            'thumbnail_url' => ['nullable', 'url'],
            'type' => ['required', new Enum(ContentKind::class)],
            'price_amount' => ['required', 'integer', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'duration_minutes' => ['nullable', 'integer', 'min:1'],
            'published_at' => ['nullable', 'date'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'is_free' => ['nullable', 'boolean'],
            'metadata' => ['nullable', 'array'],
            'completion_threshold_percent' => ['nullable', 'numeric', 'between:1,100'],
            'assets' => ['nullable', 'array'],
            'assets.*.kind' => ['required', Rule::enum(LectureAssetKind::class)],
            'assets.*.title' => ['required', 'string', 'max:255'],
            'assets.*.url' => ['nullable', 'url'],
            'assets.*.body' => ['nullable', 'string'],
            'assets.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'assets.*.is_active' => ['nullable', 'boolean'],
            'assets.*.metadata' => ['nullable', 'array'],
            'checkpoints' => ['nullable', 'array'],
            'checkpoints.*.title' => ['required', 'string', 'max:255'],
            'checkpoints.*.position_seconds' => ['nullable', 'integer', 'min:0'],
            'checkpoints.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'checkpoints.*.is_required' => ['nullable', 'boolean'],
            'checkpoints.*.metadata' => ['nullable', 'array'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $metadata = $this->input('metadata', []);
        $metadata = is_array($metadata) ? $metadata : [];
        $completionThreshold = $this->input('completion_threshold_percent');

        $this->merge([
            'track_id' => $this->filled('track_id') ? $this->integer('track_id') : null,
            'curriculum_section_id' => $this->filled('curriculum_section_id') ? $this->integer('curriculum_section_id') : null,
            'lecture_section_id' => $this->filled('lecture_section_id') ? $this->integer('lecture_section_id') : null,
            'sort_order' => $this->input('sort_order', 0),
            'is_active' => $this->boolean('is_active'),
            'is_featured' => $this->boolean('is_featured'),
            'is_free' => $this->boolean('is_free'),
            'currency' => strtoupper((string) $this->input('currency', 'EGP')),
            'metadata' => $completionThreshold !== null && $completionThreshold !== ''
                ? array_merge($metadata, ['completion_threshold_percent' => (float) $completionThreshold])
                : $metadata,
            'assets' => collect($this->input('assets', []))
                ->filter(fn ($asset): bool => is_array($asset) && filled($asset['title'] ?? null))
                ->map(function (array $asset): array {
                    return [
                        'kind' => $asset['kind'] ?? LectureAssetKind::ExternalVideo->value,
                        'title' => trim((string) ($asset['title'] ?? '')),
                        'url' => filled($asset['url'] ?? null) ? trim((string) $asset['url']) : null,
                        'body' => filled($asset['body'] ?? null) ? trim((string) $asset['body']) : null,
                        'sort_order' => max(0, (int) ($asset['sort_order'] ?? 0)),
                        'is_active' => filter_var($asset['is_active'] ?? false, FILTER_VALIDATE_BOOL),
                        'metadata' => is_array($asset['metadata'] ?? null) ? $asset['metadata'] : null,
                    ];
                })
                ->values()
                ->all(),
            'checkpoints' => collect($this->input('checkpoints', []))
                ->filter(fn ($checkpoint): bool => is_array($checkpoint) && filled($checkpoint['title'] ?? null))
                ->map(function (array $checkpoint): array {
                    return [
                        'title' => trim((string) ($checkpoint['title'] ?? '')),
                        'position_seconds' => filled($checkpoint['position_seconds'] ?? null)
                            ? max(0, (int) $checkpoint['position_seconds'])
                            : null,
                        'sort_order' => max(0, (int) ($checkpoint['sort_order'] ?? 0)),
                        'is_required' => filter_var($checkpoint['is_required'] ?? false, FILTER_VALIDATE_BOOL),
                        'metadata' => is_array($checkpoint['metadata'] ?? null) ? $checkpoint['metadata'] : null,
                    ];
                })
                ->values()
                ->all(),
        ]);
    }
}
