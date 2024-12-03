<!-- Broken Links Information Modal -->
@modal(
  [
    'closeButtonText' => 'Close',
    'heading' => $title,
    'id' => 'modal-broken-link',
    'size' => 'sm',
    'padding' => 3,
    'borderRadius' => 'lg',
    'attributeList' => [
      'style' => 'max-width: calc(var(--base, 8px) * 75);'
    ]
  ]
)
  {!!$content!!}

  @slot('bottom')
    @button([
        'id' => 'modal-broken-link-button',
        'href' => $ctaLink,
        'type' => 'filled',
        'text' => $ctaLabel,
        'icon' => 'open_in_new',
        'size' => 'md',
        'color' => 'primary',
        'classList' => ['u-width--100']
    ])
    @endbutton
  @endslot
@endmodal