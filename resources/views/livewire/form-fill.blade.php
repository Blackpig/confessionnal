<div class="confessionnal-container @if($preview) has-preview-banner @endif"
     x-data="{
        handleKey(e) {
            if (e.target.tagName === 'TEXTAREA') return;
            if (e.target.tagName === 'SELECT') return;
            e.preventDefault();
            $wire.next();
        }
     }"
     @if($isConversational)
     x-on:keydown.enter="handleKey($event)"
     @endif
>
    @if($preview)
        <div class="confessionnal-preview-banner">
            Preview mode — submissions will not be recorded
        </div>
    @endif

    @if($completed)
        @if($redirectUrl)
            <div class="confessionnal-card confessionnal-complete confessionnal-fade-in"
                 x-data
                 x-init="window.location.href = '{{ $redirectUrl }}'">
                <p>Redirecting...</p>
            </div>
        @else
            <div class="confessionnal-card confessionnal-complete confessionnal-fade-in">
                <h2>Thank you!</h2>
                <p>Your response has been recorded.</p>
                @if($completionCode)
                    <div class="confessionnal-completion-code">
                        <p>Your completion code:</p>
                        <code>{{ $completionCode }}</code>
                    </div>
                @endif
            </div>
        @endif
    @elseif($step)
        {{-- Progress --}}
        @if($showProgress)
            <div class="confessionnal-progress-wrapper">
                @if($sectionProgress)
                    <div class="confessionnal-section-indicator">
                        Section {{ $sectionProgress['current'] }} of {{ $sectionProgress['total'] }}
                    </div>
                @endif
                <div class="confessionnal-progress">
                    <div class="confessionnal-progress-bar" style="width: {{ $progress }}%"></div>
                </div>
            </div>
        @endif

        <div class="confessionnal-card confessionnal-step-transition" wire:key="step-{{ $currentStep }}">
            @if($step['type'] === 'intro')
                {{-- Section interstitial --}}
                <div class="confessionnal-intro">
                    @if($step['title'])
                        <h2>{{ $step['title'] }}</h2>
                    @endif

                    @if($step['subtext'])
                        <p>{{ $step['subtext'] }}</p>
                    @endif

                    <div class="confessionnal-nav" style="justify-content: center;">
                        @if($currentStep > 0)
                            <button wire:click="previous" wire:loading.attr="disabled" type="button" class="confessionnal-btn confessionnal-btn-secondary">
                                Back
                            </button>
                        @endif
                        <button wire:click="next" wire:loading.attr="disabled" type="button" class="confessionnal-btn confessionnal-btn-primary">
                            <span wire:loading.remove wire:target="next">Continue</span>
                            <span wire:loading wire:target="next">...</span>
                        </button>
                    </div>
                </div>
            @else
                {{-- Question step --}}
                <form wire:submit="next"
                      x-data
                      x-init="$nextTick(() => {
                          const el = $el.querySelector('input:not([type=hidden]):not([type=radio]):not([type=checkbox]):not([type=file]), textarea, select');
                          if (el) el.focus();
                      })">
                    @if($step['context_image'] ?? null)
                        <div class="confessionnal-context-image">
                            <img src="{{ $step['context_image'] }}" alt="">
                        </div>
                    @endif

                    @foreach($step['fields'] as $field)
                        @include('confessionnal::partials.field', ['field' => $field])
                    @endforeach

                    <div class="confessionnal-nav">
                        <div>
                            @if($currentStep > 0)
                                <button wire:click="previous" wire:loading.attr="disabled" type="button" class="confessionnal-btn confessionnal-btn-secondary">
                                    Back
                                </button>
                            @endif
                        </div>

                        <div style="display: flex; align-items: center; gap: 1rem;">
                            @if($isConversational)
                                <span class="confessionnal-enter-hint">press Enter &crarr;</span>
                            @endif

                            <button type="submit" wire:loading.attr="disabled" class="confessionnal-btn confessionnal-btn-primary">
                                @if($currentStep === $totalSteps - 1)
                                    <span wire:loading.remove wire:target="next">Submit</span>
                                    <span wire:loading wire:target="next">...</span>
                                @else
                                    <span wire:loading.remove wire:target="next">Next</span>
                                    <span wire:loading wire:target="next">...</span>
                                @endif
                            </button>
                        </div>
                    </div>
                </form>
            @endif
        </div>
    @endif
</div>
