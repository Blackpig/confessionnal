@include('confessionnal::partials.styles')

@php
    $blockIdentifier = 'atelier-' . $block::getBlockIdentifier();
    $fragmentId = $block->getFragmentId();
@endphp

<section class="{{ $blockIdentifier }} {{ $block->getWrapperClasses() }}"
         @if($fragmentId) id="{{ $fragmentId }}" @endif
         data-block-type="{{ $block::getBlockIdentifier() }}"
         data-block-id="{{ $block->blockId ?? '' }}">

    <div class="{{ $block->getContainerClasses() }}">
        @if($slug)
            @livewire('confessionnal.form-fill', ['slug' => $slug, 'locale' => app()->getLocale(), 'showProgress' => $showProgress], key('confessionnal-' . $blockId))
        @endif
    </div>

    @if($block->getDividerComponent())
        <x-dynamic-component
            :component="$block->getDividerComponent()"
            :to-background="$block->getDividerToBackground()"
        />
    @endif
</section>
