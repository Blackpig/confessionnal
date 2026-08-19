<div class="confessionnal-container"
     @if($isConversational)
     x-data
     x-on:keydown.enter.prevent="$wire.next()"
     @endif
>
    @if($completed)
        <div class="confessionnal-card confessionnal-complete">
            <h2>Thank you!</h2>
            <p>Your response has been recorded.</p>
        </div>
    @elseif($step)
        {{-- Progress bar --}}
        <div class="confessionnal-progress">
            <div class="confessionnal-progress-bar" style="width: {{ $progress }}%"></div>
        </div>

        <div class="confessionnal-card">
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
                            <button wire:click="previous" type="button" class="confessionnal-btn confessionnal-btn-secondary">
                                Back
                            </button>
                        @endif
                        <button wire:click="next" type="button" class="confessionnal-btn confessionnal-btn-primary">
                            Continue
                        </button>
                    </div>
                </div>
            @else
                {{-- Question step --}}
                <form wire:submit="next">
                    @foreach($step['fields'] as $field)
                        @include('confessionnal::partials.field', ['field' => $field])
                    @endforeach

                    <div class="confessionnal-nav">
                        <div>
                            @if($currentStep > 0)
                                <button wire:click="previous" type="button" class="confessionnal-btn confessionnal-btn-secondary">
                                    Back
                                </button>
                            @endif
                        </div>

                        <div style="display: flex; align-items: center; gap: 1rem;">
                            @if($isConversational)
                                <span class="confessionnal-enter-hint">press Enter &crarr;</span>
                            @endif

                            <button type="submit" class="confessionnal-btn confessionnal-btn-primary">
                                @if($currentStep === $totalSteps - 1)
                                    Submit
                                @else
                                    Next
                                @endif
                            </button>
                        </div>
                    </div>
                </form>
            @endif
        </div>
    @endif
</div>
